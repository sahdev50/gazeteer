<?php

$executionStartTime = microtime(true);
$country_code = $_REQUEST['query']; // the ISO alpha-2 country code

// Define the indicator codes for rural, and urban percentages
$rural_indicator = 'SP.RUR.TOTL.ZS'; // Rural population (% of total population)
$urban_indicator = 'SP.URB.TOTL.IN.ZS'; // Urban population (% of total population)

// API endpoints for the three requests
$api_url_rural = "http://api.worldbank.org/v2/country/{$country_code}/indicator/{$rural_indicator}?format=json";
$api_url_urban = "http://api.worldbank.org/v2/country/{$country_code}/indicator/{$urban_indicator}?format=json";

// Function to make a cURL request and return the response
function makeApiRequest($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

// Make the three cURL requests concurrently
$rural_data = makeApiRequest($api_url_rural);
$urban_data = makeApiRequest($api_url_urban);

$mydata['country'] = $rural_data[1][0]['country']['value'];
$mydata['rural'] = $rural_data[1][1]['value'];
$mydata['urban'] =  $urban_data[1][1]['value'];

// // Output the results
// echo "Rural Population (% of Total Population): " . $rural_data[1][0]['value'] . "%\n";
// echo "Urban Population (% of Total Population): " . $urban_data[1][0]['value'] . "%\n";
$output['status']['code'] = "200";
	$output['status']['name'] = "ok";
	$output['status']['description'] = "success";
	$output['status']['returnedIn'] = intval((microtime(true) - $executionStartTime) * 1000) . " ms";
	$output['data'] = $mydata;

    header('Content-Type: application/json; charset=UTF-8');

	echo json_encode($output);
?>