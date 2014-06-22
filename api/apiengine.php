<?php
require_once 'exexception.php';
require_once 'utils.php';

class apiEngine {

	private $apiFunctionName;
	private $apiFunctionParams;

	function __construct($apiFunctionName, $apiFunctionParams) {
		$this->apiFunctionName = explode('_', $apiFunctionName);
		$this->apiFunctionParams = /*stripcslashes(*/$apiFunctionParams/*)*/;
	}

	function callApiFunction() {
		require_once "listofapi.php";
		try {
			$apiName = strtolower($this->apiFunctionName[0]);
			$apiFunction = strtolower($this->apiFunctionName[1]);
			$params = json_decode($this->apiFunctionParams, true);
			if (array_key_exists($apiName, $apiList)) {
				require_once $apiList[$apiName];
				$api = new API();
				if (method_exists($api, $apiFunction)) {
                    try {
                        //sleep(3);
                        $json_data = $api->$apiFunction($params);
                    } catch (exException $ex) {
                        throw $ex;
                    }
				}
				else throw new exException("Несуществующий метод API $apiName", 1067);
			} else throw new exException("Несуществующее название API", 1066);
		} catch (exException $e) {
			$json_data = array('success'=>0, 'error_code'=>$e->getCode(), 'error_message'=>$e->getMessage());
		}
		return json_encode($json_data);
	}
}
?>