<?php
	
	require_once 'mysqlbd.php';
	require_once 'checker.php';

	class API {
		function getMinProfile($params) {
			try {
				$my = new MySQLDb();
				$my->connect();
				$uid =  Checker::checkToken($params, $my);
				$my->select('users', array('*'), "id = $uid");
				if ($my->rowCount() < 1) throw new exException("Внутренняя ошибка SQL", 3999);
				$row = $my->fetchRow();

				$name = $row['name'];
				$middlename = $row['middlename'];
				$surname = $row['surname'];
				$level = $row['level']; 
				$home = $row['home'];
				$avatar = $_SERVER['SERVER_NAME'].$home."/avatar_square_min.jpg"; //Костыль
				//$avatar_path = "..".$home."/avatar_square.jpg";
				//header('content-type: image/jpeg');
				//$avatar = file_get_contents("..".$home."/avatar_square.jpg");
				//echo $avatar;

				$returnArray = array("uid" => $uid, 
							 "name" => "$name", 
							 "middlename" => "$middlename", 
							 "surname" => "$surname",
							 "level" => $level,
							 "avatar" => "$avatar");
				return $returnArray;
			}
			catch (exException $e) {
				throw $e;
			}
		}
	}
?>