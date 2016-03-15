<?php
namespace LandingPages\Model;


use LandingPages\Hook\Mailchimp;
use LandingPages\Hook\Webhook;
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

    public function exec( $template, $data )
    {
        foreach ( $this->_getAvailableTemplateHooks($template) as $hook => $config ) {
            switch ($hook) {
                case 'mailchimp':
                    $hook = new Mailchimp($config, $data);
                    $hook->exec();
                    break;

                case 'webhook':
                    $hook = new Webhook($config, $data);
                    $hook->exec();
                    break;

                default:
                    throw new \Exception('Hook backend not implemented: ' . $hook);
            }
        }
    }

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