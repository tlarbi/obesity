<?php


namespace App\Model;

use App\Utils\Utils;

class SurveyState{

    private static $enums;

    public static $PENDING;
    public static $STARTED;
//    public static $COMPLETED;
    public static $CLOSED;

    private $code;
    private $name;
    private function __construct(string $name, int $code){
        $this->code = $code;
        $this->name = $name;
        array_push(self::$enums, $this);
    }
    static function init(){
        self::$enums = [];
        self::$PENDING = new SurveyState("Pending", 0);
        self::$STARTED = new SurveyState("Started", 1);
        self::$CLOSED = new SurveyState("Closed", 2);
//        self::$COMPLETED = new SurveyState("Completed", 3);
    }
	public function getCode() : int{
		return $this->code;
	}
	public static function getFromName(string $name) : ?SurveyState{
//        if($code == null)return null;
        if(empty(self::$enums)){
            SurveyType::init();
            if(empty(self::$enums)){
                Utils::log("Empty survey state enum");
                return null;
            }
        }
        foreach(self::$enums as $type) {
            if(strcmp($type->name, $name) === 0)return $type;
        }
        Utils::log("Can't find survey type ".$name);
		return null;
	}

    public static function getFromCode(int $code) : ?SurveyState{
        if(empty(self::$enums)){
            SurveyType::init();
            if(empty(self::$enums)){
                Utils::log("Empty survey state enum");
                return null;
            }
        }
        foreach(self::$enums as $type) {
            if($type->code == $code)return $type;
        }
        Utils::log("Can't find survey type ".$code);
        return null;
    }
    public static function get($code) : ?SurveyState{
        if(is_numeric($code)){
            return self::getFromCode($code);
        }else{
            return self::getFromName($code);
        }
    }

    public function getName() : string{
        return $this->name;
    }
}
SurveyState::init();