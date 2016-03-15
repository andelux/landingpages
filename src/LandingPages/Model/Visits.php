<?php
namespace LandingPages\Model;


use LandingPages\Mvc;
use LandingPages\Mvc\Model;
use LandingPages\Database;
use LandingPages\Template;

class Visits extends Model
{
    protected $_tablename = 'visits';
    protected $_pk = 'id';
    protected $_fields = array('id','uri','template','variation','conversion');

    public function register( $id, $uri, $template, $variation )
    {
        $this->load($id);
        if ( ! $this->getId() ) {
            $this->setData(array(
                'id'        => $id,
                'uri'       => $uri,
                'template'  => $template,
                'variation' => $variation,
            ));
            $this->save('insert');
        }

        return $this;
    }

    public function saveConversion($id, $conversion)
    {
        return $this->load($id)->setData('conversion',$conversion)->save();
    }

    public function getSessionVariation($template)
    {
        $this->load(Stats::getVisitId());

        if ( $this->getTemplate() == $template && Template::isVariation($template, $this->getVariation()) ) {
            return $this->getVariation();
        }

        return null;
    }

    public function getAll()
    {
        $data = array();

        $result = $this->db->query("
		  SELECT *
		  FROM visits
		  ORDER BY id DESC
		");
        while ( $row = $result->fetchObject() ) {
            $data[] = $row;
        }

        return $data;
    }



    protected function _create()
    {
        // VISITAS
        //  - id        : 12871781
        //  - uri       : sem/concurso-traje-buceo.html
        //  - template  : sem-concursotrajebuceo-201503
        //  - variation : A
        //  - conversion: lead
        Database::db()->exec("
			CREATE TABLE IF NOT EXISTS visits (
				id INTEGER PRIMARY KEY NOT NULL,
				uri text NOT NULL,
				template text NOT NULL,
				variation text NOT NULL,
				conversion text NULL
			)
		");
    }
}