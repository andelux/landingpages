<?php
namespace LandingPages\Hook;

use LandingPages\Hook;

class Magento extends Hook\Backend
{
    public function exec()
    {
        foreach ( $this->_variables as $name => $value ) {
            if ( isset($this->_config['map'][$name]) ) {
                $data[$this->_config['map'][$name]] = $value;
            } else {
                throw new \Exception('Field not found in Magento map: ' . $name);
            }
        }

        $this->send($data);
    }

}