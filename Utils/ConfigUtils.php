<?php

namespace App\Utils;

class ConfigUtils
{
    private static $done = false;
    private static $should_log = false;
    private static $values = [];

    private function __construct()
    {
        if (!self::$done) {
            self::$values["APP_ENV"] = 'dev';
            self::$values["APP_SECRET"] = 'd8a364c1857987f052a20a6fb621a9eb';
            self::$values["AUTH_PATH"] = '[path]';
            self::$values["RANDOM_COOLDOWN"] = 20;
            self::$values["RANDOM_DURATION"] = 20;
            self::$values["SNOOZE_DURATION"] = 30;
            self::$values["JWT_DURATION"] = 3600;
            self::$values["JWT_SECRET"] = "deget3l@2020";
            self::$values["PROJECT_ID"] = "projects/eitcco-19288";
            self::$values["LOCAL_KEY"] = "vRDwNutKyENpWb9LkVUMe3gBfuqX1XYqTVNReQcLZn84lRvzpe5je9V2sU2aaQYjIs3KQFthtkquwKECFnHejxE6cnQoGLiWCGY1IQzGTQLezRlEc8Kin2VJYvzILPke6lmxF6O5ZLpbXyVEElzeHOKAHSuaKGHY";
            self::$values['MJ_APIKEY_PUBLIC'] = '1c6a04c5b8adc378f78c74670591918a';
            self::$values['MJ_APIKEY_PRIVATE'] = '7e58fc7f96418bf57059fd2468dac756';
            self::$values['MJ_API_CALL'] = true;
            self::$values['RESET_TOKEN_VALIDITY'] = '1800 seconds';
            self::$done = true;
        }
    }

    public static function getProperty(string $property): string
    {
        if (!self::$done) {
            new ConfigUtils();
        }
        return self::$values[$property];
    }

    public static function getInt(string $property): int
    {
        return intval(self::getProperty($property));
    }

    public static function getBool(string $property)
    {
        return (bool)(self::getProperty($property));
    }

    public static function shouldLog()
    {
        return self::$should_log;
    }
}
