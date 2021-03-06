<?php
	
	require_once 'mysqlbd.php';
	require_once 'checker.php';

	class API {
		function __construct() {
			;//
		}

		private function generateString($length = 32) {
		  $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		  $numChars = strlen($chars);
		  $string = '';
		  for ($i = 0; $i < $length; $i++) {
		    $string .= substr($chars, rand(1, $numChars) - 1, 1);
		  }
		  return $string;
		}

		function login() {
			if (isset($_POST['login']) && isset($_POST['passwd'])) {
                $login = $_POST['login'];
                $passwdHash = md5(Utils::$_SALT_1.$_POST['passwd'].Utils::$_SALT_2);
                $my = new MySQLDb();
                $my->connect();
                $my->select('users', array('id', 'login', 'passwd'), "login = '$login' AND passwd = '$passwdHash'");
                if ($my->rowCount() > 0 ) {
                    $row = $my->fetchRow();
                    $uid = $row['id'];
                    $randomString = $this->generateString();
                    $my->insert('tokens', array('uid'=> $uid, 'token' => $randomString));
                    $returnArray = array("success"=>1, "token" => $randomString);
                    return $returnArray;
                }
                else throw new exException("Неправильный логин или пароль", 1000);
			}
			else throw new exException("Ошибка получения данных", 1014);
		}
		function logout($params) {
            $my = new MySQLDb();
            $my->connect();
            $token = $params["token"];
            $uid =  Checker::checkToken($params, $my);
            $my->executeQuery(
                "DELETE FROM tokens WHERE uid = :uid AND token = :token",
                array("uid" => $uid, "token" => $token)
            );
            return array("success" => 1);
		}

		function checkToken($params) {
            $my = new MySQLDb();
            $my->connect();
            Checker::checkToken($params, $my);
            return array("success" => 1);
        }
	}
?>