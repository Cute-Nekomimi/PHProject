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

  
?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="shift_jis">
    <title>Form</title>
  </head>
  <body>
    <?php 
      if ($firstNameExists) {
        echo "True";
      }
      else {
        echo "False";
      }
     ?>
  </body>
</html>
