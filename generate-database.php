<?php

/**
generate-database.php

This file serves to automatically download relevant airport data and create a 
SQL file for conversion of these sources to a SQLite database.

Because of the large amount of data generated, you may need to edit the 
memory_limit parameter of your php.ini file.  Similarly, because you may 
want to use the generated SQL for something other than SQLite, we 
generate a generic ".sql" file for use with your favorite database 
manager.

Data generated here is courtesy of the US AVIATION WEATHER CENTER at the 
NATIONAL WEATHER SERVICE, and ourairports.com.

License:
All content is released under an LGPL 2.1 license and is free for personal 
and commercial derivative work, as long as the license remains intact and 
complied with.
**/

// Constants
$debug = 				TRUE;					// Echo to stdout.
$download = 			TRUE;

$timestamp = 			gmdate('Y-m-d_His') . "z";	// Timestamp to use with filenames.
$strInfoXMLPath = 		"https://aviationweather.gov/adds/dataserver_current/httpparam?dataSource=stations&requestType=retrieve&format=xml&stationString=*";
$url_data_airports = 	"http://ourairports.com/data/airports.csv";
$url_data_runways = 	"http://ourairports.com/data/runways.csv";
$url_data_countries = 	"http://ourairports.com/data/countries.csv";

/**
 * Returns string literal NULL in place of null value if value is null, otherwise returns non-null long value.
*/
function printLongOrNull($long) {
	if($long == null) {
		return "NULL";
	} else {
		return $long;
	}
}

// Set filenames if we're downloading, otherwise use default names.
if($download) {
	$xmlfile = "adds_stations_$timestamp.xml";
	$airportsfile = "airports_$timestamp.csv";
	$runwaysfile = "runways_$timestamp.csv";
	$countriesfile = "countries_$timestamp.csv";
} else {
	$xmlfile = "adds_stations.xml";
	$airportsfile = "airports.csv";
	$runwaysfile = "runways.csv";
	$countriesfile = "countries.csv";
}
// Always use the timestamp in our SQL filename.
$sqlfile = "output/create_database_$timestamp.sql";

// Grab ALL station info from ADDS.
if($download) {
	// Get ADDS station data.
	if($debug) echo "Downloading ADDS station info...\n";
	$fileData = file_get_contents($strInfoXMLPath);
	file_put_contents("download/" . $xmlfile, $fileData);
	
	// Get ourairports.com data.
	if($debug) echo "Downloading airport data...\n";
	$fileData = file_get_contents($url_data_airports);
	file_put_contents("download/" . $airportsfile, $fileData);
	
	if($debug) echo "Downloading runway data...\n";
	$fileData = file_get_contents($url_data_runways);
	file_put_contents("download/" . $runwaysfile, $fileData);
	
	if($debug) echo "Downloading country data...\n";
	$fileData = file_get_contents($url_data_countries);
	file_put_contents("download/" . $countriesfile, $fileData);
}

if($debug) echo "<pre>";

// Begin creation of our SQL file generation.
$create_schema_statements = file_get_contents("create_schema.sql") or die("Error: missing create_schema.sql file.");
if($debug) echo $create_schema_statements;
file_put_contents($sqlfile, $create_schema_statements, FILE_APPEND | LOCK_EX);


// Parse ADDS station data.
$xml = simplexml_load_file("download/" . $xmlfile) or die("Error: Cannot create ADDS XML object.");
$stations = $xml->data;

