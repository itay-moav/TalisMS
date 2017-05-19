<?php
/*
 * Date functions
 */
abstract class Dates_Commons{
	
	const	SUNDAY		= 0,
			MONDAY		= 1,
			TUESDAY		= 2,
			WEDNESDAY	= 3,
			THURSDAY	= 4,
			FRIDAY		= 5,
			SATURDAY	= 6
	;

    /**
     * Converts SQL dates into visible date format
     *
     * @param string $sql
     * @return string
     */
    static public function date_from_sql($sql) {
        $sq = explode(' ', $sql);
        $date = explode('-', $sq[0]);
        if (count($date) == 3) {
            return "$date[1]/$date[2]/$date[0]";
        } else {
            throw new InvalidArgumentException('Date must be in SQL format: '.$sql);
        }
    }
    
    /**
     * 
     * @param unknown $sql
     * @throws InvalidArgumentException
     * @return string
     */
    static public function date_time_from_sql($sql) {
        $sq = explode(' ', $sql);
        $date = explode('-', $sq[0]);
        if (count($date) == 3) {
            return "$date[1]/$date[2]/$date[0] {$sq[1]}";
        } else {
            throw new InvalidArgumentException('Date must be in SQL format: '.$sql);
        }
    }

    /**
     * 
     * @param unknown $solr_date
     * @return string
     */
    static public function solr_date_to_normal($solr_date){
        $solr_date = substr($solr_date,0,10);
        return self::dateFromSQL($solr_date);
    }
    
    /**
     * NOW
     * @return (new DateTime())->format('Y-m-d H:m:s');
     */
    static public function mysql_now(){
        return (new DateTime())->format('Y-m-d H:m:s');
    }
    
    /**
     * NOW
     * @return (new DateTime())->format('Y-m-d');
     */
    static public function mysql_now_date(){
        return (new DateTime())->format('Y-m-d');
    }
    /**
     *  Determine if the date is a Saturday
     *  @param	string date 'Y-m-d' Format
     *  @return boolean
     */
    static public function isDateSaturday($date = null){
    	$timestamp	= isset($date)?strtotime($date):time();
    	
    	$date_of_the_week	= date("w", $timestamp);
    	return ($date_of_the_week == Dates_Commons::SATURDAY);
    }
    
    /**
     *  Determine if the date is a Tuesday
     *  @param	string date 'Y-m-d' Format
     *  @return boolean
     */
    static public function isDateTuesday($date = null){
        $timestamp	= isset($date)?strtotime($date):time();
         
        $date_of_the_week	= date("w", $timestamp);
        return ($date_of_the_week == Dates_Commons::TUESDAY);
    }
    
    /**
     *  Determine if the date is the first day of the month
     *  @param	string date	'Y-m-d' Format
     *  @return	boolean
     */
    static public function isFirstDayOfTheMonth($date = null){
    	$timestamp	= isset($date)?strtotime($date):time();
    	
    	$day_of_month	= date("j", $timestamp);
    	return	($day_of_month == 1);
    }
    
    /**
     *  Determine if the date is not a Saturday
     *  @param	string	date 'Y-m-d' Format
     *  @return	boolean
     */
    static public function isDateNotSaturday($date = null){
    	$timestamp	= isset($date)?strtotime($date):time();
    	
    	$date_of_the_week	= date("w", $timestamp);
    	return ($date_of_the_week != Dates_Commons::SATURDAY);
    }
    
}