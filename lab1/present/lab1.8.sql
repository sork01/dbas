-- 8. Show a list of country name and projected populations in
--    10, 25,50 and 100 years if current demographic trends
--    continue unabated.

SELECT
    c.name, /*c.population, p.population_growth,*/
    ROUND(c.population * ((1.0 + (p.population_growth/100)) ^ 10)) AS y10,
    ROUND(c.population * ((1.0 + (p.population_growth/100)) ^ 25)) AS y25,
    ROUND(c.population * ((1.0 + (p.population_growth/100)) ^ 50)) AS y50,
    ROUND(c.population * ((1.0 + (p.population_growth/100)) ^ 100)) AS y100
FROM country AS c
    JOIN population AS p on c.code = p.country
ORDER BY c.name ASC;
