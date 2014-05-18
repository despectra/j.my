<?php
/**
 * Created by PhpStorm.
 * User: Dmitry
 * Date: 07.04.14
 * Time: 22:35
 */

    require_once 'mysqlbd.php';
    require_once 'checker.php';

    class API {
        function __construct() {
            ;//
        }

        /*
         * groups.getGroup={"token":"<your_token>", "id":5}
         */
        function getGroup($params) {
            try {
                $my = new MySQLDb();
                $my->connect();
                $uid = Checker::checkToken($params, $my);
                Checker::checkLevel($uid, 0, $my);
                //CHECK LEVEL HERE
                $groupId = $params["id"];
                $my->select("groups", array("*"), "id = $groupId LIMIT 1");
                $row = $my->fetchRow();
                return array(
                    "success" => "1",
                    "group" => array(
                        "id" => $row["id"],
                        "name" => $row["name"],
                        "parent_group" => $row["parent_id"]
                    )
                );
            } catch (exException $ex) {
                throw $ex;
            }
        }

        /*
         * groups.getGroups={"token":"your_token", "parent_group_id":0, "offset":0, "count":10}
         */
        function getGroups($params) {
            try {
                $my = new MySQLDb();
                $my->connect();
                $uid = Checker::checkToken($params, $my);
                Checker::checkLevel($uid, 0, $my);
                //CHECK LEVEL HERE
                $offset = $params["offset"];
                $count = $params["count"];
                $limit = "";
                if ($offset != "0" || $count != "0") {
                    $limit = " LIMIT $offset, $count";
                }
                $parentId = $params["parent_group_id"];
                $my->select("groups", array("*"), "parent_id = $parentId$limit");
                $data = array();
                while ($row = $my->fetchRow()) {
                    array_push(
                        $data,
                        array(
                            "id" => $row["id"],
                            "name" => $row["name"],
                            "parent_id" => $row["parent_id"]
                        )
                    );
                }
                return array(
                    "success" => "1",
                    "groups" => $data
                );
            } catch(exException $ex) {
                throw $ex;
            }
        }

        /*
         * groups.addGroup={"token":"your_token", "name":"9A", "parent_id":0}
         */
        function addGroup($params) {
            try {
                $my = new MySQLDb();
                $my->connect();
                $uid = Checker::checkToken($params, $my);
                Checker::checkLevel($uid, 0, $my);
                //CHECK LEVEL HERE
                $group = array(
                    "name" => $params["name"],
                    "parent_id" => $params["parent_id"]
                );
                $groupId = $my->insert("groups", $group);
                return array(
                    "success" => "1",
                    "group_id" => $groupId
                );
            } catch(exException $ex) {
                throw $ex;
            }
        }

        /*
         * groups.editGroup={"token":"your_token", "id":4, "data" : {"name": "9B", "parent_id":4}}
         */
        function editGroup($params) {
            try {
                $my = new MySQLDb();
                $my->connect();
                $uid = Checker::checkToken($params, $my);
                Checker::checkLevel($uid, 0, $my);
                //CHECK LEVEL HERE
                $id = $params["id"];
                $data = $params["data"];
                $my->update("groups", $data, "id = $id");
                return array(
                    "success" => "1"
                );
            } catch (exException $ex) {
                throw $ex;
            }
        }

        /*
        * groups.removeGroup={"token":"your_token", "groups":["1", "2", "4", "6", "10"]}
        */
        function deleteGroups($params) {
            $my = new MySQLDb();
            try {
                $my->connect();
                $uid = Checker::checkToken($params, $my);
                Checker::checkLevel($uid, 0, $my);
                //CHECK LEVEL HERE
                $groups = $params["groups"];
                $my->beginTransaction();
                foreach($groups as $id) {
                    $my->delete("groups", "id = $id");
                    $my->select("groups", array("id"), "parent_id = $id LIMIT 1");
                    while($row = $my->fetchRow()) {
                        $childId = $row["id"];
                        $my->delete("groups", "id = $childId");
                    }
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