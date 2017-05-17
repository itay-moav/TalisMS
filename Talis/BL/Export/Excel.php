<?php
/**
 * Class for exporting files
 * @author holly
 */
class BL_Export_Excel extends BL_Export_Abstract {
    /**
     * File extension
     * @var string
     */
    protected	$fileExtension = 'xls';
    
    /**
     * Process for writing header to export file
     */
    protected function innerWriteHeader() { 
        $th_elements = '<tr>';
        
        foreach ($this->row as $th) {
            $th_elements .= "<td>" . $th . "</td>";
        }
        
        return "<table><thead>{$th_elements}</tr></thead><tbody>";
    }
    
    /**
     * Process for writing data to export file
     */
    protected function innerWriteData() {
        $td_elements = '<tr>';
        
        foreach($this->row as $td) {
            $td_elements .= "<td>{$td}</td>";
        }
        
        return "{$td_elements}</tr>";
    }
    
    /**
     * Process for writing footer to file
     */
    protected function innerWriteFooter() { 
        return '</tbody></table>';
    }
}
