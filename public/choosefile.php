<?php
 
/**
 * Choosing which resource type is needed
 * @copyright (C) 2013 by OKFN Belgium
 * @license AGPLv3
 * @author Leen De Baets
 * @author Jeppe Knockaert
 * @author Nicolas Dierck
 */

//needed for conntecting to the client
use Guzzle\Http\Client;
//needed for the PUT request
use Guzzle\Http\Message;
use Guzzle\Http\Query;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

$client = new Client(HOSTNAME);

// getting information about all possible resource types 
$request = $client->get('tdtinfo/admin.json');
$obj = $request->send()->getBody();
$jsonobj = json_decode($obj);

$possibleresourcetype = $jsonobj->admin->create;

$app->match('/package/resourcetype', function (Request $request) use ($possibleresourcetype,$app) {

	// Create a Silex form with all the possible resourcetypes
	$form = $app['form.factory']->createBuilder('form');

	foreach ($possibleresourcetype as $key => $value) {
		$possibilities[$key] = $key;
	}

	$form = $form->add('Type','choice',array('choices' => $possibilities, 
											'multiple' => false, 
											'expanded' => true,
											'label' => false));

	$form = $form->getForm();

	// If the method is POST, validate the form
	if ('POST' == $request->getMethod()) {
		$form->bind($request);
		if ($form->isValid()) {
			// getting the data from the form
			$data = $form->getData();
			

			// Redirect to specific page of the resource type
			return $app->redirect('../../package/'.$data['Type'].'type');
		}
	}

	// display the form
	$twigdata['form'] = $form->createView();
	// adding the datafields title and function for the twig file
	$twigdata['title']= "Choose resource type";
	$twigdata['header']= "Resource types";
	$twigdata['button']= "Choose";
	return $app['twig']->render('form.twig', $twigdata);

});