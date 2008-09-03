<?php

// Include the API
require '../../lastfmapi/lastfmapi.php';

// Get the session auth data
$file = fopen('../auth.txt', 'r');
// Put the auth data into an array
$authVars = array(
	'apiKey' => trim(fgets($file)),
	'secret' => trim(fgets($file)),
	'username' => trim(fgets($file)),
	'sessionKey' => trim(fgets($file)),
	'subscriber' => trim(fgets($file))
);
// Pass the array to the auth class to eturn a valid auth
$auth = new lastfmApiAuth('setsession', $authVars);

$apiClass = new lastfmApi();
$trackClass = $apiClass->getPackage($auth, 'track');

// Setup the variables
$methodVars = array(
	'artist' => 'Green Day',
	'track' => 'American Idiot',
	'recipient' => 'matto1990@yahoo.co.uk', // Either a username or an email address
	'message' => 'You might like this'
);

if ( $trackClass->share($methodVars) ) {
	echo '<b>Done</b>';
}
else {
	die('<b>Error '.$trackClass->error['code'].' - </b><i>'.$trackClass->error['desc'].'</i>');
}

?>