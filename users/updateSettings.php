<?php

require_once '../vendor/autoload.php';

use App\Model\UserSettingsForm;
use App\Service\UserService;
use App\Utils\Utils;

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


$returnData = [];

$user = UserService::getCurrentUser();
if (is_null($user)) {
    $returnData = Utils::msg(0, 401, "Unauthorized");
} elseif ($_SERVER["REQUEST_METHOD"] != "POST") {
    $returnData = Utils::msg(0, 404, 'Page Not Found!');
} else {
    $form = UserSettingsForm::parse(file_get_contents("php://input"));
    if (is_null($form)) {
        $returnData = Utils::msg(0, 422, 'Please Fill in all Required Fields!');
    } else {
        try {
            $settings = UserService::updateSettings($form, $user);


            if ($settings === null) {
                $returnData = Utils::msg(0, 500, 'Server error!');
            } else {
                $returnData = $settings->serialize();
                $returnData['success'] = 1;
            }
        } catch (\Exception $e) {
            $returnData = Utils::msg(0, 500, 'If the intention_code is null, the text should always be filled.');
        }
    }
}
echo json_encode($returnData);