<?php
/**
 * Filters date
 * Takes m/d/Y or m/d/y and formats it to Y-m-d
 * @author holly
 */
class Form_Filter_DateYMD implements Form_Filter_i {
    /**
     * (non-PHPdoc)
     * @see Form_Filter_i::filter()
     */
    public function filter($data) {
        $data = str_replace('-', '/', $data);
        if (!empty($data)){
            return date('Y-m-d', strtotime($data));
        }
        $data = null;
        return $data;
    }
}