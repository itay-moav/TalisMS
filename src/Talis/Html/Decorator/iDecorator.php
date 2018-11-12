<?php namespace Talis\Html\Decorator;
/**
 * Decorates a piece of string and encapsulates specific logic
 * for that encapsulation
 * 
 * @author itay
 *
 */
interface iDecorator{
    public function decorate(\Talis\Html\iHtmlElement $Element):string;
}

