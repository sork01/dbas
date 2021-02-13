-- *neode.command* setgo PGPASSWORD=goldenrule psql --username dyb --dbname lab2 < tmp.sql

-- combinations ger score
-- combinationproblems ger alla assignments för ett score

-- select c1.score, c2.assignno, c2.assignpart
-- from combinations as c1
    -- inner join combinationproblems as c2 on c1.comboid = c2.comboid

-- select *
-- from combinations as c1
    -- inner join combinationproblems as c2 on c1.comboid = c2.comboid;

-- select assignno, assignpart from attendancemarks where recitationid=1 and studentid=1;

-- select max(c.score)
-- from combinations as c
    -- inner join combinationproblems as cp on c.comboid=cp.comboid
-- where not exists (select assignno, assignpart
                 -- from combinationproblems as cp2
                 -- where cp2.comboid=c.comboid
                   -- and not exists (select assignno, assignpart
                                   -- from attendancemarks
                                   -- where recitationid=1
                                     -- and studentid=1))

select max(c.score)
from combinations as c
    inner join combinationproblems as cp on c.comboid=cp.comboid
where c.recitationid=1
  and not exists (select cp2.assignno, cp2.assignpart
                  from combinationproblems as cp2
                  where cp2.comboid=cp.comboid
                    and not exists (select s.assignno, s.assignpart
                                    from attendancemarks as s
                                    where studentid=1
                                      and recitationid=1
                                      and s.assignno=cp2.assignno
                                      and s.assignpart=cp2.assignpart))
