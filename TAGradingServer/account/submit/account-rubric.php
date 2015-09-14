<?php 

include "../../toolbox/functions.php";

$rubric_id = intval($_GET['hw']);

$student_rcs = $_GET["student"];
$db->query("SELECT student_id FROM students WHERE student_rcs=?", array($student_rcs));
$row = $db->row();
$student_id = $row['student_id'];
	
$params = array($rubric_id, $student_rcs);
$db->query("SELECT grade_id FROM grades WHERE rubric_id=? AND student_rcs=?", $params);
$row = $db->row();

$status = intval($_POST['status']);
$submitted = intval($_POST['submitted']);
$_POST["late"] = intval($_POST['late']);

if(isset($row["grade_id"]))
{
	$grade_id = intval($row["grade_id"]);
    if (isset($_POST['overwrite']) && intval($_POST['overwrite']) == 1) {
        $params = array(clean_string($_POST["comment-general"]), \app\models\User::$user_id, $_POST["late"], $submitted, $status, $grade_id, $_POST['active_assignment']);
        $db->query("UPDATE grades SET grade_comment=?, grade_finish_timestamp=NOW(), grade_user_id=?, grade_days_late=?, grade_is_regraded=1, grade_submitted=?, grade_status=?, grade_active_assignment=? WHERE grade_id=?", $params);
    }
    else {
        $params = array(clean_string($_POST["comment-general"]), $_POST["late"], $submitted, $status, $grade_id, $_POST['active_assignment']);
        $db->query("UPDATE grades SET grade_comment=?, grade_finish_timestamp=NOW(), grade_days_late=?, grade_is_regraded=1, grade_submitted=?, grade_status=?, grade_active_assignment=? WHERE grade_id=?", $params);
    }
}
else
{
	$params = array($rubric_id, $student_id, clean_string($_POST["comment-general"]), \app\models\User::$user_id, $_POST["late"], $student_rcs, $submitted, $status, $_POST['active_assignment']);
	$db->query("INSERT INTO grades (rubric_id, student_id, grade_comment, grade_finish_timestamp, grade_user_id, grade_days_late, student_rcs, grade_submitted, grade_status, grade_active_assignment) VALUES (?,?,?,NOW(),?,?,?,?,?,?)", $params);
	
	$params = array($rubric_id, $student_rcs);
	$db->query("SELECT grade_id FROM grades WHERE rubric_id=? AND student_rcs=?", $params);
	$row = $db->row();
	$grade_id = intval($row["grade_id"]);
}

$params = array($rubric_id);
$db->query("SELECT * FROM questions WHERE rubric_id=? ORDER BY question_part_number, question_number", $params);
foreach($db->rows() as $row)
{ 	
	$params = array($grade_id, $row["question_id"]);
	$db->query("DELETE FROM grades_questions WHERE grade_id=? AND question_id=?", $params);
	
	$params = array($grade_id, $row["question_id"], $_POST["grade-" . $row["question_part_number"] . "-" . $row["question_number"]],  clean_string($_POST["comment-" . $row["question_part_number"] . "-" . $row["question_number"]]));
	$db->query("INSERT INTO grades_questions (grade_id, question_id, grade_question_score, grade_question_comment) VALUES (?,?,?,?)", $params);
}

if($_GET["individual"] == "1")
{
	header('Location: '.$BASE_URL.'/account/account-summary.php?course='.$_GET['course'].'&hw=' . $_GET["hw"]);
}
else
{
	header('Location: '.$BASE_URL.'/account/index.php?course='.$_GET['course'].'&hw=' . $_GET["hw"]);
}
?>