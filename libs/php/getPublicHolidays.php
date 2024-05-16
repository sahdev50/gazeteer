<?php

$executionStartTime = microtime(true);

// Calendarific API endpoint
$api_endpoint = 'https://calendarific.com/api/v2/holidays';

// Your API key
$api_key = 'UtI6JcFwENtZLFbNBA9p3XuxVDvCJ6Jb';

// Prompt the user to enter the country code
$country_code = $_REQUEST['query'];

// Year for which you want to get public holidays
$year = date('Y'); // Default to current year

// Construct the API URL
$url = "$api_endpoint?api_key=$api_key&country=$country_code&year=$year&type=national";

// Initialize cURL session
$ch = curl_init();

// Set cURL options
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// Execute cURL request
$response = curl_exec($ch);

// Close cURL session
curl_close($ch);

// Decode JSON response
$data = json_decode($response, true);

// Check if response contains holidays data
if (isset($data['response']['holidays'])) {
    // Extract holidays data
    $holidays = $data['response']['holidays'];

    // Initialize an array to store holiday data
    $selected_holidays = [];

    foreach ($holidays as $holiday) {
        $holiday_name = $holiday['name'];
        $holiday_date = $holiday['date']['iso'];
        $holiday_date_formatted = date("D, d F", strtotime($holiday_date));

        $is_unique = true;
            foreach ($selected_holidays as $existing_holiday) {
                if ($existing_holiday['holiday_name'] === $holiday_name && $existing_holiday['date'] === $holiday_date) {
                    $is_unique = false;
                    break;
                }
            }

        // Add holiday data to selected holidays array
        if ($is_unique) {
            $selected_holidays[] = [
                'holiday_name' => $holiday_name,
                'date' => $holiday_date,
                'formatted_date' => $holiday_date_formatted,
            ];
        }
    }

    // Return holidays data as JSON response
    $output['status']['code'] = "200";
	$output['status']['name'] = "ok";
	$output['status']['description'] = "success";
	$output['status']['returnedIn'] = intval((microtime(true) - $executionStartTime) * 1000) . " ms";
	$output['data'] = $selected_holidays;

    header('Content-Type: application/json; charset=UTF-8');

	echo json_encode($output);
} else {
    echo "Failed to retrieve public holidays data.";
}

?>
