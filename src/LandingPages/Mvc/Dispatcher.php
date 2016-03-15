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

    /**
     * Dispatcher constructor.
     * @param \LandingPages\Mvc\Request $request
     */
    public function __construct( Request $request )
    {
        $this->_tokens = array();
        $this->setRequest( $request );
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
        list($controller, $action, $params) = $token;

        $class = '\\LandingPages\\Controller\\'.uc_words($controller,'-','');
        $method = 'action' . uc_words($action,'-','');

        if ( class_exists($class) ) {
            $obj = new $class( $this->getRequest() );
            if ( method_exists($obj, $method) ) {
                return array($obj, $method, $params);
            }
        }

        return null;
    }

    /**
     * @param $token
     * @param null $dispatchable
     * @return mixed|null
     */
    public function execToken( $token, $dispatchable = null )
    {
        // If we have not the dispatchable object...
        if ( $dispatchable === null ) $dispatchable = $this->isDispatchable($token);

        // This token is dispatchable?
        if ( $dispatchable !== null ) {
            // ...then run!
            $params = array_pop($dispatchable);
            $dispatchable[0]->setParams( $params );
            return call_user_func_array($dispatchable, array());
        }

        return null;
    }
}
