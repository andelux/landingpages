<?php
/**
 * Created by PhpStorm.
 * User: javier
 * Date: 10/03/16
 * Time: 11:19
 */

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