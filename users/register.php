<?php
require_once '../vendor/autoload.php';

use App\Utils\Utils;
use App\Model\RegisterForm;
use App\Service\UserService;

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


// GET DATA FORM REQUEST
$form = RegisterForm::parse(file_get_contents("php://input"));
$returnData = [];
// IF REQUEST METHOD IS NOT POST
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    $returnData = Utils::msg(0, 404, 'Page Not Found!');
} // CHECKING EMPTY FIELDS
elseif (is_null($form)) {

    $fields = ['fields' => ['code', 'username', 'password']];
    $returnData = Utils::msg(0, 422, 'Please Fill in all Required Fields!', $fields);
} // IF THERE ARE NO EMPTY FIELDS THEN-
elseif (is_array($form)) {
    $returnData = $form;
} else if (!Utils::checkPasswordPolitic($form)) {
    $returnData = Utils::msg(0, 422, 'The password does not respect the password politic');
} else {
    try {
        $user = UserService::registerUser($form);
        if (is_null($user)) {
            $returnData = Utils::msg(0, 500, 'Error');
        } else {
            $returnData = Utils::msg(1, 201, 'You have successfully registered.');
        }
    } catch (Exception $e) {
        Utils::log($e->getTraceAsString());
        $returnData = Utils::msg(0, 500, $e->getMessage());
    }

}

echo json_encode($returnData);  
