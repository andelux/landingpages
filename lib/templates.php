<?php
// TRANSLATION FUNCTION
function __TRANSLATION_FILE_PATH(){
	$landings_language = defined('LANDINGS_LANGUAGE') ? LANDINGS_LANGUAGE : 'es_ES';
	$translations_path = LANDINGS_DIR . '/translations';
	$translations_file = "{$translations_path}/{$landings_language}.csv";
	return $translations_file;
}

function __LOAD_TRANSLATIONS(){
	global $TRANSLATIONS;
	$TRANSLATIONS = array();
	$f = @fopen(__TRANSLATION_FILE_PATH(),'r');
	while ( ($row=fgetcsv($f,null,",","\"","\\")) !== false ) $TRANSLATIONS[$row[0]] = $row[1];
	fclose($f);
}

function __ADD_TRANSLATION($text, $translation){
	$f = @fopen(__TRANSLATION_FILE_PATH(),'a');
	if ( $f !== false ) {
		fputcsv($f,array($text,$translation),",","\"");
		fclose($f);
	}
}

function __($text){
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

function __URL($url){
	$translated = __("URL:{$url}");
	if ( substr($translated,0,4) == 'URL:' ) {
		return $url;
	} else {
		return $translated;
	}
}

function is_template($name){
	// ¿Es una plantilla con variantes A/B?
	if ( is_dir(LANDINGS_DIR.'/templates/'.$name) ) {
		return true;
	}

	// ¿Es una plantilla simple?
	if ( is_file(LANDINGS_DIR.'/templates/'.$name.'.php') ) {
		return true;
	}

	// No es una plantilla válida
	return false;
}

function template($name, $params = array())
{
	global $template_name, $template_variation;
	global $main_template, $main_variation;

	extract($GLOBALS);
	extract($params);

	$template_path = LANDINGS_DIR.'/templates/'.$name.'.php';
	$template_name = $name;
	$template_variation = '';

	if ( ! $main_template ) $main_template = $name;

	// ¿Es una plantilla con variantes A/B?
	if ( is_dir(LANDINGS_DIR.'/templates/'.$name) ) {
		// ¿Esta sesión ya usa una variación?
		$template_variation = stats_get_session_variation($name);
		if ( ! $template_variation ) {
			// Se usa la variación menos visitada
			// TODO: se podría querer usar la que tiene mejor conversión, pero es mejor ir eliminando las variaciones que menos convierten
			$template_variation = stats_get_less_visited_variation( $name );
		}
		$template_path = LANDINGS_DIR.'/templates/'.$name.'/'.$template_variation.'.php';
		if ( ! $main_variation ) $main_variation = $template_variation;
	}

	// Ejecutamos la plantilla
	require $template_path;
}

function is_variation($template, $variation)
{
	return is_file(LANDINGS_DIR.'/templates/'.$template.'/'.$variation.'.php');
}

function get_template_variations($template_name)
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

/**
 * Return the URL to the form processor
 *
 * @return string
 */
function get_form_action()
{
	global $template_name, $template_variation;
	return LANDINGS_URI . "post.php?name={$template_name}&variation={$template_variation}";
}

__LOAD_TRANSLATIONS();
