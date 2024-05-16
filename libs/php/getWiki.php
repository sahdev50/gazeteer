<?php

$executionStartTime = microtime(true);

function getCountryInfo($countryName) {
    $apiEndpoint = "https://en.wikipedia.org/w/api.php?action=query&format=json&prop=extracts|info&exintro=true&inprop=url&titles=" . urlencode($countryName);

    $ch = curl_init($apiEndpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response, true);
}

function extractInfo($countryInfo) {
    $pages = $countryInfo['query']['pages'];
    $pageId = key($pages);
    $info = $pages[$pageId];

    $title = $info['title'];
    $url = $info['fullurl'];
    $extract = $info['extract'];

    // Clean up the extract (remove HTML tags)
    $cleanedExtract = strip_tags($extract);

    // Trim the extract to a specific length
    $trimmedExtract = substr($cleanedExtract, 0, 500);
    $str = str_replace("\n", '', $trimmedExtract);

    return [
        'title' => $title,
        'url' => $url,
        'extract' => $str,
    ];
}
$countryInfo = getCountryInfo($_REQUEST["query"]);

// Check if there is valid information available
if (isset($countryInfo['query']['pages'])) {
    $result = extractInfo($countryInfo);

    $output['status']['code'] = "200";
	$output['status']['name'] = "ok";
	$output['status']['description'] = "success";
	$output['status']['returnedIn'] = intval((microtime(true) - $executionStartTime) * 1000) . " ms";
	$output['data'] = $result;

	
	header('Content-Type: application/json; charset=UTF-8');

	echo json_encode($output);
} else {
    // Display an error message in JSON format
    header('Content-Type: application/json');
    echo json_encode(['error' => 'No information available for the specified country.'], JSON_PRETTY_PRINT);
}
?>