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
            $my->executeQuery("SELECT students_groups.student_id AS student_group_link_id,
                                      students.id AS student_id,
                                      users.id AS user_id,
                                      users.login AS login,
                                      users.name AS name,
                                      users.middlename AS middlename,
                                      users.surname AS surname
                                FROM users
                                INNER JOIN students ON users.id = students.user_id
                                INNER JOIN students_groups ON students.id = students_groups.student_id
                                WHERE students_groups.group_id =$groupId");
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
            $name = $params["name"];
            $middlename = $params["middlename"];
            $surname = $params["surname"];

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
                "student_group_link_id" => $linkId);
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
            $dbConnection->select("users, students", array("users.id"), "students.user_id = users.id AND students.id = $id LIMIT 1");
            $row = $dbConnection->fetchRow();
            $uid = $row["id"];
            $dbConnection->delete("students_groups", "student_id = $id");
            $dbConnection->delete("students", "id = $id");
            Users::deleteUser($dbConnection, $uid);
        }
    }

}