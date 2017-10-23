<?php namespace Talis\Services\CSV;
/**
 * Iterates and reads a CSV file
 * @author itay modified by holly
 */
class FileReader implements Iterator {
	use \Talis\Data\tIterator;

    protected   $csv_file_name          = '',
                $csv_handle             = null,
                $delimiter              = ','
    ;
    
    /**
     * @param string $csv_file_name
     * @param integer $education_group_id
     */
    public function __construct($csv_file_name,$delimiter=','){
        $this->delimiter      = $delimiter;
        $this->csv_file_name  = $csv_file_name;
        if(($this->csv_handle = @fopen($this->csv_file_name, "r")) === FALSE){
            throw new \Talis\Exception\FileNotFound($this->csv_file_name);
        }
    }
    
    /**
     * Close the file
     */
    public function __destruct(){
        if($this->csv_handle) fclose($this->csv_handle);
    }

    /**
     * Close the file, so next iteration will 
     * reopen it and start from beginning.
     * 
     * @see Data_CSV_Reader::rewind()
     */
    public function rewind()
    {
        rewind($this->csv_handle);
        $this->next();
        $this->row_index = 0;
    }
    
    /**
     * (non-PHPdoc)
     * @see Data_CSV_Reader::next()
     */
    public function next()
    {
        $this->row_index++;
        $this->row = fgetcsv($this->csv_handle,0,$this->delimiter);
        if($this->row){
            $this->row = $this->trimData($this->row);
        }
        return $this->row;
    }
    
    /**
     * Utility service to clean fields of trailing stuff
     * 
     * @param array $data
     * @return string
     */
    protected function trimData(array $data){
        foreach($data as $k => $cell){
            $data[$k] = trim($cell);
        }
        return $data;
    }
}

