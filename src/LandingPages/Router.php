<?php
namespace LandingPages;


class Router
{
    protected $_template_key;
    protected $_template_name;

    public function __construct()
    {
    }

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
                case 'visit': Stats::getSingleton()->visit(); break;

                // Register a conversion
                case 'conversion': Stats::getSingleton()->conversion($_GET['id'],$_GET['co']); break;
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
}
