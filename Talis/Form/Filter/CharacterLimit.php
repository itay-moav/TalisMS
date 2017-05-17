<?php
/**
 * Filter the input into a table by limiting the character input
 * @author Itay
 */
class Form_Filter_CharacterLimit implements Form_Filter_i{
	
	private $character_limit;
	
	/**
	 *  Filter parameters set
	 *  @param unknown $param
	 */
	public function __construct($character_limit){
		$this->character_limit	= $character_limit;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Form_Filter_i::filter()
	 */
    public function filter($data){
        return substr($data,0,$this->character_limit);
    }
}