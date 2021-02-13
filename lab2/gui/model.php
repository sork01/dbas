<?php
// Recitations: C,R,D
// you can add a recitation for a given course
// you can get the list of recitations for a given course
// you can remove a recitation

// Courses: C,R,D
// you can add a course
// you can get a list of all courses
// you can remove a course

// Results:
// * given a student and a recitation, compute and store a result

// Students: C,R,D
// you can add a student
// you can get a list of all students
// you can remove a student

// Problems: C,R,D
// you can add a problem
// you can get a list of all problems
// you can remove a problem

// AssignedProblems: C,R,D
// you can assign a given problem to a given recitation
// you can get a list of problems assigned to a given recitation
// you can unassign a given problem from a given recitation

// Combinations: C,R,D
// you can add a new problemset-score combination to a given recitation
// you can get a list of problemset-score combinations for a given recitation
// you can remove a problemset-score combination from a given recitation

// CombinationProblems: C,R,D
// you can assign a recitation-assigned problem to a given combination
// you can get a list of recitation-assigned problems assigned to a given combination
// * you can unassign a recitation-assigned problem from a given combination

// AttendanceMarks: C,R,D
// * given a student, a recitation, an (assignno, assignpart), set a mark
// * given a student and a recitation, get a list of all marked assignments
// * given a student, a recitation, and an (assignno, assignpart), remove a mark

// AttendanceTrack: C,R,D
// * given a student and a recitation, set which track the student attended
// * given a student and a recitation, get which track the student attended
// * given a student and a recitation, remove the information on which track the student attended


class KTHRecitationsModel
{
    const MARK_ATTENDED = 'attended';
    const MARK_PRESENTED_PASSED = 'presented-passed';
    const MARK_PRESENTED_FAILED = 'presented-failed';
    
