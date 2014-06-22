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
        $my->select("subjects", array("id as subject_id", "name"), $limit);
        $data = Utils::dbRowsToAssocArrays($my, array("subject_id", "name"));
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
                "subject_id" => $row["id"],
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

    public function getGroupsOfAllTeachersSubjects($params) {
        return $this->getGroupsOfTeachersSubjectImpl($params, true);
    }

    public function getGroupsOfTeachersSubject($params) {
        return $this->getGroupsOfTeachersSubjectImpl($params, false);
    }

    private function getGroupsOfTeachersSubjectImpl($params, $forAll) {
        $my = new MySQLDb();
        $my->connect();
        $uid = Checker::checkToken($params, $my);
        Checker::checkLevel($uid, 0, $my);

        if (!$forAll) {
            $teacherSubjectId = $params["teacher_subject_id"];
            $my->select("teachers_subjects_groups",
                array("id as teacher_subject_group_id", "group_id"),
                "teacher_subject_id = $teacherSubjectId");
        } else {
            $my->select("teachers_subjects_groups",
                array("id as teacher_subject_group_id", "teacher_subject_id", "group_id"));
        }
        $subjectsArray = array();
        while ($row = ($my->fetchRow())) {
            array_push($subjectsArray, $row);
        }
        return array(
            "success" => "1",
            "groups" => $subjectsArray
        );
    }

    public function setGroupsOfTeachersSubject($params) {
        return $this->setOrUnsetGroups(true, $params);
    }

    public function unsetGroupsOfTeachersSubject($params) {
        return $this->setOrUnsetGroups(false, $params);
    }

    private function setOrUnsetGroups($set, $params) {
        $my = new MySQLDb();
        $my->connect();
        $uid = Checker::checkToken($params, $my);
        Checker::checkLevel($uid, 0, $my);

        $teachersSubjectId = $params["teacher_subject_id"];
        $ids = $params[$set ? "groups_ids" : "links_ids"];
        $affectedLinksArray = array();
        foreach ($ids as $id) {
            if ($set) {
                $newLink = $my->insert("teachers_subjects_groups",
                    array(
                        "group_id" => $id,
                        "teacher_subject_id" => $teachersSubjectId
                    )
                );
                array_push($affectedLinksArray, $newLink);
            } else {
                $count = $my->delete("teachers_subjects_groups", "id = $id");
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