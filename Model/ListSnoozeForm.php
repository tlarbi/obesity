<?php


namespace App\Model;

use DateTime;

class ListSnoozeForm{

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
    /**
     * @var bool|null
     */
    private $repeat;

    private function __construct(){}

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
    public function getRepeat() : ?bool{
        return $this->repeat;
    }
    private function setRepeat(bool $repeat): self{
        $this->repeat = $repeat;
        return $this;
    }
    public static function parse($var): ?ListSnoozeForm{
        if(is_null($var))return null;
        $data = null;
        $result = new ListSnoozeForm();
        if(is_string($var)){
            $data = json_decode($var, true);
        }elseif (is_array($var)){
            $data = $var;
        }else{
            return $result;
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
        if(isset($data['repeat'])){
            $result->setRepeat($data['repeat'] === true);
        }
        return $result;
    }
}