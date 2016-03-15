<?php
namespace LandingPages\Mvc;


use LandingPages\Object;

class Session extends Object
{

    public function __construct()
    {
        session_start();
    }

    public function setData($name, $value = null)
    {
        if ( is_array($name) ) {
            foreach ( $name as $field => $value ) {
                $_SESSION[$field] = $value;
            }
        } else {
            $_SESSION[$name] = $value;
        }

        return $this;
    }

    public function getData($name = null, $default = null)
    {
        if ( $name === null ) return $_SESSION;
        if ( array_key_exists($name, $_SESSION) ) return $_SESSION[$name];
        return $default;
    }

    public function unsetData($name)
    {
        if ( array_key_exists($name, $_SESSION) ) unset($_SESSION[$name]);
        return $this;
    }

    public function issetData($name)
    {
        return array_key_exists($name, $_SESSION);
    }
}