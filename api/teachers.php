<?php
/**
 * Created by PhpStorm.
 * User: Dmitry
 * Date: 29.05.14
 * Time: 11:32
 */
    require_once 'mysqlbd.php';
    require_once 'checker.php';
    require_once 'users.php';

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

                $firstName = $params["first_name"];
                $middleName = $params["middle_name"];
                $secondName = $params["last_name"];
                $login = $params["login"];

                $my->beginTransaction();
                $userId = Users::addUser($my, $login, $firstName, $secondName, $middleName, 4);
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
                                      users.first_name AS first_name,
                                      users.middle_name AS middle_name,
                                      users.last_name AS last_name
                                FROM users
                                INNER JOIN teachers ON users.id = teachers.user_id
                                $limit", array());
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
                                      users.first_name AS first_name,
                                      users.middle_name AS middle_name,
                                      users.last_name AS last_name,
                                      users.level AS level
                                FROM users INNER JOIN teachers ON users.id = teachers.user_id
                                WHERE teachers.id = :teacherId
                                LIMIT 1", array("teacherId" => $teacherId));
            if ($row = $my->fetchRow()) {
                $row["success"] = 1;
                return $row;
            } else {
                return array(
                    "success" => "0",
                    "error_code" => "100",
                    "error_message" => "Данного учителя нет в базе. Скорее всего, он был удален"
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
                Users::deleteUser($dbConnection, $uid);
            }
        }

        public function getSubjectsOfAllTeachers($params) {
            return $this->getSubjectsOfTeacherImpl(true, $params);
        }

        public function getSubjectsOfTeacher($params) {
            return $this->getSubjectsOfTeacherImpl(false, $params);
        }

        private function getSubjectsOfTeacherImpl($forAll, $params) {
            $my = new MySQLDb();
            $my->connect();
            $uid = Checker::checkToken($params, $my);
            Checker::checkLevel($uid, 0, $my);

            if (!$forAll) {
                $teacherId = $params["teacher_id"];
                $my->select("teachers_subjects", array("id as teacher_subject_id, subject_id"), "teacher_id = $teacherId");
            } else {
                $my->select("teachers_subjects", array("id as teacher_subject_id", "teacher_id", "subject_id"));
            }
            $resultArray = array();
            while ($row = ($my->fetchRow())) {
                array_push($resultArray, $row);
            }
            return array(
                    "success" => "1",
                    "subjects" => $resultArray
                );
        }

        public function setSubjectsOfTeacher($params) {
            return $this->setOrUnsetSubjects(true, $params);
        }

        public function unsetSubjectsOfTeacher($params) {
            return $this->setOrUnsetSubjects(false, $params);
        }

        private function setOrUnsetSubjects($set, $params) {
            $my = new MySQLDb();
            $my->connect();
            $uid = Checker::checkToken($params, $my);
            Checker::checkLevel($uid, 0, $my);

            $teacherId = $params["teacher_id"];

            $ids = $params[$set ? "subjects_ids" : "links_ids"];

            $affectedLinksArray = array();
            foreach ($ids as $id) {
                if ($set) {
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
            return $set
                ? array("success" => "1", "affected_links" => $affectedLinksArray)
                : array("success" => "1");
        }
    }