<?php

	ini_set('display_errors', 'On');
	error_reporting(E_ALL);

	$executionStartTime = microtime(true);

	// $countryCode = 'UK';
	$countryCode =  $_REQUEST['query'];

    // http://api.geonames.org/neighbours?country=IN&username=sahdev50
    // $url = 'http://api.geonames.org/searchJSON?country=' . $countryCode . '&maxRows=7&featureClass=P&username=sahdev50';
	// $url = 'http://api.geonames.org/searchJSON?country=IN&maxRows=10&username=sahdev50';

	// $ch = curl_init();
	// curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	// curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	// curl_setopt($ch, CURLOPT_URL,$url);

	// $result=curl_exec($ch);

	// curl_close($ch);

	$data = json_decode(file_get_contents("../data/cities.json"), true);


	// $decode = json_decode($result,true);
	// $data = $decode['geonames'];

	$cities = [];
    foreach ($data as $cityData) {
        // Check if city is within the specified country and real (population is provided)
        if (isset($cityData['country']) && $cityData['country'] === $countryCode) {


			// usort($cityData, function($a, $b){
			// 	return (int)$a['pop'] > (int)$b['pop'];
			// });
            
            $city = [
                'name' => $cityData['name'],
                'lat' => $cityData['lat'],
                'lng' => $cityData['lon'],
				'pop' => (int) $cityData['pop']
            ];
            $cities[] = $city;
        }
    }

	usort($cities,function($first,$second){
		return $first['pop'] <=> $second['pop'];
	});

	$cities = array_reverse($cities);
	$cities = array_slice($cities,0,10);

	$output['status']['code'] = "200";
	$output['status']['name'] = "ok";
	$output['status']['description'] = "success";
	$output['status']['returnedIn'] = intval((microtime(true) - $executionStartTime) * 1000) . " ms";
	$output['data'] = $cities;

	
	header('Content-Type: application/json; charset=UTF-8');

	echo json_encode($output); 

?>
