<?php


namespace App\Utils;


class Time{

    public static function parse($var) : ?int{
        if(is_null($var))return null;
        if(is_string($var)){
            $tmp = explode(":", $var);
            if($tmp == false) return null;
            if(sizeof($tmp)!=2) return null;
            $val = ((int)$tmp[0])*4+((int)$tmp [1])/15;
            if(self::isValidInt((int)$var))
            return $val;
        }else if(is_int($var)){
            if(self::isValidInt((int)$var))
            return (int)$var;
        }else if(is_array($var)){
            if (array() === $var) return null;
            if(count($var) < 2)return null;
            if(array_keys($var) !== range(0, count($var) - 1))return null;
            if(!isset($var['hour']) || !is_int($var['hour']))return null;
            if(!isset($var['minute']) || !is_int($var['minute']))return null;
            $val = ((int)$var['hour'])*4+((int)$var['minute'])/15;
            if(self::isValidInt((int)$var))
                return $val;
        }
        return null;
    }
    private static function isValidInt(int $var): bool{
        return !($var < 0 || $var > 96);
    }
}