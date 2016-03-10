<?php
namespace LandingPages;


class Hook
{
    protected $_template_name;
    protected $_variables;

    public function __construct( $template_name, $variables )
    {
        $this->_template_name = $template_name;
        $this->_variables = $variables;
    }

    protected function _getAvailableTemplateHooks()
    {
        $data = array();

        $result = Database::db()->query("SELECT hook, config FROM hooks WHERE template = '{$this->_template_name}' AND status = 1;");
        while ($row = $result->fetchObject()) {
            $data[$row->hook] = json_decode($row->config, true);
        }

        return $data;
    }

    public function exec()
    {
        foreach ( $this->_getAvailableTemplateHooks() as $hook => $config ) {
            switch ($hook) {
                case 'mailchimp':
                    $hook = new Hook\Mailchimp($config, $this->_variables);
                    $hook->exec();
                    break;

                case 'webhook':
                    $hook = new Hook\Webhook($config, $this->_variables);
                    $hook->exec();
                    break;

                default:
                    throw new \Exception('Hook backend not implemented: ' . $hook);
            }
        }
    }
}