<?php
 
/**
 * Index page
 * @copyright (C) 2013 by OKFN Belgium
 * @license AGPLv3
 * @author Leen De Baets
 * @author Jeppe Knockaert
 * @author Nicolas Dierck
 */

require_once '../vendor/autoload.php';

//Construct the Silex application
$app = new Silex\Application();

// Set the environment for error reporting
define('ENVIRONMENT', 'development');


/**
 * Alright, here we go!
 *
 * -----------------
 * DANGER ZONE BELOW
 * -----------------
 */

if (defined('ENVIRONMENT'))
{
    switch (ENVIRONMENT)
    {
        case 'development':
            error_reporting(E_ALL);
            ini_set('display_errors', True);
            $app['debug'] = true;
        break;

        case 'testing':
        case 'production':
            error_reporting(0);
        break;

        default:
            exit('The application environment is not set correctly.');
    }
}


// Website document root
define('DOCROOT', __DIR__.DIRECTORY_SEPARATOR);

// Application directory
define('APPPATH', realpath(__DIR__.'/../app/').DIRECTORY_SEPARATOR);

// Vendor directory
define('VENDORPATH', realpath(__DIR__.'/../vendor/').DIRECTORY_SEPARATOR);

// TODO: remove the lines below and use configuration instead

// Hostname of The DataTank installation (Add trailing slash!)
define('HOSTNAME', "...");

// Path to the local tdt-start folder (Add trailing slash!)
define("STARTPATH", "...");

//Register the Twig Service Provider
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => DOCROOT.'views'
));

// Register the Form Service Provider
$app->register(new Silex\Provider\FormServiceProvider());

// Register the Validator and Translation Service Providers
$app->register(new Silex\Provider\ValidatorServiceProvider());
$app->register(new Silex\Provider\TranslationServiceProvider(), array(
    'translator.messages' => array(),
));

// Register the session service provider object
$app->register(new Silex\Provider\SessionServiceProvider());

// If root is asked, redirect to the resource management
$app->get('/', function () use ($app) {
    return $app->redirect('/package');
});

// Start with resources management
require_once 'packagesAndResources.php';
// User management should always be before route management!
require_once 'usermanagement.php'; 
require_once 'routemanagement.php';
require_once 'choosefile.php';
require_once 'generictypes.php';
require_once 'makingCSV.php'; 

$app->run();
