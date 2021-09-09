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

$user = UserService::getCurrentUser();
$data = json_decode(file_get_contents("php://input"), true);


$user = UserService::getCurrentUser();
// if(is_null($user)){
//     $returnData = Utils::msg(0,401,"Unauthorized");
// }else
if($_SERVER["REQUEST_METHOD"] != "GET"){
    $returnData = Utils::msg(0,404,'Page Not Found!');
} else {
    $returnData = Utils::msg(1,201,"Success");
    if (isset($data['start']) && isset($data['end']) && isset($data['user_id'])) {
        $result = ActivityRepository::getActivity($data['user_id'], $data['start'], $data['end']);
    }
    $returnData["activity"] = is_null($result) ? null : $result;
}

echo json_encode($returnData);