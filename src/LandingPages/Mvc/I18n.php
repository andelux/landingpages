<?php
namespace LandingPages\Mvc;

use LandingPages\Mvc;
use LandingPages\Template;

class I18n
{
    protected static $_cache;
    protected static $_instance;

    protected function _getTranslationsFile($locale)
    {
        return LP_APP_DIRECTORY . "/translations/{$locale}.csv";
    }

    static public function getSingleton()
    {
        return self::$_instance ? self::$_instance : (self::$_instance = new self());
    }

    public function getTranslations($locale, $reload = false)
    {
        if ( $reload || ! array_key_exists($locale, self::$_cache) ) {
            $file_path = $this->_getTranslationsFile($locale);

            if (!is_dir(dirname($file_path))) {
                @mkdir(dirname($file_path), 0777, true);
            }

            if (($f = @fopen($file_path, 'r')) !== false) {
                while (($row = fgetcsv($f, null, ",", "\"", "\\")) !== false) {
                    @list($key, $translation, $status, $from) = $row;

                    self::$_cache[$locale][$key] = $translation;
                }
                fclose($f);
            }
        }

        return self::$_cache[$locale];
    }

    protected function _addTranslation($key, $translation, $locale = null)
    {
        if ( $locale === null ) $locale = LP_LOCALE;

        $config = Mvc::getConfig();
        $main_template = $config->getData('template_name');
        $main_variation = $config->getData('template_variation');

        $file_path = $this->_getTranslationsFile($locale);

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
            if ( array_key_exists($locale, self::$_cache) ) self::$_cache[$locale][$key] = $translation;
        }
    }

    public function translate($text, $locale = null)
    {
        $args = func_get_args();
        $text = array_shift($args);
        $locale = array_shift($args);
        $locale = ($locale === null ? LP_LOCALE : $locale);

        $TRANSLATIONS = $this->getTranslations($locale);

        if ( ! isset($TRANSLATIONS[$text]) ) {
            $this->_addTranslation($text,$text,$locale);
            $TRANSLATIONS[$text] = $text;
        }

        array_unshift($args, $TRANSLATIONS[$text]);

        return call_user_func_array('sprintf', $args);
    }

    public function translateUrl( $url, $locale = null )
    {
        if ( $locale === null ) $locale = LP_LOCALE;

        $TRANSLATIONS = $this->getTranslations($locale);

        $key = "URL:{$url}";
        if ( ! isset($TRANSLATIONS[$key]) ) {
            $this->_addTranslation($key, $key, $locale);
            $TRANSLATIONS[$key] = $key;
        }

        $translation = $TRANSLATIONS[$key];

        return substr($translation,0,4) == 'URL:' ? $url : $translation;
    }

    public function untranslateUrl( $url, $locale = null )
    {
        if ( $locale === null ) $locale = LP_LOCALE;

        $TRANSLATIONS = $this->getTranslations($locale);

        foreach ( $TRANSLATIONS as $key => $translation ) {
            if ( $url == $translation && preg_match('/^URL:(.*)$/', $key, $M) ) {
                return $M[1];
            }
        }

        return $url;
    }

}