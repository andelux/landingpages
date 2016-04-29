<?php
namespace LandingPages\Model;


use LandingPages\Mvc\Model;

class EntityTypes extends Model
{
    protected $_tablename = 'eav_entity_types';
    protected $_pk = 'id';
    protected $_fields = array('id','name',);

    protected function _create()
    {
        Database::db()->exec("
			CREATE TABLE IF NOT EXISTS eav_entity_types (
				id INTEGER PRIMARY KEY NOT NULL,
				name TEXT NOT NULL
			)
		");
    }
}