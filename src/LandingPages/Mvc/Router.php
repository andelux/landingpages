<?php
namespace LandingPages\Mvc;

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
        $params = array();

        // If the URL ends with ".html" then this is a landing page
        if ( preg_match('/^(.*)\.html$/', LP_URI, $M) ) {

            // It's a landing page!
            // Get the current locale and the template name
            list($locale, $template) = $this->_getLocaleTemplate( $M[1] );

            $request->setLocale( $locale );
            $request->setTemplateKey( $template );

            $controller = 'landing';
            if ( isset($_GET['stats']) ) {
                $action = 'stats';
            } else if ( isset($_GET['visits']) ) {
                $action = 'visits';
            } else {
                $action = 'view';
            }

        // If this is not a landing page... what is it?
        } else {

            // Get controller and action
            $U = explode('/', LP_URI);
            $controller = array_shift($U);
            $action = trim(array_shift($U));

            // ...and params
            while (count($U)>0) $params[array_shift($U)] = array_shift($U);

            // Set defaults
            if ( ! $controller ) {
                $controller = 'index';
                $action = 'index';
            } else if ( ! $action ) {
                $action = 'index';
            }

        }

        // Set the extracted variables
        $this->setController( $controller );
        $this->setAction( $action );
        $this->setParams( $params );
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
     * Get locale & template name from the $uri string
     *
     * @param $uri
     *
     * @return array
     */
    protected function _getLocaleTemplate( $uri )
    {
        $locale = null;
        $detect_methods = explode(',', LP_LOCALE_DETECT_METHODS);
        while ( $locale === null && ($detect_method = array_shift($detect_methods)) ) {
            switch ( trim($detect_method) ) {
                case 'url':
                    // language detected in URL
                    if ( preg_match('/^([a-z_\-]{2,7})\/(.*)$/', $uri, $L) ) {
                        if ($this->_isEnabledLocale($L[1])) {
                            $locale = $this->_normalizeLocaleName($L[1]);
                            $uri = $L[2];
                        }
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
        if ( $locale === null ) $locale = LP_LOCALE_DEFAULT;

        // Setup locale & translations
        define('LP_LOCALE', $locale);
        define('LP_LANGUAGE', $this->_normalizeLanguage($locale));
        __LOAD_TRANSLATIONS();

        // Translate URI to get the right template
        $template = __URL($uri);

        // Return locale and template name
        return array($locale, $template);
    }

    /**
     * Return the available locales
     *
     * @return array
     */
    protected function _getEnabledLocales()
    {
        static $locales;

        if ( ! $locales ) {
            foreach (explode(',', LP_LOCALE_ENABLED) as $locale) {
                $locales[] = $this->_normalizeLocaleName($locale);
            }
        }

        return $locales;
    }

    /**
     * Normalize the locale name
     *
     * @param $locale
     *
     * @return mixed|string
     */
    protected function _normalizeLocaleName( $locale )
    {
        $locale = trim($locale);
        $locale = strtolower($locale);
        $locale = str_replace('_','-',$locale);

        return $locale;
    }

    /**
     * Normalize the language name from a locale name
     *
     * @param $locale
     *
     * @return mixed
     */
    protected function _normalizeLanguage( $locale )
    {
        return array_shift(explode('-', $this->_normalizeLocaleName($locale), 2));
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
        return in_array($this->_normalizeLocaleName($locale), $this->_getEnabledLocales());
    }

}
