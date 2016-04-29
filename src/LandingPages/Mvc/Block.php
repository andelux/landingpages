<?php
namespace LandingPages\Mvc;


use LandingPages\Mvc;
use LandingPages\Object;
use LandingPages\Template;

class Block extends Object
{
    const DEFAULT_TTL = 60;

    protected $_template;
    protected $_cache; // allow cache
    protected $_params;
    protected $_hash;
    protected $_content;

    static public function init()
    {
        Event::register('content', function($content){

            // Block: template with cache
            return preg_replace_callback('/{{block ([^}]*)}}/', function($M){
                $params = array();

                $parameters = preg_replace_callback('/([a-zA-Z_0-9]*)=([^"]{1}[^ ]*)/', function($S){
                    return "{$S[1]}=\"{$S[2]}\"";
                },$M[1]);

                // template vars
                if ( preg_match_all('/([a-zA-Z_0-9]*)="([^"]*)"/', $parameters, $L, PREG_SET_ORDER) ) {
                    foreach ( $L as $param ) {
                        $varname = trim($param[1]);
                        $value = trim($param[2]);
                        if ( substr($value,0,3) == '~~~' ) $value = I18n::getSingleton()->translate(substr($value,3));
                        $params[$varname] = $value;
                    }
                }

                // config vars
                $block_key = "block.{$params['template']}.";
                $block_key_length = strlen($block_key);
                foreach ( Mvc::getConfig()->getData() as $key => $value ) {
                    if ( substr($key,0,$block_key_length) == $block_key ) {
                        $varname = trim(substr($key, $block_key_length));

                        $value = trim($value);
                        if ( substr($value,0,3) === '~~~' ) {
                            $value = I18n::getSingleton()->translate(substr($value,3));
                        }
                        $params[$varname] = $value;
                    }
                }

                return Block::factory($params)->getHtml();
            }, $content);

        });
    }

    public function __construct($params = array())
    {
        parent::__construct($params);

        $this->_template = $this->getData('template');
        $this->_cache = $this->getData('cache', false);

        if ( $this->isCacheEnabled() ) {
            $this->_hash = array($this->_template);
            foreach (explode(',', $this->getData('hash','')) as $hash_code) {
                switch (trim($hash_code)) {
                    case 'host':
                        $this->_hash[] = $_SERVER['SERVER_NAME'];
                        break;
                    case 'locale':
                        $this->_hash[] = LP_LOCALE;
                        break;
                }
            }
            $this->_hash = md5(implode('|', $this->_hash));
        }
    }

    /**
     * @param $params
     *
     * @return Block
     */
    static public function factory($params)
    {
        return new self($params);
    }

    public function isCacheEnabled()
    {
        return $this->_cache && Mvc::getConfig()->getData('app.cache', true);
    }

    public function getCacheFile()
    {
        return LP_ROOT_DIRECTORY . '/var/cache/blocks/' . $this->_hash;
    }

    public function hasCache()
    {
        // Is cache allowed?
        if ( ! $this->isCacheEnabled() ) return false;

        // Is cache file present?
        $path = $this->getCacheFile();
        if ( ! is_file($path) ) return false;

        // Is cache file expired?
        $expire = is_file("{$path}.expire") ? intval(file_get_contents("{$path}.expire")) : (filemtime($path) + self::DEFAULT_TTL);
        return $expire > time();
    }

    public function getHtml()
    {
        if ( $this->hasCache() ) {

            $this->_content = file_get_contents($this->getCacheFile());

        } else {
            ob_start();
            Template::parse($this->_template, $this->_data);
            $this->_content = Event::filter('content', ob_get_clean());

            if ( $this->isCacheEnabled() ) {
                @mkdir(dirname($this->getCacheFile()), 0777, true);
                file_put_contents($this->getCacheFile(), $this->_content);
                if ( $ttl = $this->getData('ttl') ) {
                    file_put_contents($this->getCacheFile().'.expire', "{$ttl}");
                } else {
                    @unlink($this->getCacheFile().'.expire');
                }
            }
        }

        return $this->_content;
    }
}