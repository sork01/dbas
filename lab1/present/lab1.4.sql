-- 4. Which hemisphere has the largest lake/desert area ratio,
--    the eastern or the western?

-- eastern: longitude > 0, western: longitude < 0


SELECT
	(CASE WHEN
        (lake_west.sum / desert_west.sum) < (lake_east.sum / desert_east.sum)
    THEN 'east'
    ELSE 'west' END) AS answer
FROM
	(SELECT SUM(area) FROM lake
        WHERE (coordinates).longitude < 0) AS lake_west,
	(SELECT SUM(area) FROM lake
        WHERE (coordinates).longitude > 0) AS lake_east,
	(SELECT SUM(area) FROM desert
        WHERE (coordinates).longitude < 0) AS desert_west,
	(SELECT SUM(area) FROM desert
        WHERE (coordinates).longitude > 0) AS desert_east
