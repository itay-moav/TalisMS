<?php namespace Talis\Html\Decorator;

class None implements iDecorator
{

    /**
     * convert object to string
     * 
     * {@inheritDoc}
     * @see \Talis\Html\Decorator\iDecorator::decorate()
     */
    public function decorate(\Talis\Html\iHtmlElement $Element):string{
        return $Element->html();
    }
}

