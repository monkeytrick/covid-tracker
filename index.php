<?php

include "jqvmap-master/REGIONS.php";

//Set to return GMT
date_default_timezone_set('Europe/Dublin');

$curl = curl_init();

curl_setopt_array($curl, [
	CURLOPT_URL => "https://covid-193.p.rapidapi.com/statistics",
	CURLOPT_RETURNTRANSFER => true,
	CURLOPT_FOLLOWLOCATION => true,
	CURLOPT_ENCODING => "",
	CURLOPT_MAXREDIRS => 10,
	CURLOPT_TIMEOUT => 30,
	CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	CURLOPT_CUSTOMREQUEST => "GET",
	CURLOPT_HTTPHEADER => [
		"x-rapidapi-host: covid-193.p.rapidapi.com",
		"x-rapidapi-key: KENTER EY HERE"
	],
]);

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

$arr = null;

if ($err) {
	echo "cURL Error #:" . $err;
} else {
	$arr = json_decode($response);
}

//Decode JSON for manipulation by PHP
$arr = json_decode($response);

//Array into which country data is sorted after code is matched and converted
$country_data = [];

foreach($arr->response as $dat) {
    foreach($countries_list as $count_code => $value) {
        if($dat->country == $value) {
            $lower_case = strtolower($count_code);
            $country_data[$lower_case] = $dat->cases->total;       
        }
    }
}

//Sort data - JQVMap cannot process if not in order
ksort($country_data);

//Convert array back to JSON so can be passed as data to table
$json_data = json_encode($country_data);

// Loop through countries to get country name - JQMap does not reference this, only two letter ISO code for this
if(isset($_GET['country'])) {
    foreach($countries_list as $count_code => $value) {
        if($_GET['country'] == $count_code) {
            // Loop through previous response using country name ($value) to find data
            foreach($arr->response as $count_data) {
                if($count_data->country == $value) {
                    //Create array with country name and data
                    $data = json_encode(["country" => $value, "country-data" => $count_data->cases]);
                    header('Content-type:application/json;charset=utf-8');
                    echo $data;
                    exit;
                };
            };
        };
    };
};

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
  <head>
    <title>Real-time Covid Info.</title>
    <meta content="text/html; charset=utf-8" http-equiv="Content-Type">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="jqvmap-master/dist/jqvmap.css" media="screen" rel="stylesheet" type="text/css"/>

    <script type="text/javascript" src="http://code.jquery.com/jquery-1.11.3.min.js"></script>
    <script type="text/javascript" src="jqvmap-master/dist/jquery.vmap.js"></script>
    <script type="text/javascript" src="jqvmap-master/dist/maps/jquery.vmap.world.js" charset="utf-8"></script>
    <script type="text/javascript" src="jscript.js"></script>
    <!-- Data for map -->
    <script type="application/javascript"> let allData = <?php echo $json_data ?>; </script>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
   
    <style>
        body {
          background-color: black;
        }
        table {
          font-family: arial, sans-serif;
          border-collapse: collapse;
          width: 85%;
        }

        td, th {
          border: 1px solid #dddddd;
          text-align: left;
          padding: 8px;
        }

        tr:nth-child(even) {
          background-color: #dddddd;
        }

        a.nav-link:hover{
          background-color: #3eb7b5;
        }

        /* Bootstrap styles */
        
        .bd-placeholder-img {
          font-size: 1.125rem;
          text-anchor: middle;
          -webkit-user-select: none;
          -moz-user-select: none;
          user-select: none;
        }

        @media (min-width: 768px) {
          .bd-placeholder-img-lg {
            font-size: 3.5rem;
          }
        }

    </style> 
  </head>
  <body cz-shortcut-listen="true">
    <main>
        <div class="container">
            <div class="row">
    
                  <!-- Sidebar -->
                  <div class="d-flex col-3 flex-column flex-shrink-0 p-3 text-white bg-dark" style="width: 280px;">
                    <a href="/" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
                      <svg class="bi me-2" width="40" height="32"><use xlink:href="#bootstrap"/></svg>
                      <span class="fs-4">Regions</span>
                    </a>
                    <hr>
                    <ul class="nav nav-pills flex-column mb-auto">
                    <li>
                        <a href="#" data-region="world" class="nav-link active text-white">
                          <svg class="bi me-2" width="16" height="16"><use xlink:href="#people-circle"/></svg>
                          World
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="#" data-region="afr" class="nav-link text-white" aria-current="page">
                          <svg class="bi me-2" width="16" height="16"><use xlink:href="#home"/></svg>
                          Africa
                        </a>
                      </li>
                      <li>
                        <a href="#" data-region="asia" class="nav-link text-white">
                          <svg class="bi me-2" width="16" height="16"><use xlink:href="#speedometer2"/></svg>
                          Asia
                        </a>
                      </li>
                      <li>
                        <a href="#" data-region="eu" class="nav-link text-white">
                          <svg class="bi me-2" width="16" height="16"><use xlink:href="#people-circle"/></svg>
                          Europe
                        </a>
                      </li>
                      <li>
                        <a href="#" data-region="midE" class="nav-link text-white">
                          <svg class="bi me-2" width="16" height="16"><use xlink:href="#table"/></svg>
                          Middle East
                        </a>
                      </li>
                      <li>
                        <a href="#" data-region="ocea" class="nav-link text-white">
                          <svg class="bi me-2" width="16" height="16"><use xlink:href="#grid"/></svg>
                          Oceania
                        </a>
                      </li>
                      <li>
                        <a href="#" data-region="nAm" class="nav-link text-white">
                          <svg class="bi me-2" width="16" height="16"><use xlink:href="#people-circle"/></svg>
                          North America
                        </a>
                      </li>
                      <li>
                        <a href="#" data-region="sAm" class="nav-link text-white">
                          <svg class="bi me-2" width="16" height="16"><use xlink:href="#people-circle"/></svg>
                          South America
                        </a>
                      </li>
                    </ul>
                    <hr>

                  </div>

                  <div class="d-flex align-items-center col-9 flex-column flex-shrink-0 p-3 bg-light">
                      <h1>Covid World Tracker</h1>
                        <p>Statistics for <?php echo date('F j, Y, g:i a', strtotime($arr->response[0]->time)); ?> GMT</p>
                        <div id="vmap" style="width: 850px; height: 550px; box-sizing: content-box !important;"></div>

                        <table class="mt-4">
                            <tr>
                              <th>Country</th>
                              <th>New</th>
                              <th>Active</th>
                              <th>Critical</th>
                              <th>Total</th>

                            </tr>
                            <tr>
                              <td id="count-name"></td>
                              <td id="new"></td>
                              <td id="active"></td>
                              <td id="critical"></td>
                              <td id="total"></td>
                            </tr>
                          </table>
                  </div>
              </div>
        </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
  </body>
</html>

         
      
