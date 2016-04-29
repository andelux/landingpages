<?php
namespace LandingPages\Model;


use LandingPages\Mvc\Model;

class Attributes extends Model
{
    protected $_tablename = 'eav_attributes';
    protected $_pk = 'id';
    protected $_fields = array(
        'id',   // Attribute ID
        'type', // string, text, wysiwyg, date, time, datetime, internal, multiinternal, select, multiselect
        'code', // Attribute code

        // internal/multiinternal values
        'values',                       // json of assoc array ID=>LABEL

        // For type=select/multiselect
        'entity_type_id',               // entity with values
        'entity_attribute_value_id',    // entity attribute with ID value
        'entity_attribute_label_id',    // entity attribute with LABEL value
    );

    /**
     *
     */
    protected function _create()
    {
        Database::db()->exec("
			CREATE TABLE IF NOT EXISTS eav_attributes (
				id INTEGER PRIMARY KEY NOT NULL,
				type TEXT NOT NULL,
				code TEXT NOT NULL,
				values TEXT NOT NULL,
				entity_type_id INTEGER NOT NULL,
				entity_attribute_value_id INTEGER NOT NULL,
				entity_attribute_label_id INTEGER NOT NULL
			)
		");
    }
}
