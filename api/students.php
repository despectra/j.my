<?php
/**
 * Created by PhpStorm.
 * User: Dmitry
 * Date: 13.04.14
 * Time: 23:45
 */

require_once 'mysqlbd.php';
require_once 'checker.php';
require_once 'users.php';

class API {
    function __construct() {
        ;//
    }

    /*
     * students.getByGroup = {"token":"<token>", "group_id":int}
     */
    public function getByGroup($params) {
        try {
            $my = new MySQLDb();
            $my->connect();
            $uid = Checker::checkToken($params, $my);
            Checker::checkLevel($uid, 0, $my);
            //CHECK LEVEL HERE
            $groupId = $params["group_id"];
            $my->executeQuery("SELECT students_groups.id AS student_group_id,
                                      students.id AS student_id,
                                      users.id AS user_id,
                                      users.login AS login,
                                      users.first_name AS first_name,
                                      users.middle_name AS middle_name,
                                      users.last_name AS last_name
                                FROM users
                                INNER JOIN students ON users.id = students.user_id
                                INNER JOIN students_groups ON students.id = students_groups.student_id
                                WHERE students_groups.group_id =:groupId", array("groupId" => $groupId));
            $data = array();
            while($row = $my->fetchRow()) {
                array_push($data, $row);
            }
            return array(
                "success" => "1",
                "students" => $data
            );

        } catch (exException $e) {
            throw $e;
        }
    }

    /*
     * students.addStudentInGroup = {"token":<token>, "group_id":int, "login":string, "passwd":md5, "name":string, "middlename":string, "surname":string}
     */

    public function addStudentInGroup($params) {
        $my = new MySQLDb();
        try {
            $my->connect();
            $uid = Checker::checkToken($params, $my);
            Checker::checkLevel($uid, 0, $my);
            $groupId = $params["group_id"];
            $login = $params["login"];
            $name = $params["first_name"];
            $middlename = $params["middle_name"];
            $surname = $params["last_name"];

            $my->beginTransaction();
            $userId = Users::addUser($my, $login, $name, $surname, $middlename, 2);

            $studentArray = array(
                "user_id" => $userId
            );
            $studentId = $my->insert("students", $studentArray);

            $groupLinkArray = array(
                "group_id" => $groupId,
                "student_id" => $studentId
            );
            $linkId = $my->insert("students_groups", $groupLinkArray);
            $my->commit();
            mkdir("../home/$login");
            return array(
                "success" => "1",
                "user_id" => $userId,
                "student_id" => $studentId,
                "student_group_id" => $linkId);
        } catch (exException $e) {
            $my->rollBack();
            throw $e;
        }
    }

    public function deleteStudents($params) {
        $my = new MySQLDb();
        try {
            $my->connect();
            $uid = Checker::checkToken($params, $my);
            Checker::checkLevel($uid, 0, $my);

            $my->beginTransaction();
            $this->deleteStudentsImpl($params["students"], $my);
            $my->commit();
            return array(
                "success" => "1"
            );

        } catch (exException $e) {
            $my->rollBack();
            throw $e;
        }
    }

    public function deleteStudentsByGroup($params) {
        $my = new MySQLDb();
        try {
            $my->connect();
            $uid = Checker::checkToken($params, $my);
            Checker::checkLevel($uid, 0, $my);

            $groupId = $params["group_id"];
            $my->select("students_groups", array("student_id"), "group_id = $groupId");
            $my->beginTransaction();
            $studentIds = array();
            while ($row = $my->fetchRow()) {
                array_push($studentIds, $row["student_id"]);
            }
            $this->deleteStudentsImpl($studentIds, $my);
            $my->commit();
            return array(
                "success" => "1"
            );
        } catch (exException $e) {
            $my->rollBack();
            throw $e;
        }
    }

    private function deleteStudentsImpl($studentIds, $dbConnection) {
        foreach($studentIds as $id) {
            $dbConnection->select("users JOIN students ON users.id = students.user_id", array("users.id"), "students.id = $id LIMIT 1");
            $row = $dbConnection->fetchRow();
            $uid = $row["id"];
            Users::deleteUser($dbConnection, $uid);
        }
    }

}