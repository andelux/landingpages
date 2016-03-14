<?php
namespace LandingPages\Mvc;

use LandingPages\Object;

/**
 * Class Request
 *
 * @method string getUri()
 * @method void setUri()
 * @method string getBaseUri()
 * @method void setBaseUri()
 * @method string getTemplateKey()
 * @method void setTemplateKey()
 * @method string getRootDirectory()
 * @method void setRootDirectory()
 * @method string getUrl()
 * @method void setUrl()
 * @method string getLocale()
 * @method void setLocale()
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
