<?php
$format = array();
/*FIAT*/
function convertCurrency($amount,$from_currency,$to_currency){
    $apikey = '6f6a03deaf95a8578db7';
    $from_Currency = urlencode($from_currency);
    $to_Currency = urlencode($to_currency);
    $query =  "{$from_Currency}_{$to_Currency}";
    // change to the free URL if you're using the free version
    $json = file_get_contents("https://free.currconv.com/api/v7/convert?q={$query}&compact=ultra&apiKey={$apikey}");
    $obj = json_decode($json, true);
    $val = floatval($obj["$query"]);
    $total = $val * $amount;
    return $total;
}
$format['EUR'] = convertCurrency(1,'USD','EUR');
$format['COP'] = convertCurrency(1,'USD','COP');
/*CRYPTO*/
$url = 'https://pro-api.coinmarketcap.com/v1/cryptocurrency/quotes/latest';
//BTC, LTC, ETH, BCH, USDT, PLCU
$parameters = [
    'id' => '1,2,1027,1831,825,17971',
    //'start' => '1',
    //'limit' => '5000',
    'convert' => 'USD'
];

/*$url = 'https://pro-api.coinmarketcap.com/v1/cryptocurrency/listings/latest';
$parameters = [
  'start' => '1',
  'limit' => '5000',
  'convert' => 'USD'
];*/

$headers = [
  'Accepts: application/json',
  'X-CMC_PRO_API_KEY: 0249fa9b-bef6-4da4-8a5d-7c495ab99a22'
];
$qs = http_build_query($parameters); // query string encode the parameters
$request = "{$url}?{$qs}"; // create the request URL

$curl = curl_init(); // Get cURL resource
// Set cURL options
curl_setopt_array($curl, array(
  CURLOPT_URL => $request,            // set the request URL
  CURLOPT_HTTPHEADER => $headers,     // set the headers 
  CURLOPT_RETURNTRANSFER => 1         // ask for raw response instead of bool
));

$response = curl_exec($curl); // Send the request, save the response
$coinmarket = json_decode($response);
foreach ($coinmarket->data as $key => $value) {
    $format[$value->symbol] = $value->quote->USD->price;
}
//echo "<pre>";
//print_r(json_decode($response)); // print json decoded response
//echo "</pre>";
curl_close($curl); // Close request
$fp = fopen('api.json', 'w');
fwrite($fp, json_encode($format));
fclose($fp);
?>
