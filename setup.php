<?php
session_start();

// TODO: detect language (config valid languages + Accept-Languages header = LANDINGS_LANGUAGE)
define('LANDINGS_LANGUAGE', 'es_ES');
define('LANDINGS_LANGUAGE_SHORT', array_shift(explode('_',LANDINGS_LANGUAGE)));

define('SETUP_LOADED', true);
define('LANDINGS_DIR', realpath(dirname(__FILE__)));

$document_root = realpath($_SERVER['DOCUMENT_ROOT']);
$app_dir = realpath(dirname(__FILE__));
$base_uri = trim(substr($app_dir, strlen($document_root)),'/');
define('LANDINGS_URI',str_replace('//','/',"/{$base_uri}/"));

// URI
$uri = trim(array_shift(explode('?',$_SERVER['REQUEST_URI'])),'/');
define('LANDINGS_URL', "http://{$_SERVER['SERVER_NAME']}/{$uri}");
$uri = trim(substr($uri,strlen($base_uri)),'/');
define('URI', $uri);

function lib($name){
    extract($GLOBALS);
    include_once LANDINGS_DIR.'/lib/'.$name.'.php';
}

lib('templates');
lib('stats');

