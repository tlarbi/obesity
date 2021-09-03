<?php

require_once '../vendor/autoload.php';

use App\Service\UserService;
use App\Utils\Utils;

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


$user = UserService::getCurrentUser();
$returnData = [];
if($_SERVER["REQUEST_METHOD"] != "GET") {
    $returnData = Utils::msg(0, 404, 'Page Not Found!');
}elseif(!is_null($user)){
    $returnData = $user->serialize();
    $returnData['success']=1;
}else{
	$returnData = Utils::msg(0,401,"Unauthorized");
}

echo json_encode($returnData);