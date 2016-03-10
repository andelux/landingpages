<?php
/**
 * Receive the form post, and send webhooks, integrate with MailChimp, etc
 *
 */

require 'vendor/autoload.php';

// Core: setup
$LP = new \LandingPages\Core(__DIR__);
$response = new \LandingPages\Response();

// Template
$template_name = $_POST['name'];
$template_variation = $_POST['variation'];

// we need a form_key from session to validate this come from a template form
if ( $_SESSION['_form_key'] != $_POST['_form_key'] ) {

    if ( $template_name && \LandingPages\Template::exists($template_name) ) {
        $response->redirect( \LandingPages\Template::getTemplateUrl($template_name) );
    } else {
        $response->redirect( LANDINGS_URL );
    }

} else {

    // Hooks manager
    try {

        $hook = new LandingPages\Hook($template_name, $_POST);
        $hook->exec();

        if (isset($_POST['success_template']) && \LandingPages\Template::exists($_POST['success_template'])) {
            $template = $_POST['success_template'];
        } else {
            $template = $template_name;
        }

        $response->redirect( \LandingPages\Template::getTemplateUrl($template) );

    } catch (Exception $e) {

        if (isset($_POST['error_template']) && \LandingPages\Template::exists($_POST['error_template'])) {
            $template = $_POST['error_template'];
        } else {
            $template = $template_name;
        }

        $response->redirect( \LandingPages\Template::getTemplateUrl($template) );

    }
}

$response->exec();
