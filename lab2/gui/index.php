<?php
error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', 'On');

require('model.php');

define('STATE_LOGIN', 1);
define('STATE_SELECT_COURSE', 2);
define('STATE_SELECT_RECITATION', 3);
define('STATE_SELECT_ASSIGNMENTS', 4);

$state = STATE_LOGIN;

if (isset($_POST['login']) && !empty($_POST['studentID']))
{
    $session_student = $_POST['studentID'];
    $state = STATE_SELECT_COURSE;
}
else if (isset($_POST['selectcourse']) && !empty($_POST['courseID']))
{
    $session_student = $_POST['studentID'];
    $session_course = $_POST['courseID'];
    $state = STATE_SELECT_RECITATION;
}
else if (isset($_POST['selectrecitation']) && !empty($_POST['recitationID']))
{
    $session_student = $_POST['studentID'];
    $session_course = $_POST['courseID'];
    $session_recit = $_POST['recitationID'];
    $state = STATE_SELECT_ASSIGNMENTS;
}

$adminview_css_display = 'none';

function sane_string($str)
{
    return htmlspecialchars($str, ENT_QUOTES | ENT_HTML5, "UTF-8");
}

$model = new KTHRecitationsModel();

if (isset($_GET['reset']))
{
    $model->reset();
    header('Location: index.php', TRUE);
    exit();
}

print_r($_POST);

if (isset($_POST['addcourse']) && !empty($_POST['name']))
{
    $model->addCourse(sane_string($_POST['name']));
}
else if (isset($_POST['delcourse']) && !empty($_POST['course']))
{
    $model->removeCourseByID($_POST['course']);
}
else if (isset($_POST['addstudent']) && !empty($_POST['name']))
{
    $model->addStudent(sane_string($_POST['name']));
}
else if (isset($_POST['delstudent']) && !empty($_POST['student']))
{
    $model->removeStudentByID($_POST['student']);
}
else if (isset($_POST['addrecit']) && !empty($_POST['courseID']) && !empty($_POST['number']))
{
    $model->addRecitation($_POST['courseID'], $_POST['number']);
}
else if (isset($_POST['delrecit']) && !empty($_POST['recitationID']))
{
    $model->removeRecitationByID($_POST['recitationID']);
}
else if (isset($_POST['addproblem']) && !empty($_POST['content']))
{
    $model->addProblem(sane_string($_POST['content']));
}
else if (isset($_POST['delproblem']) && !empty($_POST['problemID']))
{
    $model->removeProblemByID($_POST['problemID']);
}
else if (isset($_POST['assignprob']))
{
    $model->assignProblem($_POST['recitationID'], $_POST['assignNo'], $_POST['assignPart'], $_POST['problemID']);
}
else if (isset($_POST['setattendancemarks']))
{
    $n = 0;
    
    // TODO: put track in attendancetrack
    
    while (isset($_POST['check' . $n]))
    {
        $match = array();
        preg_match('/(\d+);(.+)/', $_POST['check' . $n], $match);
        
        $model->addAttendanceMark(
            $_POST['recitationID'],
            $_POST['studentID'], $match[1], $match[2], KTHRecitationsModel::MARK_ATTENDED);
        
        ++$n;
    }
}

$adminviewshow = array(
    'addcourse',
    'delcourse',
    'addstudent',
    'delstudent',
    'addrecit',
    'delrecit',
    'addproblem',
    'delproblem',
    'assignprob');

if (sizeof(array_intersect($adminviewshow, array_keys($_POST))) > 0)
{
    $adminview_css_display = 'block';
}


