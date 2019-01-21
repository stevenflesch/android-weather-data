# android-weather-data
This repository contains various open-source data sources for use in Android/Java programs.  It contains data on airports, runways, weather acronyms, and more.  Also included are various scripts to expedite the process of obtaining/updating and combining various data sources.

## File Explanations
**create_schema.sql** - (Required by generate-sql.php) Schema generation SQL for use by generate-sql.php.

**generate-database.php** - PHP script to turn downloaded data into SQL statements for importing into SQLite or other DBMS.

**WeatherConstants.java** - A collection of constant values for use in dealing with METAR/TAF data, based on the [MetarConstants](https://github.com/arimus/jweather/blob/master/src/net/sf/jweather/metar/MetarConstants.java) from the [jWeather library](https://github.com/arimus/jweather).

## Archival Data

Occasionally this repo is refreshed with the most current data from the various sources used throughout.  They are archived in the **/archived-data** directory.

## Instructions

> 1. Clone the repo: `git clone https://github.com/stevenflesch/android-weather-data.git`
> 2. Create **download/** and **output/** folders in your project folder.
> 3. Execute the generate-database.php script: `php generate-database.php`
> 4. Import the generated `output/create-database-DATE.sql` file in your DBMS program.

## Projects
This project is currently in use by:
 * [Windsock for Android](https://play.google.com/store/apps/details?id=com.iawix.windsock)

If your project is using this repository, please send a PR or an email to steve [at] iawix.com for inclusion!

## LICENSE INFO
>All content is released under an LGPL 2.1 license and is free for personal and commercial derivative work, as long as the license remains intact and complied with.
