-- 9. Create a view EightThousanders(name,mountains,height,coordinates) which
-- 9. Create a view EightThousanders(name,mountains,height,coordinates) which
--    includes the mountains over or equal to the height of 8000 meters. Query
--    for the countries including EightThousanders. Try to avoid materializing the
--    whole Mountain relation. Verify via EXPLAIN ANALYSE.

CREATE MATERIALIZED VIEW EightThousanders (name, mountains, height, coordinates)
AS SELECT name, mountains, height, coordinates
FROM mountain
WHERE height >= 8000;

-- /*EXPLAIN ANALYSE SELECT * FROM EightThousanders;*/

EXPLAIN ANALYSE SELECT DISTINCT s.country_name
-- SELECT DISTINCT s.country_name
FROM EightThousanders AS e
JOIN
    (SELECT g.mountain, c.code, c.name AS country_name
     FROM geo_mountain AS g
     JOIN country AS c ON g.country = c.code) AS s ON e.name = s.mountain;

DROP MATERIALIZED VIEW EightThousanders;
