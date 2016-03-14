<?php
namespace LandingPages\Controller;

use LandingPages\Mvc\Controller;
use LandingPages\Stats;
use LandingPages\Template;
use LandingPages\Hook;

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

    public function actionPost()
    {
        // Template
        $template_name = $_GET['name'];
        $template_variation = $_GET['variation'];

        $response = $this->getResponse();

        // we need a form_key from session to validate this come from a template form
        if ( $_SESSION['_form_key'] != $_POST['_form_key'] ) {

            if ( $template_name && Template::exists($template_name) ) {
                $response->redirect( Template::getTemplateUrl($template_name) );
            } else {
                $response->redirect( LP_URL );
            }

        } else {

            // Hooks manager
            try {

                $hook = new Hook($template_name, $_POST);
                $hook->exec();

                if (isset($_POST['success_template']) && Template::exists($_POST['success_template'])) {
                    $template = $_POST['success_template'];
                } else {
                    $template = $template_name;
                }

                $response->redirect( Template::getTemplateUrl($template) );

            } catch (Exception $e) {

                if (isset($_POST['error_template']) && Template::exists($_POST['error_template'])) {
                    $template = $_POST['error_template'];
                } else {
                    $template = $template_name;
                }

                $response->redirect( Template::getTemplateUrl($template) );

            }
        }

        return $response;
    }
}
