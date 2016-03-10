<?php
// TRANSLATION FUNCTION
function __TRANSLATION_FILE_PATH(){
    $landings_language = defined('LANDINGS_LANGUAGE') ? LANDINGS_LANGUAGE : 'es_ES';
    $translations_path = LANDINGS_DIR . '/translations';
    $translations_file = "{$translations_path}/{$landings_language}.csv";
    return $translations_file;
}

function __LOAD_TRANSLATIONS()
{
    global $TRANSLATIONS;

    $TRANSLATIONS = array();

    $f = @fopen(__TRANSLATION_FILE_PATH(),'r');
    while ( ($row=fgetcsv($f,null,",","\"","\\")) !== false ) {
        @list( $key, $translation, $status, $from ) = $row;

        $TRANSLATIONS[$key] = $translation;
    }
    fclose($f);
}

function __ADD_TRANSLATION($key, $translation)
{
    global $template_name, $template_variation;

    $translation_file_path = __TRANSLATION_FILE_PATH();
    $f = @fopen($translation_file_path,'a+');
    if ( $f !== false ) {

        $from = '';
        if ( $template_name ) {
            $from = $template_name;
            if ($template_variation) $from .= "/{$template_variation}";
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
        // No existe traducción, la guardamos en el fichero de idioma
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
        // No existe traducción, la guardamos en el fichero de idioma
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

function stats_pixel()
{
    \LandingPages\Stats::getHtmlPixel();
}
function stats_id_to_time($id)
{
    return \LandingPages\Stats::idToTime($id);
}

__LOAD_TRANSLATIONS();
