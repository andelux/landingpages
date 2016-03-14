<?php
namespace LandingPages;

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

    public function __construct()
    {

    }

    static public function init()
    {

    }

    static public function parse($name, $params = array())
    {
        global $template_name, $template_variation;
        global $main_template, $main_variation;

        extract($GLOBALS);
        extract($params);

        $template_name = $name;
        $template_variation = '';

        if ( Template::hasVariations($template_name) ) {
            // Get the session variation (if the current user have seen before this template)
            $template_variation = Stats::getSingleton()->getSessionVariation( $template_name );

            // If current user has seen this template for first time...
            if ( ! $template_variation ) {
                // Get the less visited variation
                $template_variation = Stats::getSingleton()->getLessVisitedVariation( $template_name );
            }

            $template_path = LP_ROOT_DIRECTORY.'/templates/'.$template_name.'/'.$template_variation.'.php';
        } else {
            $template_path = LP_ROOT_DIRECTORY.'/templates/'.$template_name.'.php';
        }

        // Have we got the template file?
        if ( ! is_file($template_path) ) {
            // TODO: ERROR!!!
        }

        // Set the main tamplate info
        if ( ! $main_template ) $main_template = $template_name;
        if ( ! $main_variation ) $main_variation = $template_variation;

        // Execute the template
        require $template_path;
    }

    static public function exists($name)
    {
        // If this is a single template or a A/B testing template... TRUE
        return is_dir(LANDINGS_DIR.'/templates/'.$name) || is_file(LANDINGS_DIR.'/templates/'.$name.'.php');
    }

    static public function isVariation($template, $variation)
    {
        return is_file(LANDINGS_DIR.'/templates/'.$template.'/'.$variation.'.php');
    }
    static public function hasVariations($template)
    {
        return is_dir(LANDINGS_DIR.'/templates/'.$template);
    }
    static public function getTemplateVariations($template_name)
    {
        $data = array();
        $path = LANDINGS_DIR.'/templates/'.$template_name;
        if ( is_dir($path) ) {
            foreach ( glob($path.'/*.php') as $variation ) {
                $data[] = basename($variation,'.php');
            }
        }
        return $data;
    }

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
        //global $template_name, $template_variation;
        global $main_template, $main_variation;

        return LP_BASE_URI . "post.php?name={$main_template}&variation={$main_variation}";
    }

    static public function getFormKey()
    {
        if ( ! $_SESSION['_form_key'] ) {
            $chars = self::CHARS_LOWERS . self::CHARS_UPPERS . self::CHARS_DIGITS;
            for ($i = 0, $str = '', $lc = strlen($chars)-1; $i < 16; $i++) {
                $str .= $chars[mt_rand(0, $lc)];
            }
            $_SESSION['_form_key'] = $str;
        }

        return $_SESSION['_form_key'];
    }

    static public function getFormKeyHtml()
    {
        return '<input type="hidden" name="_form_key" value="'.self::getFormKey().'" />';
    }
}

