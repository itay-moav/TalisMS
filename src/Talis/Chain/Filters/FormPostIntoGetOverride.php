<?php namespace Talis\Chain\Filters;
/**
 * Adds the $_POST into the get params. Some other partiers use this
 * This will override existing values.
 */
class FormPostIntoGetOverride extends aFilter{
    /**
     * @param \Talis\Message\Request $Request
     * 
     * {@inheritDoc}
     * @see \Talis\Chain\Filters\aFilter::filter()
     */
    public function filter(\Talis\Message\Request $Request):void{
        \Talis\TalisMain::logger()->debug('POST');
        \Talis\TalisMain::logger()->debug($_POST);
        
        $all_get_params = $this->Request->getAllGetParams();
        $new_all_get_params = array_merge($all_get_params,$_POST);
        $this->Request = new \Talis\Message\Request($Request->getUri(), $new_all_get_params, $Request->getBody());
    }
}
