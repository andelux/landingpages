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
        $this->_db = new \PDO('sqlite:'.LP_ROOT_DIRECTORY.'/stats.db');
        // Set errormode to exceptions
        $this->_db->setAttribute( \PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION );

        $this->_createTables();
    }

    /**
     * @return PDO
     */
    public function getPDO()
    {
        return $this->_db;
    }

    protected function _createTables()
    {
        // VISITAS
        //  - id        : 12871781
        //  - uri       : sem/concurso-traje-buceo.html
        //  - template  : sem-concursotrajebuceo-201503
        //  - variation : A
        //  - conversion: lead
        $this->_db->exec("
			CREATE TABLE IF NOT EXISTS visits (
				id INTEGER PRIMARY KEY NOT NULL,
				uri text NOT NULL,
				template text NOT NULL,
				variation text NOT NULL,
				conversion text NULL
			)
		");

        // VISTAS Y CONVERSIONES POR PLANTILLA/VARIACIÓN
        //  - template      : sem-concursotrajebuceo-201503
        //  - variation     : A
        //  - views         : 436
        //  - conversions   : 5
        $this->_db->exec("
			CREATE TABLE IF NOT EXISTS templates (
				template text NOT NULL,
				variation text NOT NULL,
				views integer NOT NULL,
				conversions integer NOT NULL
			)
		");

        // CONVERSIONES OBTENIDAS POR PLANTILLA/VARIANTE
        //  - template          : sem-concursotrajebuceo-201503
        //  - variation         : A
        //  - conversion_type   : lead
        //  - conversions       : 326
        //  - rate              : 134 (1,34%)
        $this->_db->exec("
			CREATE TABLE IF NOT EXISTS conversions (
				template text NOT NULL,
				variation text NOT NULL,
				conversion_type NOT NULL,
				conversions integer NOT NULL,
				rate integer NOT NULL
			)
		");

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
