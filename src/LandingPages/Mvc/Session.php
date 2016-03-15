<?php
namespace LandingPages\Mvc;

use LandingPages\Object;

/**
 * Class Session
 *
 * @package LandingPages\Mvc
 */
class Session extends Object
{

    /**
     * Session constructor.
     */
    public function __construct()
    {
        session_start();
    }

    /**
     * @param $name
     * @param null $value
     * @return $this
     */
    public function setData($name, $value = null)
    {
        if ( is_array($name) ) {
            foreach ( $name as $field => $value ) {
                $_SESSION[$field] = $value;
            }
        } else {
            $_SESSION[$name] = $value;
        }

        return $this;
    }

    /**
     * @param null $name
     * @param null $default
     * @return null
     */
    public function getData($name = null, $default = null)
    {
        if ( $name === null ) return $_SESSION;
        if ( array_key_exists($name, $_SESSION) ) return $_SESSION[$name];
        return $default;
    }

    /**
     * @param $name
     * @return $this
     */
    public function unsetData($name)
    {
        if ( array_key_exists($name, $_SESSION) ) unset($_SESSION[$name]);
        return $this;
    }

    /**
     * @param $name
     * @return bool
     */
    public function issetData($name)
    {
        return array_key_exists($name, $_SESSION);
    }

    /**
     * @param $type
     * @param $message
     * @return $this
     */
    public function addFlashMessage($type, $message)
    {
        $messages = $this->getData('_flash_messages', array());
        $messages[$type][] = $message;
        $this->setData('_flash_messages', $messages);
        return $this;
    }

    /**
     * @param $message
     * @return Session
     */
    public function addSuccessMessage($message)
    {
        return $this->addFlashMessage('success', $message);
    }
    public function getSuccessMessages()
    {
        $messages = $this->getData('_flash_messages', array());

        $data = array_key_exists('success', $messages) ? $messages['success'] : null;

        unset($messages['success']);
        $this->setData('_flash_messages', $messages);

        return $data;
    }

    /**
     * @param $message
     * @return Session
     */
    public function addErrorMessage($message)
    {
        return $this->addFlashMessage('error', $message);
    }
    public function getErrorMessages()
    {
        $messages = $this->getData('_flash_messages', array());

        $data = array_key_exists('error', $messages) ? $messages['error'] : null;

        unset($messages['error']);
        $this->setData('_flash_messages', $messages);

        return $data;
    }
}
