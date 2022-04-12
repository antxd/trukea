<?php
/************************************/
/* Basic cron price updater.
 * Autor: Jose Marin - https://github.com/antxd
 * License: MIT
 * 12/04/2022
 * Version: Beta 0.1.2
/************************************/
function CallAPI($method, $url, $data = false, $json = false){
    // Method: POST, PUT, GET etc
    // Data: array("param" => "value") ==> index.php?param=value
    $curl = curl_init();
    switch ($method){
        case "POST":
            curl_setopt($curl, CURLOPT_POST, 1);
            if ($data)
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            break;
        case "PUT":
            curl_setopt($curl, CURLOPT_PUT, 1);
            break;
        default:
            if ($data)
                $url = sprintf("%s?%s", $url, http_build_query($data));
    }
    // Optional Authentication:
    //curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    //curl_setopt($curl, CURLOPT_USERPWD, "username:password");
    if ($json) {
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Accept: application/json'));
    }
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($curl);
    curl_close($curl);
    return $result;
}
/*FIAT*/
function getCurrency($symbol){
    $apikey = '6f6a03deaf95a8578db7';
    $query = 'USD_EUR,USD_COP';
    $json = CallAPI('GET',"https://free.currconv.com/api/v7/convert?q={$query}&compact=ultra&apiKey={$apikey}");
    $obj = json_decode($json, true);
    $val = floatval($obj["$symbol"]);
    return $val;
}
/*CRYPTO*/
function GetCryptoCurrency($symbol){
    $json = CallAPI('GET',"https://coinsbit.io/api/v1/public/ticker?market={$symbol}");
    $obj = json_decode($json, true);
    //PLCU_USDT
    //BTC_USDT
    $val = (!empty($obj['success']))?floatval($obj['result']['ask']):0;
    return $val;
}
$formatbase = json_decode(file_get_contents('api.json'));
$format = array();
$time = time();
foreach ($formatbase as $key => $value) {
    $format[$key] = [$value[0],$value[1]];
    $last_modified = new DateTime(date('Y-m-d H:i:s', $value[1] ));
    $since_last_modified = $last_modified->diff(new DateTime(date('Y-m-d H:i:s',$time)));
    if ($key === 'EUR' && $since_last_modified->i >= 30 || $since_last_modified->h > 1 || $since_last_modified->d > 0) {
        $format['EUR'] = [getCurrency('USD_EUR'),$time];
        echo "update EUR";
    }
    if ($key === 'PLCU' && $since_last_modified->i >= 5 || $since_last_modified->h > 0 || $since_last_modified->d > 0) {
        $format['PLCU'] = [GetCryptoCurrency('PLCU_USDT'),$time];
        echo "update PLCU";
    }
}
//WRITE JSON
$fp = fopen('api.json', 'w');
fwrite($fp, json_encode($format));
fclose($fp);
?>
