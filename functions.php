<?php
function __TRANSLATION_FILE_PATH()
{
    $locale = LP_LOCALE;
    $translations_path = LP_APP_DIRECTORY . '/translations';
    $translations_file = "{$translations_path}/{$locale}.csv";
    return $translations_file;
}

function __LOAD_TRANSLATIONS()
{
    global $TRANSLATIONS;

    $TRANSLATIONS = array();

    $file_path = __TRANSLATION_FILE_PATH();
    if ( ! is_dir(dirname($file_path)) ) {
        @mkdir(dirname($file_path), 0777, true);
    }

    if ( ($f = @fopen($file_path,'r')) !== false ) {
        while (($row = fgetcsv($f, null, ",", "\"", "\\")) !== false) {
            @list($key, $translation, $status, $from) = $row;

            $TRANSLATIONS[$key] = $translation;
        }
        fclose($f);
    }
}

function __ADD_TRANSLATION($key, $translation)
{
    $config = \LandingPages\Mvc::getConfig();
    $main_template = $config->getData('template_name');
    $main_variation = $config->getData('template_variation');

    $translation_file_path = __TRANSLATION_FILE_PATH();
    $f = @fopen($translation_file_path,'a+');
    if ( $f !== false ) {

        $from = '';
        if ( $main_template ) {
            $from = $main_template;
            if ($main_variation) $from .= "/{$main_variation}";
        }

        @fputcsv($f,array($key,$translation, 'UNTRANSLATED', $from),",","\"");

        fclose($f);
    }
}

function __($text)
{
    global $TRANSLATIONS;

    $args = func_get_args();
    $text = array_shift($args);

    if ( ! isset($TRANSLATIONS[$text]) ) {
        __ADD_TRANSLATION($text,$text);
        $TRANSLATIONS[$text] = $text;
    }

    array_unshift($args, $TRANSLATIONS[$text]);

    return call_user_func_array('sprintf', $args);
}

function __URL($url)
{
    global $TRANSLATIONS;

    $key = "URL:{$url}";
    if ( ! isset($TRANSLATIONS[$key]) ) {
        __ADD_TRANSLATION($key,$key);
        $TRANSLATIONS[$key] = $key;
    }

    $translation = $TRANSLATIONS[$key];

    return substr($translation,0,4) == 'URL:' ? $url : $translation;
}

function template($name, $params = array())
{
    \LandingPages\Template::parse($name, $params);
}

function get_form_action()
{
    return \LandingPages\Template::getFormAction();
}
function form_key_html()
{
    return \LandingPages\Template::getFormKeyHtml();
}

function form_begin( $conversion_key = null )
{
    global $form_begin;

    $form_begin = true;

    echo "<form class=\"landing-pages-form\" action=\"".\LandingPages\Template::getFormAction()."\" method=\"post\">";

    echo \LandingPages\Template::getFormKeyHtml();

    if ( $conversion_key !== null ) {
        echo "<input type=\"hidden\" name=\"_CONVERSION\" value=\"{$conversion_key}\" />";
    }
}
function form_end()
{
    echo "</form>";
}
function is_form()
{
    global $form_begin;

    return isset($form_begin) && $form_begin;
}

function stats_pixel()
{
    \LandingPages\Model\Stats::getHtmlPixel();
}
function stats_id_to_time($id)
{
    return \LandingPages\Model\Stats::idToTime($id);
}

function uc_words($str, $destSep='_', $srcSep='_')
{
    return str_replace(' ', $destSep, ucwords(str_replace($srcSep, ' ', $str)));
}
function from_camel_case($input) {
    preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $input, $matches);
    $ret = $matches[0];
    foreach ($ret as &$match) {
        $match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
    }
    return implode('_', $ret);
}

function asset($path)
{
    if ( is_file(LP_APP_DIRECTORY.'/'.ltrim($path,'/')) ) return LP_APP_URI.ltrim($path,'/');
    if ( is_file(LP_DEFAULT_APP_DIRECTORY.'/'.ltrim($path,'/')) ) return LP_DEFAULT_APP_URI.ltrim($path,'/');
    if ( is_file(LP_ROOT_DIRECTORY.'/'.ltrim($path,'/')) ) return LP_BASE_URI.ltrim($path,'/');
    return $path;
}