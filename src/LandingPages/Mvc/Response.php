<?php
namespace LandingPages\Mvc;

use LandingPages\Mvc;
use LandingPages\Mvc\Response\Cache;
use LandingPages\Object;
use LandingPages\Template;

class Response extends Object
{
    protected $_headers;
    protected $_binary_file;
    protected $_template;

    public function __construct()
    {
        $this->_headers = array();
    }

    protected function _sendHeaders()
    {
        foreach ( $this->_headers as $name => $value ) {
            $code = null;

            if ( is_array($value) ) {
                list($value, $code) = $value;
                $header = "{$name}: {$value}";
            } else if ( is_null($value) ) {
                $header = $name;
            } else {
                $header = "{$name}: {$value}";
            }

            header($header, true, $code);
        }
    }

    public function exec()
    {
        // Download binary file
        if ( $this->_binary_file ) {
            // Send headers
            $this->_sendHeaders();
            // Send file
            readfile($this->_binary_file);
            exit();
        }

        // 404? (without template)
        if ( ! $this->_template || (!Template::exists($this->_template) && $this->_template != '_404') ) {
            $this->setData('cache.excluded', true);
            $this->_template = '_404';
        }

        // Output
        timer('start', 'template_parse');
        ob_start();
        Template::parse($this->_template, $this->_data);
        $content = ob_get_clean();
        $content = Event::filter('content', $content);
        timer('end', 'template_parse');

        // If not excluding cache, then save cache
        if ( ! $this->getData('cache.excluded') ) {
            Cache::factory()->save($this->_headers, $content, $this->getData('page_ttl'));
        }

        // Send headers
        $this->_sendHeaders();

        // Send content
        echo Event::filter('cache', $content);
    }

    public function redirect( $url, $code = 302 )
    {
        return $this->addHeader('Location', $url, $code);
    }

    public function addHeader( $name, $value = null, $code = null )
    {
        $this->_headers[$name] = ($code === null ? $value : array($value,$code));
        return $this;
    }

    /**
     * @param $name
     *
     * @return $this    Response
     */
    public function setTemplate( $name )
    {
        $this->_template = $name;
        return $this;
    }

    public function setBinaryFile( $filepath, $size = null, $mime = null )
    {
        if ( ! ($path = $this->_findBinaryFile($filepath)) ) {
            throw new \Exception('File not found: '.$filepath);
        }

        if ( ! $mime ) $mime = mime_content_type($path);
        if ( ! $size ) $size = filesize($path);

        $this->addHeader('Content-Type', $mime);
        $this->addHeader('Content-Length', $size);

        $this->_binary_file = $path;

        return $this;
    }

    protected function _findBinaryFile( $filepath )
    {
        $paths = array(
            LP_APP_DIRECTORY.'/'.$filepath,
            LP_DEFAULT_APP_DIRECTORY.'/'.$filepath,
            LP_ROOT_DIRECTORY.'/'.$filepath
        );

        foreach ( $paths as $path ) {
            if ( is_file($path) ) {
                return $path;
            }
        }

        return null;
    }

    static public function init()
    {
        Event::register('content', function($content){

            // Var assignation
            $content = preg_replace_callback('/{{var ([^=}]*)=([^}]*)}}/', function($M){
                $varname = trim($M[1]);
                $value = trim($M[2]);

                if ( substr($value,0,3) == '~~~' ) {
                    $value = I18n::getSingleton()->translate(substr($value,3));
                }

                Mvc::getResponse()->setData($varname, $value);

                return '';
            }, $content);

            // Var echo
            $content = preg_replace_callback('/{{var ([^}]*)}}/', function($M){
                return Mvc::getResponse()->getData(trim($M[1]));
            }, $content);

            // Translate
            $content = preg_replace_callback('/{{~~~([^}]*)}}/', function($M){
                return I18n::getSingleton()->translate( trim($M[1]) );
            }, $content);

            // Asset URL
            $content = preg_replace_callback('/{{asset ([^}]*)}}/', function($M){
                return asset(ltrim(trim($M[1]),'/'));
            }, $content);


            // Page URL helper
            $content = preg_replace_callback('/{{url[ ]?([^}]*)}}/', function($M){
                return page_url(trim($M[1]));
            }, $content);

            // Template include
            $content = preg_replace_callback('/{{template ([^}]*)}}/', function($M){
                ob_start();
                template(trim($M[1]));
                return Event::filter('content', ob_get_clean());
            }, $content);

            // {{header Content-Type: text/plain}}
            $content = preg_replace_callback('/{{header ([^}]*)}}/', function($M){
                if ( preg_match('/^([^:]*): (.*)$/', trim($M[1]), $L) ) {
                    Mvc::getResponse()->addHeader(trim($L[1]), trim($L[2]));
                }
                return '';
            }, $content);

            // {{redirect template/name}}
            $content = preg_replace_callback('/{{redirect ([^}]*)}}/', function($M){
                Mvc::getResponse()->redirect(page_url(trim($M[1])));
                return '';
            }, $content);

            return $content;
        });
    }
}
