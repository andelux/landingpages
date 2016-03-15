<?php
namespace LandingPages\Model;

use LandingPages\Mvc;
use LandingPages\Mvc\Model;
use LandingPages\Database;
use LandingPages\Template;

/**
 * Class Stats
 *
 * @method string getTemplate()
 * @method string getVariation()
 * @method integer getViews()
 * @method integer getConversions()
 *
 * @package LandingPages\Model
 */
class Stats extends Model
{
    protected $_tablename = 'stats';
    protected $_pk = array('template','variation');
    protected $_fields = array('template','variation','views','conversions');
    protected $_field_types = array(
        'views' => SQLITE3_INTEGER,
    );

    /**
     *
     *
     * @param $template
     * @param null $variation
     *
     * @return $this|bool
     *
     * @throws \Exception
     */
    public function register( $template, $variation = null )
    {
        $id = self::getVisitId();
        $uri = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';

        if ( ! Template::exists($template) ) return false;
        if ( $variation && ! Template::isVariation($template, $variation) ) return false;

        // Registro visita general
        $visit = new Visits();
        $visit->register($id, $uri, $template, $variation);

        // Registro visitas plantilla
        $this->load(array(
            'template'  => $template,
            'variation' => $variation,
        ));
        if ( $this->getId() ) {
            $this->setData('views', $this->getViews() + 1);

            $conversions = new Conversions();
            $conversions->recalculateRates($template, $variation, $this->getViews());
        } else {
            $this->setData('template', $template);
            $this->setData('variation', $variation);
            $this->setData('views', 1);
            $this->setData('conversions', 0);
        }

        return $this->save();
    }

    /**
     * Register conversion
     *
     * @param $conversion_type
     *
     * @throws \Exception
     */
    public function conversion($conversion_type)
    {
        $id = self::getVisitId();

        // Get the visit info & record conversion type
        $visit = new Visits();
        $visit->saveConversion( $id, $conversion_type );
        $template = $visit->getData('template');
        $variation = $visit->getData('variation');

        // Load stats info about template/variation
        $this->load(array(
            'template'  => $template,
            'variation' => $variation,
        ));
        // Increment conversions counter
        $conversions = intval($this->getData('conversions')) + 1;
        $this->setData( 'conversions', $conversions );
        // Get current views
        $views = intval($this->getViews());
        $this->save();

        // Update conversion info, recalculating conversion rate & conversions counter (about conversion type)
        $conversions = new Conversions();
        $conversions->updateConversion($template, $variation, $conversion_type, $views);
    }

    /**
     * Find the less visited variation of template
     *
     * @param $template
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function getLessVisitedVariation($template)
    {
        $variations = Template::getTemplateVariations( $template );
        $data = array_combine($variations, array_pad(array(),count($variations),0));

        $collection = $this
            ->resetCollection()
            ->addFieldToFilter('template',$template)
            ->collection();

        /** @var Stats $item */
        foreach ( $collection as $item ) {
            if ( array_key_exists($item->getVariation(), $data) ) {
                $data[$item->getVariation()] = $item->getViews();
            }
        }

        asort($data);

        return array_shift(array_keys($data));
    }

    /**
     *
     */
    static public function getHtmlPixel()
    {
        global $main_template, $main_variation;

        $url = LP_BASE_URI."stats/visit/pixel.png?la={$main_template}&va={$main_variation}";

        echo "<img src=\"{$url}\" width=\"1\" height=\"1\" />";
    }

    /**
     * @return array|null
     */
    static public function getVisitId()
    {
        static $visit_id;

        if ( ! $visit_id ) {
            $session = Mvc::getSession();

            if (!$session->issetData('visit_id')) {
                $session->setData('visit_id', self::generateId());
            }

            $visit_id = $session->getData('visit_id');
        }

        return $visit_id;
    }

    /**
     * @return float
     */
    static public function generateId()
    {
        return floor( microtime(true) * 100 );
    }

    /**
     * @param $id
     * @return float
     */
    static public function idToTime($id)
    {
        return floor( $id / 100 );
    }

    /**
     *
     */
    protected function _create()
    {
        // VISTAS Y CONVERSIONES POR PLANTILLA/VARIACIÓN
        //  - template      : sem-concursotrajebuceo-201503
        //  - variation     : A
        //  - views         : 436
        //  - conversions   : 5
        Database::db()->exec("
			CREATE TABLE IF NOT EXISTS stats (
				template text NOT NULL,
				variation text NOT NULL,
				views integer NOT NULL,
				conversions integer NOT NULL
			)
		");
    }
}