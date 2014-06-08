-- Скрипт сгенерирован Devart dbForge Studio for MySQL, Версия 6.1.166.0
-- Домашняя страница продукта: http://www.devart.com/ru/dbforge/mysql/studio
-- Дата скрипта: 08.06.2014 14:56:26
-- Версия сервера: 5.1.65-community-log
-- Версия клиента: 4.1

CREATE DATABASE IF NOT EXISTS linerdon_service
CHARACTER SET utf8
COLLATE utf8_general_ci;

USE linerdon_service;

CREATE TABLE IF NOT EXISTS events (
  id int(11) NOT NULL AUTO_INCREMENT,
  text text NOT NULL,
  permission int(11) NOT NULL,
  datetime timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
)
ENGINE = INNODB
AUTO_INCREMENT = 9
AVG_ROW_LENGTH = 3276
CHARACTER SET utf8
COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS groups (
  id int(11) NOT NULL AUTO_INCREMENT,
  name varchar(255) NOT NULL,
  info text NOT NULL,
  parent_id int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (id)
)
ENGINE = INNODB
AUTO_INCREMENT = 110
AVG_ROW_LENGTH = 1820
CHARACTER SET utf8
COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS lessons (
  id int(11) NOT NULL AUTO_INCREMENT,
  schedule_item_id int(11) NOT NULL,
  title mediumtext NOT NULL,
  date date NOT NULL,
  PRIMARY KEY (id),
  CONSTRAINT FK_lessons_schedule_id FOREIGN KEY (schedule_item_id)
  REFERENCES schedule (id) ON DELETE NO ACTION ON UPDATE NO ACTION
)
ENGINE = INNODB
AUTO_INCREMENT = 1
CHARACTER SET utf8
COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS marks (
  id int(11) NOT NULL AUTO_INCREMENT,
  lesson_id int(11) NOT NULL,
  student_id int(11) NOT NULL,
  mark int(11) NOT NULL,
  PRIMARY KEY (id),
  CONSTRAINT FK_marks_lessons_id FOREIGN KEY (lesson_id)
  REFERENCES lessons (id) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT FK_marks_students_id FOREIGN KEY (student_id)
  REFERENCES students (id) ON DELETE NO ACTION ON UPDATE NO ACTION
)
ENGINE = INNODB
AUTO_INCREMENT = 1
CHARACTER SET utf8
COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS schedule (
  id int(11) NOT NULL AUTO_INCREMENT,
  teacher_subject_group int(11) NOT NULL,
  day enum ('1', '2', '3', '4', '5', '6', '7') NOT NULL,
  lesson_number int(11) NOT NULL,
  PRIMARY KEY (id),
  CONSTRAINT FK_schedule_teahcers_subjects_groups_id FOREIGN KEY (teacher_subject_group)
  REFERENCES teachers_subjects_groups (id) ON DELETE NO ACTION ON UPDATE NO ACTION
)
ENGINE = INNODB
AUTO_INCREMENT = 1
CHARACTER SET utf8
COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS students (
  id int(11) NOT NULL AUTO_INCREMENT,
  user_id int(11) NOT NULL,
  PRIMARY KEY (id),
  CONSTRAINT FK_students_users_id FOREIGN KEY (user_id)
  REFERENCES users (id) ON DELETE NO ACTION ON UPDATE NO ACTION
)
ENGINE = INNODB
AUTO_INCREMENT = 37
AVG_ROW_LENGTH = 3276
CHARACTER SET utf8
COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS students_groups (
  id int(11) NOT NULL AUTO_INCREMENT,
  group_id int(11) NOT NULL,
  student_id int(11) NOT NULL,
  PRIMARY KEY (id),
  CONSTRAINT FK_students_groups_groups_id FOREIGN KEY (group_id)
  REFERENCES groups (id) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT FK_students_groups_students_id FOREIGN KEY (student_id)
  REFERENCES students (id) ON DELETE NO ACTION ON UPDATE NO ACTION
)
ENGINE = INNODB
AUTO_INCREMENT = 37
AVG_ROW_LENGTH = 3276
CHARACTER SET utf8
COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS subjects (
  id int(11) NOT NULL AUTO_INCREMENT,
  name varchar(64) NOT NULL,
  PRIMARY KEY (id)
)
ENGINE = INNODB
AUTO_INCREMENT = 21
AVG_ROW_LENGTH = 1260
CHARACTER SET utf8
COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS teachers (
  id int(11) NOT NULL AUTO_INCREMENT,
  user_id int(11) NOT NULL,
  PRIMARY KEY (id),
  CONSTRAINT FK_teachers_users_id FOREIGN KEY (user_id)
  REFERENCES users (id) ON DELETE NO ACTION ON UPDATE NO ACTION
)
ENGINE = INNODB
AUTO_INCREMENT = 20
AVG_ROW_LENGTH = 8192
CHARACTER SET utf8
COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS teachers_subjects (
  id int(11) NOT NULL AUTO_INCREMENT,
  teacher_id int(11) NOT NULL,
  subject_id int(11) NOT NULL,
  PRIMARY KEY (id),
  CONSTRAINT FK_teachers_subjects_subjects_id FOREIGN KEY (subject_id)
  REFERENCES subjects (id) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT FK_teachers_subjects_teachers_id FOREIGN KEY (teacher_id)
  REFERENCES teachers (id) ON DELETE NO ACTION ON UPDATE NO ACTION
)
ENGINE = INNODB
AUTO_INCREMENT = 44
AVG_ROW_LENGTH = 2730
CHARACTER SET utf8
COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS teachers_subjects_groups (
  id int(11) NOT NULL AUTO_INCREMENT,
  teacher_subject_id int(11) NOT NULL,
  group_id int(11) NOT NULL,
  PRIMARY KEY (id),
  CONSTRAINT FK_teahcers_subjects_groups_groups_id FOREIGN KEY (group_id)
  REFERENCES groups (id) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT FK_teahcers_subjects_groups_teachers_subjects_id FOREIGN KEY (teacher_subject_id)
  REFERENCES teachers_subjects (id) ON DELETE NO ACTION ON UPDATE NO ACTION
)
ENGINE = INNODB
AUTO_INCREMENT = 1
CHARACTER SET utf8
COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS tokens (
  id int(11) NOT NULL AUTO_INCREMENT,
  uid int(11) NOT NULL,
  token varchar(32) NOT NULL,
  PRIMARY KEY (id),
  CONSTRAINT FK_tokens_users_id FOREIGN KEY (uid)
  REFERENCES users (id) ON DELETE NO ACTION ON UPDATE NO ACTION
)
ENGINE = INNODB
AUTO_INCREMENT = 143
AVG_ROW_LENGTH = 356
CHARACTER SET utf8
COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS users (
  id int(11) NOT NULL AUTO_INCREMENT,
  login varchar(64) NOT NULL,
  passwd varchar(32) NOT NULL,
  name varchar(64) NOT NULL,
  middlename varchar(64) NOT NULL,
  surname varchar(64) NOT NULL,
  home varchar(64) NOT NULL,
  level int(11) NOT NULL,
  PRIMARY KEY (id)
)
ENGINE = INNODB
AUTO_INCREMENT = 57
AVG_ROW_LENGTH = 2048
CHARACTER SET utf8
COLLATE utf8_general_ci;