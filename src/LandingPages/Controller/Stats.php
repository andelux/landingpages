<?php
namespace LandingPages\Controller;

use LandingPages\Mvc\Controller;

class Stats extends Controller
{
    public function actionVisit()
    {
        $template = $_GET['la'];
        $variation = $_GET['va'];

        $stats = new \LandingPages\Model\Stats();
        $stats->register($template, $variation);

        // Return the PNG pixel image
        return $this->getResponse()->setBinaryFile('images/pixel.png', 70, 'image/png');
    }

    public function actionConversion()
    {
        // We ignore this
        //$visit_id = $_GET['id'];
        $conversion_key = $_GET['co'];

        $stats = new \LandingPages\Model\Stats();
        $stats->conversion( $conversion_key );

        // Return the PNG pixel image
        return $this->getResponse()->setBinaryFile('images/pixel.png', 70, 'image/png');
    }
}
