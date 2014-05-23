<?php
	require_once 'exexception.php';
	require_once 'mysqlbd.php';

	class Checker {
		static public function checkToken($data, $dbConnection) {
			if (!isset($data['token'])) throw new exException("Нет токена", 1011);
			$token = $data['token'];
			/*$my = new MySQLDb();
			$my->connect();*/
			$dbConnection->select('tokens', array('uid', 'token'), "token = '$token'");
			if ($dbConnection->rowCount() < 1) throw new exException("Неправильный токен. Ошибка авторизации", 1001);
			$row = $dbConnection->fetchRow();
			return $row['uid'];
		}

        public static function checkLevel($uid, $expectedLevel, $dbConnection) {
            $dbConnection->select("users", array("level"), "id = $uid LIMIT 1");
            if ($dbConnection->rowCount() < 1) {
                throw new exException("Внутренняя ошибка SQL", 3999);
            }
            $row = $dbConnection->fetchRow();
            $level = $row["level"];
            // TODO CONTINUE CHECKING LEVEL

            return $level;
        }
	}
?>