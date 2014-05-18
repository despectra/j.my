<?php
	
	require_once 'mysqlbd.php';
	require_once 'checker.php';

	class API {
		function __construct() {
			;//
		}

		function getStatus() {
			$returnArray = array ("ok" => true);
			return $returnArray;
		}

		function getInfo() {
			$returnArray = array ("ok" => true, "version" => "0.1");
			return $returnArray;
		}
	}
?>