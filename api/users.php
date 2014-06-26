<?php
/**
 * Created by PhpStorm.
 * User: Dmitry
 * Date: 07.06.14
 * Time: 23:42
 */

class Users {
    public static function addUser($dbConn, $login, $firstName, $secondName, $middleName, $level) {
        $dbConn->select("users", array("id"), "login = '$login'");
        if(($row = $dbConn->fetchRow()) && $dbConn->rowCount() > 0) {
            throw new exException("Пользователь с логином $login уже существует", 101);
        }
        $user = array(
            "login" => $login,
            "passwd" => md5(Utils::$_SALT_1.md5("000111").Utils::$_SALT_2),
            "first_name" => $firstName,
            "last_name" => $secondName,
            "middle_name" => $middleName,
            "home" => "/home/$login",
            "level" => $level
        );
        $userId = $dbConn->insert("users", $user);
        return $userId;
    }

    public static function deleteUser($dbConn, $uid) {
        $dbConn->select("users", array("login"), "id = $uid");
        $login = "";
        if(($row = $dbConn->fetchRow()) && $dbConn->rowCount() > 0) {
            $login = $row["login"];
        } else {
            return;
        }
        $dbConn->delete("users", "id = $uid");
       if(is_dir("../home/$login")) {
           rmdir("../home/$login");
       }
    }
} 