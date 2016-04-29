<?php
namespace LandingPages\Model;

use LandingPages\Mvc\Model;

class Entities extends Model
{
    protected $_tablename = 'eav_entities';
    protected $_pk = 'id';
    protected $_fields = array(
        'id',               // Entity ID
        'type_id',          // Entity type ID
        'date_created',     // Entity date created
        'date_updated',     // Entity date updated
        'owner_user_id',    // Entity owner
        'owner_group_id',   // Entity group
    );

    public function getAttributes()
    {
        $attributes = array();

        $collection = EntityAttributes::factory()
            ->addFieldToFilter('entity_type_id', $this->getData('type_id'))
            ->collection();
        foreach ( $collection as $item ) {
            $attribute = Attributes::factory()->load( $item->getData('attribute_id') );
            $attributes[$attribute->getData('code')] = $attribute;
        }

        return $attributes;
    }

    public function load( $id, $field = null )
    {
        $attributes = $this->getAttributes();

        if ( $field === null || in_array($field, $this->_fields) ) {
            // Load by table field
            parent::load($id, $field);
        } else {
            // Load by attribute field
            // TODO: ...
        }

        // Load attributes values
        // TODO: ...

        return $this;
    }

    public function save($op = null)
    {
        // Get attribute fields/values
        $attributes = $this->getAttributes();
        $attribute_values = array();
        foreach ( array_keys($attributes) as $attr_code ) {
            if ( array_key_exists($attr_code, $this->_data) ) {
                $attribute_values[$attr_code] = $this->_data[$attr_code];
            }
        }

        // Save static fields
        parent::save($op);

        // Save attribute values
        foreach ( $attribute_values as $attr_code => $value ) {
            /** @var Attributes $attribute */
            $attribute = $attributes[$attr_code];

            /** @var EntityValues $attr_value */
            $attr_value = EntityValues::factory();

            $collection = $attr_value
                ->addFieldToFilter('entity_id', $this->getId())
                ->addFieldToFilter('attribute_id', $attribute->getId())
                ->collection();
            if ( count($collection) > 0 ) {
                $attr_value = array_shift($collection);
            } else {
                $attr_value->setData('entity_id', $this->getId());
                $attr_value->setData('attribute_id', $attribute->getId());
            }

            // Get the value field name
            switch ( $attribute->getData('type') ) {
                case 'text':
                case 'wysiwyg':
                case 'string':
                case 'multiinternal':
                case 'multiselect':
                    $value_field = 'value_text';
                    break;
                case 'date':
                case 'time':
                case 'datetime':
                case 'integer':
                case 'internal':
                case 'select':
                    $value_field = 'value_integer';
                    break;
                default:
                    throw new \Exception('unknown attribute type: ' . $attribute->getData('type'));
            }

            // Save attribute value
            $attr_value->setData($value_field, $value);
            $attr_value->save();
        }

        return $this;
    }

    /**
     *
     */
    protected function _create()
    {
        Database::db()->exec("
			CREATE TABLE IF NOT EXISTS eav_entities (
				id INTEGER PRIMARY KEY NOT NULL,
				type_id INTEGER NOT NULL,
				date_created INTEGER NOT NULL,
				date_updated INTEGER NOT NULL,
				owner_user_id INTEGER NOT NULL,
				owner_group_id INTEGER NOT NULL
			)
		");
    }
}

