<?php
namespace LandingPages;

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
}
