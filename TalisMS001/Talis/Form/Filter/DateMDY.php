<?php
/**
 * Filters date
 * Takes Y-m-d and formats it to m/d/Y 
 * @author holly
 */
class Form_Filter_DateMDY implements Form_Filter_i {
    /**
     * (non-PHPdoc)
     * @see Form_Filter_i::filter()
     */
    public function filter($data) {
        $data = str_replace('-', '/', $data);
        if (!empty($data)){
            return date('m/d/Y', strtotime($data));
        }
        $data = null;
        return $data;
    }
}