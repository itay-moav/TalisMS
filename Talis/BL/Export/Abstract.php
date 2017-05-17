<?php
/**
 * Class for exporting files
 * @author holly
 */
abstract class BL_Export_Abstract {
    const 
            FILE_TYPE__EXCEL = 'excel';
    
    /**
     * Factory
     * @return BL_Export_Abstract
     */
    static public function factory(Iterator $Iterator, $file_type = self::FILE_TYPE__EXCEL, $file_id, $user_id){
        return ($file_type == self::FILE_TYPE__EXCEL) ? new BL_Export_Excel($Iterator, $file_id, $user_id) : null;
    }
    
    protected	
                $fileExtension      = '',
                $fileID             = 0,
                $userID             = 0,
                $currentIterator    = null,
                $fileHandle         = null,
                $downloadFilename   = '',
                $row                = null
    ;
    
    /**
     * Constructor
     * @param Iterator $Iterator
     * @param int $file_id
     * @param int $user_id
     */
    protected function __construct(Iterator $Iterator, int $file_id, int $user_id){
        $this->fileID           = $file_id*1;
        $this->currentIterator  = $Iterator;
        $this->userID           = $user_id*1;
        $this->open();
        // error handling
    }
    
    
    /**
     * Close file
     */
    public function __destruct() {
        // close
        $this->close();
    }
    
    /**
     * Open file
     * @return BL_Export_Abstract
     */
    protected function open(){
        // error handling
        // do not fopen if file already open
        $this->downloadFilename =  IDUHub_Lms2prod_User_Files::USER_FILE_PREFIX . $this->fileID . '.' . $this->fileExtension;
        dbgr('I am about to open ',$this->downloadFilename);
        $this->fileHandle = fopen($this->downloadFilename, 'w'); 
        return $this;
    }
    
    /**
     * 
     * @return Reports_Export_File_CreateAbstract
     */
    public function updateDataset(Iterator $Iterator){
        $this->currentIterator = $Iterator;
        return $this;
    }
    
    /**
     * Write header for export file
     * @return BL_Export_Abstract
     */
    public function writeHeader(){
        //$this->open();
        $this->currentIterator->rewind();
        $this->row = $this->currentIterator->current();
        
        $this->write($this->innerWriteHeader());
        $this->currentIterator->next();
        
        return $this;
    }
    
    /**
     * Write data for export file
     * @return BL_Export_Abstract
     */
    public function writeData(){
        while($this->currentIterator->valid()) {
            $this->row = $this->currentIterator->current();
            $this->write($this->innerWriteData());
            $this->currentIterator->next();
        }
        return $this;
    }
    
    /**
     * Write footer to export file
     * @return BL_Export_Abstract
     */
    public function writeFooter(){
        $this->write($this->innerWriteFooter());
        return $this;
    }
    

    /**
     * Process for writing header to export file
     */
    protected function innerWriteHeader() { 
        return '';
    }
    
    /**
     * Process for writing data to export file
     */
    protected function innerWriteData() { 
        return '';
    }
    
    /**
     * Process for writing footer to file
     */
    protected function innerWriteFooter() { 
        return '';
    }
    
    /**
     * Write to file
     * @param string $string
     * @return BL_Export_Abstract
     */
    protected function write($string){
        fwrite($this->fileHandle, $string);
        return $this;
    }
    
    /**
     * Close writing to the download file
     * @return BL_Export_Abstract
     */
    protected function close(){
        fclose($this->fileHandle);
        return $this;
    }
    
    /**
     * @return string
     */
    public function getDownloadFilePath() {
        return $this->downloadFilename;
    }
}