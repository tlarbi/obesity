<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\UserSettings;
use App\Utils\Utils;
use PDO;
use PDOException;
use App\Utils\Database;

class UserSettingsRepository
{
    public static function save(UserSettings $userSettings): ?UserSettings
    {
        $userSettingsOld = $userSettings->getLastSave();
        if (is_null($userSettingsOld)) {

            $intentionTextIsNull = $userSettings->getIntentionText() === null || empty($userSettings->getIntentionText());

            //New
            $conn = Database::dbConnection();
            if (Database::isSqlsrv()) {
                $insert_query = "INSERT INTO user_settings([avatar], [device_token], [time_code], [wakeup], [sleep], [user_id], [locale], [intention_code], [intention_text]) VALUES(:avatar, :device_token, :time_code, :wakeup, :sleep, :user_id, :locale, :intention_code, :intention_text)";
            } else {
                $insert_query = "INSERT INTO `user_settings`(`avatar`, `device_token`, `time_code`, `wakeup`, `sleep`, `user_id`, `locale`, `intention_code`, `intention_text`) VALUES(:avatar, :device_token, :time_code, :wakeup, :sleep, :user_id, :locale, :intention_code, :intention_text)";
            }

            $insert_stmt = $conn->prepare($insert_query);

            // DATA BINDING
            $insert_stmt->bindValue(':user_id', $userSettings->getUserId(), PDO::PARAM_INT);
            $insert_stmt->bindValue(':avatar', $userSettings->getAvatar(), PDO::PARAM_INT);
            $insert_stmt->bindValue(':time_code', $userSettings->getTimeCode(), PDO::PARAM_INT);
            $insert_stmt->bindValue(':wakeup', $userSettings->getWakeup(), PDO::PARAM_STR);
            $insert_stmt->bindValue(':sleep', $userSettings->getSleep(), PDO::PARAM_STR);
            $insert_stmt->bindValue(':device_token', $userSettings->getDeviceToken(), PDO::PARAM_STR);
            $insert_stmt->bindValue(':locale', $userSettings->getLocale(), PDO::PARAM_STR);
            if ($intentionTextIsNull) {
                $insert_stmt->bindValue(':intention_text', null, PDO::PARAM_NULL);
                $insert_stmt->bindValue(':intention_code', $userSettings->getIntentionCode(), PDO::PARAM_STR);
            } else {
                $insert_stmt->bindValue(':intention_text', $userSettings->getIntentionText(), PDO::PARAM_STR);
                $insert_stmt->bindValue(':intention_code', null, PDO::PARAM_NULL);
            }

            Utils::log($insert_stmt->queryString);
            $insert_stmt->execute();
            $userSettings->save($conn->lastInsertId());
            return $userSettings;
        } else {
            $conn = Database::dbConnection();
            try {
                $query = (Database::isSqlsrv() ? "UPDATE user_settings SET " : "UPDATE `user_settings` SET ");
                $time_code = ($userSettings->getTimeCode() == $userSettingsOld->getTimeCode()) ? null : $userSettings->getTimeCode();
                $avatar = ($userSettings->getAvatar() == $userSettingsOld->getAvatar()) ? null : $userSettings->getAvatar();
                $device_token = (strcmp($userSettings->getDeviceToken(), $userSettingsOld->getDeviceToken()) === 0) ? null : $userSettings->getDeviceToken();
                $locale = (strcmp($userSettings->getLocale(), $userSettingsOld->getLocale()) === 0) ? null : $userSettings->getLocale();
                $wakeup = (strcmp($userSettings->getWakeup(), $userSettingsOld->getWakeup()) === 0) ? null : $userSettings->getWakeup();
                $sleep = (strcmp($userSettings->getSleep(), $userSettingsOld->getSleep()) === 0) ? null : $userSettings->getSleep();
                $intentionCode = strcmp($userSettings->getIntentionCode(), $userSettingsOld->getIntentionCode()) !== 0 ? $userSettings->getIntentionCode() : null;
                $intentionText = strcmp($userSettings->getIntentionText(), $userSettingsOld->getIntentionText()) !== 0 ? $userSettings->getIntentionText() : null;

                $queryParts = [];

                if ($avatar !== null) {
                    $queryParts['avatar'] = [
                        'sql' => (Database::isSqlsrv() ? "[avatar] = :avatar" : "`avatar` = :avatar"),
                        'type' => PDO::PARAM_INT,
                        'value' => $avatar,
                    ];
                }

                if ($time_code !== null) {
                    $queryParts['time_code'] = [
                        'sql' => (Database::isSqlsrv() ? "[time_code] = :time_code" : "`time_code` = :time_code"),
                        'type' => PDO::PARAM_INT,
                        'value' => $time_code,
                    ];
                }

                if ($device_token !== null) {
                    $queryParts['device_token'] = [
                        'sql' => (Database::isSqlsrv() ? "[device_token] = :device_token" : "`device_token` = :device_token"),
                        'type' => PDO::PARAM_STR,
                        'value' => $device_token,
                    ];
                }
                if ($locale !== null) {
                    $queryParts['locale'] = [
                        'sql' => (Database::isSqlsrv() ? "[locale] = :locale" : "`locale` = :locale"),
                        'type' => PDO::PARAM_STR,
                        'value' => $locale,
                    ];
                }

                if ($wakeup !== null) {
                    $queryParts['wakeup'] = [
                        'sql' => (Database::isSqlsrv() ? "[wakeup] = :wakeup" : "`wakeup` = :wakeup"),
                        'type' => PDO::PARAM_STR,
                        'value' => $wakeup,
                    ];
                }
                if ($sleep !== null) {
                    $queryParts['sleep'] = [
                        'sql' => (Database::isSqlsrv() ? "[sleep] = :sleep" : "`sleep` = :sleep"),
                        'type' => PDO::PARAM_STR,
                        'value' => $sleep,
                    ];
                }

                if (
                    $intentionText !== null
                    || $intentionCode !== null
                ) {
                    // IntentionText have the priority, if filled in the code is set to null
                    if ($intentionText !== null && !empty($intentionText)) {
                        $intentionCode = null;
                        $userSettings->setIntentionCode(null);
                    }

                    if ($intentionCode !== null && empty($intentionText)) {
                        $intentionText = null;
                        $userSettings->setIntentionText(null);
                    }

                    $queryParts['intention_code'] = [
                        'sql' => (Database::isSqlsrv() ? "[intention_code] = :intention_code" : "`intention_code` = :intention_code"),
                        'type' => PDO::PARAM_STR,
                        'value' => $intentionCode,
                    ];

                    $queryParts['intention_text'] = [
                        'sql' => (Database::isSqlsrv() ? "[intention_text] = :intention_text" : "`intention_text` = :intention_text"),
                        'type' => PDO::PARAM_STR,
                        'value' => $intentionText,
                    ];
                }

                // Nothing to save
                if (empty($queryParts)) {
                    return $userSettings;
                }

                // Add SQL parts to the query
                $sqlParts = array_reduce($queryParts, static function ($carry, $current) {
                    $carry[] = $current['sql'];
                    return $carry;
                }, []);
                $query .= implode(', ', $sqlParts);

                $query .= ' ' . (Database::isSqlsrv() ? "WHERE [setting_id] = :setting_id" : "WHERE `setting_id` = :setting_id");
                Utils::log($query);
                $stmt = $conn->prepare($query);

                // Bind values to the query
                foreach ($queryParts as $key => $part) {
                    $stmt->bindValue(":$key", $part['value'], $part['type']);
                }

                $stmt->bindValue(':setting_id', $userSettings->getSettingId(), PDO::PARAM_INT);
                $stmt->execute();
            } catch (PDOException $e) {
                Utils::log($e->getMessage());
                return null;
            }
            $userSettings->save();
            return $userSettings;
        }
    }

