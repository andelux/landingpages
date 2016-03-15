<?php
namespace LandingPages\Model;


use LandingPages\Mvc\Model;
use LandingPages\Database;

class Conversions extends Model
{
    protected $_tablename = 'conversions';
    protected $_pk = array('template','variation','conversion_type');
    protected $_fields = array('template','variation','conversion_type','conversions','rate');
    protected $_field_types = array(
        'conversions'   => SQLITE3_INTEGER,
        'rate'          => SQLITE3_INTEGER,
    );

    public function recalculateRates($template, $variation, $views)
    {
        $collection = $this->resetCollection()
            ->addFieldToFilter('template', $template)
            ->addFieldToFilter('variation', $variation)
            ->collection();

        foreach ( $collection as $conversion ) {
            /** @var $conversion Conversions */
            $conversion->setRate(round((doubleval($conversion->getConversions()) / $views) * 10000));
            $conversion->save();
        }

        return $this;
    }

    public function updateConversion($template, $variation, $conversion_type, $views)
    {
        $this->load(array(
            'template'          => $template,
            'variation'         => $variation,
            'conversion_type'   => $conversion_type,
        ));

        if ( ! $this->hasData() ) {
            $this->setData('template', $template);
            $this->setData('variation', $variation);
            $this->setData('conversion_type', $conversion_type);
        }

        $this->setData( 'conversions', $this->getData('conversions') + 1 );
        $this->setData( 'rate', round(($this->getData('conversions') / $views) * 10000) );
        return $this->save();
    }

    public function getTemplateConversions($template)
    {
        return $this->resetCollection()
            ->addFieldToFilter('template', $template)
            ->addOrderBy('rate DESC, conversions DESC')
            ->collection();
    }


    public function factory(array $data = array())
    {
        return new Conversions($data);
    }

    protected function _create()
    {
        // CONVERSIONES OBTENIDAS POR PLANTILLA/VARIANTE
        //  - template          : sem-concursotrajebuceo-201503
        //  - variation         : A
        //  - conversion_type   : lead
        //  - conversions       : 326
        //  - rate              : 134 (1,34%)

        Database::db()->exec("
			CREATE TABLE IF NOT EXISTS conversions (
				template text NOT NULL,
				variation text NOT NULL,
				conversion_type NOT NULL,
				conversions integer NOT NULL,
				rate integer NOT NULL
			)
		");
    }
}
