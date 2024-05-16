<?php

function debug_to_console($data) {
    // $output = $data;
    // if (is_array($output))
    //     $output = implode(',', $output);

    echo "<script>console.log(". json_encode($data) . ");</script>";
}

$apiKey = '86497246c4e83219ef1a8b68aa9c11c0';

ini_set('display_errors', 'On');
	error_reporting(E_ALL);

	$executionStartTime = microtime(true);

	$lat = json_decode($_REQUEST['query'])[0];
    $lng = json_decode($_REQUEST['query'])[1];
	// var_dump($_REQUEST['query']);
    // $lat = 51.5;
    // $lng = -0.08;

    // https://api.openweathermap.org/data/2.5/weather?lat=51.5&lon=-0.08&&appid=
    $url = "https://api.openweathermap.org/data/2.5/forecast?lat=" . $lat . "&lon=". $lng ."&units=metric&appid=" . $apiKey;

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_URL,$url);

	$result=curl_exec($ch);

	curl_close($ch);

	$decode = json_decode($result,true);

    // Build forecast data array grouped by date
    $forecastData = array();
    if (isset($decode['list'])) {
        foreach ($decode['list'] as $forecast) {
            $timestamp = $forecast['dt'];
            $date = date("D, d F Y", $timestamp); // Extract date without time
            $time = date('h:i A', $timestamp); // Extract time only
            $temp = $forecast['main']['temp'];
            $weather = $forecast['weather'][0]['description'];
            $icon = $forecast['weather'][0]['icon'];

            // Aggregate forecast data for each date
            if (!isset($forecastData[$date])) {
                $forecastData[$date] = array(
                    'min_temperature' => $temp,
                    'max_temperature' => $temp,
                    'avg_temperature' => $temp,
					'temps' => array(),
                    'weather' => array(),
                    'icons' => array(),
                    'times' => array()
                );
            }

            $forecastData[$date]['weather'][] = $weather;
            $forecastData[$date]['icons'][] = $icon;
            $forecastData[$date]['times'][] = $time;
			$forecastData[$date]['temps'][] = $temp;

            // Update min and max temperature for the day
            if ($temp < $forecastData[$date]['min_temperature']) {
                $forecastData[$date]['min_temperature'] = $temp;
            }

            if ($temp > $forecastData[$date]['max_temperature']) {
                $forecastData[$date]['max_temperature'] = $temp;
            }
        }


        // Calculate average temperature and choose most frequent weather for each date
        foreach ($forecastData as $date => &$values) {
            $values['avg_temperature'] = round(($values['min_temperature'] + $values['max_temperature']) / 2, 2);
			$values['wer'] = array_count_values($values['weather']);
            $values['most_frequent_weather'] = array_search(max($values['wer']), $values['wer']);
        }
		$forecastData = (array) $forecastData;

    } else {
        $forecastData = array(
            'error' => 'No forecast data available.'
        );
    }
    
	$output['status']['code'] = "200";
	$output['status']['name'] = "ok";
	$output['status']['description'] = "success";
	$output['status']['returnedIn'] = intval((microtime(true) - $executionStartTime) * 1000) . " ms";
	$output['data'] = $forecastData;

	
	header('Content-Type: application/json; charset=UTF-8');

	echo json_encode($output);

?>
