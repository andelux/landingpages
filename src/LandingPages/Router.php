<?php
namespace LandingPages;


class Router extends Object
{


    public function __construct( Request $request )
    {
        $params = array();

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

        } else {
            // Is it a custom controller?
            $U = explode('/', LP_URI);
            $controller = array_shift($U);
            $action = trim(array_shift($U));
            while (count($U)>0) $params[array_shift($U)] = array_shift($U);

            if ( ! $controller ) {
                $controller = 'index';
                $action = 'index';
            } else if ( ! $action ) {
                $action = 'index';
            }
        }

        $this->setController( $controller );
        $this->setAction( $action );
        $this->setParams( $params );
    }

    public function getToken()
    {
        return array(
            $this->getController(),
            $this->getAction(),
            $this->getParams(),
        );
    }

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

    protected function _normalizeLocaleName( $locale )
    {
        $locale = trim($locale);
        $locale = strtolower($locale);
        $locale = str_replace('_','-',$locale);

        return $locale;
    }

    protected function _normalizeLanguage( $locale )
    {
        return array_shift(explode('-', $this->_normalizeLocaleName($locale), 2));
    }

    protected function _isEnabledLocale( $locale )
    {
        return in_array($this->_normalizeLocaleName($locale), $this->_getEnabledLocales());
    }

    /*
    public function getResponse()
    {
        $response = new Response();

        if ( ! defined('SETUP_LOADED') ) {
            return $response->addHeader('Location', LANDINGS_URI . 'install.php');
        }

        $response->addHeader('Content-Type', 'text/html; charset=utf-8');

        if ( substr(URI,-5) == '.html' ) {

            // name = "sem/landing-name"
            $this->_template_key = substr(URI,0,-5);

            // Translating "sem/landing-name" to "sem/my-real-template-file" as is in translations files ("translations/en_US.csv")
            $this->_template_name = __URL($this->_template_key);

            if ( isset($_GET['stats']) ) {

                // If URL is http://mysite.com/landing/whatever-you-want/?stats
                $response->setTemplate('_stats');
                $response->setParam('result', \LandingPages\Database::db()->query("
                    SELECT variation,conversion_type,conversions,rate
                    FROM conversions
                    WHERE conversions.template = \"{$this->_template_name}\"
                    ORDER BY rate DESC, conversions DESC
                "));

            } else if ( isset($_GET['visits']) ) {

                // If URL is http://mysite.com/landing/whatever-you-want/?visits
                $response->setTemplate('_visits');
                $response->setParam('result', \LandingPages\Database::db()->query("
                    SELECT *
                    FROM visits
                    WHERE visits.template = \"{$this->_template_name}\"
                    ORDER BY id DESC
                "));

            } else if ( Template::exists($this->_template_name) ) {

                // Load template with its language
                $response->setTemplate( $this->_template_name );

            } else {

                // ERROR 404
                $response->addHeader('HTTP/1.0 404 Not Found');
                $response->setTemplate('_404');

            }

        } else if ( URI == 'stats.png' ) {

            // Usage statistics
            switch ( $_GET['ac'] ) {
                // Register a visit
                case 'visit': Stats::visit(); break;

                // Register a conversion
                case 'conversion': Stats::conversion($_GET['id'],$_GET['co']); break;
            }

            // Return the PNG pixel image
            $response->setBinaryFile('images/pixel.png', 70, 'image/png');
        } else {
            // ERROR 404
            $response->addHeader('HTTP/1.0 404 Not Found');
            $response->setTemplate('_404');
        }

        return $response;
    }
    */
}
