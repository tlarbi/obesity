<?php

use App\Service\SnoozeService;
use App\Utils\ConfigUtils;
use App\Utils\Utils;

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once '../vendor/autoload.php';

$returnData = [];
if($_SERVER["REQUEST_METHOD"] != "GET" || !isset($_GET['code_key']) || strcmp(ConfigUtils::getProperty("LOCAL_KEY"), $_GET['code_key']) !== 0) {
    $returnData = Utils::msg(0, 404, 'Page Not Found!');
}else{
    //TODO return more info
    SnoozeService::cleanSnooze();
    $returnData = Utils::msg(1, 201, 'Success');
}
echo json_encode($returnData);