?><!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>KTH - Recitations</title>
        <style type="text/css">
            * { font-family: sans-serif; font-size: 9pt; }
            div.spacer { margin-top: 10px; }
            div.panel { border: 1px solid black; background: #dedede; }
            div.inner-panel { margin: 2px; padding: 4px; border: 1px solid #999; }
            select.itemlist { width: 340px; }
        </style>
    </head>
    <body>
        
        <!-- student view -->
        <div id="studentview">
            <div class="panel">
<?php           if ($state == STATE_LOGIN): ?>
                <form method="post" action="index.php">
                    <fieldset>
                        <legend>Student Login</legend>
                        <select name="studentID">
<?php                       foreach ($model->getStudents() as $student): ?>
                            <option value="<?php echo $student['id']; ?>"><?php echo $student['name']; ?></option>
<?php                       endforeach; ?>
                        </select>
                        <input type="submit" name="login" value="Login" />
                    </fieldset>
                </form>
<?php           endif; ?>
<?php           if ($state >= STATE_SELECT_COURSE): ?>
                <form method="post" action="index.php">
                    <fieldset>
                        <legend>User</legend>
                        <p>Logged in as <?php echo $model->getStudentName($session_student); ?></p>
                        <input type="submit" name="logout" value="Log out" />
                    </fieldset>
                </form>
                <form method="post" action="index.php">
                    <fieldset>
                        <legend>Select Course</legend>
                        <select name="courseID">
<?php                       foreach ($model->getCourses() as $course): ?>
                            <option <?php if (isset($_POST['courseID']) && $_POST['courseID'] == $course['id']) { echo 'selected="selected"'; } ?> value="<?php echo $course['id']; ?>"><?php echo $course['name']; ?></option>
<?php                       endforeach; ?>
                        </select>
                        <input type="hidden" name="studentID" value="<?php echo $session_student; ?>" />
                        <input type="submit" name="selectcourse" value="Select" />
                    </fieldset>
                </form>
<?php           endif; ?>
<?php           if ($state >= STATE_SELECT_RECITATION): ?>
                <form method="post" action="index.php">
                    <fieldset>
                        <legend>Select Recitation</legend>
                        <select class="itemlist" name="recitationID">
<?php                       foreach ($model->getRecitations($session_course) as $recit): ?>
                            <option value="<?php echo $recit['id']; ?>"><?php echo $recit['number']; ?></option>
<?php                       endforeach; ?>
                        </select>
                        <input type="hidden" name="studentID" value="<?php echo $session_student; ?>" />
                        <input type="hidden" name="courseID" value="<?php echo $session_course; ?>" />
                        <input type="submit" name="selectrecitation" value="Select" />
                    </fieldset>
                </form>
<?php           endif; ?>
<?php           if ($state == STATE_SELECT_ASSIGNMENTS): ?>
                <form method="post">
                    <fieldset>
                        <legend>Track</legend>
                        <select name="track">
<?php                       foreach ($model->getRecitationTracks($_POST['recitationID']) as $track): ?>
                            <option value="<?php echo $track['trackname']; ?>"><?php echo $track['trackname']; ?></option>
<?php                       endforeach; ?>
                        </select>
                    </fieldset>
                    <br />
                    <fieldset>
                        <legend>Solutions</legend>
<?php                   $n = 0; ?>
<?php                   foreach ($model->getAssignedProblems($_POST['recitationID']) as $problem): ?>
                        <input type="checkbox" name="check<?php echo $n++; ?>" value="<?php echo $problem['assignno'], ';', $problem['assignpart']; ?>" /> <?php echo $problem['assignno'], $problem['assignpart'], ') ', $problem['content']; ?> <br />
<?php                   endforeach; ?>
                    </fieldset>
                    <br />
                    <input type="hidden" name="studentID" value="<?php echo $_POST['studentID']; ?>" />
                    <input type="hidden" name="recitationID" value="<?php echo $_POST['recitationID']; ?>" />
                    <input type="submit" name="setattendancemarks" value="Submit" />
                </form>
<?php           endif; ?>
            </div>
        </div>
        
        
        
        <!-- admin view -->
        <div id="adminview" style="display:<?php echo $adminview_css_display; ?>;">
            <div class="spacer">&nbsp;</div>
            <div class="spacer">&nbsp;</div>
            <div class="panel">
                <form method="post" action="index.php">
                    <fieldset>
                        <legend>Courses</legend>
                        <select class="itemlist" name="courseID" size="5">
<?php                       foreach ($model->getCourses() as $course): ?>
                            <option value="<?php echo $course['id']; ?>"><?php echo $course['name']; ?></option> 
<?php                       endforeach; ?>
                        </select>
                        <input type="submit" name="delcourse" value="Delete Selected" />
                    </fieldset>
                </form>
                <form method="post" action="index.php">
                    <fieldset>
                        <legend>Add Course</legend>
                        <input type="text" name="name" value="" />
                        <input type="submit" name="addcourse" value="Add" />
                    </fieldset>
                </form>
            </div>
            <div class="spacer">&nbsp;</div>
            <div class="panel">
                <form method="post" action="index.php">
                    <fieldset>
                        <legend>Students</legend>
                        <select class="itemlist" name="studentID" size="5">
<?php                       foreach ($model->getStudents() as $student): ?>
                            <option value="<?php echo $student['id']; ?>"><?php echo $student['name']; ?></option> 
<?php                       endforeach; ?>
                        </select>
                        <input type="submit" name="delstudent" value="Delete Selected" />
                    </fieldset>
                </form>
                <form method="post" action="index.php">
                    <fieldset>
                        <legend>Add Student</legend>
                        <input type="text" name="name" value="" />
                        <input type="submit" name="addstudent" value="Add" />
                    </fieldset>
                </form>
            </div>
            <div class="spacer">&nbsp;</div>
            <div class="panel">
<?php           foreach ($model->getCourses() as $course): ?>
<?php           $recitations = $model->getRecitations($course['id']); ?>
<?php           if (sizeof($recitations) < 1): continue; endif; ?>
                <form method="post" action="index.php">
                    <fieldset>
                        <legend>Recitations for <?php echo $course['name']; ?></legend>
                        <select class="itemlist" name="recitationID" size="5">
<?php                       foreach ($model->getRecitations($course['id']) as $recit): ?>
                            <option value="<?php echo $recit['id']; ?>"><?php echo $recit['number']; ?></option>
<?php                       endforeach; ?>
                        </select>
                        <input type="submit" name="delrecit" value="Delete Selected" />
                    </fieldset>
                </form>
<?php           endforeach; ?>
                <form method="post" action="index.php">
                    <fieldset>
                        <legend>Add Recitation</legend>
                        Course: <select class="itemlist" name="courseID">
<?php                       foreach ($model->getCourses() as $course): ?>
                            <option value="<?php echo $course['id']; ?>"><?php echo $course['name']; ?></option> 
<?php                       endforeach; ?>
                        </select><br />
                        Number / Name: <input type="text" name="number" value="" />
                        <input type="submit" name="addrecit" value="Add" />
                    </fieldset>
                </form>
            </div>
            <div class="spacer">&nbsp;</div>
            <div class="panel">
                <form method="post" action="index.php">
                    <fieldset>
                        <legend>Problems</legend>
                        <select class="itemlist" name="problemID" size="5">
<?php                       foreach ($model->getAllProblems() as $problems): ?>
                            <option value="<?php echo $problems['id']; ?>"><?php echo $problems['content']; ?></option> 
<?php                       endforeach; ?>
                        </select>
                        <input type="submit" name="delproblem" value="Delete Selected" />
                    </fieldset>
                </form>
                <form method="post" action="index.php">
                    <fieldset>
                        <legend>Add Problem</legend>
                        <input type="text" name="content" value="" />
                        <input type="submit" name="addproblem" value="Add" />
                    </fieldset>
                </form>
            </div>
            <div class="spacer">&nbsp;</div>
            <div class="panel">
                <form method="post" action="index.php">
                    <fieldset>
                        <legend>Assign Problem</legend>
                        Course:<br/>
                        <select class="itemlist" name="courseID" onchange="e = this.form.getElementsByTagName('select'); for (nth in e) { if (e[nth].name && e[nth].name.startsWith('recits')) { e[nth].style.display='none'; }; }; this.form['recits' + this.value].style.display='block';"> 
<?php                       foreach ($model->getCourses() as $course): ?>
                            <option value="<?php echo $course['id']; ?>"><?php echo $course['name']; ?></option> 
<?php                       endforeach; ?>
                        </select><br /><br />
                        Recitation:<br />
<?php                   foreach ($model->getCourses() as $nth => $course): ?>
                        <select class="itemlist" style="display:<?php echo ($nth == 0 ? 'block' : 'none'); ?>;" id="recits<?php echo $course['id']; ?>" name="recits<?php echo $course['id']; ?>">
<?php                       foreach ($model->getRecitations($course['id']) as $recit): ?>
                            <option value="<?php echo $recit['id']; ?>"><?php echo $recit['number']; ?></option>
<?php                       endforeach; ?>
                        </select>
<?php                   endforeach; ?>
                        <br />
                        Problem:<br />
                        <select class="itemlist" name="problemID">
<?php                       foreach ($model->getAllProblems() as $problems): ?>
                            <option value="<?php echo $problems['id']; ?>"><?php echo $problems['content']; ?></option> 
<?php                       endforeach; ?>
                        </select><br />
                        Assignment Number (e.g "3"): <input type="text" name="assignNo" value="" /><br />
                        Assignment Part: (e.g "a"): <input type="text" name="assignPart" value="" /><br />
                    </fieldset>
                    <input type="hidden" name="recitationID" value="" />
                    <input type="submit" name="assignprob" value="Assign" onclick="this.form['recitationID'].value=this.form['recits' + this.form['courseID'].value].value;" />
                </form>
            </div>
            <p>
                <a href="index.php?reset=1">Reset everything</a>
            </p>
            <div class="spacer">&nbsp;</div>
            <div class="spacer">&nbsp;</div>
            <div class="spacer">&nbsp;</div>
        </div>
        <div style="position:fixed; bottom:0px; padding:4px;" class="panel">
            <p style="margin:0px; padding:0px; color:blue;" onclick="javascript:document.getElementById('adminview').style.display='block';">Admin</a>
        </div>
    </body>
</html>
