<?php
namespace LandingPages\Model;


use LandingPages\Mvc\Model;

class EntityAttributes extends Model
{
    protected $_tablename = 'eav_entity_attributes';
    protected $_pk = array('entity_type_id','attribute_id',);
    protected $_fields = array('entity_type_id','attribute_id',);

    protected function _create()
    {
        Database::db()->exec("
			CREATE TABLE IF NOT EXISTS eav_entity_attributes (
				entity_type_id INTEGER PRIMARY KEY NOT NULL,
				attribute_id INTEGER PRIMARY KEY NOT NULL
			)
		");
    }
}