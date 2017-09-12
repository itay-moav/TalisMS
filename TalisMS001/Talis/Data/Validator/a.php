<?php namespace Talis\Data\Validator;
/**
 * I could not find an easy way to separate view (language) from server functionality = Sorry
 *
 * @author itaymoav
 */
abstract class a{
	protected	$message = '',
				$params	 = []
	;
	
	/**
	 * Over write the class  defined message
	 *
	 * @param string $overwrite_message
	 */
	public function __construct(string $overwrite_message='',array $elm_specific_params = []){
		$this->message = $overwrite_message?:$this->message;
		$this->params  = $elm_specific_params;
	}
	
	/**
	 * @return string
	 */
	public function message():string{
		return $this->message;
	}
	
	/**
	 * @return array params
	 */
	public function params():array{
		return $this->params;
	}
	
	/**
	 * @param mixed $value
	 */
	abstract public function validate($value);
}
