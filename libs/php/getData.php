<?php

// function debug_to_console($data) {
    // $output = $data;
    // if (is_array($output))
    //     $output = implode(',', $output);

    // echo "<script>console.log(". json_encode($data) . ");</script>";
// }

// Replace 'YOUR_API_KEY' with your actual OpenCage API key
$apiKey = '3c8b0f3de815418b95903c9b209e693a';

ini_set('display_errors', 'On');
	error_reporting(E_ALL);

	$executionStartTime = microtime(true);

	$location = $_REQUEST['query'];
	// var_dump($location)
    $url = "https://api.opencagedata.com/geocode/v1/json?q=" . urldecode($location) . "&key=" . $apiKey;

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_URL,$url);

	$result=curl_exec($ch);
    // debug_to_console($result);

	curl_close($ch);

	$decode = json_decode($result,true);

	$data = $decode['results'][0];

	$mydata = null;

	$mydata['name'] = $data['components']['country'];
	$mydata["code"] = $data['components']['ISO_3166-1_alpha-2'];
	$mydata['geometry'] = $data['geometry'];
	if ($location == "20,-12"){
		$mydata['currency']['name'] = 'Mauritanian Ouguiya';
		$mydata['currency']['code'] = 'MRU';
	} elseif($location == "-20,30"){
		$mydata['currency']['name'] = ' Zimbabwean dollar';
		$mydata['currency']['code'] = 'ZWL';
	}else {
		$mydata['currency']['name'] = $data['annotations']['currency']['name'];
		$mydata['currency']['code'] = $data['annotations']['currency']['iso_code'];
	}

	$output['status']['code'] = "200";
	$output['status']['name'] = "ok";
	$output['status']['description'] = "success";
	$output['status']['returnedIn'] = intval((microtime(true) - $executionStartTime) * 1000) . " ms";
	$output['data'] = $mydata;

	
	header('Content-Type: application/json; charset=UTF-8');

	echo json_encode($output);

?>