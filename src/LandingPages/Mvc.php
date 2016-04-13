<?php
namespace LandingPages;

use LandingPages\Mvc\Config;
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
    /** @var  Config */
    public $config;

    public function __construct($root_dir)
    {
        global $MVC;
        $MVC = $this;

        $root_dir = realpath($root_dir);

        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // 1. Config

        $this->config = new Config( $root_dir . '/etc/config.ini' );

        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // 2. Setup

        $this->session = new Session();

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
        if ( ! $this->config->issetData('locale.detect_methods') ) $this->config->setData('locale.detect_methods', 'url,domain,geoip,browser');
        if ( ! $this->config->issetData('locale.enabled') ) $this->config->setData('locale.enabled', 'en_US,en_GB');
        if ( ! $this->config->issetData('locale.default') ) $this->config->setData('locale.default', array_shift(explode(',',$this->config->getData('locale.enabled'))));

        // Database
        if ( ! $this->config->issetData('database') ) $this->config->setData('database', 'sqlite:'.LP_ROOT_DIRECTORY.'/var/general.db');

        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // 3. Request

        $this->request = new Request();
        $this->request->setRootDirectory( LP_ROOT_DIRECTORY );
        $this->request->setUrl( LP_URL );
        $this->request->setBaseUri( LP_BASE_URI );
        $this->request->setUri( LP_URI );
        $this->request->setSession( $this->session );
        $this->request->setConfig( $this->config );

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
     * @return Config
     */
    static public function getConfig()
    {
        global $MVC;
        return $MVC->config;
    }

    /**
     * @return Response
     */
    static public function getResponse()
    {
        global $MVC;
        return $MVC->response;
    }

    /**
     * @return Request
     */
    static public function getRequest()
    {
        global $MVC;
        return $MVC->request;
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