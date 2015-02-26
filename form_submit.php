<?php 
  /*
    first-name
    last-name
    email
    date-of-birth
    course-1
    course-2
    course-3
    course-4
    photo
  */

  $db = new PDO("sqlite:course.db");


  $ass_values = $_POST; //[a]rray (Type) [s]tring (Key) [s]tring (Value)
  $b_firstNameExists = preg_match("/\S+/",$ass_values["first-name"]);
  $b_lastNameExists = preg_match("/\S+/",$ass_values["last-name"]);
  $b_emailExists = preg_match("/\S+/",$ass_values["email"]);
  $b_dobExists = preg_match("/\S+/",$ass_values["date-of-birth"]);

  $asi_course1 = explode(":",$ass_values["course-1"],2);
  $asi_course2 = explode(":",$ass_values["course-2"],2);
  $asi_course3 = explode(":",$ass_values["course-3"],2);
  $asi_course4 = explode(":",$ass_values["course-4"],2);

  $sql_query = $db->prepare("SELECT count(*) FROM `course` WHERE (`name`=:name1 AND `grade`=:grade1) OR (`name`=:name2 AND `grade`=:grade2) OR (`name`=:name3 AND `grade`=:grade3) OR (`name`=:name4 AND `grade`=:grade4);");
  $sql_query->bindParam(":name1",$asi_course1[0]);
  $sql_query->bindParam(":grade1",$asi_course1[1]);
  $sql_query->bindParam(":name2",$asi_course2[0]);
  $sql_query->bindParam(":grade2",$asi_course2[1]);
  $sql_query->bindParam(":name3",$asi_course3[0]);
  $sql_query->bindParam(":grade3",$asi_course3[1]);
  $sql_query->bindParam(":name4",$asi_course4[0]);
  $sql_query->bindParam(":grade4",$asi_course4[1]);

  $sql_query->execute();
  $cols = $sql_query->fetchColumn();


  $b_correctCourseCount = ($cols==4);
  $b_hasAllValues = ($b_firstNameExists && $b_lastNameExists && $b_emailExists && $b_dobExists);
  $b_hasPhoto =  ((!$_FILES["photo"]["error"]) && $_FILES["photo"]["type"]=="image/jpeg");
  $b_hasEmail = (!(filter_var($ass_values["email"],FILTER_VALIDATE_EMAIL) == false));
  $b_hasDOB = preg_match("/[0-9][0-9]-(0[0-9]|1[0-2])-[0-9]{4}/",$ass_values["date-of-birth"]);

  $b_allClear = ($b_correctCourseCount && $b_hasAllValues && $b_hasPhoto && $b_hasEmail && $b_hasDOB);
  
  if ($b_allClear) {
    $result = "yayz!";
  }
  else {
    $result = "$b_correctCourseCount $b_hasAllValues $b_hasPhoto $b_hasEmail $b_hasDOB";
  }
?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="shift_jis">
    <title>Form</title>
  </head>
  <body>
    <?php 
      echo $result;
     ?>
  </body>
</html>
