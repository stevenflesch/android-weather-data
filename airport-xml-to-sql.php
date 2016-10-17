<?php

/**
Converts XML output from ADDS about weather stations to SQL.
**/

// Constants
$debug = TRUE;					// Change when wanting to change data.
$download = FALSE;
$xmlfile = "adds_stations.xml";	// Local file to work with.
$strInfoXMLPath = "https://aviationweather.gov/adds/dataserver_current/httpparam?dataSource=stations&requestType=retrieve&format=xml&stationString=*";

// Grab ALL station info from ADDS.
if($download) {
	$fileData = file_get_contents($strInfoXMLPath);
	file_put_contents($xmlfile, $fileData);
}

$xml = simplexml_load_file($xmlfile) or die("Error: Cannot create object");
echo "<pre>";
$stations = $xml->data;

foreach($stations->children() as $station) {
	$airport_icao = $station->station_id;
	$airport_name = $station->site;
	
	$site_types = $station->site_type->children();
	if(@$site_types->count() > 0) {
		$has_metar = (int)$site_types->METAR->count();
		$has_taf = (int)$site_types->TAF->count();
		$has_nexrad = (int)$site_types->NEXRAD->count();	
	} else {
		$has_metar = 0;
		$has_taf = 0;
		$has_nexrad = 0;
	}
	
	$latitude = $station->latitude;
	$longitude = $station->longitude;
	$country_code = $station->country;
	$state = $station->state;
	
	if($debug) {
		echo $station->station_id . " - " . $station->site . "\n";
		echo "METAR:  " . $has_metar . "\n";
		echo "TAF:    " . $has_taf . "\n";
		echo "NEXRAD: " . $has_nexrad . "\n\n";
	}
	
	// Determine if we need to add to database.
	if(($has_metar == true) || ($has_taf == true)) {
		// Add to database.
		echo "INSERT OR IGNORE INTO airports (airport_icao, airport_name, airport_countrycode, airport_state, airport_latitude, airport_longitude, airport_metar, airport_taf, airport_nexrad) ";
		echo "VALUES (\"$airport_icao\", \"$airport_name\", \"$country_code\", \"$state\", $latitude, $longitude, $has_metar, $has_taf, $has_nexrad);\n";
	}
}

?>