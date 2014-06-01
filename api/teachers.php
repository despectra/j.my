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
                    "passwd" => "000111",
                    "name" => $firstName,
                    "surname" => $secondName,
                    "middlename" => $middleName,
                    "home" => "home/$login",
                    "level" => 4
                );
                $userId = $my->insert("users", $user);
                $teacher = array(
                    "user_id" => $userId
                );
                $teacherId = $my->insert("teachers", $teacher);
                $my->commit();
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

    }