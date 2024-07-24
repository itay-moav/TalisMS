<?php namespace Talis\commons;
/**
 * 
 * @author itay
 * @date 2024-07-24
 * 
 * The library files has various log calles to emit debug/info/warning/error/fatal messages.
 * You can send those log messages anywere you want and use which ever logger mechanizem you want.
 * All you need to do is write a thin wrapper around the logger you will use which implements 
 * the following interface.
 * 
 * Below the interface I provide a default dev/null logger which u can use if u prefer not to have a logger
 * IT IS NOT set as the default, YOU must do it in the bootstrap file (see example in /TalisMS/config/
 *
 */
interface iLogger{
   
    /**
     *
     * @param mixed $inp : The piece you want to send the log. Make sure it isa datatype your logger can handle
     */
    public function debug(mixed $inp):void;
    
    /**
     *
     * @param mixed $inp : The piece you want to send the log. Make sure it isa datatype your logger can handle
     * @param bool $full_stack : A boolean flag to tell the logger (if it has this capability, otherwise just send false) to add
     *                           some default stuff to the log entry (can be trace, some stats, SESSION etc)
     */
    public function info(mixed $inp,bool $full_stack):void;
    
    /**
     *
     * @param mixed $inp : The piece you want to send the log. Make sure it isa datatype your logger can handle
     * @param bool $full_stack : A boolean flag to tell the logger (if it has this capability, otherwise just send false) to add
     *                           some default stuff to the log entry (can be trace, some stats, SESSION etc)
     */
    public function warning(mixed $inp,bool $full_stack):void;
    
    /**
     *
     * @param mixed $inp : The piece you want to send the log. Make sure it isa datatype your logger can handle
     * @param bool $full_stack : A boolean flag to tell the logger (if it has this capability, otherwise just send false) to add
     *                           some default stuff to the log entry (can be trace, some stats, SESSION etc)
     */
    public function error(mixed $inp,bool $full_stack):void;
    
    /**
     *
     * @param mixed $inp : The piece you want to send the log. Make sure it isa datatype your logger can handle
     * @param bool $full_stack : A boolean flag to tell the logger (if it has this capability, otherwise just send false) to add
     *                           some default stuff to the log entry (can be trace, some stats, SESSION etc)
     */
    public function fatal(mixed $inp,bool $full_stack):void;   
}



/**
 * A bullshit logger you can use if u do not want a logger.
 * Add it in the bootstrap (see the example in the config/bootsrap)
 */
class NullLogger implements iLogger{
    /**
     *
     * @param mixed $inp : The piece you want to send the log. Make sure it isa datatype your logger can handle
     */
    public function debug(mixed $inp):void{
        //boo
    }
    
    /**
     *
     * @param mixed $inp : The piece you want to send the log. Make sure it isa datatype your logger can handle
     * @param bool $full_stack : A boolean flag to tell the logger (if it has this capability, otherwise just send false) to add
     *                           some default stuff to the log entry (can be trace, some stats, SESSION etc)
     */
    public function info(mixed $inp,bool $full_stack):void{
        //boo
    }
    
    /**
     *
     * @param mixed $inp : The piece you want to send the log. Make sure it isa datatype your logger can handle
     * @param bool $full_stack : A boolean flag to tell the logger (if it has this capability, otherwise just send false) to add
     *                           some default stuff to the log entry (can be trace, some stats, SESSION etc)
     */
    public function warning(mixed $inp,bool $full_stack):void{
        //boo
    }
    
    /**
     *
     * @param mixed $inp : The piece you want to send the log. Make sure it isa datatype your logger can handle
     * @param bool $full_stack : A boolean flag to tell the logger (if it has this capability, otherwise just send false) to add
     *                           some default stuff to the log entry (can be trace, some stats, SESSION etc)
     */
    public function error(mixed $inp,bool $full_stack):void{
        //boo
    }
    
    /**
     *
     * @param mixed $inp : The piece you want to send the log. Make sure it isa datatype your logger can handle
     * @param bool $full_stack : A boolean flag to tell the logger (if it has this capability, otherwise just send false) to add
     *                           some default stuff to the log entry (can be trace, some stats, SESSION etc)
     */
    public function fatal(mixed $inp,bool $full_stack):void{
        //boo
    }   
}
