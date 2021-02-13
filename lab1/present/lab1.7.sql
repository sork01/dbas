-- 7. Names of the organizations in Europe containing the word 'Nuclear',
--    with an unknown date of establishment.

SELECT *	
FROM organization
WHERE organization.name LIKE '%Nuclear%' 
   AND organization.country IN 
      (SELECT country
       FROM encompasses
       WHERE encompasses.continent = 'Europe')
   AND organization.established IS NULL;
