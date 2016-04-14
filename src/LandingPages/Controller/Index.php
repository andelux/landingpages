<?php
namespace LandingPages\Controller;

use LandingPages\Mvc\Controller;

/**
 * Class Index
 *
 * @package LandingPages\Controller
 */
class Index extends Controller
{

    public function actionIndex()
    {
        return $this->getResponse()
            ->setTemplate('views/index');
    }

}
