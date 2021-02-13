-- 1. Give the name of the capitals of all the countries that border the Baltic Sea.
/*
SELECT DISTINCT country.capital
FROM geo_sea
JOIN country ON country.code = geo_sea.country
WHERE geo_sea.sea = 'Baltic Sea';
*/

-- robin lösning
/*
SELECT DISTINCT country.capital
FROM public.geo_sea JOIN public.country ON geo_sea.country = country.code
WHERE geo_sea.sea = 'Baltic Sea';
*/

-- 2. Give the name and area of the world's northernmost desert.
/*
SELECT name, area
FROM desert
WHERE (coordinates).latitude = (
	SELECT MAX((coordinates).latitude)
	FROM desert
);
*/

-- robin lösning
/*
SELECT desert.name, desert.area	
FROM public.desert
WHERE (desert.coordinates).latitude 
   IN (SELECT MAX((desert.coordinates).latitude)
	   FROM public.desert );
*/

-- 3. Generate a table of all the mountain ranges that contain at
--   least 3 mountains and the number of countries each range touches.
/*
SELECT mountains AS range, COUNT(DISTINCT country) AS countries_touched
FROM mountain INNER JOIN geo_mountain ON mountain.name = geo_mountain.mountain
WHERE mountains IS NOT NULL
GROUP BY mountains HAVING COUNT(DISTINCT mountain) >= 3;
*/


-- 4. Which hemisphere has the largest lake/desert area ratio, the eastern or the western?
-- eastern: longitude > 0, western: longitude < 0
/*
SELECT
	(CASE WHEN (lake_west.sum / desert_west.sum) < (lake_east.sum / desert_east.sum) THEN 'east' ELSE 'west' END) AS answer
FROM
	(SELECT SUM(area) FROM lake WHERE (coordinates).longitude < 0) AS lake_west,
	(SELECT SUM(area) FROM lake WHERE (coordinates).longitude > 0) AS lake_east,
	(SELECT SUM(area) FROM desert WHERE (coordinates).longitude < 0) AS desert_west,
	(SELECT SUM(area) FROM desert WHERE (coordinates).longitude > 0) AS desert_east
*/

-- 5. Generate the continent names and the number of cities on each continent that are situated
--     no more than 35 degrees from the Equator, for those continents that have an land area above 9000000 sq km.
-- latitude gives degrees off equator

-- SELECT continent, COUNT(_city.name)
-- FROM city AS _city
-- JOIN encompasses AS _enc ON _city.country = _enc.country
-- JOIN continent AS _cont ON _cont.name = _enc.continent
-- WHERE ABS(_city.latitude) <= 35 AND (_cont.area) > 9000000
-- GROUP BY continent
-- ORDER BY count DESC;

-- 6 List the number of rivers for each European country in descending order.

/*
SELECT COUNT(DISTINCT geo_river.river), countryname
FROM geo_river JOIN 
   (SELECT country.code AS countrycode, country.name AS countryname
    FROM encompasses JOIN country ON encompasses.country = country.code
	WHERE encompasses.continent = 'Europe') AS A on geo_river.country = A.countrycode
	GROUP BY countryname
	ORDER BY COUNT(DISTINCT geo_river.river) DESC;
*/

-- 7 Names of the organizations in Europe containing the word 'Nuclear', with an unknown date of establishment.

/*
SELECT *	
FROM organization
WHERE organization.name like '%Nuclear%' 
   AND organization.country IN 
      (SELECT country
       FROM encompasses
       WHERE encompasses.continent = 'Europe') 
   AND organization.established IS NULL;
*/

-- 8. Show a list of country name and projected populations in
--    10, 25,50 and 100 years if current demographic trends
--    continue unabated.

-- SELECT
    -- c.name, /*c.population, p.population_growth,*/
    -- ROUND(c.population * (p.population_growth ^ 10)) AS y10,
    -- ROUND(c.population * (p.population_growth ^ 25)) AS y25,
    -- ROUND(c.population * (p.population_growth ^ 50)) AS y50,
    -- ROUND(c.population * (p.population_growth ^ 100)) AS y100
-- FROM country AS c
    -- JOIN population AS p on c.code = p.country
-- ORDER BY c.name ASC;

-- 9. Create a view EightThousanders(name,mountains,height,coordinates) which
   -- includes the mountains over or equal to the height of 8000 meters. Query
   -- for the countries including EightThousanders. Try to avoid materializing the
   -- whole Mountain relation. Verify via EXPLAIN ANALYSE.

-- CREATE MATERIALIZED VIEW EightThousanders (name, mountains, height, coordinates)
CREATE MATERIALIZED VIEW EightThousanders (name, mountains, height, coordinates)
AS SELECT name, mountains, height, coordinates
FROM mountain
WHERE height >= 8000;

-- /*EXPLAIN ANALYSE SELECT * FROM EightThousanders;*/

EXPLAIN ANALYSE SELECT DISTINCT s.country_name
FROM EightThousanders AS e
JOIN
    (SELECT g.mountain, c.code, c.name AS country_name
     FROM geo_mountain AS g
     JOIN country AS c ON g.country = c.code) AS s ON e.name = s.mountain;

DROP MATERIALIZED VIEW EightThousanders;






-- WITH RECURSIVE s(n) AS(                     -- working table called s with attributes (n)
    -- SELECT 1 AS n                           -- create initial working table
        -- UNION ALL                           -- discard (UNION) or dont discard (UNION ALL) duplicates
    -- SELECT n + 1 FROM s WHERE n < 100       -- replace working table, allowed to refer to current working table
-- )
-- SELECT n FROM s;

-- SELECT *
-- FROM borders AS b
-- JOIN country AS c ON c.name = 'Sweden' AND (b.country1 = c.code OR b.country2 = c.code);
-- WITH RECURSIVE path(country1, country2) AS(
    -- (SELECT country1, country2
     -- FROM borders
     -- WHERE country1 = (SELECT code FROM country WHERE name = 'Sweden')
        -- OR country2 = (SELECT code FROM country WHERE name = 'Sweden')
    -- )
      -- UNION
    -- (SELECT borders.country1, borders.country2
     -- FROM borders
     -- JOIN path ON
        -- borders.country1 = path.country1
        -- OR borders.country1 = path.country2
        -- OR borders.country2 = path.country1
        -- OR borders.country2 = path.country2
    -- ))
-- SELECT DISTINCT country.name AS country_name
-- FROM path
-- JOIN country ON country.code = path.country1
  -- UNION
-- SELECT DISTINCT country.name AS country_name
-- FROM path
-- JOIN country ON country.code = path.country2;
/*EXCEPT SELECT country.name FROM country
JOIN encompasses ON country.code = encompasses.country
AND (encompasses.continent = 'Europe' OR encompasses.continent = 'Africa'
OR encompasses.continent = 'Asia');*/
