<?php
namespace LandingPages;

class Stats
{
    static protected $_visit_id;
    static protected $_instance;

    public function __construct()
    {

    }

    static public function getSingleton()
    {
        return self::$_instance ? self::$_instance : (self::$_instance = new self());
    }

    static public function getVisitId()
    {
        if ( ! self::$_visit_id ) {
            if (isset($_SESSION['visit_id']) && $_SESSION['visit_id']) {
                self::$_visit_id = $_SESSION['visit_id'];
            } else {
                self::$_visit_id = $_SESSION['visit_id'] = self::getSingleton()->generateId();
            }
        }

        return self::$_visit_id;
    }

    public function generateId()
    {
        return floor( microtime(true) * 100 );
    }
    static public function idToTime($id)
    {
        return floor( $id / 100 );
    }

    static public function visit( $template, $variation = null )
    {
        $id = doubleval($_SESSION['visit_id']);
        $uri = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';

        //$template = $_GET['la'];
        if ( ! \LandingPages\Template::exists($template) ) return false;

        //$variation = $_GET['va'];
        if ( $variation && ! \LandingPages\Template::isVariation($template, $variation) ) return false;

        // Registro visita general
        $visit = \LandingPages\Database::db()->query("SELECT * FROM visits WHERE id = $id;")->fetchObject();
        if ( ! $visit ) {
            $stmt = \LandingPages\Database::db()->prepare( "INSERT INTO visits (id,uri,template,variation,conversion) VALUES (:id,:uri,:template,:variation,NULL);" );
            $stmt->bindParam( ':id', $id, SQLITE3_INTEGER );
            $stmt->bindParam( ':uri', $uri );
            $stmt->bindParam( ':template', $template );
            $stmt->bindParam( ':variation', $variation );
            //$stmt->bindParam(':conversion', null);
            try {
                $stmt->execute();
            } catch ( \Exception $e ) {
                trigger_error($e->getMessage(), E_USER_WARNING);
            }
        }

        // Registro visitas plantilla
        $result = \LandingPages\Database::db()->query("SELECT views FROM templates WHERE template = '$template' AND variation = '$variation';");
        if ( $row = $result->fetchObject() ) {
            $views = $row->views + 1;

            $stmt = \LandingPages\Database::db()->prepare("UPDATE templates SET views = :views WHERE template = :template AND variation = :variation;");
            $stmt->bindParam(':template', $template);
            $stmt->bindParam(':variation', $variation);
            $stmt->bindParam(':views', $views, SQLITE3_INTEGER);
            try {
                $stmt->execute();
            } catch ( \Exception $e ) {
                trigger_error($e->getMessage(), E_USER_WARNING);
            }

            // Actualizamos ratios de conversión
            $result = \LandingPages\Database::db()->query("SELECT conversion_type,conversions FROM conversions WHERE template = '$template' AND variation = '$variation';");
            if ( $row = $result->fetchObject() ) {
                $rate = round((doubleval($row->conversions) / $views) * 10000);

                $stmt = \LandingPages\Database::db()->prepare("UPDATE conversions SET rate = :rate WHERE template = :template AND variation = :variation AND conversion_type = :conversion_type;");
                $stmt->bindParam(':template', $template);
                $stmt->bindParam(':variation', $variation);
                $stmt->bindParam(':conversion_type', $row->conversion_type);
                $stmt->bindParam(':rate', $rate, SQLITE3_INTEGER);
                try {
                    $stmt->execute();
                } catch ( \Exception $e ) {
                    trigger_error($e->getMessage(), E_USER_WARNING);
                }
            }

        } else {
            $stmt = \LandingPages\Database::db()->prepare("INSERT INTO templates (template,variation,views,conversions) VALUES (:template,:variation,1,0);");
            $stmt->bindParam(':template', $template);
            $stmt->bindParam(':variation', $variation);
            try {
                $stmt->execute();
            } catch ( \Exception $e ) {
                trigger_error($e->getMessage(), E_USER_WARNING);
            }
        }

        return true;
    }

