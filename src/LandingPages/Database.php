<?php
namespace LandingPages;

class Database
{
    /** @var PDO */
    protected $_db;

    static protected $_instance;

    /**
     * Database constructor.
     */
    public function __construct()
    {
        /** @var PDO $statsdb */
        //$this->_db = new \PDO('sqlite:'.LP_ROOT_DIRECTORY.'/stats.db');
        $this->_db = new \PDO(LP_DATABASE);
        // Set errormode to exceptions
        $this->_db->setAttribute( \PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION );
    }

    /**
     * @return PDO
     */
    public function getPDO()
    {
        return $this->_db;
    }

    /**
     * @return Database
     */
    static public function getSingleton()
    {
        return self::$_instance ? self::$_instance : (self::$_instance = new self());
    }

    /**
     * @return PDO
     */
    static public function db()
    {
        return self::getSingleton()->getPDO();
    }

}
