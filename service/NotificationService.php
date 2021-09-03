<?php

namespace App\Service;

use App\Model\Locale;
use App\Utils\ConfigUtils;
use App\Utils\TranslationUtils;
use Google_Client;
use Google_Service_FirebaseCloudMessaging;
use Google_Service_FirebaseCloudMessaging_AndroidConfig;
use Google_Service_FirebaseCloudMessaging_ApnsConfig;
use Google_Service_FirebaseCloudMessaging_ApnsFcmOptions;
use Google_Service_FirebaseCloudMessaging_Message;
use Google_Service_FirebaseCloudMessaging_Notification;
use Google_Service_FirebaseCloudMessaging_SendMessageRequest;

class NotificationService{
    private static $client;
    private static $datastore;
    private static function getClient() : Google_Client{
        if(is_null(self::$client)){
            self::$client = new Google_Client();
            self::$client->setAuthConfig(realpath(ConfigUtils::getProperty("AUTH_PATH")));
            self::$client->getRefreshToken();
            self::$client->addScope( ['https://www.googleapis.com/auth/firebase','https://www.googleapis.com/auth/firebase.messaging']);
        }
        return self::$client;
    }
    private static function getDatastore() : Google_Service_FirebaseCloudMessaging{
        if(is_null(self::$client)){
            self::$datastore = new Google_Service_FirebaseCloudMessaging(self::getClient());
        }
        return self::$datastore;
    }
    public static function getMessage(string $title, string $body, bool $high, int $ttl, ?string $image = null, ?string $collaps_key = null) : Google_Service_FirebaseCloudMessaging_Message{
        $message = new Google_Service_FirebaseCloudMessaging_Message();
        $notification = new Google_Service_FirebaseCloudMessaging_Notification();
        $apn = new Google_Service_FirebaseCloudMessaging_ApnsConfig();
        $fcm_option = new Google_Service_FirebaseCloudMessaging_ApnsFcmOptions();
        $notification->setTitle($title);
        $notification->setBody($body);
        if(is_null($image)){
            $notification->setImage($image);
            $fcm_option->setImage($image);
            $apn->setFcmOptions($fcm_option);
            $message->setApns($apn);
        }
        $message->setNotification($notification);
        $header = [];
        if(!$high){
            $header["apns-priority"] = "5";
        }
        if(!is_null($ttl) || !is_null($collaps_key) || $high){
            $android = new Google_Service_FirebaseCloudMessaging_AndroidConfig();

            if(!is_null($collaps_key)){
                $android->setCollapseKey($collaps_key);
                $header["apns-collapse-id"] = $collaps_key;
            }
            if($high){
                $android->setPriority("high");
                $header["apns-priority"] = "10";
            }
            if(!is_null($ttl)){
                $android->setTtl($ttl."s");
                $header["apns-expiration"] = (time()+$ttl)."";
            }

            $message->setAndroid($android);
        }
        $apn->setHeaders($header);
        $message->setApns($apn);
        return $message;
    }
    public static function sendNotification(Google_Service_FirebaseCloudMessaging_Message $message, ?string $token = null, $id = null){
        if(!is_null($token)){
            $message->setToken($token);
        }

        $request = new Google_Service_FirebaseCloudMessaging_SendMessageRequest();
        if(!is_null($id)){
            $message->setData(["token"=>$id]);
        }
        $request->setMessage($message);
        $result = self::getDatastore()->projects_messages->send(self::getProjectId(),$request);
    }
    private static function getProjectId() : string{
        return ConfigUtils::getProperty("PROJECT_ID");
    }
    public static function getRandomMessage(Locale $locale, int $step) : Google_Service_FirebaseCloudMessaging_Message{
        return self::getMessage(TranslationUtils::getTranslation($locale, "Notification.".$step.".Title"), TranslationUtils::getTranslation($locale, "Notification.".$step.".Body"), true, 1200, "https://i.pinimg.com/originals/0c/50/a1/0c50a13f4cc1faf617f12848d5bef477.png", "Random");
    }
}