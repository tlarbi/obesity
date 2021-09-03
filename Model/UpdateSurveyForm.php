<?php


namespace App\Model;
use App\Utils\Utils;
use DateTime;

class UpdateSurveyForm{
    /**
     * @var int
     */
    private $id;

    /**
     * @var DateTime|null
     */
    private $start;
    /**
     * @var DateTime|null
     */
    private $end;
    /**
     * @var SurveyState|null
     */
    private $state;

    private function __construct(){}

    public function getId(): int{
        return $this->id;
    }
    public function setId(int $id): self{
        $this->id = $id;
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
    public function getState(): SurveyState{
        return $this->state;
    }
    public function setState(SurveyState $state): self{
        $this->state = $state;
        return $this;
    }
    public static function parse($var): ?UpdateSurveyForm{
        if(is_null($var))return null;
        $data = null;
        if(is_string($var)){
            $data = json_decode($var, true);
        }elseif (is_array($var)){
            $data = $var;
        }else{
            Utils::log("Invalid data for SurveyForm parsing!");
            return null;
        }
        if(isset($data['survey_id'])
        && (isset($data['start'])
            ||isset($data['end'])
            ||isset($data['state']))){
            $result = new UpdateSurveyForm();
            $result->setId($data['survey_id']);
            if(isset($data['start'])){
                $var = DateTime::createFromFormat('Y-m-d H:i:s', $data['start']);
                if(!($var instanceof DateTime)) {
                    Utils::log("Invalid datetime : " . $data['start']);
                    return null;
                }
                $result->setStart($var);
            }
            if(isset($data['end'])){
                $var = DateTime::createFromFormat('Y-m-d H:i:s', $data['end']);
                if(!($var instanceof DateTime)) {
                    Utils::log("Invalid datetime : " . $data['end']);
                    return null;
                }
                $result->setEnd($var);
            }
            if(isset($data['state'])){
                $state = SurveyState::get($data['state']);
                if(is_null($state)){
                    if(is_null($result->getStart())){
                        $state = SurveyState::$PENDING;
                    }else{
                        $state = SurveyState::$STARTED;
                    }
                }
                $result->setState($state);
            }
            return $result;
        }
        return null;
    }
}