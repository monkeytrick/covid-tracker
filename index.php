<?php

//Set to return GMT
date_default_timezone_set('Europe/Dublin');

include "jqvmap-master/REGIONS.php";

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
		"x-rapidapi-key: "
	],
]);

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
	echo "cURL Error #:" . $err;
} else {
	$arr = json_decode($response);
}


$arr = json_decode($response);

//Array into which countries' data is stored
$country_data = [];

foreach($arr->response as $dat) {
    foreach($countries_list as $count_code => $value) {
        
        // Match names to list stored in file 'Regions' and pull country code from this
        if($dat->country == $value) {
            
            // Change code to lower case for use with case-sensitve JQMap
            $lower_case = strtolower($count_code);
            
            // Push country code and case data into assoc. 
            $country_data[$lower_case] = $dat->cases->total;
        }
    }
}

//Sort data - JQMap cannot process unless in order
ksort($country_data);

//Send as JSON
$json_data = json_encode($country_data);

// Single country requests
if(isset($_GET['country'])) {

    // Loop through countries to get country name - JQMap does not reference this, only two letter ISO code for this
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
    <title>World Covid Case Tracker</title>
    <meta content="text/html; charset=utf-8" http-equiv="Content-Type">

    <link href="jqvmap-master/dist/jqvmap.css" media="screen" rel="stylesheet" type="text/css"/>

    <script type="text/javascript" src="https://code.jquery.com/jquery-1.11.3.min.js"></script>
    <script type="text/javascript" src="jqvmap-master/dist/jquery.vmap.js"></script>
    <script type="text/javascript" src="jqvmap-master/dist/maps/jquery.vmap.world.js" charset="utf-8"></script>

    <script>
      jQuery(document).ready(function () {
        jQuery('#vmap').vectorMap({
          map: 'world_en',
          backgroundColor: '#fff',
          color: '#ffffff',
          hoverOpacity: 0.7,
          selectedColor: '#666666',
          enableZoom: true,
          showTooltip: true,
          scaleColors: ['#C8EEFF', '#006491'],
          values: <?php echo $json_data ?>,
          normalizeFunction: 'polynomial',
          onRegionClick: function(element, code, region)
          {
            //Convert code to uppercase so searchable on REGIONS array
            let ctyCode = code.toUpperCase()

            try {
                
                 fetch('' + '?country=' + ctyCode, 
                       {method: 'GET', headers: {'Content-Type':'application/json'}})
                .then(response => response.json())
                .then(data => {
                    
                //Data filtered for null values
                    let dataFilter = new Map;
                    
                    Object.entries(data['country-data']).forEach(([key, val]) => {
                        if(val == null) {
                            val = "0"
                            dataFilter.set(key, val)
                        }
                        else{
                            dataFilter.set(key, val)
                        }
                    })
                    
                  document.getElementById('count-name').innerText = data.country
                  document.getElementById('new').innerText = dataFilter.get('new').toLocaleString("en-us")
                  document.getElementById('active').innerText = dataFilter.get('active').toLocaleString("en-us")
                  document.getElementById('critical').innerText = dataFilter.get('critical').toLocaleString("en-us")
                  document.getElementById('total').innerText = dataFilter.get('total').toLocaleString("en-us")
                      
                  })

                } catch (e) {
             
              console.log(e)
            }
                       
          }
          
        });
      });
        </script>   
        <style>
            table {
              font-family: arial, sans-serif;
              border-collapse: collapse;
              width: 30%;
            }

            td, th {
              border: 1px solid #dddddd;
              text-align: left;
              padding: 8px;
            }

            tr:nth-child(even) {
              background-color: #dddddd;
            }
        </style>    
  </head>
  <body>
    <h1>Covid World Tracker</h1>
    <p>Statistics for <?php echo date('F j, Y, g:i a', strtotime($arr->response[10]->time)); ?> GMT</p>
    <div id="vmap" style="width: 600px; height: 400px;"></div>

    <table>
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
        <p>Data from RapidAPI/api-sports</p>


  </body>
</html>

         
      