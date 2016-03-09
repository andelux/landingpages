<?php
require_once 'setup.php';

// ROUTER
if ( substr(URI,-5) == '.html' ) {

    // name = "sem/landing-name"
    $template_name = substr(URI,0,-5);

    // Translating "sem/landing-name" to "sem/my-real-template-file" as is in translations files ("translations/en_US.csv")
    $template_name = __URL($template_name);

    // If URL is http://mysite.com/landing/whatever-you-want/?stats
    if ( isset($_GET['stats']) ) require 'stats.php'; // ...and end here
    // If URL is http://mysite.com/landing/whatever-you-want/?visits
    if ( isset($_GET['visits']) ) require 'visits.php'; // ...and end here

    // Visit ID
    if ( isset($_SESSION['visit_id']) && $_SESSION['visit_id'] ) {
        $visit_id = $_SESSION['visit_id'];
    } else {
        $visit_id = $_SESSION['visit_id'] = stats_generate_id();
    }

    // Load template with its language
    if ( is_template($template_name) ) {
        header('Content-Type: text/html; charset=utf-8');
        template($template_name);
        exit();
    }

} else if ( URI == 'stats.png' ) {

    // Usage statistics
    switch ( $_GET['ac'] ) {
        // Register a visit
        case 'visit': stats_visit(); break;

        // Register a conversion
        case 'conversion': stats_conversion($_GET['id'],$_GET['co']); break;
    }

    // Return the PNG pixel image
    header('Content-Type: image/png');
    header('Content-Length: 70');
    readfile('images/pixel.png');
    exit();

}

// ERROR 404
header('HTTP/1.0 404 Not Found');
header('Content-Type: text/html; charset=utf-8');
template('404');
