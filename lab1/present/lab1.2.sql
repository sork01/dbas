-- 2. Give the name and area of the world's northernmost desert.

SELECT name, area
FROM desert
WHERE (coordinates).latitude = (
	SELECT MAX((coordinates).latitude)
	FROM desert
);
