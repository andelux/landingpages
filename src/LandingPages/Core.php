<?php

namespace LandingPages;

class Core
{
    public function __construct( $root_directory )
    {
        session_start();

        define('LANDINGS_DIR', realpath($root_directory));

        $document_root = realpath($_SERVER['DOCUMENT_ROOT']);
        $app_dir = realpath($root_directory);
        $base_uri = trim(substr($app_dir, strlen($document_root)),'/');
        define('LANDINGS_URI',str_replace('//','/',"/{$base_uri}/"));

        // URI
        $uri = trim(array_shift(explode('?',$_SERVER['REQUEST_URI'])),'/');
        define('LANDINGS_URL', "http://{$_SERVER['SERVER_NAME']}/{$uri}");
        $uri = trim(substr($uri,strlen($base_uri)),'/');
        define('URI', $uri);

        // Load config
        if ( is_file(LANDINGS_DIR.'/config.php') ) {
            require LANDINGS_DIR . '/config.php';

            // TODO: detect language (config valid languages + Accept-Languages header = LANDINGS_LANGUAGE)
            define('LANDINGS_LANGUAGE', 'es_ES');
            define('LANDINGS_LANGUAGE_SHORT', array_shift(explode('_', LANDINGS_LANGUAGE)));

            define('SETUP_LOADED', true);
        }

        require LANDINGS_DIR.'/functions.php';

        return $this;
    }
}