// For speeding up airports.csv processing, we create an array of ICAO identifiers to strip the airports.csv array entries
// not present in the ADDS array.
$arrayADDSairports = array();

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
		echo "METAR: $has_metar // TAF: $has_taf // NEXRAD: $has_nexrad // $station->station_id - $station->site\n";
	}
	
	// Determine if we need to add to database.
	if(($has_metar == true) || ($has_taf == true)) {
		// Add to database.
		$str_insert_adds_airport = "INSERT OR IGNORE INTO airports (airport_icao, airport_name, airport_countrycode, airport_state, airport_latitude, airport_longitude, airport_metar, airport_taf, airport_nexrad) " .
								   "VALUES (\"$airport_icao\", \"$airport_name\", \"$country_code\", \"$state\", $latitude, $longitude, $has_metar, $has_taf, $has_nexrad);\n";
		if($debug) echo $str_insert_adds_airport;		
		file_put_contents($sqlfile, $str_insert_adds_airport, FILE_APPEND | LOCK_EX);
	}
	
	// airports.csv processing array helper
	$arrayADDSairports[] = (string)$airport_icao;
}

// End ADDS processing.

// Add spacing to SQL file.
file_put_contents($sqlfile, "\n\n\n\n", FILE_APPEND | LOCK_EX);

// Loop through airports CSV file to clean up airport names and identifiers.
$airport_array = array_map('str_getcsv', file("download/" . $airportsfile));
array_walk($airport_array, function(&$a) use ($airport_array) {
  $a = array_combine($airport_array[0], $a);
});
array_shift($airport_array); // remove column header

// Update airport names and IATA codes.
foreach($airport_array as $airport_data) {
	if(in_array($airport_data["gps_code"], $arrayADDSairports)) {
		// Ignore airports in airports.csv that aren't in our ADDS file, as they won't be affected.
		if($airport_data["gps_code"] != "") {
			$str_update_airport_name = "UPDATE airports SET airport_nicename = '" . SQLite3::escapeString($airport_data["name"]) . "' WHERE (airport_icao = '" . $airport_data["gps_code"] . "');\n";
			if($debug) echo $str_update_airport_name;
			file_put_contents($sqlfile, $str_update_airport_name, FILE_APPEND | LOCK_EX);
		}
		
		if(($airport_data["iata_code"] != "") && ($airport_data["gps_code"] != "")) {	// only update IATA codes if there is a corresponding ICAO code.
			$str_update_airport_iata = "UPDATE airports SET airport_iata = '" . $airport_data["iata_code"] . "' WHERE (airport_icao = '" . $airport_data["gps_code"] . "');\n";
			if($debug) echo $str_update_airport_iata;
			file_put_contents($sqlfile, $str_update_airport_iata, FILE_APPEND | LOCK_EX);
		}
	}
}

// End airports.csv processing.

// Add spacing to SQL file.
file_put_contents($sqlfile, "\n\n\n\n", FILE_APPEND | LOCK_EX);


// Loop through runways CSV file to add runways to airports.
$runway_array = array_map('str_getcsv', file("download/" . $runwaysfile));
//print_r($runway_array[0]); print_r($runway_array[1]); exit;
// Sometimes, the runways.csv file has an extra comma (ghost column) in the first line of the file, we check for that here and eliminate it if found.
if(count($runway_array[0]) > 19) array_pop($runway_array[0]);
//print_r($runway_array[0]); print_r($runway_array[1]); exit;
array_walk($runway_array, function(&$a) use ($runway_array) {
  $a = array_combine($runway_array[0], $a);
});
array_shift($runway_array); // remove column header

