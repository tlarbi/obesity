<?php

use App\Model\ListSnoozeForm;
use App\Service\SnoozeService;
use App\Service\UserService;
use App\Utils\Utils;

require_once '../vendor/autoload.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


$returnData = [];

$user = UserService::getCurrentUser();
if(is_null($user)){
    $returnData = Utils::msg(0,401,"Unauthorized");
}elseif($_SERVER["REQUEST_METHOD"] != "POST"){
    $returnData = Utils::msg(0,404,'Page Not Found!');
}else{
    $form = ListSnoozeForm::parse(file_get_contents("php://input"));
    if(is_null($form)){
        $returnData = Utils::msg(0,422,'Please Fill in all Required Fields!');
    }else{
        $ans = SnoozeService::listSnooze($form);
        if(!is_null($ans)){
            $returnData = Utils::msg(1,201,'Result list.');
            $data = [];
            foreach ($ans as $value){
                array_push($data, $value->serialize());
            }
            $returnData['data']=$data;
        }else{
            $returnData = Utils::msg(0,500, "Server error.");
        }
    }
}
echo json_encode($returnData);