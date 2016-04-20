<?php
namespace LandingPages\Mvc;

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

    public function exec()
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

        if ( $this->_binary_file ) {
            readfile($this->_binary_file);
            exit();
        }

        if ( $this->_template ) Template::parse($this->_template, $this->_data);

        exit();
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
}
