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
        // Get locale and URI (based on locale)
        list($locale, $uri) = $this->_getLocale(LP_URI);

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

    protected function _getToken( $uri )
    {
        $config = Mvc::getConfig();

        // Have we a URI?
        if ( $uri ) {

            // Is a landing template?
            if ( preg_match('/^(.*)\.html$/', LP_URI, $M) ) {
                // Translate URI to get the right template
                return $this->_getLandingToken( __URL($M[1]) );
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
        return array(
            $this->getController(),
            $this->getAction(),
            $this->getParams(),
        );
    }

    /**
     * @param $uri
     * @return array|mixed|null|string
     */
    protected function _getLocale( $uri )
    {
        $config = Mvc::getConfig();

        $locale = null;
        $detect_methods = explode(',', $config->getData('locale.detect_methods'));
        while ( $locale === null && ($detect_method = array_shift($detect_methods)) ) {
            switch ( trim($detect_method) ) {
                case 'url':
                    // language detected in URL
                    if ( preg_match('/^([a-z_\-]{2,7})\/?(.*)$/', $uri, $L) && $this->_isEnabledLocale($L[1])) {
                        $locale = normalize_locale_name($L[1]);
                        $uri = "{$L[2]}";
                    } else if ( preg_match('/^([^\/]*)\/?(.*)$/', $uri, $L) && ($matched = $config->getData("locale.url.map.{$L[1]}")) && $this->_isEnabledLocale($matched) ) {
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

                case 'browser':
                    // TODO: get locale from HTTP headers (Accept-Languages)
                    break;
            }
        }

        // If no one was detected then we use the default one
        if ( $locale === null ) $locale = normalize_locale_name($config->getData('locale.default'));

        // Setup locale & translations
        define('LP_LOCALE', $locale);
        define('LP_LANGUAGE', normalize_language($locale));
        __LOAD_TRANSLATIONS();

        return array($locale, $uri);
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
