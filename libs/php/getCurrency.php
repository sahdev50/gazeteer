<?php

	ini_set('display_errors', 'On');
	error_reporting(E_ALL);

    $base = 'USD';
    $exchage = $_REQUEST['query'] ?? 'EUR';
	$amount = 1;

	$executionStartTime = microtime(true);

    // https://open.er-api.com/v6/latest?base=USD&apikey=224e992c930640009e64d3cfe980db57
	$url='https://open.er-api.com/v6/latest?base=' . $base . '&apikey=224e992c930640009e64d3cfe980db57';

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_URL,$url);

	$result=curl_exec($ch);

	curl_close($ch);

	$decode = json_decode($result,true);

	$mydata['base'] = $base;
	$mydata['exchange'] = $exchage;
    $mydata['rate'] = $amount;
	$mydata['exchangeRate'] = $amount * $decode['rates'][$exchage];

	$output['status']['code'] = "200";
	$output['status']['name'] = "ok";
	$output['status']['description'] = "success";
	$output['status']['returnedIn'] = intval((microtime(true) - $executionStartTime) * 1000) . " ms";
	$output['data'] = $mydata;

	
	header('Content-Type: application/json; charset=UTF-8');

	echo json_encode($output);

?>
