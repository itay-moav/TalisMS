<?php namespace Talis\Data\Filter;
/**
 * Formats m/d/y H:i or m/d/Y H:i to MYSQL date time format y-m-d H:i:s
 * @author holly
 */
class DatetimeYMDHIS implements i {
    /**
     * (non-PHPdoc)
     * @see Form_Filter_i::filter()
     */
    public function filter($data) {
        return date('Y-m-d H:i:s', strtotime($data));
    }
}