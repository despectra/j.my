<?php
	header('content-type: application/json; charset=utf-8');
	require_once "apiengine.php";
	foreach ($_GET as $key => $value) 
	{
        $start = microtime(true) / 1000;
        $log = date('Y-m-d H:i:s')."> Method call: $key, started = $start ... ";

		$APIEngine = new apiEngine($key, $value);
		echo $APIEngine->callApiFunction();

		$end = microtime(true) / 1000;
        $dif = $end - $start;
        $log .= " ended=".$end.". DIFF = $dif s. \n";
        file_put_contents("LoadTestLog.txt", $log, FILE_APPEND);

		break;
	}
?>