<?php
namespace LandingPages\Model;

use LandingPages\Mvc\Model;
use LandingPages\Database;

/**
 * Class Hooks
 *
 * @method string getHook()
 * @method string getConfig()
 *
 *
 * @package LandingPages\Model
 */
class Hooks extends Model
{
    protected $_tablename = 'hooks';
    protected $_pk = 'id';
    protected $_fields = array('id','hook','config','template','status');
    protected $_field_types = array(
        'status'    => SQLITE3_INTEGER,
    );

    /**
     *
     * @param $template
     *
     * @return array
     *
     * @throws \Exception
     */
    protected function _getAvailableTemplateHooks( $template )
    {
        $data = array();
        /** @var Hooks $hook */
        foreach ( $this
                      ->resetCollection()
                      ->addFieldToFilter('template', $template)
                      ->addFieldToFilter('status', 1)
                      ->collection() as $hook ) {

            $data[$hook->getHook()] = json_decode($hook->getConfig(), true);
        }

        return $data;
    }

    /**
     *
     * @param $template
     * @param $data
     *
     * @throws \Exception
     */
    public function exec( $template, $data )
    {
        // Every template has its own config data for each backend
        foreach ( $this->_getAvailableTemplateHooks($template) as $hook => $config ) {

            // Backend class name
            $class_name = '\\LandingPages\\Hook\\'.uc_words($hook,'_','');
            if ( class_exists($class_name) ) {
                // Instance backend & exec
                new $class_name($config, $data);
            } else {
                // Backend not found
                throw new \Exception('Hook backend not implemented: ' . $hook);
            }

        }
    }

    /**
     *
     */
    protected function _create()
    {
        Database::db()->exec("
			CREATE TABLE IF NOT EXISTS hooks (
				id INTEGER PRIMARY KEY NOT NULL,
				hook text NOT NULL,
				config text NOT NULL,
				template text NOT NULL,
				status INTEGER NOT NULL
			)
        ");

    }
}