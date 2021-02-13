-- 6. List the number of rivers for each European country in descending order.


SELECT COUNT(DISTINCT geo_river.river), europe.countryname
FROM geo_river JOIN 
   (SELECT country.code AS countrycode, country.name AS countryname
    FROM encompasses JOIN country ON encompasses.country = country.code
	WHERE encompasses.continent = 'Europe') AS europe ON geo_river.country = europe.countrycode
	GROUP BY europe.countryname
	ORDER BY COUNT(DISTINCT geo_river.river) DESC;
