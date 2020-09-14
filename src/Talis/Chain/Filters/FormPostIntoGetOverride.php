<?php namespace Talis\Chain\Filters;
/**
 * Adds the $_POST into the get params. Some other partiers use this
 * This will override existing values.
 */
class FormPostIntoGetOverride extends aFilter{
    public function filter(\Talis\Message\Request $Request):void{
        $all_get_params = $this->Request->getAllGetParams();
        if(isset($_POST) && is_array($_POST) && $_POST){
            dbgr('POST',$_POST);
            $new_all_get_params = array_merge($all_get_params,$_POST);
            $this->Request = new \Talis\Message\Request($Request->getUri(), $new_all_get_params, $Request->getBody());
        }
    }
}
