<?php
require 'vendor/autoload.php';

// Core: setup
$LP = new \LandingPages\Core(__DIR__);

// Route
$router = new \LandingPages\Router();

// Get response object
$response = $router->getResponse();

// Exec response
$response->exec();
