<?php
function get_translations_file($locale)
{
    return LP_APP_DIRECTORY . "/translations/{$locale}.csv";
}
function get_translations($locale, $reload = false)
{
    static $cache = array();

    if ( $reload || ! array_key_exists($locale, $cache) ) {
        $file_path = get_translations_file($locale);

        if (!is_dir(dirname($file_path))) {
            @mkdir(dirname($file_path), 0777, true);
        }

        if (($f = @fopen($file_path, 'r')) !== false) {
            while (($row = fgetcsv($f, null, ",", "\"", "\\")) !== false) {
                @list($key, $translation, $status, $from) = $row;

                $cache[$locale][$key] = $translation;
            }
            fclose($f);
        }
    }

    return $cache[$locale];
}

function add_translation($key, $translation, $locale = null)
{
    if ( $locale === null ) $locale = LP_LOCALE;

    $config = \LandingPages\Mvc::getConfig();
    $main_template = $config->getData('template_name');
    $main_variation = $config->getData('template_variation');

    $file_path = get_translations_file($locale);

    $f = @fopen($file_path,'a+');
    if ( $f !== false ) {

        $from = '';
        if ( $main_template ) {
            $from = $main_template;
            if ($main_variation) $from .= "/{$main_variation}";
        }

        @fputcsv($f,array($key,$translation, 'UNTRANSLATED', $from),",","\"");

        fclose($f);

        // Reload translations
        get_translations($locale, true);
    }

}

function __($text)
{
    $args = func_get_args();
    $text = array_shift($args);

    array_unshift($args, LP_LOCALE);
    array_unshift($args, $text);

    return call_user_func_array('translate', $args);
}

function translate($text, $locale = null)
{
    $args = func_get_args();
    $text = array_shift($args);
    $locale = array_shift($args);
    $locale = ($locale === null ? LP_LOCALE : $locale);

    $TRANSLATIONS = get_translations($locale);

    if ( ! isset($TRANSLATIONS[$text]) ) {
        add_translation($text,$text,$locale);
        $TRANSLATIONS[$text] = $text;
    }

    array_unshift($args, $TRANSLATIONS[$text]);

    return call_user_func_array('sprintf', $args);
}

function __URL($url)
{
    return translate_url($url, LP_LOCALE);
}

function translate_url( $url, $locale = null )
{
    if ( $locale === null ) $locale = LP_LOCALE;

    $TRANSLATIONS = get_translations($locale);

    $key = "URL:{$url}";
    if ( ! isset($TRANSLATIONS[$key]) ) {
        //__ADD_TRANSLATION($key,$key);
        add_translation($key, $key, $locale);
        $TRANSLATIONS[$key] = $key;
    }

    $translation = $TRANSLATIONS[$key];

    return substr($translation,0,4) == 'URL:' ? $url : $translation;
}

function untranslate_url( $url, $locale = null )
{
    if ( $locale === null ) $locale = LP_LOCALE;

    $TRANSLATIONS = get_translations($locale);

    foreach ( $TRANSLATIONS as $key => $translation ) {
        if ( $url == $translation && preg_match('/^URL:(.*)$/', $key, $M) ) {
            return $M[1];
        }
    }

    return $url;
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

function page_url( $template_name = null, $locale = null )
{
    $url = LP_BASE_URL;
    $locale === null ? LP_LOCALE : $locale;

    $config = \LandingPages\Mvc::getConfig();

    if ( in_array('url', explode(',', $config->getData('locale.detect_methods'))) ) {
        $locale_map = array_search($locale, get_locale_url_map());
        if ( ! $locale_map ) $locale_map = $locale;
        $url .= $locale_map . '/';
    }

    if ( $template_name !== null ) {
        $url .= translate_url($template_name, $locale) . '.html';
    }

    return $url;
}

function change_locale_url($locale)
{
    $url = LP_BASE_URL;

    $config = \LandingPages\Mvc::getConfig();

    if ( in_array('url', explode(',', $config->getData('locale.detect_methods'))) ) {
        $locale_map = array_search($locale, get_locale_url_map());
        if ( ! $locale_map ) $locale_map = $locale;
        $url .= $locale_map . '/';
    }

    list($controller, $action, $params) = \LandingPages\Mvc::getDispatcher()->getCurrentToken();
    if ( ! LP_IS_HOME && $controller == 'landing' && $action == 'view' ) {
        $url .= translate_url($params['template'], $locale) . '.html';
    }

    return $url;

}

/**
 * Return the available locales
 *
 * @return array
 */
function get_enabled_locales()
{
    static $locales;

    $config = \LandingPages\Mvc::getConfig();

    if ( ! $locales ) {
        foreach (explode(',', $config->getData('locale.enabled')) as $locale) {
            $locales[] = normalize_locale_name($locale);
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
function normalize_locale_name( $locale )
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
function normalize_language( $locale )
{
    return array_shift(explode('-', normalize_locale_name($locale), 2));
}


function get_locale_url_map()
{
    static $map;

    if ( ! $map ) {
        $map = array();
        $config = \LandingPages\Mvc::getConfig()->getData();
        foreach ($config as $key => $value) if (preg_match('/^locale\.url\.map\.(.*)$/', $key, $M)) {
            $map[$M[1]] = normalize_locale_name( $value );
        }
    }

    return $map;
}

