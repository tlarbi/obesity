<?php

use App\Service\SnoozeService;
use App\Service\UserService;
use App\Repository\ActivityRepository;
use App\Utils\Utils;

require_once '../vendor/autoload.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$returnData = [];

//$user = UserService::getCurrentUser();
$data = json_decode(file_get_contents("php://input"), true);

/*
$user = UserService::getCurrentUser();
if(is_null($user)){
    $returnData = Utils::msg(0,401,"Unauthorized");
}*/
if($_SERVER["REQUEST_METHOD"] != "GET"){
    $returnData = Utils::msg(0,404,'Page Not Found!');
} else {
    //$date_creation = isset($data['date']) ? new DateTime($data['date']) : new DateTime();
    $returnData = Utils::msg(1,201,"Success");
    if (isset($data['start']) && $data['end']) {
        $result = ActivityRepository::getActivity($data['start'], $data['end']);
    }
    //$result = ActivityRepository::getActivity($data['date']);
    $returnData["activity"] = is_null($result) ? null : $result;
}

echo json_encode($returnData);