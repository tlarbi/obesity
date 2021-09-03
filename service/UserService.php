<?php


namespace App\Service;

use App\Entity\User;
use App\Entity\UserSettings;
use App\Middleware\Auth;
use App\Model\RegisterForm;
use App\Model\UserSettingsForm;
use App\Repository\UserSettingsRepository;
use App\Repository\UserRepository;
use Exception;

class UserService
{
    private static $user = null;

    public static function registerUser(RegisterForm $form): User
    {
        return self::registerNewUserAccount($form);
    }

    private static function registerNewUserAccount(RegisterForm $form): User
    {
        $id = UserRepository::canRegister($form->getCode());
        if ($id === -1) {
            throw new Exception("Can't use this id!");
        }
        if (UserRepository::findByUsername($form->getUsername()) !== null) {
            throw new Exception("This username already exist !");
        }
        $user = new User($id);
        $user->setEnabled(true);
        $user->setUsername($form->getUsername());
        $user->setPassword(password_hash($form->getPassword(), PASSWORD_DEFAULT));
        $settings = new UserSettings();
        $user->setUserSettings($settings);
        $user = UserRepository::save($user);
        $settings->setUser($user);
        UserSettingsRepository::save($settings);
        return $user;
    }

    public static function getCurrentUser(): ?User
    {
        if (!is_null(self::$user)) return self::$user;
        $auth = new Auth(getallheaders());
        self::$user = $auth->getCurrentUser();
        return self::$user;
    }

    public static function saveRegisterUser(User $user)
    {
        UserRepository::save($user);
    }

    public static function updateSettings(UserSettingsForm $form, User $user = null): ?UserSettings
    {
        if ($user === null) {
            $user = self::getCurrentUser();
            if ($user === null) {
                return null;
            }
        }

        $settings = $user->getUserSettings();
        if (!is_null($form->getAvatar())) {
            $settings->setAvatar($form->getAvatar());
        }
        if (!is_null($form->getTimeCode())) {
            $settings->setTimeCode($form->getTimeCode());
        }
        if (!is_null($form->getDeviceToken())) {
            $settings->setDeviceToken($form->getDeviceToken());
        }
        if (!is_null($form->getLocale())) {
            $settings->setLocale($form->getLocale());
        }
        if (!is_null($form->getWakeup())) {
            $settings->setWakeup($form->getWakeup());
        }
        if (!is_null($form->getSleep())) {
            $settings->setSleep($form->getSleep());
        }
        if (!is_null($form->getIntentionText())) {
            $settings->setIntentionText($form->getIntentionText());
        }
        if (!is_null($form->getIntentionCode())) {
            $settings->setIntentionCode($form->getIntentionCode());
        }
        return UserSettingsRepository::save($settings);
    }
}