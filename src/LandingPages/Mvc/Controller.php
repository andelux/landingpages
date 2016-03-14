<?php
namespace LandingPages\Mvc;

use LandingPages\Object;

/**
 * Class Controller
 *
 * @method Request getRequest()
 *
 * @package LandingPages
 */
class Controller extends Object
{
    protected $_template;

    public function __construct( Request $request )
    {
        $this->setRequest( $request );
    }

    public function getResponse()
    {
        $response = new Response();
        $response->addHeader('Content-Type', 'text/html; charset=utf-8');
        return $response;
    }

    public function setParams( $params )
    {
        return $this->setData( $params );
    }

    public function getParam( $name, $default = null )
    {
        if ( $this->issetData($name) ) return $this->getData($name);
        if ( isset($_REQUEST[$name]) ) return $_REQUEST[$name];

        return $default;
    }
}
