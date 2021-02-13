-- 3. Generate a table of all the mountain ranges that
--    contain at  least 3 mountains  and the number of
--    countries each range touches.

SELECT
    mountains AS range,
    COUNT(DISTINCT country) AS countries_touched
FROM mountain
    JOIN geo_mountain ON mountain.name = geo_mountain.mountain
WHERE mountains IS NOT NULL
GROUP BY mountains HAVING COUNT(DISTINCT mountain) >= 3;
