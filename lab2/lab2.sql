-- *neode.command* setgo PGPASSWORD=goldenrule psql --username dyb --dbname lab2 < lab2.sql

-- clear the db
DROP SCHEMA public CASCADE;
CREATE SCHEMA public;

CREATE TABLE Students -- straightforward
(
id SERIAL PRIMARY KEY,
name varchar(255)
);
-- FDs:
-- id -> name


CREATE TABLE Courses -- straightforward
(
id SERIAL PRIMARY KEY,
name varchar(255)
);
-- FDs:
-- id -> name


CREATE TABLE Recitations -- straightforward
(
id SERIAL PRIMARY KEY,
courseID int NOT NULL REFERENCES Courses(id),
number varchar(64)
);
-- FDs:
-- id -> courseID number


CREATE TABLE RecitationTracks -- straightforward
(
recitationID int NOT NULL REFERENCES Recitations(id),
trackName varchar(64),
CONSTRAINT RecitationTracks_pkey PRIMARY KEY (recitationID, trackName)
);



CREATE TABLE Results -- straightforward
(
studentID int REFERENCES Students(id),
recitationID int REFERENCES Recitations(id),
score varchar(64),
CONSTRAINT Results_pkey PRIMARY KEY (studentID, recitationID)
);
-- FDs:
-- studentID recitationID -> score


CREATE TABLE Problems -- straightforward
(
id SERIAL PRIMARY KEY,
content TEXT
);
-- FDs:
-- id -> content


-- CREATE TYPE assignment AS -- worth it?
-- (
    -- number int,
    -- part varchar(3)
-- );


CREATE TABLE AssignedProblems -- connect problem to recitation with alias
(
recitationID int REFERENCES Recitations(id),
assignNo int,
assignPart varchar(3),
problemID int REFERENCES Problems(id),
CONSTRAINT AssignedProblems_pkey PRIMARY KEY (recitationID, assignNo, assignPart)
);
-- FDs:
-- recitationID assignNo assignPart -> problemID


CREATE TABLE Combinations -- define a combination to exist (with a score)
(
recitationID int REFERENCES Recitations(id),
comboID int,
score int,
CONSTRAINT Combo_pkey PRIMARY KEY (recitationID, comboID)
);
-- FDs:
-- recitationID comboID -> score


CREATE TABLE CombinationProblems -- assign problems to a combination
(
recitationID int,
comboID int,
assignNo int,
assignPart varchar(3),
FOREIGN KEY (recitationID, comboID) REFERENCES Combinations,
FOREIGN KEY (recitationID, assignNo, assignPart) REFERENCES AssignedProblems,
CONSTRAINT CombinationProblems_pkey PRIMARY KEY (recitationID, comboID, assignNo, assignPart)
);
-- FDs:
-- none


CREATE TYPE recitationmark AS ENUM ('attended', 'presented-passed', 'presented-failed');

CREATE TABLE AttendanceMarks -- register student attendance marks
(
studentID int REFERENCES Students(id),
recitationID int,
assignNo int,
assignPart varchar(3),
mark recitationmark,
-- track varchar(64)
FOREIGN KEY (recitationID, assignNo, assignPart) REFERENCES AssignedProblems,
CONSTRAINT Attendance_pkey PRIMARY KEY (studentID, recitationID, assignNo, assignPart)
);
-- FDs:
-- studentID recitationID assignNo assignPart -> mark


-- "track" in Attendance violates 3NF:
--
-- Definition: whenever A1 A2 ... AN -> B1 B2 ... BN is a nontrivial FD
--             then either the set A = {A1 A2 ... AN} is a superkey
--             or those B's that are not in A are each a member of
--             some candidate key
--
-- We have studentid recitationid -> track
-- 1) {studentid, recitationid} is not a superkey since it does not
--      functionally determine assignno, assignpart or mark
-- 2) track is not part of a candidate key:
--  we have FDs
--      studentid recitationid -> track
--      studentid recitationid assignno assignpart -> mark track
--  the only candidate key is {studentid, recitationid, assigno, assignpart}

CREATE TABLE AttendanceTrack -- register which track a student attended
(
studentID int REFERENCES Students(id),
recitationID int REFERENCES Recitations(id),
track varchar(64),
CONSTRAINT AttendanceTrack_pkey PRIMARY KEY (studentID, recitationID)
);
-- FDs:
-- studentID recitationID -> track



-- student loggar in, väljer recitation, väljer grupp, får fram
-- kryssboxar för alla assignade problem, kryssar i de hen har
-- lösningar för och submittar



-- INSERT INTO Students (name)
-- VALUES ('Mikael Forsberg');

-- INSERT INTO Students (name)
-- VALUES ('Robin Gunning');

-- INSERT INTO Courses (name)
-- VALUES ('Bananböjarkursen');

-- SELECT * FROM Courses;

-- INSERT INTO Recitations (courseID,number)
-- VALUES ('1','82s');





