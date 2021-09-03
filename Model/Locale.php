<?php


namespace App\Model;

use App\Utils\Utils;

class Locale{

    private static $enums;

    public static $fr_FR;
    public static $en_GB;
    public static $nl_NL;

    private $code;
    private function __construct(string $code){
        $this->code = $code;
        array_push(self::$enums, $this);
    }
    static function init(){
        self::$enums = [];
        self::$fr_FR = new Locale("fr_FR");
        self::$en_GB = new Locale("en_GB");
        self::$nl_NL = new Locale("nl_NL");
    }
	public function getCode() : string{
		return $this->code;
	}
	public static function getFromCode(?string $code) : ?Locale{
//        if($code == null)return null;
        if(empty(self::$enums)){
            Locale::init();
            if(empty(self::$enums)){
                Utils::log("Empty Locale type enum");
                return self::$en_GB;
            }
        }
        foreach(self::$enums as $type) {
            if(strcmp($type->code, $code) === 0)return $type;
        }
        Utils::log("Can't find survey type ".$code);
		return self::$en_GB;
	}
}
Locale::init();