<?php
namespace LandingPages\Hook;


abstract class Backend
{
    protected $_config;

    public function __construct( $config, $variables )
    {
        $this->_config = $config;
        $this->_variables = $variables;
    }

    abstract public function exec();

}