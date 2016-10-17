# android-weather-data
This repository contains various open-source data sources for use in Android/Java programs.  It contains data on airports, runways, weather acronyms, and more.  Also included are various scripts to expedite the process of obtaining/updating and combining various data sources.

## File Explanations
**WeatherConstants.java** - A collection of constant values for use in dealing with METAR/TAF data, based on the [MetarConstants](https://github.com/arimus/jweather/blob/master/src/net/sf/jweather/metar/MetarConstants.java) from the [jWeather library](https://github.com/arimus/jweather).

**adds_stations.xml** - Data grabbed from ADDS on station information available for weather data.

**airport-xml-to-sql.php** - PHP script to turn adds_stations.xml into SQL statements for importing into sqlite.

**airports.csv** - Data grabbed from ourairports.com (pushed for archival purposes).

**airports.db** - sqlite Database file with information on airports (with METAR/TAF/NEXRAD availability), runways, and countries.

**countries.csv** - Data grabbed from ourairports.com (pushed for archival purposes).

**runways.csv** - Data grabbed from ourairports.com (pushed for archival purposes).

**select_join.sql** - SQL script to copy IATA three-letter airport codes to ADDS/ICAO airports. (ourairports.com -> ADDS)

**trim_runways.sql** - SQL script to delete runways from ourairports.com that are not in the list of ADDS stations.

**update_names.sql** - SQL script to copy "nice" names from ourairport.com to ADDS station airports.


## LICENSE INFO
>All content is released under an LGPL 2.1 license and is free for personal and commercial derivative work, as long as the license remains intact and complied with.
