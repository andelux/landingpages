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
    static public function parse($name, $params = array())
    {
        global $template_name, $template_variation;
        global $main_template, $main_variation;

        extract($GLOBALS);
        extract($params);

        $session = Mvc::getSession();
        $request = Mvc::getRequest();
        $response = Mvc::getResponse();

        $template_name = $name;
        $template_variation = '';

        if ( Template::hasVariations($template_name) ) {
            // Get the session variation (if the current user have seen before this template)
            $template_variation = Mvc::getModel('visits')->getSessionVariation( $template_name );

            // If current user has seen this template for first time...
            if ( ! $template_variation ) {
                // Get the less visited variation
                $template_variation = Mvc::getModel('stats')->getLessVisitedVariation( $template_name );
            }

            $template_path = LP_ROOT_DIRECTORY.'/templates/'.$template_name.'/'.$template_variation.'.php';
        } else {
            $template_path = LP_ROOT_DIRECTORY.'/templates/'.$template_name.'.php';
        }

        // Have we got the template file?
        if ( ! is_file($template_path) ) {
            // TODO: ERROR!!! 500?
        }

        // Set the main tamplate info
        if ( ! $main_template ) $main_template = $template_name;
        if ( ! $main_variation ) $main_variation = $template_variation;

        // Execute the template
        require $template_path;
    }

    /**
     * @param $name
     *
     * @return bool
     */
    static public function exists($name)
    {
        // If this is a single template or a A/B testing template... TRUE
        return is_dir(LP_ROOT_DIRECTORY.'/templates/'.$name) || is_file(LP_ROOT_DIRECTORY.'/templates/'.$name.'.php');
    }

    /**
     * @param $template
     * @param $variation
     * @return bool
     */
    static public function isVariation($template, $variation)
    {
        return is_file(LP_ROOT_DIRECTORY.'/templates/'.$template.'/'.$variation.'.php');
    }

    /**
     * @param $template
     * @return bool
     */
    static public function hasVariations($template)
    {
        return is_dir(LP_ROOT_DIRECTORY.'/templates/'.$template);
    }

    /**
     * @param $template_name
     * @return array
     */
    static public function getTemplateVariations($template_name)
    {
        $data = array();
        $path = LP_ROOT_DIRECTORY.'/templates/'.$template_name;
        if ( is_dir($path) ) {
            foreach ( glob($path.'/*.php') as $variation ) {
                $data[] = basename($variation,'.php');
            }
        }
        return $data;
    }

    /**
     * @param $name
     * @return string
     */
    static public function getTemplateUrl( $name )
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

            $chars = self::CHARS_LOWERS . self::CHARS_UPPERS . self::CHARS_DIGITS;
            for ($i = 0, $str = '', $lc = strlen($chars)-1; $i < 16; $i++) {
                $str .= $chars[mt_rand(0, $lc)];
            }

            $session->setData('_form_key', $str);

        }

        return $session->getData('_form_key');
    }

    /**
     * @return string
     */
    static public function getFormKeyHtml()
    {
        return '<input type="hidden" name="_form_key" value="'.self::getFormKey().'" />';
    }
}

