<?php
namespace LandingPages\Mvc;

use LandingPages\Object;

class Config extends Object
{
    const LOAD_SECTION_KEY = '_load_section';

    public function __construct($file)
    {
        parent::__construct();

        $this->loadIni($file);
    }

    /**
     * @param $file
     * @throws \Exception
     */
    public function loadIni( $file )
    {
        if ( ! is_file($file) ) {
            throw new \Exception('Unable to load configuration file: '.$file);
        }

        // Load INI configuration
        $scanner = version_compare(phpversion(),'5.6.1','>=') ? INI_SCANNER_TYPED : INI_SCANNER_NORMAL;
        $data = parse_ini_file($file, true, $scanner);

        // Load sections contextually
        $this->_data = array_key_exists($_SERVER['SERVER_NAME'], $data) ? array_merge($data['general'], $data[$_SERVER['SERVER_NAME']]) : $data['general'];

        // Load children sections
        while ( array_key_exists(self::LOAD_SECTION_KEY, $this->_data) && array_key_exists($section=$this->_data[self::LOAD_SECTION_KEY], $data) ) {
            unset($this->_data[self::LOAD_SECTION_KEY]);
            $this->_data = array_merge($this->_data, $data[$section]);
        }
    }
}
