-- Use this file to delete runways from ourairports.com that are not in the list of ADDS stations.
DELETE FROM runways WHERE runways.airport_ident NOT IN (SELECT airports.airport_icao FROM airports);