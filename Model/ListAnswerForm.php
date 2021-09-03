<?php


namespace App\Model;

use DateTime;

class ListAnswerForm{
    private $surveyId;

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

    public function getSurveyId(){
        return $this->surveyId;
    }
    private function setSurveyId($surveyId): self{
        $this->surveyId = $surveyId;
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
    public static function parse($var): ?ListAnswerForm{
        if(is_null($var))return null;
        $data = null;
        if(is_string($var)){
            $data = json_decode($var, true);
        }elseif (is_array($var)){
            $data = $var;
        }else{
            return null;
        }
        $result = new ListAnswerForm();
        if(isset($data['survey_id']) /*&& is_int($data->surveyId)*/){
            $result->setSurveyId($data['survey_id']);
        }
        if(isset($data['code'])){
            $code = AnswerType::getFromCode($data['code']);
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