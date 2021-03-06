<?php
namespace LandingPages;

use LandingPages\Mvc\Block;
use LandingPages\Mvc\Config;
use LandingPages\Mvc\Event;
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

        define('LP_DEBUG', $this->config->getData('app.debug',false) ? true : false);

        timer('start', 'init');

        define('LP_ROOT_DIRECTORY', $root_dir);
        define('LP_APP_DIRECTORY', "{$root_dir}/app/{$this->config->getData('app.name','default')}");
        define('LP_DEFAULT_APP_DIRECTORY', "{$root_dir}/app/default");

        // /landingpages/
        $document_root = realpath($_SERVER['DOCUMENT_ROOT']);
        $base_uri = trim(substr($root_dir, strlen($document_root)),'/');
        define('LP_BASE_URI',str_replace('//','/',"/{$base_uri}/"));
        define('LP_APP_URI', LP_BASE_URI."app/{$this->config->getData('app.name','default')}/");
        define('LP_DEFAULT_APP_URI', LP_BASE_URI."app/default/");

        // Ex: http://localhost/landingpages/example-simple-v1.html
        $uri = trim(array_shift(explode('?',$_SERVER['REQUEST_URI'])),'/');
        if ( preg_match('/^(.*)\/index\.php$/', $uri, $M) ) $uri = $M[1];
        define('LP_URL', "http://{$_SERVER['SERVER_NAME']}/{$uri}");
        define('LP_BASE_URL', "http://{$_SERVER['SERVER_NAME']}".LP_BASE_URI);

        // Ex: example-simple-v1.html
        $uri = trim(substr($uri,strlen($base_uri)),'/');
        define('LP_URI', $uri);

        // languages
        if ( ! $this->config->issetData('locale.detect_methods') ) $this->config->setData('locale.detect_methods', 'url,domain,geoip,browser');
        if ( ! $this->config->issetData('locale.enabled') ) $this->config->setData('locale.enabled', 'en_US,en_GB');
        if ( ! $this->config->issetData('locale.default') ) $this->config->setData('locale.default', array_shift(explode(',',$this->config->getData('locale.enabled'))));

        // Database
        if ( ! $this->config->issetData('database') ) $this->config->setData('database', 'sqlite:'.LP_ROOT_DIRECTORY.'/var/general.db');

        Response::init();
        Response\Cache::init();
        Block::init();

        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // 3. Request

        $this->request = new Request();
        $this->request->setRootDirectory( LP_ROOT_DIRECTORY );
        $this->request->setUrl( LP_URL );
        $this->request->setBaseUri( LP_BASE_URI );
        $this->request->setUri( LP_URI );
        $this->request->setSession( $this->session );
        $this->request->setConfig( $this->config );

        if ( ! ($this->response = $this->request->getCacheResponse()) ) {

            ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            // 4. Router

            $this->router = new Router($this->request);
            $token = $this->router->getToken();

            ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            // 5. Dispatcher

            $this->dispatcher = new Dispatcher($this->request);

            if (!$token || !$this->dispatcher->isDispatchable($token)) {
                $token = array('error', '404', array('uri' => LP_URI));
            }

            // We have a token to dispatch
            $this->dispatcher->addToken($token);

            // Dispatch tokens and get the final response
            /** @var Response $response */
            $this->response = $this->dispatcher->doLoop();

        }

        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // 6. Response
        $this->response->exec();

        timer('end','init');
        timer('print');
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

    static public function getRouter()
    {
        /** @var $MVC Mvc */
        global $MVC;
        return $MVC->router;
    }

    /**
     * @return Dispatcher
     */
    static public function getDispatcher()
    {
        /** @var $MVC Mvc */
        global $MVC;
        return $MVC->dispatcher;
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