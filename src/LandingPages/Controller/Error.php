<?php
namespace LandingPages\Controller;

use LandingPages\Mvc\Controller;

/**
 * Class Index
 *
 * @package LandingPages\Controller
 */
class Error extends Controller
{

    public function action404()
    {
        return $this->getResponse()
            ->addHeader('HTTP/1.1 404 Not Found',null,404)
            ->setTemplate('_404')
            ;
    }

    public function action500()
    {
        return $this->getResponse()
            ->addHeader('HTTP/1.1 500 Server error',null,500)
            ->setTemplate('_500')
            ;
    }

}
