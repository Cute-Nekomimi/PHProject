CREATE TABLE `course` (
  `name` TEXT,
  `grade` INT CHECK(`grade`>=1 and `grade`<=12),
  PRIMARY KEY(`name`,`grade`)
);

CREATE TABLE `student` (
  `name` TEXT PRIMARY KEY
);

CREATE TABLE `enrollment` (
  `student` TEXT,
  `course` TEXT,
  `grade` INT,
  PRIMARY KEY(`student`,`course`,`grade`),
  FOREIGN KEY(`student`) REFERENCES `student`(`name`)
    ON UPDATE CASCADE
    ON DELETE CASCADE,
  FOREIGN KEY(`course`,`grade`) REFERENCES `course`(`name`,`grade`)
    ON UPDATE CASCADE
    ON DELETE RESTRICT
);

CREATE TRIGGER `enrollment_insert`
  BEFORE INSERT ON `enrollment`
  WHEN (SELECT count(*) FROM `enrollment` WHERE `student`=NEW.`student`)>=4
  BEGIN
    SELECT RAISE(ABORT,"Student is already enrolled in 4 courses");
  END;

INSERT INTO `course`(`name`,`grade`)  VALUES
  ("Accounting",11),
  ("Biology",11),
  ("Communications",12),
  ("Digital-Arts",11),
  ("English",12),
  ("French",11),
  ("History",12),
  ("Law",12),
  ("Physical-Education",10),
  ("Robotics",11);
