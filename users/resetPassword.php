<?php

require_once '../vendor/autoload.php';

use App\Repository\UserRepository;
use App\Service\UserService;
use App\Utils\Utils;

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$data = json_decode(file_get_contents("php://input"), true);
$returnData = [];
if($_SERVER["REQUEST_METHOD"] != "POST") {
    $returnData = Utils::msg(0, 404, 'Page Not Found!');
}elseif(!isset($data['password']) || !isset($data['token']) || !isset($data['username'])) {
    $fields = ['fields' => [
        'username',
        'password',
        'token',
    ]];
    $returnData = Utils::msg(0, 422, 'Please Fill in all Required Fields!', $fields);
}else if (!Utils::checkPasswordPolitic($data['password'])) {
    $returnData = Utils::msg(0, 422, 'The password does not respect the password politic');
}else{
    $user = UserRepository::findByResetToken($data['token'], $data['username']);

    if (!$user) {
        $returnData = Utils::msg(0, 422, 'Token is invalid');
    } else {
        $user->setPassword(password_hash($data['password'], PASSWORD_DEFAULT));
        UserRepository::save($user);

        $returnData = Utils::msg(1, 200, 'Password have been successfully updated');
    }
}

echo json_encode($returnData);