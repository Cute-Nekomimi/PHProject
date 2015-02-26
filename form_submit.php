<?php 
	session_start();

  $db = new PDO("sqlite:course.db");
  $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $db->exec( 'PRAGMA foreign_keys=ON;' );

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
  $sql_query->closeCursor();

  $b_correctCourseCount = ($cols==4);
  $b_hasAllValues = ($b_firstNameExists && $b_lastNameExists && $b_emailExists && $b_dobExists);
  $b_hasPhoto =  ((!$_FILES["photo"]["error"]) && $_FILES["photo"]["type"]=="image/jpeg");
  $b_hasEmail = (!(filter_var($ass_values["email"],FILTER_VALIDATE_EMAIL) == false));
  $b_hasDOB = preg_match("/[0-9][0-9]-(0[0-9]|1[0-2])-[0-9]{4}/",$ass_values["date-of-birth"]);
  $b_allClear = ($b_correctCourseCount && $b_hasAllValues && $b_hasPhoto && $b_hasEmail && $b_hasDOB);
  
  $result = "";


    $ftp_server = "localhost";
    $ftp_username = "php";
    $ftp_userpass = "TYPE:PHP";
    $ftp_conn = ftp_connect($ftp_server) or $result = "Could not connect to server";
    if ($result == "") {
      $login = ftp_login($ftp_conn,$ftp_username, $ftp_userpass) or $result = "Could not authenticate to server";
      if ($result == "") {
        $file = $_FILES["photo"]['tmp_name'];
        $up_filename = $ass_values['email'] . ".jpg";
        $up_dir = "/";
        if (!(ftp_put($ftp_conn,$up_dir.$up_filename,$file,FTP_BINARY))) {
          $result = "Could not upload file to FTP server";
        }
      }
    }

  if ($b_allClear && $result == "") {
    $_SESSION['check'] = true;
    $_SESSION['first-name'] = $ass_values['first-name'];
    $_SESSION['last-name'] = $ass_values['last-name'];
    $_SESSION['email'] = $ass_values['email'];
    $_SESSION['date-of-birth'] = $ass_values['date-of-birth'];
    $_SESSION['course-1'] = $ass_values['course-1'];
    $_SESSION['course-2'] = $ass_values['course-2'];
    $_SESSION['course-3'] = $ass_values['course-3'];
    $_SESSION['course-4'] = $ass_values['course-4'];
    $_SESSION['photo'] = $_FILES['photo'];
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
    <div id="data">
      <div class="table">

        <div class="table-row">
          <div class="table-cell table-start cell-left">
            <span class="head">Name</span>
          </div>
          <div class="table-cell table-start cell-right">
            <span class="content"><?php echo $ass_values['last-name'] . ", ". $ass_values['first-name']; ?></span>
          </div>
        </div>

        <div class="table-row">
          <div class="table-cell cell-left">
            <span class="head">Email:</span>
          </div>
          <div class="table-cell cell-right">
            <span class="content"><?php echo $ass_values['email']; ?></span>
          </div>
        </div>

        <div class="table-row">
          <div class="table-cell cell-left">
            <span class="head">Date of Birth:</span>
          </div>
          <div class="table-cell cell-right">
            <span class="content"><?php echo $ass_values['date-of-birth']; ?></span>
          </div>
        </div>

        <div class="table-row">
          <div class="table-cell cell-left">
            <span class="head">Courses:</span>
          </div>
          <div class="table-cell cell-right">
            <span class="content"><?php echo str_replace("-"," ",$asi_course1[0]) . $asi_course1[1]; ?><br/>
                                  <?php echo str_replace("-"," ",$asi_course2[0]) . $asi_course2[1]; ?><br/>
                                  <?php echo str_replace("-"," ",$asi_course3[0]) . $asi_course3[1]; ?><br/>
                                  <?php echo str_replace("-"," ",$asi_course4[0]) . $asi_course4[1]; ?></span>
          </div>
        </div>

        <div class="table-row">
          <div class="table-cell table-end cell-left">
            <span class="head">Image:</span>
          </div>
          <div class="table-cell table-end cell-right">
            <img class="content" src="images/<?php echo $ass_values['email'] . ".jpg";?>" alt="Your Image"/>
         </div>
        </div>

      </div> 
		<form id="root-form" method="POST" action="form_final.php" enctype="multipart/form-data">
      <input type="submit" value="Confirm">
    </form>
    </div> 
  </body>
</html>
