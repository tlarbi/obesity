<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once '../vendor/autoload.php';

use App\Service\SurveyService;
use App\Service\UserService;
use App\Utils\Utils;
$returnData = [];
if($_SERVER["REQUEST_METHOD"] != "GET") {
    $returnData = Utils::msg(0, 404, 'Page Not Found!');
}elseif(is_null(UserService::getCurrentUser())) {
    $returnData = Utils::msg(0, 401, "Unauthorized");
}elseif(!isset($_GET['id'])){
    $returnData = Utils::msg(0, 422, "Invalid id!");
}else{
    $result = SurveyService::isFirstOfTheDay((int)$_GET['id'], isset($_GET['state'])?$_GET['state'] : null);
    if(is_null($result)){
        $returnData = Utils::msg(0,500,'Error.');
    }else{
        $returnData = Utils::msg(1,201,'Result.');
        if($result){
            $returnData['value']=1;
        }else{
            $returnData['value']=0;
        }
    }
}

echo json_encode($returnData);