    public static function getByUser(User $user): ?UserSettings
    {
        return self::getByUserId($user->getUserId());
    }

    public static function getByUserId(int $user): ?UserSettings
    {
        $conn = Database::dbConnection();
        try {
            $fetch_setting_by_user = (Database::isSqlsrv() ? "SELECT * FROM user_settings WHERE [user_id]=:id" : "SELECT * FROM `user_settings` WHERE `user_id`=:id");
            $query_stmt = $conn->prepare($fetch_setting_by_user);
            $query_stmt->bindValue(':id', $user, PDO::PARAM_INT);
            $query_stmt->execute();

            // IF THE USER IS FOUNDED BY EMAIL
            if ($query_stmt->rowCount()) {
                $row = $query_stmt->fetch(PDO::FETCH_ASSOC);
                return UserSettings::parse($row);
                // IF THE USER IS NOT FOUNDED BY EMAIL THEN SHOW THE FOLLOWING ERROR
            }
        } catch (PDOException $e) {
            Utils::log($e->getMessage());
//            $returnData = msg(0,500,$e->getMessage());
            return null;
        }
        return null;
    }

    /**
     * Returns a list of survey objects
     * @return UserSettings[]
     */
    public static function getPage(int $page): ?array
    {
        $conn = Database::dbConnection();
        try {
            if ($page < 1) $page = 1;
            $offset = ($page - 1) * 50;
            $end = ($page * 50) + 1;
            //TODO this cause issue if 50 user was deleted but work on all sql server
            $fetch_user_by_email = (Database::isSqlsrv() ? "SELECT * FROM [user_settings] WHERE [setting_id] > :begin AND [setting_id] < :end" : "SELECT * FROM `user_settings` WHERE `setting_id` > :begin AND `setting_id` < :end");
            $query_stmt = $conn->prepare($fetch_user_by_email);
            $query_stmt->bindValue(':begin', $offset, PDO::PARAM_INT);
            $query_stmt->bindValue(':end', $end, PDO::PARAM_INT);
            Utils::log($query_stmt->queryString);
            $query_stmt->execute();

            if ($query_stmt->rowCount()) {
                $result = [];
                while ($row = $query_stmt->fetch(PDO::FETCH_ASSOC)) {
                    $answer = UserSettings::parse($row);
                    if (!is_null($answer)) {
                        array_push($result, $answer);
                    }
                }
                return $result;
            }
            return [];
        } catch (PDOException $e) {
            Utils::log($e->getMessage());
//            $returnData = msg(0,500,$e->getMessage());
        }
        return null;
    }

}