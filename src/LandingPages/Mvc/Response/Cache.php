<?php
namespace LandingPages\Mvc\Response;


use LandingPages\Mvc;
use LandingPages\Mvc\Config;
use LandingPages\Mvc\Event;
use LandingPages\Mvc\Response;

class Cache extends Response
{
    const DEFAULT_TTL = 60;

    protected $_hash;
    protected $_hash_file;
    protected $_expire;

    static public function init()
    {
        Event::register('cache', function($content){
            $content = preg_replace_callback('/{{form_key}}/', function($M){
                return form_key_html();
            }, $content);
            return $content;
        });
    }

    public function __construct()
    {
        parent::__construct();

        $this->_hash = $this->generateHash();

        if ( is_file($this->_getCachePath()) ) {
            $this->_expire = @doubleval(file_get_contents("{$this->_getCachePath()}.expire"));

            // Purge cache
            $this->purge();
        }
    }

    /**
     * @return Cache
     */
    static public function factory()
    {
        return new self();
    }

    public function save( $headers, $content, $ttl = null )
    {
        if ( ! is_dir(dirname($this->_getCachePath())) ) @mkdir(dirname($this->_getCachePath()),0777,true);
        file_put_contents("{$this->_getCachePath()}.expire", (time()+($ttl?$ttl:self::DEFAULT_TTL)).'');
        file_put_contents("{$this->_getCachePath()}.json", json_encode($headers));
        file_put_contents($this->_getCachePath(), $content);
    }

    public function generateHash()
    {
        static $hash;

        if ( ! $hash ) {
            // Generate hash
            $hash = '';
            $hash .= $_SERVER['SERVER_NAME'] . ':';
            $hash .= $_SERVER['REQUEST_URI'];

            // Get others hashes... currency? user?
            $hash = md5(Event::filter('cache.hash', $hash));
        }

        return $hash;
    }

    public function hasCache()
    {
        return Mvc::getConfig()->getData('app.cache', true) && ($this->_expire > time());
    }

    public function purge()
    {
        if ( ! $this->hasCache() ) {
            @unlink($this->_getCachePath());
            @unlink($this->_getCachePath() . '.json');
            @unlink($this->_getCachePath() . '.expire');
        }
    }

    public function exec()
    {
        timer('start', 'cache.parse');

        // Recover cached headers
        $this->_headers = @json_decode(file_get_contents("{$this->_getCachePath()}.json"), true);

        // Cache specific headers
        $this->addHeader('LP-Cache', $this->_hash);
        //$this->addHeader('Expires', date('r', $this->_expire)); // Thu, 19 Nov 1981 08:52:00 GMT
        $this->addHeader('Pragma', 'public');
        $this->addHeader('Cache-Control', 'public, max-age=' . ($this->_expire-time()));

        // Send headers
        $this->_sendHeaders();

        // Optimizations:
        //  - optimized local images, convert small images into inline base64
        //  - compress and combine local CSS & JS
        //  - get local external CSS files into the HTML output

        // TODO: compress HTML?

        echo Event::filter('cache', file_get_contents($this->_getCachePath()));

        timer('end', 'cache.parse');
    }

    protected function _getCachePath()
    {
        return LP_ROOT_DIRECTORY . '/var/cache/' . $this->_hash;
    }
}