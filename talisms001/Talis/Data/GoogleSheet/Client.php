<?php
/**
 * All utilities for accessing a Google Sheet
 * 
 * NOTE: In order to access a Google Sheet, you must share that sheet with your Google service account user email.
 *       You must also download a key file onto your system. You can download it from here: https://code.google.com/apis/console under "Credentials"
 *       You can put multiple google service accounts in your env. Just make sure to label them.
 *       You will have to indicate which service account you're using for each instance.
 *       
 * NOTE: When retrieving headers, they will always be lowercase with all whitespaces removed.
 * 
 * 
 * Get instance: 
 *      $DB = Data_GoogleSheet_Client::getInstance($account_name = 'Account indicate in env', $sheet_title = 'Your Sheet Title', $worksheet_title = 'Your worksheet Title');
 * Select all records:
 *      $DB->selectAll();
 * Insert:
 *      Array of one row to insert. No headers required.
 *      $DB->insertRecords($insert_record);
 * Insert batch (recommended for bulk insert): 
 *      You will need to indicate if you just want to append records to the end of the sheet or if you want to start at a specified row.
 *      No headers required.
 *      $DB->insertBatch($insert_entries, $append = true, $row_index = self::START_ROW_INDX_WITH_HEADER);
 * Update:
 *      Update ['rowheader' => 'new value'] where ['rowheader' => 'old value']
 *      $DB->updateRecord($update_record, $where);
 * 
 * @author holly
 */
class Data_GoogleSheet_Client {
    /**
     * Constant for starting row at 2 (where 1 = header row)
     */
    const START_ROW_INDX_WITH_HEADER = 2,
    
    /**
     * Constants for credentials to access spreadsheet
     */
          GOOGLE_CRED_SPREADSHEET_FEEDS      = 'https://spreadsheets.google.com/feeds',
          GOOGLE_CRED_DOC_FEEDS              = 'https://docs.google.com/feeds'
          ;
    
    /**
     * Access token to access Google Sheet
     * @var $accessToken
     */
    private 
            $accessToken        = null,
            
    /**
     * Records retrieved from Google Sheet
     * @var $records 
     */
            $records            = [],
            
    /**
     * Record headers of Google Sheet
     * @var $record_headers 
     */
            $record_headers     = [],
            
    /**
     * Google sheet info
     * @var $sheet_title
     */
            $sheet_title        = '',
            
    /**
     * Google Sheet worksheet title to access
     * Default: Sheet1
     * @var $worksheet_title 
     */
            $worksheet_title    = 'Sheet1',
    
    /**
     * Name on Google service account to access
     * @var $service_account_name 
     */
            $service_account_name = '',
    
    /**
     * Configuration -- either use default environment or override environment
     */
            $config = null;
    
    /**
     * List of special characters to be converted to html num entity
     */
    protected static 
            $spec_chars_num_entity = 
                                        [
                                            '&' => '&#38;',
                                            '>' => '&#62;',
                                            '<' => '&#60;',
                                            '"' => '&#34;'
                                         ];
    
    /**
     * Create instance of class
     * @param string $sheet_title
     * @param string $worksheet_title
     * @return Data_GoogleSheet_Client
     */
    public static function getInstance($account_name, $sheet_title, $worksheet_title = 'Sheet1', $override_config = null) {
        return new self($account_name, $sheet_title, $worksheet_title, $override_config);
    }
    
    /**
     * Construct class
     * You must indicate the sheet title and worksheet, also the google service account you want to use (found in your env)
     * @param string $sheet_title
     * @param string $worksheet_title
     * @param string $account_name
     */
    private function __construct($account_name, $sheet_title, $worksheet_title, $override_config) {
        if (!$account_name) {
            throw new Exception('Please indicate which Google service account to use in your env.');
        } else {
            $this->service_account_name = $account_name;            
        }
        
        $this->sheet_title = $sheet_title;
        $this->worksheet_title = $worksheet_title;
        
        // override default config if needed
        if (!is_null($override_config)) {
            $this->config = $override_config['federated_login']['google_service'][$this->service_account_name];
        } else {
            $this->config = app_env()['federated_login']['google_service'][$this->service_account_name];
        }
        
        $this->authenticateOAuth2Access();
    }
    
