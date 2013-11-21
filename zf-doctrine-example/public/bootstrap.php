<?php

// Define path to project 
defined('PROJECT_PATH') || define('PROJECT_PATH', realpath(dirname(__FILE__) . '/..'));

// Define path to application directory
defined('APPLICATION_PATH') || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

// Define application environment
defined('APPLICATION_ENV') || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'development'));

/* Zaladowanie autoloadera compossera */
require_once(PROJECT_PATH . '/vendor/autoload.php');

// Create application, bootstrap, and run
$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/configs/application.ini'
);

$application->bootstrap();

// Retrieve Doctrine Container resource
$container = Zend_Registry::get('doctrine');

/* @var $em \Doctrine\ORM\EntityManager */
$em = $container->getEntityManager('default');

/* @var $config Zend_Config */
$config = Zend_Registry::get('config');

