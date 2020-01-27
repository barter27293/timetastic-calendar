<?php

$API_KEY = "..."; //Enter your Timetasic API key here

// Create a stream
$opts = array(
  'http'=>array(
    'method'=>"GET",
    'header'=>"Authorization: Bearer " . $API_KEY 
  )
);

$context = stream_context_create($opts);

$start = date("Y-m-d", strtotime( "-1 month" ));  // Alter according to how many months data prior to today you want to show

$dept_id = "DepartmentId=119746";  // If you want to show only a specific department - enter the ID here (You can find the dept. id's by querying https://app.timetastic.co.uk/api/departments)  leave the string empty if you want to show all.

// Open the file using the HTTP headers set above
$json = file_get_contents('https://app.timetastic.co.uk/api/holidays?'. $dept_id . '&Start=' . $start . "'", false, $context);

// Also get public holdays data
$json_ph = file_get_contents('http://app.timetastic.co.uk/api/publicholidays', false, $context);

// Store JSON data in a PHP variable

$data = json_decode($json, true);
$data_ph = json_decode($json_ph, true);


// Create Array combining names when employees are off at the same time, rather than having individual entries for every employee
$combined = [];
foreach ($data['holidays'] as $value) {

	$holdate = $value["startDate"].$value["endDate"];
    $adjend = date("Y-m-d H:i:s", strtotime($value["endDate"] . " +20 hour"));

     if (isset($combined[$holdate])) {
         $combined[$holdate] = [
            "startDate" => $value["startDate"],
            "endDate" => $adjend,
            "userName" => $value["userName"].", ".$combined[$holdate]["userName"],
            "color" => "#84A9C4"
         ];
     } else {
		$combined[$holdate] = [
			"userName" => $value["userName"],
			"startDate" => $value["startDate"],
            "endDate" => $adjend,
            "color" => "#84A9C4"
		];
     }
}


// Add the public holidays data to the array
foreach ($data_ph as $value) {
    $combined[] = array(
    "userName" => $value["name"],
    "startDate" => $value["date"],
    "color" => "#AC9866"
);
}


$output = json_encode($combined);

header('Content-type: application/json');

echo $output;

?>