    /**
     * Authenticate access to google sheet and sets access token
     */
    private function authenticateOAuth2Access() {
        // Authenticate and get access token
        $config = $this->config;
        $client = new Google_Client();
        $client->setClientId($config['client_id']);
    
        try {
            $creds = new Google_Auth_AssertionCredentials(
                                                            $config['client_email'],
                                                            [self::GOOGLE_CRED_SPREADSHEET_FEEDS, self::GOOGLE_CRED_DOC_FEEDS],
                                                            file_get_contents($config['key_file_path'] . $config['client_id']),
                                                            $config['key_secret']
            );
            $client->setAssertionCredentials($creds);
            $client->getAuth()->refreshTokenWithAssertion();
        } catch (Exception $e) {
            throw new Google_Spreadsheet_UnauthorizedException('Error with  Google Sheet credentials.');
        }
    
        $token = json_decode($client->getAccessToken());
        $this->accessToken = $token->access_token;
    
        // Check that an access token was acquired
        if ($this->accessToken) {
            dbgn('Google Sheet access authenticated.');
        } else {
            throw new Google_Spreadsheet_UnauthorizedException('Denied access token for Google Sheet.');
        }
    }
    
    /**
     * Use access token to open connection
     */
    private function useAccessToken() {
        $serviceRequest = new Google_Spreadsheet_DefaultServiceRequest($this->accessToken);
        Google_Spreadsheet_ServiceRequestFactory::setInstance($serviceRequest);
    }
    
    /**
     * Use access token to access the Google Sheet & return the specified worksheet
     * @return Google_Spreadsheet_Worksheet
     */
    private function getWorksheet() {
        $this->useAccessToken();
        
        // get records
        $spreadsheetService = new Google_Spreadsheet_SpreadsheetService();
        $spreadsheetFeed = $spreadsheetService->getSpreadsheets();
        $spreadsheet = $spreadsheetFeed->getByTitle($this->sheet_title);
        $worksheetFeed = $spreadsheet->getWorksheets();
        dbgr("GOOGLE: retrieving from {$this->sheet_title} in worksheet {$this->worksheet_title}", $worksheetFeed);
        $worksheet = $worksheetFeed->getByTitle($this->worksheet_title);
        
        return $worksheet;
    }
    
    /**
     * Get records as list feed
     * @throws Google_Spreadsheet_Exception
     * @return Google_Spreadsheet_ListFeed object
     */
    private function getListFeed() {
        $worksheet = $this->getWorksheet();
        $listFeed = $worksheet->getListFeed();
        
        // error catching, make sure record are returned
        if (!$listFeed) {
            throw new Google_Spreadsheet_Exception('No records retrieved.');
        }
        
        dbg('GOOGLE: Number records retrieved: ' . count($listFeed->getEntries()));
        return $listFeed;
    }
    
    /**
     * Get records as cell feed
     * @return Google_Spreadsheet_CellFeed
     */
    private function getCellFeed() {
        $worksheet = $this->getWorksheet();
        $cellFeed = $worksheet->getCellFeed();
        return $cellFeed;
    }
    
    /**
     * Set record headers once records have been retrieved
     * (Headers are all lowercase and have no spaces)
     */
    private function setRecordHeaders() {
        $this->record_headers = array_keys($this->records[0]->getValues());
    }
    
    /**
     * Retrieve record headers
     * Note: all headers will be lower case with any whitespace excluded
     * @return array record_headers
     */
    public function getRecordHeaders() {
        return $this->record_headers;
    }
    
