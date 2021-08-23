<?php 
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

// $STAGE_PATH = "https://stg-fal-careerguide.s3.ap-south-1.amazonaws.com/india-stage/JSON";
// $PROD_PATH = "https://static.glowandlovelycareers.in/india-live/JSON";

// $Drupal_Api_File_Path = "https://liveonlinetraining.in/assets/GALC_Drupal_API_List_STAGE.txt";
$Drupal_Api_File_Path = "assets/GALC_Drupal_API_List_STAGE.txt";


$Api_NotWorking_List = array();

function apiChecker($method,$url,$data=false){
    global $Api_NotWorking_List;
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

    switch ($method)
    {
        case "GET": 
            // echo("GET Request RESPONSE =>");        
            curl_setopt($curl, CURLOPT_URL, $url);
            break;
        case "POST":
                echo("POST Request RESPONSE =>");
                curl_setopt($curl, CURLOPT_POST, 1);
                if ($data)
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                break;
        default:
    }
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HEADER, 1);
    
    
    if(strpos($url, '.json') === false){
        curl_setopt($curl, CURLOPT_USERPWD, 'stgfalcfaccess:St@geG!owLovely20K20');  
    } 

    $response = curl_exec($curl);
    $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
    $header = substr($response, 0, $header_size);
    $body = substr($response, $header_size);
    $statusCode = substr($header, 9, 3 );
        
    
    // print_r($statusCode);
    curl_close($curl);
    
    if($statusCode == 200){
        // echo ("Your API is Working Fine! <br> API Name = $url <br>");
        // echo ("Status Code = $statusCode <hr>");
        return $response;
    }else{
        array_push($Api_NotWorking_List,$statusCode .' '.$url);
        echo ("<div style='color:red;'> Your API is Down! Inform Your Application Developer Team To Check It!<br></div>");
        echo ("<div style='color:red;'> API Name = $url </div>");
        echo ("<div style='color:red;'> Status Code = $statusCode </div><hr>");
        // print_r(substr($header, 7, 3 ));
    }
    
}


$DRUPAL_Api_File_Data = fopen($Drupal_Api_File_Path,'r');
$count = 0;
$DRUPAL_API_Array_List = array();

while ($file = fgets($DRUPAL_Api_File_Data)) {
    // array_push($DRUPAL_API_Array_List, $STAGE_PATH .$file);
    array_push($DRUPAL_API_Array_List, $file);
    apiChecker("GET", trim($DRUPAL_API_Array_List[$count]), false);
    $count++;
}
fclose($DRUPAL_Api_File_Data);

$apiDownList = "";
foreach($Api_NotWorking_List as $v)
{
  $apiDownList .= '<li>'.$v.'</li>';
}

// Below code for Sending email to users if API is not working 
$to      = "prashant.wakodikar@digitas.com,sagar.shinde@digitas.com";
$subject = 'GALC IN Stage - API Status Report';
$headers = 'From: India API Status Report <falf.digitas@gmail.com>' . "\r\n" .
    'Reply-To: falf.digitas@gmail.com' . "\r\n" .
    'Content-type: text/html; charset: utf8' . "\r\n" .
    'Cc: falf.digitas@gmail.com' . "\r\n" .
    'X-Mailer: PHP/' . phpversion();
$message = 'Hi Team,
            <br><br>
            <div> Below list of API is Down! 
            inform your Technology Team to check!</div><br>
            <div><strong>Platform: </strong>GALC India Stage</div>
            <div><strong>Total Api Scan: </strong>' .$count. '</div>
            <div><strong>Impacted Api: </strong>' .count($Api_NotWorking_List). '</div>
            <div><strong>Impacted Api List: </strong></div>
            <ul style="color:red;">'.$apiDownList.'</ul>
            <br>
            Regards<br>
            GALC Digitas Team';

// mail($to, $subject, $message, $headers);
echo $message;

?>