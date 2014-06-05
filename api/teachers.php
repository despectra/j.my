<?php
/**
 * Created by PhpStorm.
 * User: Dmitry
 * Date: 29.05.14
 * Time: 11:32
 */
    require_once 'mysqlbd.php';
    require_once 'checker.php';

    class API {
        function __construct() {
            ;//
        }

        public function addTeacher($params) {
            $my = new MySQLDb();
            try {
                $my->connect();
                $uid = Checker::checkToken($params, $my);
                Checker::checkLevel($uid, 0, $my);

                $firstName = $params["firstName"];
                $middleName = $params["middleName"];
                $secondName = $params["secondName"];
                $login = $params["login"];

                $my->beginTransaction();
                $user = array(
                    "login" => $login,
                    "passwd" => md5(Utils::$_SALT_1.md5("000111").Utils::$_SALT_2),
                    "name" => $firstName,
                    "surname" => $secondName,
                    "middlename" => $middleName,
                    "home" => "/home/$login",
                    "level" => 4
                );
                $userId = $my->insert("users", $user);
                $teacher = array(
                    "user_id" => $userId
                );
                $teacherId = $my->insert("teachers", $teacher);
                $my->commit();
                mkdir("../home/$login");
                return array(
                    "success" => "1",
                    "user_id" => $userId,
                    "teacher_id" => $teacherId
                );

            } catch (exException $e) {
                $my->rollBack();
                throw $e;
            }
        }

        public function getTeachers($params) {
            $my = new MySQLDb();
            $my->connect();
            $uid = Checker::checkToken($params, $my);
            Checker::checkLevel($uid, 0, $my);

            $offset = $params["offset"];
            $count = $params["count"];
            $limit = "";
            if ($offset != "0" || $count != "0") {
                $limit = " LIMIT $offset, $count";
            }
            $my->executeQuery("SELECT teachers.id AS teacher_id,
                                      users.id AS user_id,
                                      users.login AS login,
                                      users.name AS name,
                                      users.middlename AS middlename,
                                      users.surname AS surname
                                FROM users
                                INNER JOIN teachers ON users.id = teachers.user_id
                                $limit");
            $data = array();
            while($row = $my->fetchRow()) {
                array_push($data, $row);
            }
            return array(
                "success" => "1",
                "teachers" => $data
            );
        }

        public function getTeacher($params) {
            $my = new MySQLDb();
            $my->connect();
            Checker::checkToken($params, $my);

            $teacherId = $params["teacher_id"];
            $my->executeQuery("SELECT users.id AS user_id,
                                      users.login AS login,
                                      users.name AS name,
                                      users.middlename AS middlename,
                                      users.surname AS surname,
                                      users.level AS level FROM users INNER JOIN teachers ON users.id = teachers.user_id
                                WHERE teachers.id = $teacherId
                                LIMIT 1");
            if ($row = $my->fetchRow()) {
                return array(
                    "success" => "1",
                    "user_id" => $row["user_id"],
                    "login" => $row["login"],
                    "name" => $row["name"],
                    "middlename" => $row["middlename"],
                    "surname" => $row["surname"],
                    "level" => $row["level"]
                );
            } else {
                return array(
                    "success" => "0",
                    "error_code" => "100",
                    "error_message" => "Данного учителя нет в базе. Возможно, он был удален"
                );
            }
        }

        public function deleteTeachers($params) {
            $my = new MySQLDb();
            try {
                $my->connect();
                $uid = Checker::checkToken($params, $my);
                Checker::checkLevel($uid, 0, $my);

                $my->beginTransaction();
                $this->deleteTeachersImpl($params["teachers"], $my);
                $my->commit();
                return array(
                    "success" => "1"
                );
            } catch (exException $e) {
                $my->rollBack();
                throw $e;
            }
        }

        private function deleteTeachersImpl($teachersIds, $dbConnection) {
            foreach($teachersIds as $id) {
                $dbConnection->select("users INNER JOIN teachers ON users.id = teachers.user_id", array("users.id"), "teachers.id = $id LIMIT 1");
                $row = $dbConnection->fetchRow();
                $uid = $row["id"];
                $dbConnection->delete("teachers", "id = $id");
                $dbConnection->delete("users", "id = $uid");
            }
        }

        public function getSubjectsOfTeacher($params) {
            $my = new MySQLDb();
            $my->connect();
            $uid = Checker::checkToken($params, $my);
            Checker::checkLevel($uid, 0, $my);

            $teacherId = $params["teacher_id"];
            $my->select("teachers_subjects", array("id, subject_id"), "teacher_id = $teacherId");
            $subjectsArray = array();
            while ($row = ($my->fetchRow())) {
                array_push($subjectsArray,
                    array("id" => $row["id"], "subject_id" => $row["subject_id"]));
            }
            return array(
                "success" => "1",
                "subjects" => $subjectsArray
            );
        }

        public function getAvailableSubjectsForTeacher($params) {
            $my = new MySQLDb();
            $my->connect();
            $uid = Checker::checkToken($params, $my);
            Checker::checkLevel($uid, 0, $my);

            $teacherId = $params["teacher_id"];
            $my->executeQuery("
                SELECT id, name FROM subjects
                WHERE id NOT IN (SELECT subject_id FROM teachers_subjects WHERE teacher_id = $$teacherId)"
            );
            $subjectsArray = array();
            while ($row = $my->fetchRow()) {
                array_push($subjectsArray,
                    array("id" => $row["id"],
                        "name" => $row["name"])
                );
            }
            return array(
                "success" => "1",
                "subjects" => $subjectsArray
            );
        }

        public function setSubjectsOfTeacher($params) {
            return $this->setOrUnsetSubjects(true, $params);
        }

        public function unsetSubjectsOfTeacher($params) {
            return $this->setOrUnsetSubjects(false, $params);
        }

        private function setOrUnsetSubjects($doLink, $params) {
            $my = new MySQLDb();
            $my->connect();
            $uid = Checker::checkToken($params, $my);
            Checker::checkLevel($uid, 0, $my);

            $teacherId = $params["teacher_id"];

            $ids = $params[$doLink ? "subjects_ids" : "links_ids"];

            $affectedLinksArray = array();
            foreach ($ids as $id) {
                if ($doLink) {
                    $newLink = $my->insert("teachers_subjects",
                        array(
                            "subject_id" => $id,
                            "teacher_id" => $teacherId
                        )
                    );
                    array_push($affectedLinksArray, $newLink);
                } else {
                    $count = $my->delete("teachers_subjects", "id = $id");
                    if ($count > 0) {
                        array_push($affectedLinksArray, $id);
                    }
                }
            }
            return $doLink
                ? array("success" => "1", "affected_links" => $affectedLinksArray)
                : array("success" => "1");
        }

    }