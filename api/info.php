<?php
	
	require_once 'mysqlbd.php';
	require_once 'checker.php';

	class API {
		function __construct() {
			;//
		}

		function getStatus() {
			$returnArray = array ("success" => "1");
			return $returnArray;
		}

		function getInfo() {
			$returnArray = array ("success" => "1", "version" => "0.1");
			return $returnArray;
		}
	}
?>