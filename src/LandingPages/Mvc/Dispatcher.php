<?php
namespace LandingPages\Mvc;

use LandingPages\Object;
use LandingPages\Mvc\Request;

/**
 * Class Dispatcher
 *
 * @method Dispatcher setRequest( Request $request )
 * @method Request getRequest()
 *
 * @package LandingPages\Mvc
 */
class Dispatcher extends Object
{
    protected $_tokens;
    protected $_token;

    /**
     * Dispatcher constructor.
     * @param \LandingPages\Mvc\Request $request
     */
    public function __construct( Request $request )
    {
        $this->_tokens = array();
        $this->setRequest( $request );
    }

    public function getToken()
    {
        return $this->hasTokens() ? ($this->_token = $this->shiftToken()) : null;
    }

    public function getCurrentToken()
    {
        return $this->_token;
    }

    /**
     *
     * @return Response
     */
    public function doLoop()
    {
        $response = null;

        while ( $this->getToken() ) {

            // Exec token and get the response
            $response = $this->execToken();

            // Have we a response?
            if ( $response === null && ! $this->hasTokens() ) {
                // ERROR 500
                $this->addToken( array('error','500',array()) );
            }

        }

        return $response;
    }

    /**
     * @param $token
     * @return $this
     */
    public function addToken( $token )
    {
        $this->_tokens[] = $token;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasTokens()
    {
        return count($this->_tokens) > 0;
    }

    /**
     * @return mixed
     */
    public function shiftToken()
    {
        return array_shift($this->_tokens);
    }

    /**
     * @param $token
     * @return array|null
     */
    public function isDispatchable( $token )
    {
        static $cache = array();

        $key = md5(serialize($token));

        if ( ! array_key_exists($key, $cache) ) {
            $cache[$key] = null;

            list($controller, $action, $params) = $token;

            $class = '\\LandingPages\\Controller\\' . uc_words($controller, '-', '');
            $method = 'action' . uc_words($action, '-', '');

            if (class_exists($class)) {
                $obj = new $class($this->getRequest());
                if (method_exists($obj, $method)) {
                    $cache[$key] = array($obj, $method, $params);
                }
            }
        }

        return $cache[$key];
    }

    /**
     * @param $token
     * @param null $dispatchable
     * @return mixed|null
     */
    public function execToken( $token = null, $dispatchable = null )
    {
        if ( ! $token ) $token = $this->_token;

        // If we have not the dispatchable object...
        if ( $dispatchable === null ) $dispatchable = $this->isDispatchable($token);

        // This token is dispatchable?
        if ( $dispatchable !== null ) {
            // ...then run!

            /** @var $controller_object Controller */
            list($controller_object, $method, $params) = $dispatchable;

            // Set params
            $controller_object->setData( $params );

            // Exec!
            // This must return a Response object
            return call_user_func_array(array($controller_object, $method), array());
        }

        return null;
    }
}
