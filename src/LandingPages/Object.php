<?php
namespace LandingPages;


class Object
{
    protected $_data;

    public function __construct( $data = array() )
    {
        $this->_data = $data;
    }

    public function hasData()
    {
        return count($this->_data) > 0;
    }

    public function setData( $name, $value = null )
    {
        if ( is_array($name) ) {
            $this->_data = array_merge($this->_data, $name);
        } else if ( is_string($name) ) {
            $this->_data[$name] = $value;
        } else {
            // TODO: Exception?
        }

        return $this;
    }

    public function getData( $name = null, $default = null )
    {
        if ( $name === null ) {
            return $this->_data;
        }

        if ( array_key_exists($name, $this->_data) ) {
            return $this->_data[$name];
        }

        return $default;
    }

    public function unsetData( $name )
    {
        if ( array_key_exists($name, $this->_data) ) {
            unset($this->_data[$name]);
        }
        return $this;
    }

    public function issetData( $name )
    {
        return array_key_exists($name, $this->_data);
    }

    public function __set($name, $value)
    {
        $this->setData($name, $value);
    }

    public function __get($name)
    {
        return $this->getData($name);
    }

    public function __isset($name)
    {
        return $this->issetData($name);
    }

    public function __unset($name)
    {
        $this->unsetData($name);
    }

    public function __call($name, $arguments)
    {
        if ( preg_match('/^(get|set|unset|isset)(.*)$/', $name, $M) ) {
            $func = $M[1];
            $varname = from_camel_case($M[2]);

            switch ( $func ) {
                case 'set':
                    return $this->setData($varname, array_shift($arguments));
                case 'get':
                    return $this->getData($varname, array_shift($arguments));
                case 'unset':
                    return $this->unsetData($varname);
                case 'isset':
                    return $this->issetData($varname);
            }
        }

        // TODO: throw Exception?
    }
}
