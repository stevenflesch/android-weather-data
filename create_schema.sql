BEGIN TRANSACTION;
CREATE TABLE "runways" (
	`_id`	INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE,
	`runway_icao`	TEXT,
	`runway_length`	TEXT,
	`runway_width`	TEXT,
	`runway_surface`	TEXT,
	`runway_lighted`	INTEGER,
	`runway_le_ident`	TEXT,
	`runway_le_heading_degT`	REAL,
	`runway_he_ident`	TEXT,
	`runway_he_heading_degT`	REAL
);
CREATE TABLE "countries" (
	`_id`	INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE,
	`country_code`	TEXT,
	`country_name`	TEXT,
	`country_continent`	TEXT,
	`country_wikipedia_link`	TEXT
);
CREATE TABLE "airports" (
	`_id`	INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE,
	`airport_icao`	TEXT UNIQUE,
	`airport_iata`	TEXT,
	`airport_name`	TEXT,
	`airport_nicename`	TEXT,
	`airport_countrycode`	TEXT,
	`airport_state`	TEXT,
	`airport_latitude`	REAL,
	`airport_longitude`	REAL,
	`airport_metar`	INTEGER DEFAULT 0,
	`airport_taf`	INTEGER DEFAULT 0,
	`airport_nexrad`	INTEGER DEFAULT 0
);
COMMIT;
