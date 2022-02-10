<?php 

function getSQL(&$WKT, int $year) {

   $limit = 5;

	return 

		"SELECT 
			rec_group,
			id, 
			na, 
			url as URL,
			rid as murl, 
			jb->>'@id' as iurl, 
			coalesce(year, $year) as year, 
			concat(jb->>'tileset_url', '', 	jb->'images'->0->'resource'->'service'->>'@id') as sURL, 
			coalesce(jb->>'service_name', '') as sName,
			coalesce(jb->>'zoomLevelFrom','0') as zf, 
			coalesce(jb->>'zoomLevelTo','18') as zt,
			ST_AsGeoJSON(geom) as geojson,
			CASE
			    WHEN ty=0 THEN 'tiles'
			    WHEN ty=1 THEN 'iiif'
			END as ty,
			ST_Area(geom) as geo_area,
			cat_new as cat, 
			CASE
				WHEN cat_new = 'Surrounding' 
			        THEN 0
				WHEN cat_new = 'Taiwan' OR 
				     cat_new = 'Japan' OR 
				     cat_new = 'India' OR 
				     cat_new = 'Korea' OR 
				     cat_new = 'Luxembourg' 
					THEN 1
				WHEN cat_new = 'China' OR 
				     cat_new = 'Russia' 
				    THEN 3
			    WHEN cat_new = 'ASIA' OR 
			         cat_new = 'Africa' OR 
			         cat_new = 'Europe' OR 
			         cat_new = 'North America' OR 
			         cat_new = 'South America' OR 
			         cat_new = 'Oceania' 
			        THEN 4
			    WHEN cat_new = 'World' 
			        THEN 5
			    ELSE 2
			END as cat_int
		FROM (
			SELECT * FROM (
				SELECT *, 'Surrounding'::text as cat_new, 1 as rec_group
				FROM public.rs 
				WHERE ty in (0, 1) AND 
				      ST_AREA(geom) < 270 AND 
				      ST_Contains(geom, ST_GeomFromText('$WKT',4326))
				limit 5 ) a

			union all

			SELECT *, coalesce(cat, '') as cat_new, 2 as rec_group
			FROM public.rs 
			WHERE ty in (0, 1) AND 
			      ST_Contains(geom, ST_GeomFromText('$WKT',4326))

		) c
		order by rec_group, geo_area, cat_int, year;";


}

function getSQL_All(int $year) {

   return 

		"SELECT 
			id, 
			na, 
			url as URL,
			rid as murl, 
			jb->>'@id' as iurl, 
			coalesce(year, $year) as year, 
			concat(jb->>'tileset_url', '', 	jb->'images'->0->'resource'->'service'->>'@id') as sURL, 
			coalesce(jb->>'service_name', '') as sName,
			coalesce(jb->>'zoomLevelFrom','0') as zf, 
			coalesce(jb->>'zoomLevelTo','18') as zt,
			ST_AsGeoJSON(geom) as geojson,
			CASE
			    WHEN ty=0 THEN 'tiles'
			    WHEN ty=1 THEN 'iiif'
			END as ty,
			ST_Area(geom) as geo_area,
			coalesce(cat, '') as cat, 
			CASE
				WHEN cat = 'Surrounding' 
			        THEN 0
				WHEN cat = 'Taiwan' OR 
				     cat = 'Japan' OR 
				     cat = 'India' OR 
				     cat = 'Korea' OR 
				     cat = 'Luxembourg' 
					THEN 1
				WHEN cat = 'China' OR 
				     cat = 'Russia' 
				    THEN 3
			    WHEN cat = 'ASIA' OR 
			         cat = 'Africa' OR 
			         cat = 'Europe' OR 
			         cat = 'North America' OR 
			         cat = 'South America' OR 
			         cat = 'Oceania' 
			        THEN 4
			    WHEN cat = 'World' 
			        THEN 5
			    ELSE 2
			END as cat_int
		FROM public.rs 
		WHERE ty in (0, 1)
		order by cat_int, cat, na;";


}

error_reporting(-1);
ini_set('display_errors', 'On');
header('Content-Type: application/json');

if (isset($_GET["lat"]) && isset($_GET["lng"])) {

	$lat= $_GET["lat"];
	$lng= $_GET["lng"];
	$WKT= "POINT($lng $lat)";

} else if ( isset($_GET["WKT"])) {
	
	$WKT= $_GET["WKT"];	
	
}

$db_connection = pg_connect("host=localhost dbname=postgis user=postgis password=changeme");

$sql = (isset($WKT)) ? getSQL($WKT, date("Y")) : getSQL_All(date("Y")) ;

$result = pg_query($db_connection, $sql);

$rows = array();

while($r = pg_fetch_assoc($result)) {
    $rows[] = $r;
}

echo json_encode($rows);

pg_close($db_connection);

?>