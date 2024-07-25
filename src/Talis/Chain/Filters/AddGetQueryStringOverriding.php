<?php namespace Talis\Chain\Filters;
/**
 * Adds the $_GET into the get params. Some other partiers use this
 * This will override existing values.
 *
 */
class AddGetQueryStringOverriding extends aFilter{
    public function filter(\Talis\Message\Request $Request):void{
	    $all_get_params = $this->Request->getAllGetParams();
        $new_all_get_params = array_merge($all_get_params,$_GET);
        $this->Request = new \Talis\Message\Request($Request->getUri(), $new_all_get_params, $Request->getBody());
    
	    \Talis\TalisMain::logger()->debug('FILTERED REQUEST');
	    \Talis\TalisMain::logger()->debug($this->Request);
	}
}
