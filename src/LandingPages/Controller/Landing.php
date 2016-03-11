<?php
namespace LandingPages\Controller;

use LandingPages\Controller;
use LandingPages\Response;

class Landing extends Controller
{
    public function actionView()
    {
        return $this->getResponse()
            ->setTemplate( $this->getRequest()->getTemplateKey() );
    }

    public function actionStats()
    {
        return $this->getResponse()
            ->setTemplate( '_stats' );
    }

    public function actionVisits()
    {
        return $this->getResponse()
            ->setTemplate( '_visits' );
    }
}
