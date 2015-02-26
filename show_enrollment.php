<?php
  $db = new PDO("sqlite:course.db");
  $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $db->exec( 'PRAGMA foreign_keys=ON;' );

  $result = "";

  $sql_listCourse = $db->prepare("SELECT * FROM `course`;");
  $sql_listCourse->execute();
  $courses = $sql_listCourse->fetchAll();
  $sql_listCourse->closeCursor();
  foreach ($courses as $course) {
    $sql_listStudents = $db->prepare("SELECT `first_name`, `last_name` FROM `student` WHERE `email` IN (SELECT `email` FROM `enrollment` WHERE `course`='" . $course['name'] . "' and `grade`='" . $course['grade'] . "');");
    $result .= "<div>";   
    $result .= "<span class=\"bold\">";
    $result .= str_replace("-"," ",$course['name']) . " " . $course['grade'];
    $result .= "</span><br/>";

    $sql_listStudents->execute();
    $students = $sql_listStudents->fetchAll();
    $sql_listStudents->closeCursor();
    foreach ($students as $student) {
      $result .= $student['first_name'] . " " . $student['last_name'] . "<br/>";
    }
    $result .= "</div><br/>";
  }
  
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="shift_jis">
    <title>Form</title>
    <link rel="stylesheet" type="text/css" href="list_style.css">
  </head>
  <body>
    <?php echo $result; ?>
  </body>
</html>
