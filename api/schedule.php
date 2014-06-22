<?php
/**
 * Created by PhpStorm.
 * User: Dmitry
 * Date: 19.06.14
 * Time: 1:03
 */

require_once 'mysqlbd.php';
require_once 'checker.php';

class API {
    function __construct() {
        ;//
    }

    public function getWeekScheduleForGroup($params) {
        $my = new MySQLDb();
        $my->connect();
        $uid = Checker::checkToken($params, $my);
        Checker::checkLevel($uid, 0, $my);

        $groupId = $params["group_id"];
        $query = "SELECT schedule.teacher_subject_group as teacher_subject_group_id,
                        schedule.id as schedule_item_id,
                        schedule.day as day,
                        schedule.lesson_number as lesson_number
                  FROM schedule
                  JOIN teachers_subjects_groups
                  ON schedule.teacher_subject_group = teachers_subjects_groups.id
                  WHERE teachers_subjects_groups.group_id = :groupId";
        $queryArgs = array("groupId" => $groupId);
        $my->executeQuery($query, $queryArgs);

        $data = array();
        while($row = $my->fetchRow()) {
            array_push($data, $row);
        }
        return array(
            "success" => "1",
            "schedule" => $data
        );
    }

    public function addScheduleItem($params) {
        $my = new MySQLDb();
        $my->connect();
        $uid = Checker::checkToken($params, $my);
        Checker::checkLevel($uid, 0, $my);

        $day = $params["day"];
        $lessonNum = $params["lesson_number"];
        $tsgId = $params["teacher_subject_group_id"];

        //TODO check whether group and teacher are not busy this lesson
        $inserted = $my->insert("schedule",
            array("day" => $day, "lesson_number" => $lessonNum, "teacher_subject_group_id" => $tsgId));
        return array(
            "success" => "1",
            "schedule_item_id" => $inserted
        );
    }

    public function updateScheduleItem($params) {
        $my = new MySQLDb();
        $my->connect();
        $uid = Checker::checkToken($params, $my);
        Checker::checkLevel($uid, 0, $my);

        $scheduleItemId = $params["schedule_item_id"];
        $tsgId = $params["teacher_subject_group_id"];
        $affected = $my->update("schedule", array("teacher_subject_group" => $tsgId), "id = $scheduleItemId");
        if ($affected > 0) {
            return array(
                "success" => "1"
            );
        } else {
            return array(
                "success" => "0",
                "error_code" => 150,
                "error_message" => "Такой записи в расписании не существует"
            );
        }
    }

    public function deleteScheduleItems($params) {
        $my = new MySQLDb();
        $my->connect();
        $uid = Checker::checkToken($params, $my);
        Checker::checkLevel($uid, 0, $my);

        $itemsIds = $params["schedule_items_ids"];
        $my->beginTransaction();
        try {
            foreach ($itemsIds as $id) {
                $my->delete("schedule", "id = $id");
            }
        } catch(exException $e) {
            $my->rollBack();
            throw $e;
        }
        $my->commit();
        return array(
            "success" => "1"
        );
    }
}