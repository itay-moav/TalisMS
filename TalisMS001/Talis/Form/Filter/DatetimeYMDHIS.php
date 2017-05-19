<?php
/**
 * Formats m/d/y H:i or m/d/Y H:i to MYSQL date time format y-m-d H:i:s
 * @author holly
 */
class Form_Filter_DatetimeYMDHIS implements Form_Filter_i {
    /**
     * (non-PHPdoc)
     * @see Form_Filter_i::filter()
     */
    public function filter($data) {
        return date('Y-m-d H:i:s', strtotime($data));
    }
}