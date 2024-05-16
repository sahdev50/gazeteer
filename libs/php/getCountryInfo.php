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
	$url='https://restcountries.com/v3.1/alpha/' . $_REQUEST['query'];

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_URL,$url);

	$result=curl_exec($ch);

	curl_close($ch);

	$decode = json_decode($result,true);

    $data = $decode[0];
    $mydata["name"] = $data["name"]["common"];
    $mydata["officialName"] = $data["name"]["official"];
    $mydata["capital"] = $data["capital"][0];
    $mydata["capitalGeometry"] = $data["capitalInfo"]["latlng"];
    if($data["coatOfArms"]){
        $mydata["coatOfArms"] = $data["coatOfArms"]["png"];
    } else {
        $mydata["coatOfArms"] = "";
    }
    $mydata["region"] = $data["region"];
    $mydata["subRegion"] = $data["subregion"];
    $mydata["population"] = $data["population"];
    $mydata["languages"] = $data["languages"];
    $mydata["area"] = $data["area"];


	$output['status']['code'] = "200";
	$output['status']['name'] = "ok";
	$output['status']['description'] = "success";
	$output['status']['returnedIn'] = intval((microtime(true) - $executionStartTime) * 1000) . " ms";
	$output['data'] = $mydata;

	
	header('Content-Type: application/json; charset=UTF-8');

	echo json_encode($output); 

?>
