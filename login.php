<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once 'vendor/autoload.php';

use App\Utils\Utils;
use App\Utils\JwtHandler;
use App\Repository\UserRepository;

//require_once (__DIR__ . '/classes/Database.php');
//require_once (__DIR__ . '/classes/JwtHandler.php');
//require_once (__DIR__.'/repository/UserRepository.php');
//require_once (__DIR__.'/entity/User.php');

//$db_connection = new Database();
//$conn = $db_connection->dbConnection();

$data = json_decode(file_get_contents("php://input"), true);
$returnData = [];

// IF REQUEST METHOD IS NOT EQUAL TO POST
if($_SERVER["REQUEST_METHOD"] != "POST") {
    $returnData = Utils::msg(0, 404, 'Page Not Found!');
}
// CHECKING EMPTY FIELDS
elseif(!isset($data['username'])
    || !isset($data['password'])
    || empty(trim($data['username']))
    || empty(trim($data['password']))
    ) {

    $fields = ['fields' => ['username', 'password']];
    $returnData = Utils::msg(0, 422, 'Please Fill in all Required Fields!', $fields);
}
// IF THERE ARE NO EMPTY FIELDS THEN-
else {
    $username = trim($data['username']);
    $password = trim($data['password']);

    $user = UserRepository::findByUsername($username);
    if (!is_null($user)) {
        $check_password = password_verify($password, $user->getPassword());

        // VERIFYING THE PASSWORD (IS CORRECT OR NOT?)
        // IF PASSWORD IS CORRECT THEN SEND THE LOGIN TOKEN
        if ($check_password) {
            $jwt = new JwtHandler();
            $token = $jwt->_jwt_encode_data(
                'http://localhost/php_auth_api/',
                array("user_id" => $user->getUserId())
            );
            $user->setCurrentToken($token);
            $user = UserRepository::save($user);
            if (is_null($user)) {
                $returnData = Utils::msg(0, 500, 'Internal error!');
            } else {
                $returnData = $user->serialize();
                $returnData['success'] = 1;
                $returnData['message'] = 'You have successfully logged in.';
                $returnData['token'] = $user->getCurrentToken();
            }

//
//                    $query = "UPDATE `user` SET `current_token` = :token";
//                    $stmt = $conn->prepare($query);
//                    // DATA BINDING
//                    $stmt->bindValue(':token', $token,PDO::PARAM_STR);
//                    $stmt->execute();
            // IF INVALID PASSWORD
        }else {
            $returnData = Utils::msg(0, 422, 'Invalid credentials!');
        }

    }
    else {
        $returnData = Utils::msg(0, 422, 'Invalid credentials!');
    }
}

echo json_encode($returnData);