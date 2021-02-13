-- 10. Give names of all the countries (recursively) reachable from Sweden
--     via borders.

WITH RECURSIVE path(country1, country2) AS(
    (SELECT country1, country2
     FROM borders
     WHERE country1 = (SELECT code FROM country WHERE name = 'Sweden')
        OR country2 = (SELECT code FROM country WHERE name = 'Sweden'))
      UNION
    (SELECT borders.country1, borders.country2
     FROM borders
     JOIN path ON
        borders.country1 = path.country1
        OR borders.country1 = path.country2
        OR borders.country2 = path.country1
        OR borders.country2 = path.country2))
SELECT DISTINCT country.name AS country_name
FROM path
JOIN country ON country.code = path.country1
  UNION
SELECT DISTINCT country.name AS country_name
FROM path
JOIN country ON country.code = path.country2
/*EXCEPT SELECT country.name FROM country
JOIN encompasses ON country.code = encompasses.country
AND (encompasses.continent = 'Europe' OR encompasses.continent = 'Africa'
OR encompasses.continent = 'Asia');*/
ORDER BY country_name ASC;
