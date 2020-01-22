<?php namespace Talis\Html\Decorator;
/**
 * This interface means this object is decorable.
 * Object has to atleast provide a bare minimum of html code
 * to work.
 * 
 * @author itay
 *
 */
interface iFormElementDecorable{
    public function get_decorator():iFormElementDecorator;
    public function set_decorator(iFormElementDecorator $decorator);
}

