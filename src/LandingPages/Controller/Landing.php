<?php
namespace LandingPages\Controller;

use LandingPages\Model\Conversions;
use LandingPages\Model\Hooks;
use LandingPages\Mvc;
use LandingPages\Mvc\Controller;
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
        $response = $this->getResponse();

        $conversions = new Conversions();
        $response->setParam('result', $conversions->resetCollection()
            ->addFieldToFilter('template', $this->getRequest()->getTemplateKey())
            ->addOrderBy('rate DESC, conversions DESC')
            ->collection()
        );

        $response->setTemplate('_stats');

        return $response;
    }

    public function actionVisits()
    {
        $response = $this->getResponse();
        $response->setParam('result', Mvc::getModel('visits')->getAll());
        $response->setTemplate('_visits');

        return $response;
    }

    public function actionPost()
    {
        // Template
        //$template_name = $_GET['t'];
        $template_name = $this->getRequest()->getTemplateKey();
        $template_variation = $this->getParam('v');

        $response = $this->getResponse();

        // we need a form_key from session to validate this come from a template form
        if ( $this->getSession()->getData('_form_key') != $this->getParam('_form_key') ) {
        //if ( $_SESSION['_form_key'] != $_POST['_form_key'] ) {
            if ( $template_name && Template::exists($template_name) ) {
                $response->redirect( Template::getTemplateUrl($template_name) );
            } else {
                $response->redirect( LP_URL );
            }

        } else {

            // Hooks manager
            try {
                Mvc::getModel('hooks')->exec( $template_name, $_POST);

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
