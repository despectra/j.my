<?php
	header('content-type: application/json; charset=utf-8');
	require_once "apiengine.php";
	foreach ($_GET as $key => $value) 
	{
        $start = microtime(true);
		$APIEngine = new apiEngine($key, $value);
		echo $APIEngine->callApiFunction();
        $dif = microtime(true) - $start;
		break;
	}
?>