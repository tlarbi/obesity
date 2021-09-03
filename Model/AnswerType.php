<?php


namespace App\Model;

use App\Utils\ConfigUtils;
use App\Utils\Utils;

class AnswerType{
    private const LIST = 0;
    private const QUANTITY = 1;
    private const QUANTITY_LIST = 2;

    private static $enums;

    private $code;
    private $type;
    private function __construct(string $code, int $type = self::QUANTITY){
        $this->code = $code;
        $this->type = $type;
        array_push(self::$enums, $this);
    }
    static function init(){
        self::$enums = [];
        new AnswerType("location", self::LIST);
        new AnswerType("activity", self::LIST);
        new AnswerType("specific_crave_names", self::LIST);
        new AnswerType("drink_names", self::LIST);
        new AnswerType("company_qualitative", self::LIST);
        new AnswerType("sleep_quality");
        new AnswerType("sick");
        new AnswerType("company_quantitative");
        new AnswerType("strength_emotion_sad");
        new AnswerType("strength_emotion_anxious");
        new AnswerType("strength_emotion_angry");
        new AnswerType("strength_emotion_tired");
        new AnswerType("strength_emotion_nervous");
        new AnswerType("strength_emotion_calm");
        new AnswerType("strength_emotion_happy");
        new AnswerType("strength_emotion_bored");
        new AnswerType("craving");
        new AnswerType("eat_portion_of_cereals");
        new AnswerType("eat_portion_of_fish_meat");
        new AnswerType("eat_portion_of_fruit");
        new AnswerType("eat_portion_of_nuts");
        new AnswerType("eat_portion_of_pasta");
        new AnswerType("eat_portion_of_potatoes");
        new AnswerType("eat_portion_of_rice");
        new AnswerType("eat_portion_of_salad");
        new AnswerType("eat_portion_of_sandwich");
        new AnswerType("eat_portion_of_soup");
        new AnswerType("eat_portion_of_dairy");
        new AnswerType("eat_portion_of_candy");
        new AnswerType("eat_portion_of_bar");
        new AnswerType("eat_portion_of_chips");
        new AnswerType("eat_portion_of_cookies");
        new AnswerType("eat_portion_of_fries");
        new AnswerType("eat_portion_of_burger");
        new AnswerType("eat_portion_of_ice_cream");
        new AnswerType("eat_portion_of_pastry");
        new AnswerType("eat_portion_of_pizza");
    }

	public function hasList() : bool {
		return $this->type != self::QUANTITY;
	}
	public function hasQuantity() : bool {
		return $this->type != self::LIST;
	}
	public function getCode() : string{
		return $this->code;
	}
	public static function getFromCode(string $code) : ?AnswerType{
        if($code == null)return null;
        if(empty(self::$enums)){
            AnswerType::init();
            if(empty(self::$enums)){
                Utils::log("Empty answer type enum");
                return null;
            }
        }
        foreach(self::$enums as $type) {
            if(strcmp($type->code, $code) === 0)return $type;
        }
		return null;
	}
}
AnswerType::init();