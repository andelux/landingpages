<?php
namespace LandingPages\Hook;


use LandingPages\Object;

abstract class Backend extends Object
{
    protected $_config;

    public function __construct( $config, $variables )
    {
        $this->_config = $config;

        parent::__construct($variables);

        $this->exec();
    }

    public function getConfig( $name, $default = null )
    {
        return array_key_exists($name, $this->_config) ? $this->_config[$name] : $default;
    }

    abstract public function exec();

}