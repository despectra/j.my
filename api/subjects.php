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
        try {
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
        } catch(exException $ex) {
            throw $ex;
        }
    }

}