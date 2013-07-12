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

if (defined('ENVIRONMENT') && strcmp(ENVIRONMENT,'development') == 0){
    $app['debug'] = true;
}

// Website document root
define('UIDOCROOT', __DIR__.DIRECTORY_SEPARATOR);

// Vendor directory
define('UIVENDORPATH', realpath(__DIR__.'/../vendor/').DIRECTORY_SEPARATOR);

// TODO: remove the lines below and use configuration instead

// Path to the local tdt-start folder (Add trailing slash!)
define("STARTPATH", "...");

//Register the Twig Service Provider
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => UIDOCROOT.'views'
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

// Register the security service provider object
$app->register(new Silex\Provider\SecurityServiceProvider(), array(
    'security.firewalls' => array(),
));

// register ...
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());

// must be included first
require_once 'authentication.php';

use Symfony\Component\Security\Http\Firewall;

// Load users from file
// Fetch users from the auth.json file
$filename = STARTPATH."app/config/auth.json";
// This object will also be used by user management and route management, so don't delete it
$userObject = json_decode(file_get_contents($filename));
$users = get_object_vars($userObject);

// Set custom encoder, this should match the encoding used in auth.json
use Symfony\Component\Security\Core\Encoder\PlaintextPasswordEncoder;
$app['security.encoder.digest'] = $app->share(function ($app) {
    return new PlaintextPasswordEncoder();
});

// Check for a user called tdtui in auth.json
if (!isset($users['tdtuiadmin'])){
    echo "You need a user called tdtui in auth.json to continue.";
    exit(1);
}

// Authorization
$app['security.firewalls'] = array(
    'login' => array(
        'pattern' => '^/ui/login\.?[a-z]*$',
    ),
    'secured' => array(
        'pattern' => '^.*$',
        'form' => array('login_path' => '/ui/login', 'check_path' => '/ui/login_check'),
        'users' => array(
            'tdtuiadmin' => array('ROLE_ADMIN', $users['tdtuiadmin']->password)
        )
    )
);

// If root is asked, redirect to the resource management
$app->get('/ui{url}', function () use ($app) {
    return $app->redirect('/ui/package');
});

// The parameters that cannot be edited
$app['session']->set('notedible',array('generic_type' => 'generic_type', 
                                        'resource_type' => 'resource_type',
                                        'columns' => 'columns',
                                        'column_aliases' => 'column_aliases'));

// Get The DataTank hostname for use in /ui/package
$hostname = $this->hostname;

//start with resources management
require_once 'packagesAndResources.php';
require_once 'usermanagement.php';
require_once 'routemanagement.php';
require_once 'choosefile.php';
require_once 'generictypes.php';
require_once 'puttingfile.php';
require_once 'editPackagesAndResources.php';
require_once 'editResource.php';


$app->run();
