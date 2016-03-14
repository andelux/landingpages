<?php
namespace LandingPages\Mvc;

use LandingPages\Template;

class Response
{
    protected $_headers;
    protected $_binary_file;
    protected $_template;
    protected $_params;

    public function __construct()
    {
        $this->_headers = array();
        $this->_params = array();
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

        Template::parse($this->_template, $this->_params);

        exit();
    }

    public function setParam($name, $value)
    {
        $this->_params[$name] = $value;
        return $this;
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

    public function setTemplate( $name )
    {
        $this->_template = $name;
        return $this;
    }

    public function setBinaryFile( $filepath, $size = null, $mime = null )
    {
        if ( ! is_file($filepath) ) {
            throw new \Exception('File not found: '.$filepath);
        }

        if ( ! $mime ) $mime = mime_content_type($filepath);
        if ( ! $size ) $size = filesize($filepath);

        $this->addHeader('Content-Type', $mime);
        $this->addHeader('Content-Length', $size);

        $this->_binary_file = $filepath;

        return $this;
    }
}
