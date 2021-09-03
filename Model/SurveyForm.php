<?php


namespace App\Model;
use App\Utils\Utils;
use DateTime;

class SurveyForm{
    /**
     * @var string
     */
    private $code;

    /**
     * @var DateTime|null
     */
    private $begin;
    /**
     * @var DateTime|null
     */
    private $start;
    /**
     * @var DateTime|null
     */
    private $end;
    /**
     * @var SurveyState
     */
    private $state;

    private function __construct(){}

    public function getCode(): string{
        return $this->code;
    }
    public function setCode(string $code): self{
        $this->code = $code;
        return $this;
    }
    public function getBegin() : ?DateTime{
        return $this->begin;
    }
    public function setBegin(DateTime $begin): self{
        $this->begin = $begin;
        return $this;
    }
    public function getStart() : ?DateTime{
        return $this->start;
    }
    public function setStart(DateTime $start): self{
        $this->start = $start;
        return $this;
    }
    public function getEnd() : ?DateTime{
        return $this->end;
    }
    public function setEnd(DateTime $end): self{
        $this->end = $end;
        return $this;
    }
    public function getState() : SurveyState{
        return $this->state;
    }
    public function setState(SurveyState $state) : self{
        $this->state = $state;
        return $this;
    }
    public static function parse($var): ?SurveyForm{
        if(is_null($var))$var = "";
        $data = null;
        if(is_string($var)){
            $data = json_decode($var, true);
        }elseif (is_array($var)){
            $data = $var;
        }else{
            Utils::log("Invalid data for SurveyForm parsing!");
            return null;
        }
        $result = new SurveyForm();
        if(isset($data['code'])){
            $code = SurveyType::getFromCode($data['code']);
            if(is_null($code)){
                return null;
            }
            if(strcmp($code->getCode(), SurveyType::$RANDOM->getCode()) == 0){
                if(!isset($data['from']) || !isset($data['to'])){
                    Utils::log("Missing data to parse SurveyForm");
                    return null;
                }
            }
            $result->setCode($code->getCode());
        }else{
            $result->setCode(SurveyType::$AFTER_EATING->getCode());
        }
        if(isset($data['from'])){
            $var = DateTime::createFromFormat('Y-m-d H:i:s', $data['from']);
            if(!($var instanceof DateTime)) {
                Utils::log("Invalid datetime : " . $data['from']);
                return null;
            }
            $result->setBegin($var);
        }
        if(isset($data['start'])){
            $var = DateTime::createFromFormat('Y-m-d H:i:s', $data['start']);
            if(!($var instanceof DateTime)) {
                Utils::log("Invalid datetime : " . $data['start']);
                return null;
            }
            $result->setStart($var);
        }else{
            if(strcmp($result->getCode(), SurveyType::$AFTER_EATING->getCode()) == 0){
                $result->setStart(new DateTime());
            }
        }
        if(isset($data['to'])){
            $var = DateTime::createFromFormat('Y-m-d H:i:s', $data['to']);
            if(!($var instanceof DateTime)) {
                Utils::log("Invalid datetime : " . $data['to']);
                return null;
            }
            $result->setEnd($var);
        }
        $state = isset($data['state']) ? SurveyState::getFromCode($data['state']) : null;
        if(is_null($state)){
            if(is_null($result->getStart())){
                $state = SurveyState::$PENDING;
            }else{
                $state = SurveyState::$STARTED;
            }
        }
        $result->setState($state);
        //TODO check from <= start <= to
        return $result;
    }

}