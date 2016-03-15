<?php
namespace LandingPages\Mvc;

use LandingPages\Object;
use LandingPages\Mvc\Session;

/**
 * Class Request
 *
 * @method string getUri()
 * @method void setUri( string )
 * @method string getBaseUri()
 * @method void setBaseUri( string )
 * @method string getTemplateKey()
 * @method void setTemplateKey( string )
 * @method string getRootDirectory()
 * @method void setRootDirectory( string )
 * @method string getUrl()
 * @method void setUrl( string )
 * @method string getLocale()
 * @method void setLocale()
 * @method void setSession(Session $session)
 * @method Session getSession()
 *
 * @package LandingPages
 */
class Request extends Object
{
    public function __construct()
    {
        parent::__construct();
    }
}