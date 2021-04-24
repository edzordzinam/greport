<?php

// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),
    get_include_path(),
)));

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../vendor'),
    get_include_path(),
)));

/** Zend_Application */
require_once APPLICATION_PATH . '/../vendor/autoload.php';
//require_once 'Zend/Loader/Autoloader.php';

Zend_Loader_Autoloader::getInstance()->getAutoloaders();
Zend_Loader_Autoloader::getInstance()->registerNamespace(array('Alc_', 'Twitter_', 'Mpdf_', 'Custom_', 'Highcharts_','PDFTable_','Utils_', 'PDFMerger_','REST_'));

// Create application, bootstrap, and run
$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/configs/application.ini'
);

//Zend_Session::start();

$application->bootstrap()
            ->run();