    public function __construct()
    {
        $this->dbc = new PDO('pgsql:user=dyb password=goldenrule dbname=lab2');
        
        // you can add a student
        $this->stmt_students_add = $this->dbc->prepare('
            INSERT INTO Students (name) VALUES (:name)');
        
        // you can get a list of all students
        $this->stmt_students_get = $this->dbc->prepare('
            SELECT id, name FROM Students ORDER BY name ASC');
            
        // you can get the name of a student
        $this->stmt_students_get_name = $this->dbc->prepare('
            SELECT name FROM Students WHERE id = :id');
        
        // you can remove a student
        $this->stmt_students_del_id = $this->dbc->prepare('
            DELETE FROM Students WHERE id = :id');
        
        // you can add a course
        $this->stmt_courses_add = $this->dbc->prepare('
            INSERT INTO Courses (name) VALUES (:name)');
        
        // you can get a list of all courses
        $this->stmt_courses_get = $this->dbc->prepare('
            SELECT id, name FROM Courses ORDER BY name ASC');
        
        // you can get the name of a course
        $this->stmt_courses_get_name = $this->dbc->prepare('
            SELECT name FROM Courses WHERE id = :id');
        
        // you can remove a course
        $this->stmt_courses_del_id = $this->dbc->prepare('
            DELETE FROM Courses WHERE id = :id');
        
        // you can add a recitation for a given course
        $this->stmt_recitations_add = $this->dbc->prepare('
            INSERT INTO Recitations (courseID, number) VALUES (:courseID, :number)');
        
        // you can get the list of recitations for a given course
        $this->stmt_recitations_get = $this->dbc->prepare('
            SELECT id, number FROM Recitations WHERE courseID = :courseID');
        
        // you can remove a recitation
        $this->stmt_recitations_del_id = $this->dbc->prepare('
            DELETE FROM Recitations WHERE id = :id');
        
        $this->stmt_recitations_add_track = $this->dbc->prepare('
            INSERT INTO RecitationTracks (recitationID, trackName) VALUES (:recitationID, :trackName)');
        
        $this->stmt_recitations_del_track = $this->dbc->prepare('
            DELETE FROM RecitationTracks WHERE recitationID = :recitationID AND trackName = :trackName');
        
        $this->stmt_recitations_get_tracks = $this->dbc->prepare('
            SELECT trackName FROM RecitationTracks WHERE recitationID = :recitationID');
        
        // you can add a problem
        $this->stmt_problems_add = $this->dbc->prepare('
            INSERT INTO Problems (content) VALUES (:content)');
        
        // you can get a list of all problems
        $this->stmt_problems_get_all = $this->dbc->prepare('
            SELECT id, content FROM Problems ORDER BY id ASC');
        
        // you can remove a problem
        $this->stmt_problems_del_id = $this->dbc->prepare('
            DELETE FROM Problems WHERE id = :id');
        
        // you can assign a given problem to a given recitation
        $this->stmt_problems_assign = $this->dbc->prepare('
            INSERT INTO
                AssignedProblems (assignNo, assignPart, recitationID, problemID)
            VALUES
                (:assignNo, :assignPart, :recitationID, :problemID)');
        
        // you can get a list of problems assigned to a given recitation
        $this->stmt_problems_get_assigned = $this->dbc->prepare('
            SELECT a.assignNo, a.assignPart, p.id, p.content
            FROM AssignedProblems AS a
            INNER JOIN Problems AS p ON a.problemID = p.id
            WHERE a.recitationID = :recitationID');
        
        // you can unassign a given problem from a given recitation
        $this->stmt_problems_unassign = $this->dbc->prepare('
            DELETE FROM AssignedProblems
            WHERE recitationID = :recitationID
              AND problemID = :problemID');
        
        // you can add a new problemset-score combination to a given recitation
        $this->stmt_combinations_add = $this->dbc->prepare('
            INSERT INTO
                Combinations (recitationID, comboID, score)
            VALUES
                (:recitationID, :comboID, :score)');
        
        // you can get a list of problemset-score combinations for a given recitation
        $this->stmt_combinations_get = $this->dbc->prepare('
            SELECT comboID, score
            FROM Combinations
            WHERE recitationID = :recitationID
            ORDER BY comboID ASC');
        
        // you can remove a problemset-score combination from a given recitation
        $this->stmt_combinations_del = $this->dbc->prepare('
            DELETE FROM Combinations
            WHERE recitationID = :recitationID
              AND comboID = :comboID');
        
        // you can assign a recitation-assigned problem to a given combination
        $this->stmt_combinations_assign_problem = $this->dbc->prepare('
            INSERT INTO
                CombinationProblems (comboID, assignNo, assignPart)
            VALUES
                (:comboID, :assignNo, :assignPart)');
        
        // you can get a list of recitation-assigned problems assigned to a given combination
        $this->stmt_combinations_get_assigned_problems = $this->dbc->prepare('
            SELECT cp.assignNo, cp.assignPart, a.problemID
            FROM CombinationProblems cp
                INNER JOIN Combinations c ON c.comboID = cp.comboID
                INNER JOIN AssignedProblems a ON a.recitationID = c.recitationID
            WHERE cp.comboID = :comboID');
        
        // you can unassign a recitation-assigned problem from a given combination
        // $this->stmt_combinations_unassign_problem TODO
        
        // AttendanceMarks: C,R,D <- create, retrieve, delete
        // * given a student, a recitation, an (assignno, assignpart), set a mark
        // * given a student and a recitation, get a list of all marked assignments
        // * given a student, a recitation, and an (assignno, assignpart), remove a mark
        
        // CREATE TABLE AttendanceMarks -- register student attendance marks
        // (
        // studentID int REFERENCES Students(id),
        // recitationID int,
        // assignNo int,
        // assignPart varchar(3),
        // mark recitationmark,
        // -- track varchar(64)
        // FOREIGN KEY (recitationID, assignNo, assignPart) REFERENCES AssignedProblems,
        // CONSTRAINT Attendance_pkey PRIMARY KEY (studentID, recitationID, assignNo, assignPart)
        // );
        
        $this->stmt_attendance_add_mark = $this->dbc->prepare('
            INSERT INTO AttendanceMarks (studentID, recitationID, assignNo, assignPart, mark)
            VALUES (:studentID, :recitationID, :assignNo, :assignPart, :mark)');
        
        // AttendanceTrack: C,R,D
        // * given a student and a recitation, set which track the student attended
        // * given a student and a recitation, get which track the student attended
        // * given a student and a recitation, remove the information on which track the student attended
    }
    
    private function executeStatement($stmt, $data)
    {
        $result = $stmt->execute($data);
        $error = $stmt->errorInfo();
        
        if ((int) $error[0] !== 0)
        {
            throw new RuntimeException($error[2]
                . ' QUERY STRING: [' . $stmt->queryString . ']'
                . ' DATA: [' . var_export($data, TRUE) . ']<END_DATA>');
        }
        
        return $result;
    }
    
    public function reset()
    {
        $this->dbc->exec(file_get_contents('../lab2.sql'));
    }
    
    public function addStudent($name)
    {
        return $this->executeStatement($this->stmt_students_add, array(
            'name' => $name));
    }
    
    public function getStudents()
    {
        $this->executeStatement($this->stmt_students_get, array());
        return $this->stmt_students_get->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getStudentName($id)
    {
        $this->executeStatement($this->stmt_students_get_name, array(
            'id' => $id));
        
        return $this->stmt_students_get_name->fetchAll(PDO::FETCH_ASSOC)[0]['name'];
    }
    
    public function removeStudentByID($id)
    {
        return $this->executeStatement($this->stmt_students_del_id, array(
            'id' => $id));
    }
    
    public function addCourse($name)
    {
        return $this->executeStatement($this->stmt_courses_add, array(
            'name' => $name));
    }
    
    public function getCourses()
    {
        $this->stmt_courses_get->execute();
        return $this->stmt_courses_get->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    public function getCourseName($id)
    {
        $this->executeStatement($this->stmt_courses_get_name, array(
            'id' => $id));
        
        return $this->stmt_courses_get_name->fetchAll(\PDO::FETCH_ASSOC)[0]['name'];
    }
    
    public function removeCourseByID($id)
    {
        return $this->executeStatement($this->stmt_courses_del_id, array(
            'id' => $id));
    }
    
    public function addRecitation($course_id, $recitation_number)
    {
        return $this->executeStatement($this->stmt_recitations_add, array(
            'courseID' => $course_id,
            'number' => $recitation_number));
    }
    
    public function getRecitations($course_id)
    {
        $this->stmt_recitations_get->execute(array('courseID' => $course_id));
        return $this->stmt_recitations_get->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    public function removeRecitationByID($id)
    {
        return $this->stmt_recitations_del_id->execute(array('id' => $id));
    }
    
    public function addRecitationTrack($recitation_id, $track_name)
    {
        return $this->executeStatement($this->stmt_recitations_add_track, array(
            'recitationID' => $recitation_id,
            'trackName' => $track_name));
    }
    
    public function removeRecitationTrack($recitation_id, $track_name)
    {
        return $this->executeStatement($this->stmt_recitations_del_track, array(
            'recitationID' => $recitation_id,
            'trackName' => $track_name));
    }
    
    public function getRecitationTracks($recitation_id)
    {
        $this->executeStatement($this->stmt_recitations_get_tracks, array(
            'recitationID' => $recitation_id));
        
        return $this->stmt_recitations_get_tracks->fetchAll();
    }
    
    public function addProblem($content)
    {
        return $this->stmt_problems_add->execute(array('content' => $content));
    }
    
    public function getAllProblems()
    {
        $this->stmt_problems_get_all->execute();
        return $this->stmt_problems_get_all->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    public function removeProblemByID($id)
    {
        return $this->executeStatement($this->stmt_problems_del_id, array(
            'id' => $id));
    }
    
    public function assignProblem($recitation_id, $assign_no, $assign_part, $problem_id)
    {
        return $this->executeStatement($this->stmt_problems_assign, array(
            'recitationID' => $recitation_id,
            'assignNo' => $assign_no,
            'assignPart' => $assign_part,
            'problemID' => $problem_id));
    }
    
    public function getAssignedProblems($recitation_id)
    {
        $this->stmt_problems_get_assigned->execute(array(
            'recitationID' => $recitation_id));
        
        return $this->stmt_problems_get_assigned->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    public function addAttendanceMark($recitation_id, $student_id, $assign_no, $assign_part, $mark)
    {
        switch ($mark)
        {
            case self::MARK_ATTENDED:
            case self::MARK_PRESENTED_PASSED:
            case self::MARK_PRESENTED_FAILED:
                break;
            
            default:
                throw new Exception('INVALID PARAMETER OUT OF RANGE BAD NOT ACCEPT GO AWAY');
                return;
        }
        
        return $this->executeStatement($this->stmt_attendance_add_mark, array(
            'recitationID' => $recitation_id,
            'studentID' => $student_id,
            'assignNo' => $assign_no,
            'assignPart' => $assign_part,
            'mark' => $mark));
    }
    
    private
        $dbc,
        $stmt_students_add,
        $stmt_students_get,
        $stmt_students_get_name,
        $stmt_students_del_id,
        $stmt_courses_add,
        $stmt_courses_get,
        $stmt_courses_get_name,
        $stmt_courses_del_id,
        $stmt_recitations_add,
        $stmt_recitations_get,
        $stmt_recitations_del_id,
        $stmt_recitations_add_track,
        $stmt_recitations_del_track,
        $stmt_recitations_get_tracks,
        $stmt_problems_add,
        $stmt_problems_get_all,
        $stmt_problems_del_id,
        $stmt_problems_assign,
        $stmt_problems_get_assigned,
        $stmt_problems_unassign,
        $stmt_combinations_add,
        $stmt_combinations_get,
        $stmt_combinations_del,
        $stmt_combinations_assign_problem,
        $stmt_combinations_get_assigned_problems,
        $stmt_attendance_add_mark
        ;
}

class KTHRecitationsModelTests
{
    public function __construct(KTHRecitationsModel $model)
    {
        $this->model = $model;
    }
    
    public function runAll()
    {
        $this->testAddGetCourse();
        $this->testAddGetStudent();
        $this->testAddGetProblem();
        $this->testAddGetRecitation();
        $this->testRecitationsReferentialIntegrity();
        $this->testAssignProblem();
        $this->testAssignProblemReferentialIntegrity();
    }
    
    public function testAddGetCourse()
    {
        $random = mt_rand();
        
        $this->model->reset();
        $this->model->addCourse('Datateknik' . $random);
        
        // if failed: was unable to insert a course
        assert($this->model->getCourses()[0]['name'] == 'Datateknik' . $random);
    }
    
    public function testAddGetStudent()
    {
        $random = mt_rand();
        
        $this->model->reset();
        $this->model->addStudent('Mikael' . $random);
        
        // if failed: was unable to insert a student
        assert($this->model->getStudents()[0]['name'] == 'Mikael' . $random);
    }
    
    public function testAddGetProblem()
    {
        $random = mt_rand();
        
        $this->model->reset();
        $this->model->addProblem('Problem: ' . $random);
        
        // if failed: was unable to insert a problem
        assert($this->model->getAllProblems()[0]['content'] == 'Problem: ' . $random);
    }
    
    public function testAddGetRecitation()
    {
        $random = mt_rand();
        
        $this->model->reset();
        
        $this->model->addCourse('Datateknik' . $random);
        $course = $this->model->getCourses()[0];
        
        $this->model->addRecitation($course['id'], $random);
        
        // if failed: was unable to insert recitation for existing course
        assert($this->model->getRecitations($course['id'])[0]['number'] == $random);
    }
    
    public function testRecitationsReferentialIntegrity()
    {
        $random = mt_rand();
        $this->model->reset();
        
        try
        {
            $this->model->addRecitation($random, $random);
            
            // if failed: was able to insert recitation with no matching course
            assert(FALSE);
        }
        catch (RuntimeException $e) { }
    }
    
    public function testAssignProblem()
    {
        $random = mt_rand();
        
        $this->model->reset();
        
        $this->model->addCourse('Datateknik' . $random);
        $course = $this->model->getCourses()[0];
        
        $this->model->addRecitation($course['id'], $random);
        $recitation = $this->model->getRecitations($course['id'])[0];
        
        $this->model->addProblem('Problem: ' . $random);
        $problem = $this->model->getAllProblems()[0];
        
        $this->model->assignProblem($recitation['id'], $random, 'a', $problem['id']);
        $assigned = $this->model->getAssignedProblems($recitation['id'])[0];
        
        // if any failed: unable to assign existing problems to existing recitations for existing courses
        assert($assigned['assignno'] == $random);
        assert($assigned['assignpart'] == 'a');
        assert($assigned['content'] == 'Problem: ' . $random);
    }
    
    public function testAssignProblemReferentialIntegrity()
    {
        $random1 = mt_rand();
        $random2 = mt_rand();
        $random3 = mt_rand();
        
        $this->model->reset();
        
        try
        {
            $this->model->assignProblem($random1, $random2, 'a', $random3);
            
            // if failed: was able to assign nonexistant problem to nonexistant recitation
            assert(FALSE);
        }
        catch (RuntimeException $e) { }
        
        $random1 = mt_rand();
        $random2 = mt_rand();
        $random3 = mt_rand();
        
        $this->model->reset();
        
        $this->model->addProblem('Problem: ' . $random1);
        $problem = $this->model->getAllProblems()[0];
        
        try
        {
            $this->model->assignProblem($random1, $random2, 'a', $problem['id']);
            
            // if failed: was able to assign existing problem to nonexistant recitation
            assert(FALSE);
        }
        catch (RuntimeException $e) { }
        
        $random1 = mt_rand();
        $random2 = mt_rand();
        $random3 = mt_rand();
        
        $this->model->reset();
        
        $this->model->addCourse('Datateknik' . $random1);
        $course = $this->model->getCourses()[0];
        
        $this->model->addRecitation($course['id'], $random2);
        $recitation = $this->model->getRecitations($course['id'])[0];
        
        try
        {
            $this->model->assignProblem($recitation['id'], $random2, 'a', $random3);
            
            // if failed: was able to assign nonexistant problem to existing recitation
            assert(FALSE);
        }
        catch (RuntimeException $e) { }
    }
    
    private
        $model;
}

// $tests = new KTHRecitationsModelTests(new KTHRecitationsModel());
// $tests->runAll();