foreach($runway_array as $runway_data) {
	$runway_icao = 						$runway_data["airport_ident"];
	if(in_array($runway_icao, $arrayADDSairports)) {
		// Skip airports not already added to our database.
		$runway_length = 					printLongOrNull($runway_data["length_ft"]);
		$runway_width = 					printLongOrNull($runway_data["width_ft"]);
		$runway_surface = 					$runway_data["surface"];
		$runway_lighted = 					printLongOrNull($runway_data["lighted"]);
		$runway_closed = 					printLongOrNull($runway_data["closed"]);
		$runway_le_ident = 					$runway_data["le_ident"];
		$runway_le_heading_degT = 			printLongOrNull($runway_data["le_heading_degT"]);
		$runway_le_displaced_threshold = 	printLongOrNull($runway_data["le_displaced_threshold_ft"]);
		$runway_le_lat =					printLongOrNull($runway_data["le_latitude_deg"]);
		$runway_le_lon = 					printLongOrNull($runway_data["le_longitude_deg"]);
		$runway_le_elevation = 				printLongOrNull($runway_data["le_elevation_ft"]);
		$runway_he_ident = 					$runway_data["he_ident"];
		$runway_he_heading_degT = 			printLongOrNull($runway_data["he_heading_degT"]);
		$runway_he_displaced_threshold = 	printLongOrNull($runway_data["he_displaced_threshold_ft"]);
		$runway_he_lat =					printLongOrNull($runway_data["he_latitude_deg"]);
		$runway_he_lon = 					printLongOrNull($runway_data["he_longitude_deg"]);
		$runway_he_elevation = 				printLongOrNull($runway_data["he_elevation_ft"]);
		
		$str_insert_runway = 	"INSERT OR IGNORE INTO runways (`runway_icao`, `runway_length`, `runway_width`, `runway_surface`, `runway_lighted`, `runway_closed`, `runway_le_ident`, " .
								"`runway_le_heading_degT`, `runway_le_displaced_threshold`, `runway_le_lat`, `runway_le_lon`, `runway_le_elevation`, " .
								"`runway_he_ident`, `runway_he_heading_degT`, `runway_he_displaced_threshold`, `runway_he_lat`, `runway_he_lon`, `runway_he_elevation`) " .
								"VALUES (\"$runway_icao\", $runway_length, $runway_width, \"$runway_surface\", $runway_lighted, $runway_closed, " .
								"\"$runway_le_ident\", $runway_le_heading_degT, $runway_le_displaced_threshold, $runway_le_lat, $runway_le_lon, $runway_le_elevation, " .
								"\"$runway_he_ident\", $runway_he_heading_degT, $runway_he_displaced_threshold, $runway_he_lat, $runway_he_lon, $runway_he_elevation);\n";
		if($debug) echo $str_insert_runway;
		file_put_contents($sqlfile, $str_insert_runway, FILE_APPEND | LOCK_EX);
	}
}

// End runways.csv processing.

// Add spacing to SQL file.
file_put_contents($sqlfile, "\n\n\n\n", FILE_APPEND | LOCK_EX);


// Loop through countries CSV file to add runways to airports.  We ignore the keywords field.
$country_array = array_map('str_getcsv', file("download/" . $countriesfile));
array_walk($country_array, function(&$a) use ($country_array) {
  $a = array_combine($country_array[0], $a);
});
array_shift($country_array); // remove column header

foreach($country_array as $country_data) {
	$country_code = 			$country_data["code"];
	$country_name = 			$country_data["name"];
	$country_continent = 		$country_data["continent"];
	$country_wikipedia_link = 	$country_data["wikipedia_link"];
	
	$str_insert_country = 	"INSERT OR IGNORE INTO countries (`country_code`, `country_name`, `country_continent`, `country_wikipedia_link`) " .
							"VALUES (\"$country_code\", \"$country_name\", \"$country_continent\", \"$country_wikipedia_link\");\n";
	if($debug) echo $str_insert_country;
	file_put_contents($sqlfile, $str_insert_country, FILE_APPEND | LOCK_EX);
}

// End countries.csv processing.

// Add spacing to SQL file.
file_put_contents($sqlfile, "\n\n\n\n", FILE_APPEND | LOCK_EX);

/****************************************
CLEANUP SECTION
****************************************/

// Clean up runways.
$str_trim_runways = "\n\nDELETE FROM runways WHERE runways.airport_icao NOT IN (SELECT airports.airport_icao FROM airports);\n\n";
if($debug) echo $str_trim_runways;
file_put_contents($sqlfile, $str_trim_runways, FILE_APPEND | LOCK_EX);

// Vacuum/Compact database.
file_put_contents($sqlfile, "\n\nVACUUM;", FILE_APPEND | LOCK_EX);

?>