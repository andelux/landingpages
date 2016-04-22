<?php
namespace LandingPages;

use LandingPages\Model\Stats;
use LandingPages\Model\Visits;

class Template
{
    const CHARS_LOWERS                          = 'abcdefghijklmnopqrstuvwxyz';
    const CHARS_UPPERS                          = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    const CHARS_DIGITS                          = '0123456789';
    const CHARS_SPECIALS                        = '!$*+-.=?@^_|~';
    const CHARS_PASSWORD_LOWERS                 = 'abcdefghjkmnpqrstuvwxyz';
    const CHARS_PASSWORD_UPPERS                 = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
    const CHARS_PASSWORD_DIGITS                 = '23456789';
    const CHARS_PASSWORD_SPECIALS               = '!$*-.=?@_';

    /**
     * @param $name
     * @param array $params
     */
    static public function parse($template_name, $params = array())
    {
        if ( is_array($GLOBALS) ) extract($GLOBALS);
        if ( is_array($params) ) extract($params);

        $session = Mvc::getSession();
        $request = Mvc::getRequest();
        $response = Mvc::getResponse();
        $config = Mvc::getConfig();
        $router = Mvc::getRouter();
        $dispatcher = Mvc::getDispatcher();

        foreach ( array(LP_APP_DIRECTORY, LP_DEFAULT_APP_DIRECTORY, LP_ROOT_DIRECTORY) as $_dir ) {
            $template_path = "{$_dir}/templates/{$template_name}.php";
            if ( is_file($template_path) ) {
                // Execute the template
                require $template_path;
                return;
            }
        }

        throw new \Exception("Template not found: /templates/{$template_name}.php");
    }

    /**
     * @param $name
     *
     * @return bool
     */
    static public function exists($name)
    {
        // If this is a single template or a A/B testing template... TRUE
        return is_dir(LP_APP_DIRECTORY.'/templates/'.$name) || is_file(LP_APP_DIRECTORY.'/templates/'.$name.'.php')
            || is_dir(LP_DEFAULT_APP_DIRECTORY.'/templates/'.$name) || is_file(LP_DEFAULT_APP_DIRECTORY.'/templates/'.$name.'.php')
            || is_dir(LP_ROOT_DIRECTORY.'/templates/'.$name) || is_file(LP_ROOT_DIRECTORY.'/templates/'.$name.'.php');
    }

    /**
     * @param $template
     * @param $variation
     * @return bool
     */
    static public function isVariation($template, $variation)
    {
        return is_file(LP_APP_DIRECTORY.'/templates/'.$template.'/'.$variation.'.php')
            || is_file(LP_DEFAULT_APP_DIRECTORY.'/templates/'.$template.'/'.$variation.'.php')
            || is_file(LP_ROOT_DIRECTORY.'/templates/'.$template.'/'.$variation.'.php');
    }

    /**
     * @param $template
     * @return bool
     */
    static public function hasVariations($template)
    {
        return is_dir(LP_APP_DIRECTORY.'/templates/'.$template)
            || is_dir(LP_DEFAULT_APP_DIRECTORY.'/templates/'.$template)
            || is_dir(LP_ROOT_DIRECTORY.'/templates/'.$template);
    }

    /**
     * @param $template_name
     * @return array
     */
    static public function getTemplateVariations($template_name)
    {
        $data = array();

        foreach ( array(LP_APP_DIRECTORY,LP_DEFAULT_APP_DIRECTORY,LP_ROOT_DIRECTORY) as $prefix ) {
            $path = "{$prefix}/templates/{$template_name}";
            if ( is_dir($path) ) {
                foreach ( glob($path.'/*.php') as $variation ) {
                    $basename = basename($variation,'.php');
                    if ( ! in_array($basename, $data) ) {
                        $data[] = $basename;
                    }
                }
            }
        }

        return $data;
    }

    /**
     * @deprecated functions.php/page_url()
     *
     * @param $name
     * @return string
     */
    static public function getTemplateUrl( $name, $locale = null )
    {
        $uri = $name;

        // TODO: get reverse translation of $name
        // TODO: add language in URI (?)
        // TODO: add scheme and domain path

        return LP_BASE_URI . $uri . '.html';
    }

    /**
     * Return the URL to the form processor
     *
     * @return string
     */
    static public function getFormAction()
    {
        return LP_URL.'?post';
    }

    /**
     * @return null
     */
    static public function getFormKey()
    {
        $session = Mvc::getSession();

        if ( ! $session->issetData('_form_key') || ! $session->getData('_form_key') ) {

            $session->setData('_form_key', generate_form_key());

        }

        return $session->getData('_form_key');
    }

    /**
     * @return string
     */
    static public function getFormKeyHtml()
    {
        $form_key = defined('LP_CACHE_GENERATOR_MODE') ? '{{FORM_KEY}}' : self::getFormKey();

        return "<input type=\"hidden\" name=\"_form_key\" value=\"{$form_key}\" />";
    }
}

