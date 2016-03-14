<?php
namespace LandingPages\Controller;

use LandingPages\Controller;
use LandingPages\Response;

class Stats extends Controller
{
    public function actionVisit()
    {
        $template = $_GET['la'];
        $variation = $_GET['va'];

        \LandingPages\Stats::visit( $template, $variation );

        // Return the PNG pixel image
        return $this->getResponse()->setBinaryFile('images/pixel.png', 70, 'image/png');
    }

    public function actionConversion()
    {
        // We ignore this
        //$visit_id = $_GET['id'];
        $conversion_key = $_GET['co'];

        \LandingPages\Stats::conversion( $conversion_key );

        // Return the PNG pixel image
        return $this->getResponse()->setBinaryFile('images/pixel.png', 70, 'image/png');
    }
}
