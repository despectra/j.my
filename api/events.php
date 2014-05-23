<?php
	
	require_once 'mysqlbd.php';
	require_once 'checker.php';

	class API {
		function __construct() {
			;//
		}

		function getEvents($params) {
			try {
				$my = new MySQLDb();
				$my->connect();
                $uid = Checker::checkToken($params, $my);
                $level = Checker::checkLevel($uid, 0, $my);

                $offset = $params["offset"];
                $count = $params["count"];
                if($offset != "0" || $count != "0") {
                    $limit = " LIMIT $offset, $count";
                } else {
                    $limit = "";
                }
                $my->select("events", array("*"), "permission & $level > 0 ORDER BY datetime DESC$limit");

                $data = Utils::dbRowsToAssocArrays($my, array("id", "text", "datetime"));
                return array(
                    "success" => "1",
                    "events" => $data
                );
				
			} catch(exException $ex) {
				throw $ex;
			}
		}
	}
?>