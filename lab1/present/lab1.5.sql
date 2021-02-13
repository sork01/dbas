-- 5. Generate the continent names and the number of cities on each continent
--    that are  situated no more  than 35 degrees from the Equator, for those
--    continents that have an land area above 9000000 sq km.

-- latitude gives degrees off equator

SELECT continent, COUNT(_city.name)
FROM city AS _city
    JOIN encompasses AS _enc ON _city.country = _enc.country
    JOIN continent AS _cont ON _cont.name = _enc.continent
WHERE ABS(_city.latitude) <= 35 AND (_cont.area) > 9000000
GROUP BY continent
ORDER BY count DESC;
