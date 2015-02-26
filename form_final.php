<?php 
	session_start();

  $db = new PDO("sqlite:course.db");
  $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $db->exec( 'PRAGMA foreign_keys=ON;' );

  $ass_values = $_SESSION; //[a]rray (Type) [s]tring (Key) [s]tring (Value)
  
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
  $sql_query->closeCursor();

  $b_correctCourseCount = ($cols==4);
  $b_hasAllValues = ($b_firstNameExists && $b_lastNameExists && $b_emailExists && $b_dobExists);
  $b_hasPhoto =  ((!$ass_values["photo"]["error"]) && $ass_values["photo"]["type"]=="image/jpeg");
  $b_hasEmail = (!(filter_var($ass_values["email"],FILTER_VALIDATE_EMAIL) == false));
  $b_hasDOB = preg_match("/[0-9][0-9]-(0[0-9]|1[0-2])-[0-9]{4}/",$ass_values["date-of-birth"]);

  $b_allClear = ($b_correctCourseCount && $b_hasAllValues && $b_hasPhoto && $b_hasEmail && $b_hasDOB);
  
  $result = "";

  if ($b_allClear) {
    $s_mailTo = "php-project-test-mail@mailinator.com";
    $s_mailSub = "Enrollment from " . $ass_values['first-name'];
    $s_mailMes = "Name: " . $ass_values['last-name'] . ", " . $ass_values['first-name'] . "\n\rEmail: " . $ass_values['email'] . "\n\rDate of Birth: " . $ass_values['date-of-birth'] . "\n\rCourse 1: " . $asi_course1[0] . $asi_course1[1] . "\n\rCourse 2: " . $asi_course2[0] . $asi_course2[1] . "\n\rCourse 3: " . $asi_course3[0] . $asi_course3[1] . "\n\rCourse 4: " . $asi_course4[0] . $asi_course4[1] . "";
    $s_mailHead = "From: Mister.Bushido@00.com" . "\r\n";
    //mail($s_mailTo,$s_mailSub,$s_mailMes,$s_mailHead);
    //mail($ass_values['email'],$s_mailSub,$s_mailMes,$s_mailHead);

    $db->beginTransaction();
    $sql_insert = $db->prepare("INSERT OR REPLACE INTO `student`(`email`,`first_name`,`last_name`) VALUES (:email,:firstname,:lastname);");
    $sql_insert->bindParam(":email",$ass_values['email']);
    $sql_insert->bindParam(":firstname",$ass_values['first-name']);
    $sql_insert->bindParam(":lastname",$ass_values['last-name']);
    $sql_insert->execute();
    $sql_insert->closeCursor();

    $sql_enroll = $db->prepare("INSERT INTO `enrollment`(`email`,`course`,`grade`) VALUES (:email,:course,:grade);");
    $sql_enroll->bindParam(":email",$ass_values['email']);
    $sql_enroll->bindParam(":course",$asi_course1[0]);
    $sql_enroll->bindParam(":grade",$asi_course1[1]);
    $sql_enroll->execute();
    $sql_enroll->bindParam(":email",$ass_values['email']);
    $sql_enroll->bindParam(":course",$asi_course2[0]);
    $sql_enroll->bindParam(":grade",$asi_course2[1]);
    $sql_enroll->execute();
    $sql_enroll->bindParam(":email",$ass_values['email']);
    $sql_enroll->bindParam(":course",$asi_course3[0]);
    $sql_enroll->bindParam(":grade",$asi_course3[1]);
    $sql_enroll->execute();
    $sql_enroll->bindParam(":email",$ass_values['email']);
    $sql_enroll->bindParam(":course",$asi_course4[0]);
    $sql_enroll->bindParam(":grade",$asi_course4[1]);
    $sql_enroll->execute();
    $sql_enroll->closeCursor();

    $ftp_server = "localhost";
    $ftp_username = "php";
    $ftp_userpass = "TYPE:PHP";
    $ftp_conn = ftp_connect($ftp_server) or $result = "Could not connect to server";
    if ($result == "") {
      $login = ftp_login($ftp_conn,$ftp_username, $ftp_userpass) or $result = "Could not authenticate to server";
      if ($result == "") {
        $file = $ass_values["photo"]['tmp_name'];
        $up_filename = $ass_values['email'] . ".jpg";
        $up_dir = "/";
        if (!(ftp_put($ftp_conn,$up_dir.$up_filename,$file,FTP_BINARY))) {
          $result = "Could not upload file to FTP server";
        }
      }
    }
    if ($result == "") {
      $db->commit();
      
    }
    else {
      $db->rollBack();
    }
  }
  else {
    if (!$b_hasAllValues) {
      $result = "Required form values missing";
    } elseif (!$b_hasPhoto) {
      $result = "Photo missing or not a jpg";
    } elseif (!$b_hasEmail) {
      $result = "Email in invalid format";
    } elseif (!$b_hasDOB) {
      $result = "Date of birth in invalid format";
    } elseif (!$b_correctCourseCount) {
      $result = "Duplicate course selected";
    }
  }
  session_unset();
  session_destroy();
	setcookie("PHPSESSID", "", time()-3600, "/");
?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="shift_jis">
    <title>Form</title>
    <link rel="stylesheet" type="text/css" href="confirm_style.css">
  </head>
  <body>
    <?php 
      if ($result != "") {
        echo $result;
        die;
      }
     ?>
  </body>
</html>
