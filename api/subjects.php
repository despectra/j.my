<?php
/**
 * Created by PhpStorm.
 * User: Dmitry
 * Date: 18.05.14
 * Time: 16:41
 */

require_once 'mysqlbd.php';
require_once 'checker.php';

class API {
    function __construct() {
        ;//
    }

    function addSubject($params) {
        $my = new MySQLDb();
        $my->connect();
        $uid = Checker::checkToken($params, $my);
        Checker::checkLevel($uid, 0, $my);
        //CHECK LEVEL HERE
        $subject = array(
            "name" => $params["name"],
        );
        $subjectId = $my->insert("subjects", $subject);
        return array(
            "success" => "1",
            "subject_id" => $subjectId
        );
    }

    function getSubjects($params) {
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
        $my->select("subjects", array("*"), $limit);
        $data = Utils::dbRowsToAssocArrays($my, array("id", "name"));
        return array(
            "success" => "1",
            "subjects" => $data
        );
    }

    function getSubject($params) {
        $my = new MySQLDb();
        $my->connect();
        $uid = Checker::checkToken($params, $my);
        Checker::checkLevel($uid, 0, $my);

        $subjectId = $params["id"];
        $my->select("subjects", array("*"), "id = $subjectId LIMIT 1");
        $row = $my->fetchRow();
        return array(
            "success" => "1",
            "subject" => array(
                "id" => $row["id"],
                "name" => $row["name"]
            )
        );
    }

    function updateSubject($params) {
        $my = new MySQLDb();
        $my->connect();
        $uid = Checker::checkToken($params, $my);
        Checker::checkLevel($uid, 0, $my);
        //CHECK LEVEL HERE
        $id = $params["id"];
        $data = $params["data"];
        $my->update("subjects", $data, "id = $id");
        return array(
            "success" => "1"
        );
    }

    function deleteSubjects($params) {
        $my = new MySQLDb();
        try {
            $my->connect();
            $uid = Checker::checkToken($params, $my);
            Checker::checkLevel($uid, 0, $my);
            //CHECK LEVEL HERE
            $groups = $params["subjects"];
            $my->beginTransaction();
            foreach($groups as $id) {
                $my->delete("subjects", "id = $id");
            }
            $my->commit();
            return array(
                "success" => "1"
            );
        } catch (exException $ex) {
            $my->rollBack();
            throw $ex;
        }
    }

}