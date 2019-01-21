# android-weather-data
This repository contains various open-source data sources for use in Android/Java programs.  It contains data on airports, runways, weather acronyms, and more.  Also included are various scripts to expedite the process of obtaining/updating and combining various data sources.

## File Explanations
**create_schema.sql** - (Required by generate-sql.php) Schema generation SQL for use by generate-sql.php.

**generate-sql.php** - PHP script to turn downloaded data into SQL statements for importing into SQLite or other DBMS.

**WeatherConstants.java** - A collection of constant values for use in dealing with METAR/TAF data, based on the [MetarConstants](https://github.com/arimus/jweather/blob/master/src/net/sf/jweather/metar/MetarConstants.java) from the [jWeather library](https://github.com/arimus/jweather).

## Archival Data

Occasionally this repo is refreshed with the most current data from the various sources used throughout.  They are archived in the **/archived-data** directory.


## LICENSE INFO
>All content is released under an LGPL 2.1 license and is free for personal and commercial derivative work, as long as the license remains intact and complied with.
