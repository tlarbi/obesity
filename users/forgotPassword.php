<?php

require_once '../vendor/autoload.php';

use App\Entity\ResetPasswordToken;
use App\Repository\ResetPasswordTokenRepository;
use App\Repository\UserRepository;
use App\Repository\UserSettingsRepository;
use App\Utils\TranslationUtils;
use App\Utils\Utils;
use App\Model\Locale;

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


$data = json_decode(file_get_contents("php://input"), true);
$returnData = [];
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    $returnData = Utils::msg(0, 404, 'Page Not Found!');
} else if (!isset($data['username'])
    || !isset($data['email'])
    || !isset($data['device_token'])
    || empty($data['username'])
    || empty($data['email'])
    || empty($data['device_token'])) {
    // Throw a message if some field are missing/empty

    $fields = ['fields' => [
        'username',
        'email',
        'device_token'
    ]];
    $returnData = Utils::msg(0, 422, 'Please Fill in all Required Fields!', $fields);
} else {
    // Check if the username exists
    $user = UserRepository::findByUsername($data['username']);
    if ($user === null) {
        $returnData = Utils::msg(0, 422, 'Username does not exists');
    } else {
        $userSettings = UserSettingsRepository::getByUser($user);

        if ($userSettings->getDeviceToken() !== $data['device_token']) {
            $returnData = Utils::msg(0, 422, 'Device Token does not correspond to the username');
        } else if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $returnData = Utils::msg(0, 422, 'The email is not valid');
        } else {
            $token = new ResetPasswordToken($user);
            $locale = Locale::getFromCode($user->getUserSettings()->getLocale());
            $token = ResetPasswordTokenRepository::save($token);
            $renderedTemplate = TranslationUtils::getTemplateTranslation(
                'forgotPassword', [
                    'intro' => TranslationUtils::getTranslation($locale, 'Email.ForgotPassword.Text.Intro', ['%username%' => $user->getUsername()]),
                    'body' => TranslationUtils::getTranslation($locale, 'Email.ForgotPassword.Text.Body', ['%username%' => $user->getUsername()]),
                    'token' => $token->getToken()
            ]);
            $email = [
                'FromEmail' => 'contact@ccoproject.eu',
                'FromName' => 'CCO',
                'To' => $user->getUsername() . ' <' . $data['email'] . '>',
                'Subject' => 'Reset your password',
                'Html-part' => $renderedTemplate,
            ];
            $response = Utils::sendMail($email);

            if ($response->success()) {
                $returnData = Utils::msg(1, 200, 'An email have been sent with the token');
            } else {
                $returnData = Utils::msg(0, 403, 'The email can\'t be send');
                Utils::log(json_encode($response->getData()));
            }
        }
    }
}

echo json_encode($returnData);