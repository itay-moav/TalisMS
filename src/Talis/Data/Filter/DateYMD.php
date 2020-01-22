<?php namespace Talis\Data\Filter;
/**
 * Filters date
 * Takes m/d/Y or m/d/y and formats it to Y-m-d
 * @author holly
 */
class DateYMD implements i {
    /**
     * Formats the date or return empty string, use validator if data is important.
     * 
     * {@inheritDoc}
     * @see \Talis\Data\Filter\i::filter()
     */
    public function filter($data) {
        $data = str_replace('-', '/', $data);
        if (!empty($data)){
            return date('Y-m-d', strtotime($data));
        }
        return '';
    }
}