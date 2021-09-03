<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once '../vendor/autoload.php';
use App\Utils\Utils;

$form = \App\Model\ListAnswerForm::parse(file_get_contents("php://input"));
$returnData = [];
if($_SERVER["REQUEST_METHOD"] != "POST") {
    $returnData = Utils::msg(0, 404, 'Page Not Found!');
}elseif(is_null($form)){
    $returnData = Utils::msg(0, 422, 'Please Fill in all Required Fields!');
}elseif(!is_null(\App\Service\UserService::getCurrentUser())){
    $ans = \App\Service\SurveyService::listAns($form);
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
}else{
    $returnData = [
        "success" => 0,
        "status" => 401,
        "message" => "Unauthorized"
    ];
}

echo json_encode($returnData);