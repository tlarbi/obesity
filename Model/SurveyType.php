<?php


namespace App\Model;

use App\Utils\Utils;

class SurveyType{

    private static $enums;

    public static $AFTER_EATING;
    public static $RANDOM;

    private $code;
    private function __construct(string $code){
        $this->code = $code;
        array_push(self::$enums, $this);
    }
    static function init(){
        self::$enums = [];
        self::$AFTER_EATING = new SurveyType("After-Eating");
        self::$RANDOM = new SurveyType("Random");
    }
	public function getCode() : string{
		return $this->code;
	}
	public static function getFromCode(string $code) : ?SurveyType{
        if(is_null($code == null))return null;
        if(empty(self::$enums)){
            SurveyType::init();
            if(empty(self::$enums)){
                Utils::log("Empty survey type enum");
                return null;
            }
        }
        foreach(self::$enums as $type) {
            if(strcmp($type->code, $code) === 0)return $type;
        }
        Utils::log("Can't find survey type ".$code);
		return null;
	}
}
SurveyType::init();