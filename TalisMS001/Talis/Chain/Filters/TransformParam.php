<?php namespace Talis\Chain\Filters;
use function \Talis\Logger\dbgr;
/**
 * Looks for a field in the params $params[0], if exists, it changes it from $params[1] to $params[2]
 * @author admin
 *
 */
class TransformParam extends aFilter{
	public function filter(\Talis\Message\aMessage $message):void{
		$params = $message->getBody()->params;
		dbgr('input params for filter TransformParam',$params);
		if(isset($params->{$this->params[0]}) && $params->{$this->params[0]} == $this->params[1]){
			$params->{$this->params[0]} = $this->params[2];
		}
	}
}
