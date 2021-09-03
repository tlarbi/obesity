<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once '../vendor/autoload.php';

use App\Model\UpdateSurveyForm;
use App\Service\SurveyService;
use App\Service\UserService;
use App\Utils\Utils;
$returnData = [];
$form = UpdateSurveyForm::parse(file_get_contents("php://input"));
if($_SERVER["REQUEST_METHOD"] != "POST") {
    $returnData = Utils::msg(0, 404, 'Page Not Found!');
}elseif(is_null($form)) {
    $returnData = Utils::msg(0, 422, 'Please Fill in all Required Fields!');
}elseif(!is_null(UserService::getCurrentUser())){
    if(SurveyService::updateSurvey($form)){
        $returnData = Utils::msg(1,201,'Survey updated.');
    }else{
        $returnData = Utils::msg(0,500,"Can't update survey!");
    }
}else{
    $returnData = [
        "success" => 0,
        "status" => 401,
        "message" => "Unauthorized"
    ];
}

echo json_encode($returnData);