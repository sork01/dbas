-- 1. Give the name of the capitals of all the
--    countries that border the Baltic Sea.

SELECT DISTINCT country.capital   -- välj unika huvudstäder 
FROM geo_sea         -- från geo_sea 
JOIN country ON country.code = geo_sea.country  -- slå ihop country med geo_sea där country.code = geo_sea.country
WHERE geo_sea.sea = 'Baltic Sea'; -- och geo_sea.sea = baltic shit
