<?php
/**
 * Created by PhpStorm.
 * User: javier
 * Date: 15/03/16
 * Time: 8:18
 */

namespace LandingPages\Mvc;


use LandingPages\Object;
use LandingPages\Database;

class Model extends Object
{
    static protected $_create_flags = array();

    /** @var \LandingPages\PDO */
    protected $db;

    protected $_tablename;
    protected $_pk;
    protected $_fields;
    protected $_field_types;

    protected $_collection_filters;
    protected $_order_by;

    public function __construct(array $data = array())
    {
        parent::__construct($data);

        $this->db = Database::db();
        $this->_collection_filters = array();
        $this->_order_by = null;

        if ( ! self::$_create_flags[__CLASS__]++ ) $this->_create();
    }

    /**
     * @return bool
     */
    public function isPersistent()
    {
        return $this->_tablename !== null && $this->_pk !== null;
    }

    /**
     * @return array|null
     */
    public function getId()
    {
        if ( $this->isPersistent() ) {
            if ( is_array($this->_pk) ) {
                $data = array();
                foreach ( $this->_pk as $key ) if ($this->issetData($key)) $data[$key] = $this->getData($key);
                return $data;
            }

            return $this->getData($this->_pk);
        }

        return null;
    }

    /**
     * @param $id
     * @param null $field
     * @return $this
     * @throws \Exception
     */
    public function load( $id, $field = null )
    {
        if ( ! $this->isPersistent() ) return $this;

        if ( $field === null ) {
            if ( is_array($id) ) {
                $field = array_keys($id);
            } else {
                $field = $this->_pk;
            }
        }
        if ( ! is_array($field) ) {
            $id = array($field => $id);
            $field = array($field);
        }

        $filters_sql = array();
        foreach ( $field as $F ) $filters_sql[] = "{$F} = :{$F}";

        $sql = "SELECT * FROM {$this->_tablename} WHERE ".implode(' AND ', $filters_sql)." LIMIT 1";

        $stmt = $this->db->prepare($sql);
        foreach ( $field as $F ) {
            if ( $this->_field_types && in_array($F, $this->_field_types) ) {
                $stmt->bindParam(':'.$F, $id[$F], $this->_field_types[$F]);
            } else {
                $stmt->bindParam(':'.$F, $id[$F]);
            }
        }

        try {
            $stmt->execute();
            $row = $stmt->fetch( \PDO::FETCH_ASSOC );
            $this->_data = $this->_validateData($row);
        } catch ( \Exception $e ) {
            throw new \Exception('Error on load model: '. __CLASS__);
        }

        return $this;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    public function save( $op = null )
    {
        if ( ! $this->isPersistent() ) return $this;

        $primary_keys = is_array($this->_pk) ? $this->_pk : array($this->_pk);
        $fields = $pks = array();

        foreach ( array_keys($this->_data) as $fieldname ) {
            if ( $this->_fields && ! in_array($fieldname, $this->_fields) ) continue;

            if ( in_array($fieldname, $primary_keys) ) {
                $pks[] = $fieldname;
            } else {
                $fields[] = $fieldname;
            }
        }

        if ( $op == 'insert' ) {
            // FORCE INSERT WITH PK
            $fields = array_merge($pks, $fields);
            $sets = array();
            foreach ( $fields as $field ) $sets[] = ":{$field}";
            $sql = "INSERT INTO {$this->_tablename} (";
            $sql .= implode(',', $fields);
            $sql .= ") VALUES (";
            $sql .= implode(',', $sets);
            $sql .= ");";
        } else if ( $this->getId() ) {
            // UPDATE
            // check if exists, if not, insert
            $item = clone $this;
            $item->load( $this->getId() );
            if ( ! $item->hasData() ) return $this->save('insert');

            // ...update
            $sets = $wheres = array();
            foreach ( $fields as $field ) $sets[] = "{$field} = :{$field}";
            foreach ( $pks as $pk ) $wheres[] = "{$pk} = :{$pk}";

            $sql = "UPDATE {$this->_tablename} SET ";
            $sql .= implode(', ', $sets);
            $sql .= " WHERE ";
            $sql .= implode(' AND ', $wheres);

            $fields = array_merge($pks, $fields);
        } else {
            // INSERT
            $sets = array();
            foreach ( $fields as $field ) $sets[] = ":{$field}";

            $sql = "INSERT INTO {$this->_tablename} (";
            $sql .= implode(',', $fields);
            $sql .= ") VALUES (";
            $sql .= implode(',', $sets);
            $sql .= ");";
        }

        try {

            $stmt = $this->db->prepare($sql);

            foreach ( $fields as $field ) {
                if ( $this->_field_types && array_key_exists($field, $this->_field_types) ) {
                    $stmt->bindParam(':' . $field, $this->_data[$field], $this->_field_types[$field]);
                } else {
                    $stmt->bindParam(':' . $field, $this->_data[$field]);
                }
            }

            $stmt->execute();

        } catch ( \Exception $e ) {
            throw new \Exception('Error saving model: ' . __CLASS__);
        }

        return $this;
    }

    /**
     * @return $this|Collection
     * @throws \Exception
     */
    public function collection()
    {
        if ( ! $this->isPersistent() ) return $this;

        $filters_sql = array();
        foreach ( $this->_collection_filters as $field => $value ) {
            $filters_sql[] = "{$field} = :{$field}";
        }

        $sql = "SELECT * FROM {$this->_tablename} WHERE ".implode(' AND ', $filters_sql);
        if ( $this->_order_by ) $sql .= " ORDER BY {$this->_order_by}";

        $stmt = $this->db->prepare($sql);
        foreach ( array_keys($this->_collection_filters) as $field ) {
            if ( $this->_field_types && array_key_exists($field, $this->_field_types) ) {
                $stmt->bindParam(':' . $field, $this->_collection_filters[$field], $this->_field_types[$field]);
            } else {
                $stmt->bindParam(':' . $field, $this->_collection_filters[$field]);
            }
        }

        $collection = new Collection();
        try {
            $stmt->execute();
            while ( $row = $stmt->fetch( \PDO::FETCH_ASSOC ) ) {
                $collection->append( $this->factory($row) );
            }
        } catch ( \Exception $e ) {
            throw new \Exception('Error on load model: '. __CLASS__);
        }

        return $collection;
    }

    public function factory(array $data = array())
    {
        return new self($data);
    }

    /**
     * @param $field
     * @param $value
     * @return $this
     * @throws \Exception
     */
    public function addFieldToFilter($field, $value)
    {
        if ( $this->_fields && is_array($this->_fields) && ! in_array($field, $this->_fields) ) {
            throw new \Exception('Field not found in scheme: ' . $field);
        }

        $this->_collection_filters[$field] = $value;

        return $this;
    }

    public function resetCollection()
    {
        $this->_collection_filters = array();
        $this->_order_by = null;
        return $this;
    }
    public function addOrderBy( $order_by )
    {
        $this->_order_by = $order_by;
        return $this;
    }

    /**
     * @param $row
     * @return array
     */
    protected function _validateData( $row )
    {
        if ( ! $row ) return array();

        if ( $this->_fields === null || ! is_array($this->_fields)) return $row;

        $data = array();
        foreach ( $this->_fields as $field ) {
            if ( array_key_exists($field, $row) ) {
                $data[$field] = $row[$field];
            }
        }

        return $data;
    }

    protected function _create(){}
}