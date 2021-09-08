<?php

use App\Model\ActivityForm;
use App\Service\ActivityService;
use App\Repository\ActivityRepository;
use App\Service\UserService;
use App\Utils\Utils;

require_once '../vendor/autoload.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


$returnData = [];

/*
$user = UserService::getCurrentUser();
if(is_null($user)){
    $returnData = Utils::msg(0,401,"Unauthorized");
}else*/

if($_SERVER["REQUEST_METHOD"] != "POST"){
    $returnData = Utils::msg(0,404,'Page Not Found!');
}else{
    $form = json_decode(file_get_contents("php://input"), true);
    if(is_null($form)){
        $returnData = Utils::msg(0,422,'Please Fill in all Required Fields!');
    }else{
        $activity = ActivityRepository::insertActivity($form);
        if(is_null($activity)){
            $returnData = Utils::msg(0,500,'Server error!');
        }else{
            $returnData = Utils::msg(1,201,'Registered!');
        }
    }
}
echo json_encode($returnData);