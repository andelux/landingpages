<?php
function __($text)
{
    $args = func_get_args();
    $text = array_shift($args);

    array_unshift($args, LP_LOCALE);
    array_unshift($args, $text);

    return call_user_func_array(array(new \LandingPages\Mvc\I18n(),'translate'), $args);
}

function __URL($url)
{
    return call_user_func_array(array(new \LandingPages\Mvc\I18n(),'translateUrl'), array($url,LP_LOCALE));
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
    $locale = ($locale === null ? LP_LOCALE : $locale);

    $config = \LandingPages\Mvc::getConfig();

    if ( in_array('url', explode(',', $config->getData('locale.detect_methods'))) ) {
        $locale_map = array_search($locale, get_locale_url_map());
        if ( ! $locale_map ) $locale_map = $locale;
        $url .= $locale_map . '/';
    }

    if ( $template_name ) {
        $i18n = new \LandingPages\Mvc\I18n();
        $url .= $i18n->translateUrl($template_name, $locale) . '.html';
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
        $i18n = new \LandingPages\Mvc\I18n();
        $url .= $i18n->translateUrl($params['template'], $locale) . '.html';
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

function timer( $action, $scope = null )
{
    static $register = array();

    if ( ! LP_DEBUG ) return;

    if ( $action == 'print' ) {
        if ( $scope === null ) {
            $scopes = array_keys($register);
        } else {
            $scopes = array($scope);
        }
        echo '<pre>';
        foreach ( $scopes as $scope ) {
            printf("%15s: %-.4fs\n", $scope, $register[$scope]['end'] - $register[$scope]['start']);
        }
        echo '</pre>';
        return;
    }

    if ( $action == 'start' && isset($register[$scope]['start']) ) {
        return;
    }

    $register[$scope][$action] = microtime(true);
}