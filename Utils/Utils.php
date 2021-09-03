<?php

namespace App\Utils;

use Mailjet\Client;
use Mailjet\Resources;

class Utils{
    public static function msg($success,$status,$message,$extra = []){
        return array_merge([
            'success' => $success,
            'status' => $status,
            'message' => $message
        ],$extra);
    }

    private static $chars = [ 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S',
        'T', 'U', 'V', 'W', 'X', 'Y', 'Z', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9' ];
    public static function generateKeyString(int $length) : string {
        $result = '';
        for ($i = 0; $i < $length; $i++) {
            $result .= array_rand(self::$chars);
        }

        return $result;
    }


    private static $numbers = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
    public const RESET_PASSWORD_TOKEN_LENGTH = 8;
    public static function generateResetToken()
    {
        $result = '';
        for ($i = 0; $i < self::RESET_PASSWORD_TOKEN_LENGTH; $i++) {
            $result .= array_rand(self::$numbers);
        }

        return $result;
    }

    public static function sendMail($body)
    {
        $mailerClient = new Client(ConfigUtils::getProperty('MJ_APIKEY_PUBLIC'), ConfigUtils::getProperty('MJ_APIKEY_PRIVATE'), ConfigUtils::getBool('MJ_API_CALL'));
        return $mailerClient->post(Resources::$Email, ['body' => $body]);
    }

    public static function log(string $string){
        if(ConfigUtils::shouldLog()){
            error_log($string);
        }
    }

    /**
     * Check if the password respect the password politic
     *
     * @param string $password
     * @return bool
     */
    public static function checkPasswordPolitic(string $password): bool
    {
        return strlen($password) >= 8;
    }
}