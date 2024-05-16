<?php

// function debug_to_console($data) {
//     // $output = $data;
//     // if (is_array($output))
//     //     $output = implode(',', $output);

//     echo "<script>console.log(". json_encode($data) . ");</script>";
// }

	ini_set('display_errors', 'On');
	error_reporting(E_ALL);

	$executionStartTime = microtime(true);
    // var_dump($_REQUEST['iso_code']);

    // https://open.er-api.com/v6/latest?base=USD&apikey=224e992c930640009e64d3cfe980db57
	$url='https://restcountries.com/v3.1/alpha/' . $_REQUEST['query'];
    // $url='https://restcountries.com/v3.1/alpha/CA';

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_URL,$url);

	$result=curl_exec($ch);

	curl_close($ch);

	$decode = json_decode($result,true);
    // debug_to_console($decode[0]["latlng"]);
    

	$output['status']['code'] = "200";
	$output['status']['name'] = "ok";
	$output['status']['description'] = "success";
	$output['status']['returnedIn'] = intval((microtime(true) - $executionStartTime) * 1000) . " ms";
	$output['data'] = $decode[0]["latlng"];

	
	header('Content-Type: application/json; charset=UTF-8');

	echo json_encode($output); 

?>
