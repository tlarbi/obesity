<?php

namespace App\Entity;

use App\Model\SurveyState;
use App\Model\SurveyType;
use App\Repository\AnswerRepository;
use App\Repository\UserRepository;
use DateTime;

/**
 * Survey
 */
class Survey{

    /**
     * @var int
     */
    private $survey_id;
    /**
     * @var string
     */
    private $code;
    /**
     * @var DateTime|null
     */
    private $started;
    /**
     * @var DateTime|null
     */
    private $ended;
    /**
     * @var DateTime|null
     */
    private $from;
    /**
     * @var DateTime|null
     */
    private $to;
    /**
     * @var SurveyState
     */
    private $state;
//    /**
//     * @var int
//     */
//    private $tries;

    private $user_id;

    private $answers;
    private $user;
    public function __construct(){
//        $this->started = new DateTime();
    }

    public function getSurveyId(): ?string{
        return $this->survey_id;
    }

    public function getCode(): string{
        return $this->code;
    }

    public function setCode(string $code): self{
        $this->code = $code;

        return $this;
    }
    public function getStarted(): ?DateTime{
        return $this->started;
    }

    public function setStarted(?DateTime $started): self{
        $this->started = $started;
        return $this;
    }
    public function getEnded(): ?DateTime{
        return $this->ended;
    }

    public function setEnded(?DateTime $ended): self{
        $this->ended = $ended;
        return $this;
    }
    public function getBegin(): ?DateTime{
        return $this->from;
    }

    public function setBegin(?DateTime $begin): self{
        $this->from = $begin;

        return $this;
    }
    public function getEnd(): ?DateTime{
        return $this->to;
    }

    public function setEnd(?DateTime $end): self{
        $this->to = $end;

        return $this;
    }

    public function getAnswers(): ?array{
        if(is_null($this->answers)){
            $this->answers = AnswerRepository::findBySurveyAndCodeAndCreatedAtAfterAndCreatedAtBefore($this->getSurveyId(), null, null, null);
        }
        return $this->answers;
    }
    public function getUser(): ?User{
        if(is_null($this->user)){
            $this->user = UserRepository::getOne($this->getUserId());
        }
        return $this->user;
    }
    public function setUser(User $user): self{
        $this->user_id = $user->getUserId();
        $this->user = $user;
        return $this;
    }
    public function getUserId(): int{
        return $this->user_id;
    }

    public function setUserId(int $user_id): self{
        $this->user_id = $user_id;
        return $this;
    }

    public function getState(): int{
        return $this->state->getCode();
    }
    public function getStateName(): string{
        return $this->state->getName();
    }
    public function setState(SurveyState $state): self{
        $this->state = $state;
        return $this;
    }
//    public function getTries(): int{
//        return $this->tries;
//    }
//    public function setTries(int $tries): self{
//        $this->tries = $tries;
//        return $this;
//    }
    public static function parse($var): ?Survey{
        if(is_null($var))return null;
        $data = null;
        if(is_string($var)){
            $data = json_decode($var, true);
        }elseif (is_array($var)){
            $data = $var;
        }else{
            return null;
        }
        if(isset($data['survey_id'])
            && isset($data['user_id'])
            && isset($data['state'])
//            && isset($data['tries'])
            /*&& isset($data['started'])*/){
            $result = new Survey();
            $result->survey_id = $data['survey_id'];
            $result->user_id = $data['user_id'];
//            $result->setStarted(DateTime::createFromFormat("Y-m-d H:i:s", $data['started']));
            if(isset($data['from'])){
                $result->setBegin(DateTime::createFromFormat("Y-m-d H:i:s", $data['from']));
            }
            if(isset($data['to'])){
                $result->setEnd(DateTime::createFromFormat("Y-m-d H:i:s", $data['to']));
            }
            if(isset($data['started'])){
                $result->setStarted(DateTime::createFromFormat("Y-m-d H:i:s", $data['started']));
            }
            if(isset($data['ended'])){
                $result->setEnded(DateTime::createFromFormat("Y-m-d H:i:s", $data['ended']));
            }
//            $result->setTries($data['tries']);
            $result->setCode($data['code']);
            $result->setState(SurveyState::getFromCode($data['state']));
            $result->save();
            return $result;
        }
        return null;
    }

    public function serialize(){
        return [
            'survey_id' => $this->getSurveyId(),
//            'tries' => $this->getTries(),
            'code' => $this->getCode(),
            'started' => is_null($this->getStarted()) ? null : $this->getStarted()->format("Y-m-d H:i:s"),
            'ended' => is_null($this->getEnded()) ? null : $this->getEnded()->format("Y-m-d H:i:s"),
            'from' => is_null($this->getBegin()) ? null : $this->getBegin()->format("Y-m-d H:i:s"),
            'to' => is_null($this->getEnd()) ? null : $this->getEnd()->format("Y-m-d H:i:s"),
            'state' => [
                'id' => $this->getState(),
                'name' => $this->getStateName(),
                ],
        ];
    }

    private $lastSave = null;
    public function __clone(){
        if(!is_null($this->started)){
            $this->started = clone $this->started;
        }
        if(!is_null($this->ended)){
            $this->ended = clone $this->ended;
        }
        if(!is_null($this->from)){
            $this->from = clone $this->from;
        }
        if(!is_null($this->to)){
            $this->to = clone $this->to;
        }
    }
    public function save($id = null){
        if(!is_null($id) && is_null($this->lastSave)){
            $this->survey_id=$id;
        }
        $this->lastSave = clone $this;
    }
    public function getLastSave(): ?Survey{
        return $this->lastSave;
    }

}
