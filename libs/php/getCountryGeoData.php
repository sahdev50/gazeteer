<?php

  $executionStartTime = microtime(true);

  $countryData = json_decode(file_get_contents("../data/countryBorders.geo.json"), true);

  $country = null;

  foreach ($countryData['features'] as $feature) {

    if($_REQUEST['query'] == $feature["properties"]['iso_a2']){
        $country['geometry'] = $feature['geometry'];
    }
    
  }

  $output['status']['code'] = "200";
  $output['status']['name'] = "ok";
  $output['status']['description'] = "success";
  $output['status']['executedIn'] = intval((microtime(true) - $executionStartTime) * 1000) . " ms";
  $output['data'] = $country;
  
  header('Content-Type: application/json; charset=UTF-8');

  echo json_encode($output);

?>