<?php namespace Talis\Data\Filter;
/**
 * Filters date
 * Takes Y-m-d and formats it to m/d/Y 
 * @author holly
 */
class DateMDY implements i {

	/**
	 * Either formats the date or returns empty string on a bad date value.
	 * (Really should come after a validator if it is important)
	 */
	public function filter($data) {
        $data = str_replace('-', '/', $data);
        if (!empty($data)){
            return date('m/d/Y', strtotime($data));
        }
        return '';
    }
}