<?php
function stats_init()
{
	global $statsdb;

	//$install = ! is_file('stats.db');
	//$statsdb = sqlite_open('stats.db', 0666, $error);
	/** @var PDO $statsdb */
	$statsdb = new PDO('sqlite:stats.db');
	// Set errormode to exceptions
	$statsdb->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

	// TODO: check if stats.db exists... and if not exec the following code

	//if ( $install ) {
		// VISITAS
		//  - id        : 12871781
		//  - uri       : sem/concurso-traje-buceo.html
		//  - template  : sem-concursotrajebuceo-201503
		//  - variation : A
		//  - conversion: lead
		$statsdb->exec("
			CREATE TABLE IF NOT EXISTS visits (
				id INTEGER PRIMARY KEY NOT NULL,
				uri text NOT NULL,
				template text NOT NULL,
				variation text NOT NULL,
				conversion text NULL
			)
		");

		// VISTAS Y CONVERSIONES POR PLANTILLA/VARIACIÓN
		//  - template      : sem-concursotrajebuceo-201503
		//  - variation     : A
		//  - views         : 436
		//  - conversions   : 5
		$statsdb->exec("
			CREATE TABLE IF NOT EXISTS templates (
				template text NOT NULL,
				variation text NOT NULL,
				views integer NOT NULL,
				conversions integer NOT NULL
			)
		");

		// CONVERSIONES OBTENIDAS POR PLANTILLA/VARIANTE
		//  - template          : sem-concursotrajebuceo-201503
		//  - variation         : A
		//  - conversion_type   : lead
		//  - conversions       : 326
		//  - rate              : 134 (1,34%)
		$statsdb->exec("
			CREATE TABLE IF NOT EXISTS conversions (
				template text NOT NULL,
				variation text NOT NULL,
				conversion_type NOT NULL,
				conversions integer NOT NULL,
				rate integer NOT NULL
			)
		");
	//}

	return true;
}

function stats_generate_id()
{
	return floor( microtime(true) * 100 );
}
function stats_id_to_time($id)
{
	return floor( $id / 100 );
}
function stats_visit()
{
	global $statsdb;

	$id = doubleval($_SESSION['visit_id']);
	$uri = $_SERVER['HTTP_REFERER'];
	$template = $_GET['la'];
	if ( ! is_template($template) ) return false;
	$variation = $_GET['va'];
	if ( $variation && ! is_variation($template, $variation) ) return false;

	// Registro visita general
	$visit = $statsdb->query("SELECT * FROM visits WHERE id = $id;")->fetchObject();
	if ( ! $visit ) {
		$stmt = $statsdb->prepare( "INSERT INTO visits (id,uri,template,variation,conversion) VALUES (:id,:uri,:template,:variation,NULL);" );
		$stmt->bindParam( ':id', $id, SQLITE3_INTEGER );
		$stmt->bindParam( ':uri', $uri );
		$stmt->bindParam( ':template', $template );
		$stmt->bindParam( ':variation', $variation );
		//$stmt->bindParam(':conversion', null);
		$stmt->execute();
	}

	// Registro visitas plantilla
	$result = $statsdb->query("SELECT views FROM templates WHERE template = '$template' AND variation = '$variation';");
	if ( $row = $result->fetchObject() ) {
		$views = $row->views + 1;

		$stmt = $statsdb->prepare("UPDATE templates SET views = :views WHERE template = :template AND variation = :variation;");
		$stmt->bindParam(':template', $template);
		$stmt->bindParam(':variation', $variation);
		$stmt->bindParam(':views', $views, SQLITE3_INTEGER);
		$stmt->execute();

		// Actualizamos ratios de conversión
		$result = $statsdb->query("SELECT conversion_type,conversions FROM conversions WHERE template = '$template' AND variation = '$variation';");
		if ( $row = $result->fetchObject() ) {
			$rate = round((doubleval($row->conversions) / $views) * 10000);

			$stmt = $statsdb->prepare("UPDATE conversions SET rate = :rate WHERE template = :template AND variation = :variation AND conversion_type = :conversion_type;");
			$stmt->bindParam(':template', $template);
			$stmt->bindParam(':variation', $variation);
			$stmt->bindParam(':conversion_type', $row->conversion_type);
			$stmt->bindParam(':rate', $rate, SQLITE3_INTEGER);
			$stmt->execute();
		}

	} else {
		$stmt = $statsdb->prepare("INSERT INTO templates (template,variation,views,conversions) VALUES (:template,:variation,1,0);");
		$stmt->bindParam(':template', $template);
		$stmt->bindParam(':variation', $variation);
		$stmt->execute();
	}

	return true;
}
function stats_conversion($id, $conversion)
{
	global $statsdb;

	$id = doubleval($id);

	$stmt = $statsdb->prepare("UPDATE visits SET conversion = :conversion WHERE id = :id");
	$stmt->bindParam(':conversion', $conversion);
	$stmt->bindParam(':id', $id);
	$stmt->execute();

	$visit = $statsdb->query("SELECT * FROM visits WHERE id = $id;")->fetchObject();

	$stats = $statsdb->query("SELECT views,conversions FROM templates WHERE templates.template = '$visit->template' AND templates.variation = '$visit->variation';")->fetchObject();

	$stmt = $statsdb->prepare("UPDATE templates SET conversions = :conversions WHERE template = :template AND variation = :variation;");
	$conversions = $stats->conversions + 1;
	$stmt->bindParam(':template', $visit->template);
	$stmt->bindParam(':variation', $visit->variation);
	$stmt->bindParam(':conversions', $conversions);
	$stmt->execute();

	// Registro conversiones plantilla
	$result = $statsdb->query("SELECT conversions.conversions FROM conversions WHERE conversions.template = '$visit->template' AND conversions.variation = '$visit->variation' AND conversions.conversion_type = '$conversion';");
	if ( $row = $result->fetchObject() ) {
		$conversions = $row->conversions + 1;
		$rate = round(($conversions / $stats->views) * 10000);

		$stmt = $statsdb->prepare("UPDATE conversions SET conversions = :conversions, rate = :rate WHERE template = :template AND variation = :variation AND conversion_type = :conversion_type;");
		$stmt->bindParam(':template', $visit->template);
		$stmt->bindParam(':variation', $visit->variation);
		$stmt->bindParam(':conversion_type', $conversion);
		$stmt->bindParam(':conversions', $conversions, SQLITE3_INTEGER);
		$stmt->bindParam(':rate', $rate, SQLITE3_INTEGER);
		$stmt->execute();
	} else {
		$conversions = 1;
		$rate = round(($conversions / $stats->views) * 10000);

		$stmt = $statsdb->prepare("INSERT INTO conversions (template,variation,conversion_type,conversions,rate) VALUES (:template,:variation,:conversion_type,:conversions,:rate);");
		$stmt->bindParam(':template', $visit->template);
		$stmt->bindParam(':variation', $visit->variation);
		$stmt->bindParam(':conversion_type', $conversion);
		$stmt->bindParam(':conversions', $conversions, SQLITE3_INTEGER);
		$stmt->bindParam(':rate', $rate, SQLITE3_INTEGER);
		$stmt->execute();
	}

}

function stats_get_visits()
{
	global $statsdb;

	$data = array();

	$result = $statsdb->query("
		SELECT *
		FROM visits
		ORDER BY id DESC
		");
	while ( $row = $result->fetchObject() ) {
		$data[] = $row;
	}

	return $data;
}

function stats_get_template_conversions($template)
{
	global $statsdb;

	$data = array();

	$result = $statsdb->query("
		SELECT variation,conversion_type,conversions,rate
		FROM conversions
		WHERE conversions.template = \"$template\"
		ORDER BY rate DESC, conversions DESC
		");
	while ( $row = $result->fetchObject() ) {
		$data[] = $row;
	}

	return $data;
}

function stats_get_session_variation($template)
{
	global $statsdb;

	$id = doubleval($_SESSION['visit_id']);
	$visit = $statsdb->query("SELECT * FROM visits WHERE id = $id AND template = '$template';")->fetchObject();
	if ( $visit && is_variation($visit->template, $visit->variation) ) {
		return $visit->variation;
	}

	return null;
}

function stats_get_less_visited_variation($template)
{
	/** @var PDO $statsdb */
	global $statsdb;

	$variations = get_template_variations( $template );
	$data = array_combine($variations, array_pad(array(),count($variations),0));

	$result = $statsdb->query("SELECT variation, views FROM templates WHERE template = '$template';");
	while ( $row = $result->fetchObject() ) {
		if ( isset($data[$row->variation]) ) {
			$data[ $row->variation ] = $row->views;
		}
	}

	asort($data);

	return array_shift(array_keys($data));
}

function stats_pixel()
{
	global $main_template, $main_variation;

	$url = LANDINGS_URI."stats.png?ac=visit&la={$main_template}&va={$main_variation}";

	echo "<img src=\"{$url}\" width=\"1\" height=\"1\" />";
}

function stats_conversion_url($conversion)
{
	global $visit_id;
	return LANDINGS_URI."stats.png?ac=conversion&id={$visit_id}&co={$conversion}";
}

stats_init();
