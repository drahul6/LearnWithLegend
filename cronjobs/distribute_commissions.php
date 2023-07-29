<?php


function make_get($url,  $header = [])
{

    $ch = curl_init($url);

    if (count($header) > 0) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    }

    // Receive server response ...
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);


    $result = curl_exec($ch);
    // $response = curl_getinfo($ch);

    curl_close($ch);

    return $result;
}

$domain = "https://learnwithlegends.com";
$date = date("Y-m");
$url = "$domain/distribute_commission/$date/1";
make_get($url);