    public function conversion($conversion)
    {
        // WARNING: prove this works!
        //$id = doubleval($id);
        $id = self::getVisitId();

        $stmt = Database::db()->prepare("UPDATE visits SET conversion = :conversion WHERE id = :id");
        $stmt->bindParam(':conversion', $conversion);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        $visit = Database::db()->query("SELECT * FROM visits WHERE id = $id;")->fetchObject();

        $stats = Database::db()->query("SELECT views,conversions FROM templates WHERE templates.template = '$visit->template' AND templates.variation = '$visit->variation';")->fetchObject();

        $stmt = Database::db()->prepare("UPDATE templates SET conversions = :conversions WHERE template = :template AND variation = :variation;");
        $conversions = $stats->conversions + 1;
        $stmt->bindParam(':template', $visit->template);
        $stmt->bindParam(':variation', $visit->variation);
        $stmt->bindParam(':conversions', $conversions);
        $stmt->execute();

        // Registro conversiones plantilla
        $result = Database::db()->query("SELECT conversions.conversions FROM conversions WHERE conversions.template = '$visit->template' AND conversions.variation = '$visit->variation' AND conversions.conversion_type = '$conversion';");
        if ( $row = $result->fetchObject() ) {
            $conversions = $row->conversions + 1;
            $rate = round(($conversions / $stats->views) * 10000);

            $stmt = Database::db()->prepare("UPDATE conversions SET conversions = :conversions, rate = :rate WHERE template = :template AND variation = :variation AND conversion_type = :conversion_type;");
            $stmt->bindParam(':template', $visit->template);
            $stmt->bindParam(':variation', $visit->variation);
            $stmt->bindParam(':conversion_type', $conversion);
            $stmt->bindParam(':conversions', $conversions, SQLITE3_INTEGER);
            $stmt->bindParam(':rate', $rate, SQLITE3_INTEGER);
            $stmt->execute();
        } else {
            $conversions = 1;
            $rate = round(($conversions / $stats->views) * 10000);

            $stmt = Database::db()->prepare("INSERT INTO conversions (template,variation,conversion_type,conversions,rate) VALUES (:template,:variation,:conversion_type,:conversions,:rate);");
            $stmt->bindParam(':template', $visit->template);
            $stmt->bindParam(':variation', $visit->variation);
            $stmt->bindParam(':conversion_type', $conversion);
            $stmt->bindParam(':conversions', $conversions, SQLITE3_INTEGER);
            $stmt->bindParam(':rate', $rate, SQLITE3_INTEGER);
            $stmt->execute();
        }

    }

    public function getVisits()
    {
        $data = array();

        $result = Database::db()->query("
		SELECT *
		FROM visits
		ORDER BY id DESC
		");
        while ( $row = $result->fetchObject() ) {
            $data[] = $row;
        }

        return $data;
    }

    public function getTemplateConversions($template)
    {
        $data = array();

        $result = Database::db()->query("
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

    function getSessionVariation($template)
    {
        $id = doubleval($_SESSION['visit_id']);
        $visit = Database::db()->query("SELECT * FROM visits WHERE id = $id AND template = '$template';")->fetchObject();
        if ( $visit && is_variation($visit->template, $visit->variation) ) {
            return $visit->variation;
        }

        return null;
    }

    function getLessVisitedVariation($template)
    {
        $variations = Template::getTemplateVariations( $template );
        $data = array_combine($variations, array_pad(array(),count($variations),0));

        $result = Database::db()->query("SELECT variation, views FROM templates WHERE template = '$template';");
        while ( $row = $result->fetchObject() ) {
            if ( isset($data[$row->variation]) ) {
                $data[ $row->variation ] = $row->views;
            }
        }

        asort($data);

        return array_shift(array_keys($data));
    }

    static public function getHtmlPixel()
    {
        global $main_template, $main_variation;

        $url = LP_BASE_URI."stats/visit/pixel.png?la={$main_template}&va={$main_variation}";

        echo "<img src=\"{$url}\" width=\"1\" height=\"1\" />";
    }

    static public function getConversionUrl($conversion)
    {
        $visit_id = self::getVisitId();
        return LP_BASE_URI."stats.png?ac=conversion&id={$visit_id}&co={$conversion}";
    }

}