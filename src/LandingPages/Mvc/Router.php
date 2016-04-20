<?php
namespace LandingPages\Mvc;

use LandingPages\Mvc;
use LandingPages\Object;

/**
 * Class Router
 *
 * @method Router setController( string )
 * @method Router setAction( string )
 * @method Router setParams( array )
 *
 * @package LandingPages\Mvc
 */
class Router extends Object
{

    /**
     * Router constructor.
     *
     * @param Request $request
     */
    public function __construct( Request $request )
    {
        if ( $this->_is404(LP_URI) ) return;

        // Get locale and URI (based on locale)
        list($locale, $uri) = $this->_getLocale(LP_URI);
        define('LP_IS_HOME', $uri == '' ? true : false);

        // Get controller and action
        $U = @explode('/', $this->_getToken($uri));
        $controller = array_shift($U);
        $action = trim(array_shift($U));

        // ...and params
        $params = array();
        while (count($U)>0) $params[array_shift($U)] = array_shift($U);

        // Set defaults
        if ( ! $action ) $action = 'index';

        // Setup request
        $request->setLocale( $locale );
        $request->setController( $controller );
        $request->setAction( $action );

        // Set the extracted variables
        $this->setController( $controller );
        $this->setAction( $action );
        $this->setParams( $params );
    }

    protected function _is404( $uri )
    {
        switch ( $uri ) {
            case 'favicon.ico':
            case 'robots.txt':
                return true;
        }

        return false;
    }

    protected function _getToken( $uri )
    {
        $config = Mvc::getConfig();

        // Have we a URI?
        if ( $uri ) {

            // Is a landing template?
            if ( preg_match('/^(.*)\.html$/', $uri, $M) ) {
                // Translate URI to get the right template
                //return $this->_getLandingToken( __URL($M[1]) );
                return $this->_getLandingToken( untranslate_url($M[1]) );
            }

            // ...else it should be a token
            return $uri;

        }

        // Check home.landing (template)
        if ( $template = $config->getData('home.landing') ) {
            return $this->_getLandingToken( $template );
        }

        // Check home.token (controller/action)
        if ( $token = $config->getData('home.token') ) {
            return $token;
        }

        return 'index/index';
    }

    /**
     * @return string
     */
    protected function _getLandingToken( $template )
    {
        $controller = 'landing';
        if ( isset($_GET['stats']) ) {
            $action = 'stats';
        } else if ( isset($_GET['visits']) ) {
            $action = 'visits';
        } else if ( isset($_GET['post']) && count($_POST) > 0 ) {
            $action = 'post';
        } else {
            $action = 'view';
        }

        return "{$controller}/{$action}/template/{$template}";
    }


    /**
     * Get the token that Router has detected
     *
     * @return array
     */
    public function getToken()
    {
        return $this->getController()
            ? array(
                $this->getController(),
                $this->getAction(),
                $this->getParams(),
            )
            : null;
    }

    /**
     * @param $uri
     * @return array|mixed|null|string
     */
    protected function _getLocale( $uri )
    {
        $config = Mvc::getConfig();

        $locale = null;

        if ( substr(LP_URI,-10) == '/pixel.png' ) {
            // Set the default locale
            $locale = normalize_locale_name($config->getData('locale.default'));
        } else {
            $detect_methods = explode(',', $config->getData('locale.detect_methods'));
            while ($locale === null && ($detect_method = array_shift($detect_methods))) {
                switch (trim($detect_method)) {
                    // Store detected in URL by the first element
                    case 'url':
                        if (preg_match('/^([a-z_\-]{2,7})\/?(.*)$/', $uri, $L) && $this->_isEnabledLocale($L[1])) {
                            $locale = normalize_locale_name($L[1]);
                            $uri = "{$L[2]}";
                        } else if (preg_match('/^([^\/]*)\/?(.*)$/', $uri, $L) && ($matched = $config->getData("locale.url.map.{$L[1]}")) && $this->_isEnabledLocale($matched)) {
                            $locale = normalize_locale_name($matched);
                            $uri = "{$L[2]}";
                        }
                        break;

                    case 'domain':
                        $domain = $_SERVER['SERVER_NAME'];
                        // TODO: get locale from domain though a map array
                        break;

                    case 'geoip':
                        // TODO: get locale from country/region
                        break;

                    // Get locale from browser HTTP headers (Accept-Languages)
                    case 'browser':
                        $languages = array();
                        foreach (explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']) as $item) {
                            if (preg_match('/^([^;]*);?q?=?(.*)$/', $item, $M)) {
                                $lang = normalize_locale_name(trim($M[1]));
                                $q = round(($M[2] ? @floatval($M[2]) : 1) * 100);
                                $languages["$q"][] = $lang;
                            }
                        }
                        krsort($languages);
                        while (!$locale && $languages) {
                            $langs = array_shift($languages);
                            while (!$locale && $langs) {
                                $lang = array_shift($langs);
                                if ($this->_isEnabledLocale($lang)) {
                                    $locale = $lang;

                                    // Need we redirect to the right URL after browser detection?
                                    $this->_needToRedirect('browser', $locale);
                                }
                            }
                        }
                        break;

                    // Locale stored in session
                    case 'session':
                        if (Mvc::getSession()->issetData('locale') && $this->_isEnabledLocale(Mvc::getSession()->getData('locale'))) {
                            $locale = Mvc::getSession()->getData('locale');
                        }
                        break;
                }
            }

            // If no one was detected then we use the default one
            if ( $locale === null ) {
                // Set the default locale
                $locale = normalize_locale_name($config->getData('locale.default'));

                // Need we redirect to the right URL?
                $this->_needToRedirect('default',$locale);
            }
        }

        // Setup locale & translations
        define('LP_LOCALE', $locale);
        define('LP_LANGUAGE', normalize_language($locale));

        return array($locale, $uri);
    }

    protected function _needToRedirect( $when, $locale )
    {
        $config = Mvc::getConfig();

        $detect_methods = explode(',', $config->getData('locale.detect_methods'));
        if ( in_array('url', $detect_methods) && $config->getData('locale.url_redirect_after.'.$when) ) {
            $url_key = array_search($locale, get_locale_url_map());
            if ( $url_key ) {
                header('Location: '.LP_BASE_URL.$url_key.'/');
                exit();
            }
        }
    }

    /**
     * Return if the locale is enabled
     *
     * @param $locale
     *
     * @return bool
     */
    protected function _isEnabledLocale( $locale )
    {
        return in_array(normalize_locale_name($locale), get_enabled_locales());
    }

}
