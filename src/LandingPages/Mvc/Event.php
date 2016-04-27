<?php
namespace LandingPages\Mvc;


use LandingPages\Object;

class Event extends Object
{
    protected static $_filters = array();

    static public function register( $hook, $callable )
    {
        if ( is_callable($callable) ) {
            self::$_filters[$hook][] = $callable;
        }
    }

    static public function call( $hook )
    {
        $args = func_get_args();
        $hook = array_shift($args);

        $result = null;

        if ( array_key_exists($hook, self::$_filters) ) {
            foreach (self::$_filters[$hook] as $callable) {
                $result = call_user_func_array($callable, $args);
            }
        }

        return $result;
    }

    static public function filter( $hook, $value )
    {
        $args = func_get_args();
        $hook = array_shift($args);

        if ( array_key_exists($hook, self::$_filters) ) {
            foreach (self::$_filters[$hook] as $callable) {
                $args[0] = call_user_func_array($callable, $args);
            }
        }

        return $args[0];
    }
}