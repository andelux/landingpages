<?php
/**
 * Created by PhpStorm.
 * User: javier
 * Date: 11/03/16
 * Time: 12:31
 */

namespace LandingPages;


class Dispatcher extends Object
{
    protected $_tokens;

    public function __construct( Request $request )
    {
        $this->_tokens = array();
        $this->setRequest( $request );
    }

    public function addToken( $token )
    {
        $this->_tokens[] = $token;
        return $this;
    }

    public function hasTokens()
    {
        return count($this->_tokens) > 0;
    }

    public function shiftToken()
    {
        return array_shift($this->_tokens);
    }

    public function isDispatchable( $token )
    {
        list($controller, $action, $params) = $token;

        $class = '\\LandinPages\\Controller\\'.uc_words($controller,'-','');
        $method = 'action' . uc_words($action,'-','');

        if ( class_exists($class) ) {
            $obj = new $class( $this->getRequest() );
            if ( method_exists($obj, $method) ) {
                return array($obj, $method, $params);
            }
        }

        return null;
    }

    public function execToken( $token, $dispatchable = null )
    {
        // If we have not the dispatchable object...
        if ( $dispatchable === null ) $dispatchable = $this->isDispatchable($token);

        // This token is dispatchable?
        if ( $dispatchable !== null ) {
            // ...then run!
            $params = array_pop($dispatchable);
            return call_user_func_array($dispatchable, $params);
        }

        return null;
    }
}