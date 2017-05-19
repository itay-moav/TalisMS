<?php
/**
 * Iterates and reads a Google Sheet
 * 
 * Usage:
 * Input Google Sheet name and worksheet name. Google Sheet must be shared with the client email, found in environment.
 * Google Sheet MUST have a header, but the header won't be included in the retrieved records. 
 * 
 * @author holly
 */
abstract class Data_GoogleSheet_Reader implements Iterator{
    use BL_tIteratorHelper;
    
    protected
                /**
                 * Records          - all rows retrieved from Google Sheet
                 * @var records
                 */
                $records            = [],
                
                /**
                 * Record headers   - all headers retrieved from Google Sheet
                 * Note: will be all lowercase with white spaces removed
                 * @var record_headers
                 */
                $record_headers     = []
                
    ;
    
    /**
     * Construct: input sheet title and worksheet title
     * Authenticates user and retrieves Google Sheet record
     *  
     * @param Data_GoogleSheet_Client $DB
     */
    public function __construct(Data_GoogleSheet_Client $DB){
        $this->records = $DB->selectAll();
        $this->record_headers = $DB->getRecordHeaders();
    }
    
    /**
     * Go to next row
     * @return array $row
     */
    public function next () {
        if(isset($this->records[$this->row_index])) {
            $this->row = $this->getCleanedRecord();
            $this->row_index ++;
        } else {
            $this->row = null;
        }
        
        return $this->row;
    }
    
   /**
    * Rewinds the file
    * @return array $row
    */
    public function rewind () {
        $this->row_index = 0;
        $this->row = $this->getCleanedRecord();
        return $this->row;
    }
    
    /**
     * Returns current row
     * (non-PHPdoc)
     * @see Iterator::current()
     */
    public function current () {
        return $this->row;
    }
    
    /**
     * Returns header of google sheet
     * @return array
     */
    public function getRecordHeaders(){
        return $this->record_headers;
    }
    
    /**
     * Cleans each entry of the row
     * @param array $row
     * @return array $row
     */
    protected function cleanRecord($row) {
        /*
         * This is an example:
        foreach($row as $header => $value) {
            $row[$header] = trim($value);
        }
        */
        
        return $row;
    }
    
    /**
     * Returns cleaned record at current row index
     * @return array
     */
    protected function getCleanedRecord() {
        return $this->cleanRecord(array_values(($this->records[$this->row_index]->getValues())));
    }
}