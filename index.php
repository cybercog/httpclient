<?php
require_once('classes/CURL/HttpClient.php');
require_once('classes/CURL/Cookie.php');

$apiUrl = 'https://example.com';
$endpointUrl = '/some/folders';
$url = $apiUrl.$endpointUrl;
$queryData = array(
    'api'=>'1',
);
$postData = array(
    'username'=>'root',
    'password'=>'pwd',
);

$cURL = new \CURL\HttpClient();
$cURL->enableCookie();
$cURL->setRequestPost($postData);
$cURL->sendRequest($url, $queryData, \CURL\HttpClient::METHOD_POST);
$httpCode = $cURL->getHttpCode();
$response = $cURL->getResponse();
$debug = $cURL->fetchDebug();

echo '<pre>Response:'.$response.'</pre>';
echo '<pre>'.$debug.'</pre>';