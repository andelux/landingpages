<?php
namespace LandingPages;


class Bootstrap
{
    public function __construct($root_dir)
    {
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

        $document_root = realpath($_SERVER['DOCUMENT_ROOT']);
        $base_uri = trim(substr($root_dir, strlen($document_root)),'/');
        define('LP_BASE_URI',str_replace('//','/',"/{$base_uri}/"));

        $uri = trim(array_shift(explode('?',$_SERVER['REQUEST_URI'])),'/');
        if ( preg_match('/^(.*)\/(index|post|install)\.php$/', $uri, $M) ) $uri = $M[1];
        define('LP_BASE_URL', "http://{$_SERVER['SERVER_NAME']}/{$uri}");

        $uri = trim(substr($uri,strlen($base_uri)),'/');
        define('LP_URI', $uri);

        // languages
        if ( ! defined('LP_LOCALE_DETECT_METHODS') ) define('LP_LOCALE_DETECT_METHODS', 'url,domain,geoip,browser');
        if ( ! defined('LP_LOCALE_ENABLED') ) define('LP_LOCALE_ENABLED', 'en_US,en_GB');
        if ( ! defined('LP_LOCALE_DEFAULT') ) define('LP_LOCALE_DEFAULT', array_shift(explode(',',LP_LOCALE_ENABLED)));

        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // 3. Request

        $request = new Request();
        $request->setRootDirectory( LP_ROOT_DIRECTORY );
        $request->setBaseUrl( LP_BASE_URL );
        $request->setBaseUri( LP_BASE_URI );
        $request->setUri( LP_URI );

        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // 4. Router

        $router = new Router($request);
        $token = $router->getToken();

        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // 5. Dispatcher

        /** @var Response $response */
        $response = null;

        $dispatcher = new Dispatcher( $request );
        $dispatcher->addToken( $token );

        while ( $dispatcher->hasTokens() ) {
            $token = $dispatcher->shiftToken();
            $response = $dispatcher->execToken( $token );
        }

        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // 6. Response

        if ( $response === null ) {
            // ERROR 500
            $response = new Response();
            $response->addHeader('HTTP/1.1 500 Server error',null,500);
            $response->setTemplate('_500');
        }

        $response->exec();
    }

}