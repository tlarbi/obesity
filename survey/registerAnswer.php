<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once '../vendor/autoload.php';

use App\Model\AnswerForm;
use App\Service\SurveyService;
use App\Service\UserService;
use App\Utils\Utils;

$form = AnswerForm::parse(file_get_contents("php://input"));
if($_SERVER["REQUEST_METHOD"] != "POST") {
    $returnData = Utils::msg(0, 404, 'Page Not Found!');
}elseif(is_null($form)){
    $returnData = Utils::msg(0, 422, 'Please Fill in all Required Fields!');
}elseif(!is_null(UserService::getCurrentUser())){
    $ans = SurveyService::registerAns($form);
    if(!is_null($ans)){
        if(is_string($ans)){
            $returnData = Utils::msg(0,400,$ans);
        }elseif(is_array($ans)){
            $returnData = Utils::msg(1,201,'You have registered '.((sizeof($ans) === 1) ? 'a new answer.' : sizeof($ans).' new answers.'));
            $returnData["amount"] = sizeof($ans);
        }else{
            $returnData = [];
        }
    }else{
        $returnData = Utils::msg(0,500,'Error.');
    }
}else{
    $returnData = [
        "success" => 0,
        "status" => 401,
        "message" => "Unauthorized"
    ];
}

echo json_encode($returnData);