<?php
namespace LandingPages\Mvc;

use LandingPages\Mvc;
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
     * @param $action
     * @param null $controller
     * @param array $params
     * @return null
     */
    protected function forward( $action, $controller = null, $params = array() )
    {
        /** @var Dispatcher $dispatcher */
        $dispatcher = Mvc::getDispatcher();

        // Get controller
        list($current_controller, ) = $dispatcher->getCurrentToken();

        // Create token
        $token = array(
            $controller === null ? $current_controller : $controller,
            $action,
            $params
        );

        // Add token
        $dispatcher->addToken( $token );

        return null;
    }

    /**
     * @return Session
     */
    public function getSession()
    {
        return $this->getRequest()->getSession();
    }

    /**
     * @return Config
     */
    public function getConfig()
    {
        return Mvc::getConfig();
    }

    /**
     * @return Response
     */
    public function getResponse()
    {
        $response = new Response();
        $response->addHeader('Content-Type', 'text/html; charset=utf-8');
        $response->setData($this->getData());
        return $response;
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
