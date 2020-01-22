<?php namespace Talis\Html\Decorator;
/**
 * This interface means this object is decorable.
 * Object has to atleast provide a bare minimum of html code
 * to work.
 * 
 * @author itay
 *
 */
interface iDecorable{
    public function get_decorator():iDecorator;
    public function set_decorator(iDecorator $decorator);
}

