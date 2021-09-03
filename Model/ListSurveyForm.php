<?php


namespace App\Model;

use DateTime;

class ListSurveyForm{

    /**
     * @var int
     */
    private $state;

    /**
     * @var string
     */
    private $type;

    /**
     * @var DateTime|null
     */
    private $startDate;
    /**
     * @var DateTime|null
     */
    private $endDate;

    private function __construct(){}

    public function getState(): ?int{
        return $this->state;
    }
    private function setState(int $state): self{
        $this->state = $state;
        return $this;
    }

    public function getType(): ?string{
        return $this->type;
    }
    private function setType(string $type): self{
        $this->type = $type;
        return $this;
    }
    public function getStartDate() : ?DateTime{
        return $this->startDate;
    }
    private function setStartDate(?DateTime $startDate): self{
        $this->startDate = $startDate;
        return $this;
    }
    public function getEndDate() : ?DateTime{
        return $this->endDate;
    }
    private function setEndDate(DateTime $endDate): self{
        $this->endDate = $endDate;
        return $this;
    }
    public static function parse($var): ?ListSurveyForm{
        if(is_null($var))return null;
        $data = null;
        if(is_string($var)){
            $data = json_decode($var, true);
        }elseif (is_array($var)){
            $data = $var;
        }else{
            return null;
        }
        $result = new ListSurveyForm();
        if(isset($data['state'])){
            $code = SurveyState::get($data['state']);
            if(!is_null($code)){
                $result->setState($code->getCode());
            }
        }
        if(isset($data['code'])){
            $code = SurveyType::getFromCode($data['code']);
            if(!is_null($code)){
                $result->setType($code->getCode());
            }
        }
        if(isset($data['start_date'])){
            $date = DateTime::createFromFormat("Y-m-d H:i:s", $data['start_date']);
            if($date instanceof DateTime){
                $result->setStartDate($date);
            }
        }
        if(isset($data['end_date'])){
            $date = DateTime::createFromFormat("Y-m-d H:i:s", $data['end_date']);
            if($date instanceof DateTime){
                $result->setEndDate($date);
            }
        }
        return $result;
    }
}