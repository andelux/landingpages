<?php
namespace LandingPages\Mvc;

use LandingPages\Object;
use LandingPages\Mvc\Request;
use LandingPages\Mvc\Response;
use LandingPages\Mvc\Session;

/**
 * Class Controller
 *
 * @method Request getRequest()
 * @method Controller setRequest(Request $request)
 *
 * @package LandingPages
 */
class Controller extends Object
{
    protected $_template;

    /**
     * Controller constructor.
     * @param \LandingPages\Mvc\Request $request
     */
    public function __construct( Request $request )
    {
        $this->setRequest( $request );
    }

    /**
     * @return Session
     */
    public function getSession()
    {
        return $this->getRequest()->getSession();
    }

    /**
     * @return Response
     */
    public function getResponse()
    {
        $response = new Response();
        $response->addHeader('Content-Type', 'text/html; charset=utf-8');
        return $response;
    }

    /**
     * @param $params
     * @return $this
     */
    public function setParams( $params )
    {
        return $this->setData( $params );
    }

    /**
     * @param $name
     * @param null $default
     * @return array|null
     */
    public function getParam( $name, $default = null )
    {
        if ( $this->issetData($name) ) return $this->getData($name);
        if ( isset($_REQUEST[$name]) ) return $_REQUEST[$name];

        return $default;
    }
}
