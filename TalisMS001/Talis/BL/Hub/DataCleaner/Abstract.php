<?php
/**
 * Class used to clean specific fields in hub just before update/insert
 * The fields have to be the DB fields
 * 
 * @author Matt
 */
abstract class BL_Hub_DataCleaner_Abstract {
	abstract function filter($data);
}