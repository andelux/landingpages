<?php
namespace LandingPages\Model;


use LandingPages\Mvc\Model;

class EntityValues extends Model
{
    protected $_tablename = 'eav_entity_values';
    protected $_pk = 'id';
    protected $_fields = array('id','entity_id','attribute_id','value_integer','value_text');

    /**
     *
     */
    protected function _create()
    {
        Database::db()->exec("
			CREATE TABLE IF NOT EXISTS eav_entity_values (
				id INTEGER PRIMARY KEY NOT NULL,
				entity_id INTEGER NOT NULL,
				attribute_id INTEGER NOT NULL,
				value_integer INTEGER NOT NULL,
				value_text TEXT NOT NULL
			)
		");
    }
}