    /**
     * Retrieves all records from Google Sheet
     * @return array $records
     */
    public function selectAll() {
        $this->records = $this->getListFeed()->getEntries();
        $this->setRecordHeaders();

        dbgn('GOOGLE: returning records from ' . $this->sheet_title);
        return $this->records;
    }
    
    /**
     * Insert a row into Google Sheet. Can only insert one row.
     * Need array of row to insert. [field1, field2, field3, ...] No headers required.
     * For inserting multiple rows, use insertBatch.
     * 
     * @param array $record_rows
     */
    public function insertRecord($insert_record) {
        $listFeed = $this->getListFeed();
        dbgr('GOOGLE: insert record', $insert_record);
        $listFeed->insert($insert_record);
        dbg('GOOGLE: insert complete');
    }
    
    /**
     * Insert batch of rows -- more efficient than inserting one at a time.
     * Need array of arrays of rows to insert. [[row1], [row2], [row3]]. No headers required.
     * If you want to append rows to end of sheet, set $append = true
     * If not, set $append = false and set $row_index to the row to start inserting at.
     * Default row_index is row 2, which is the row after the header. This will overwrite whatever's already on the sheet.
     *  
     * @param array $insert_entries
     * @param bool $append
     * @param number $row_index
     */
    public function insertBatch($insert_entries, $append = true, $row_index = self::START_ROW_INDX_WITH_HEADER) {
        $cellFeed = $this->getCellFeed();
        $batchRequest = new Google_Spreadsheet_Batch_BatchRequest();
    
        if ($append) {
            $row_index = count($cellFeed->getEntries())/count($insert_entries[0]) + 1;
        }
    
        foreach($insert_entries as $row_entry) {
            $col_index = 1;
            foreach($row_entry as $col_entry) {
                $col_entry = htmlspecialchars($this->filterToNumEntity($col_entry));
                $batchRequest->addEntry($cellFeed->createInsertionCell($row_index, $col_index, $col_entry));
                $col_index++;
            }
            dbgr("GOOGLE: batch insert at row: {$row_index}", $row_entry);
            $row_index++;
        }
    
        $batchResponse = $cellFeed->insertBatch($batchRequest);
    
        if (!$batchResponse || $batchResponse->hasErrors()) {
            warning('GOOGLE: Error inserting records', false);
            throw new Google_Spreadsheet_Exception('Error inserting records');
        } else {
            dbgn('GOOGLE: batch insert complete');
        }
    }
    
    /**
     * Update a record.
     * Update ['rowheader' => 'new value'] where ['rowheader' => 'old value']
     * You can update multiple rows based on the where statement. This process is slow because it checks every row for the where condition.
     * TODO optimize this
     * Reminder: row headers are always lowercase with all whitespaces removed.
     * 
     * @param array $update_records
     * @param array $where
     */
    public function updateRecord($update_record, $where) {
        $listFeed = $this->getListFeed();
        $entries = $listFeed->getEntries();
        
        foreach($entries as $entry) {
            $values = $entry->getValues();
            if($this->checkEntryWhere($where, $values)) {
                foreach($update_record as $update_key => $update_value) {
                    $values[$update_key] = $update_value; 
                    dbg("GOOGLE: update {$values[$update_key]} to {$update_value}");
                }
                $entry->update($values);
                dbgn('GOOGLE: update complete');
            }
        } 
    }
    
    /**
     * Check record if where statement applies.
     * Checks one row at a time, one value at a time.
     * Check the where statemt(s) ['rowheader' => 'value to look for']
     * 
     * @param array $where
     * @param array $entry_values
     * @return boolean
     */
    private function checkEntryWhere($where, $entry_values) {
        foreach($where as $key => $value) {
            if(!($entry_values[$key] == $value)) {
                return false;
            }
        }
        return true;
    }
    
    /**
     * Filter special symbols to numeric html entities
     * @param string $entry
     * @return string $entry
     */
    private function filterToNumEntity($entry) {
        return str_replace(array_keys(self::$spec_chars_num_entity),array_values(self::$spec_chars_num_entity),$entry);
    }
}