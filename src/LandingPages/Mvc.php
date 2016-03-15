<?php
namespace LandingPages;

use LandingPages\Mvc\Model;
use LandingPages\Mvc\Response;
use LandingPages\Mvc\Request;
use LandingPages\Mvc\Router;
use LandingPages\Mvc\Dispatcher;
use LandingPages\Mvc\Session;

class Mvc
{
    public $response;
    public $request;
    public $router;
    public $dispatcher;
    public $session;

    public function __construct($root_dir)
    {
        global $MVC;
        $MVC = $this;

        $this->session = new Session();

        $root_dir = realpath($root_dir);

        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // 1. Config

        if ( is_file($root_dir.'/config.php') ) {
            require $root_dir.'/config.php';
            define('CONFIG_LOADED', true);
        } else {
            define('CONFIG_LOADED', false);
        }

        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // 2. Setup

        require $root_dir.'/functions.php';

        define('LP_ROOT_DIRECTORY', $root_dir);

        // /landingpages/
        $document_root = realpath($_SERVER['DOCUMENT_ROOT']);
        $base_uri = trim(substr($root_dir, strlen($document_root)),'/');
        define('LP_BASE_URI',str_replace('//','/',"/{$base_uri}/"));

        // Ex: http://localhost/landingpages/example-simple-v1.html
        $uri = trim(array_shift(explode('?',$_SERVER['REQUEST_URI'])),'/');
        if ( preg_match('/^(.*)\/index\.php$/', $uri, $M) ) $uri = $M[1];
        define('LP_URL', "http://{$_SERVER['SERVER_NAME']}/{$uri}");

        // Ex: example-simple-v1.html
        $uri = trim(substr($uri,strlen($base_uri)),'/');
        define('LP_URI', $uri);

        // languages
        if ( ! defined('LP_LOCALE_DETECT_METHODS') ) define('LP_LOCALE_DETECT_METHODS', 'url,domain,geoip,browser');
        if ( ! defined('LP_LOCALE_ENABLED') ) define('LP_LOCALE_ENABLED', 'en_US,en_GB');
        if ( ! defined('LP_LOCALE_DEFAULT') ) define('LP_LOCALE_DEFAULT', array_shift(explode(',',LP_LOCALE_ENABLED)));

        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // 3. Request

        $this->request = new Request();
        $this->request->setRootDirectory( LP_ROOT_DIRECTORY );
        $this->request->setUrl( LP_URL );
        $this->request->setBaseUri( LP_BASE_URI );
        $this->request->setUri( LP_URI );
        $this->request->setSession( $this->session );

        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // 4. Router

        $this->router = new Router($this->request);
        $token = $this->router->getToken();

        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // 5. Dispatcher

        /** @var Response $response */
        $this->response = null;

        $this->dispatcher = new Dispatcher( $this->request );
        $this->dispatcher->addToken( $token );

        while ( $this->dispatcher->hasTokens() ) {
            $token = $this->dispatcher->shiftToken();
            $this->response = $this->dispatcher->execToken( $token );
        }

        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // 6. Response

        if ( $this->response === null ) {
            // ERROR 500
            $this->response = new Response();
            $this->response->addHeader('HTTP/1.1 500 Server error',null,500);
            $this->response->setTemplate('_500');
        }

        $this->response->exec();
    }


    /**
     * @return Session
     */
    static public function getSession()
    {
        /** @var $MVC Mvc */
        global $MVC;
        return $MVC->session;
    }

    /**
     * @param $model
     *
     * @return Model
     */
    static public function getModel($model)
    {
        $class_name = '\\LandingPages\\Model\\'.uc_words($model,'_','');
        return new $class_name();
    }
}