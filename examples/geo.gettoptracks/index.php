<?php

echo ' <h1><i>geo.getTopTracks is currently broken on Last.FM\'s end. You cannot use it</i></h1>';

$file = fopen('../auth.txt', 'r');
$apiKey = fgets($file);
$secret = fgets($file);
$username = fgets($file);
$sessionKey = fgets($file);
$subscriber = fgets($file);

require '../../lastfmapi/lastfmapi.php';

$location = 'Spain';

$geoClass = new lastfmApiGeo($apiKey, $location);

if ( $tracks = $geoClass->getToTracks() ) {
	echo '<b>Data Returned</b>';
	echo '<pre>';
	print_r($tracks);
	echo '</pre>';
}
else {
	die('<b>Error '.$geoClass->error['code'].' - </b><i>'.$geoClass->error['desc'].'</i>');
}

?>