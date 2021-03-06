CREATE TABLE `course` (
  `name` TEXT,
  `grade` INT CHECK(`grade`>=1 and `grade`<=12),
  PRIMARY KEY(`name`,`grade`)
);

CREATE TABLE `student` (
  `email` TEXT PRIMARY KEY,
  `first_name` TEXT NOT NULL,
  `last_name` TEXT NOT NULL
);

CREATE TABLE `enrollment` (
  `email` TEXT,
  `course` TEXT,
  `grade` INT,
  PRIMARY KEY(`email`,`course`,`grade`),
  FOREIGN KEY(`email`) REFERENCES `student`(`email`)
    ON UPDATE CASCADE
    ON DELETE CASCADE,
  FOREIGN KEY(`course`,`grade`) REFERENCES `course`(`name`,`grade`)
    ON UPDATE CASCADE
    ON DELETE RESTRICT
);

CREATE TRIGGER `enrollment_insert`
  BEFORE INSERT ON `enrollment`
  WHEN (SELECT count(*) FROM `enrollment` WHERE `email`=NEW.`email`)>=4
  BEGIN
    SELECT RAISE(ABORT,"Student is already enrolled in 4 courses");
  END;

CREATE TRIGGER `student_insert`
  AFTER INSERT ON `student`
  WHEN (length(NEW.`last_name`)>1)
  BEGIN
    UPDATE `student` SET `last_name`=substr(NEW.`last_name`,1,1) WHERE `email`=NEW.`email`;
  END;

CREATE TRIGGER `student_update`
  AFTER UPDATE ON `student`
  WHEN (length(NEW.`last_name`)>1)
  BEGIN
    UPDATE `student` SET `last_name`=substr(NEW.`last_name`,1,1) WHERE `email`=NEW.`email`;
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
