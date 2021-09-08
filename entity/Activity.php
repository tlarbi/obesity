<?php


namespace App\Entity;

use App\Repository\UserRepository;
use DateTime;


class Activity {

    /**
     * @var int
     */
    private $activity_id;

    /**
     * @var int
     */
    private $user_id;
    
    /**
     * @var int
     */
    private $timestamp_creation;

    /**
     * @var string
     */
    private $type;
    
    /**
     * @var int
     */
    private $survey_id;
    
    /**
     * @var int
     */
    private $tip_id;
    
    /**
     * @var int
     */
    private $training_id;    
 
    /**
     * Constructor Entity Activity
     */
    public function __construct(){
       // $this->timestamp_creation = new DateTime();
    }

    public function getActivityId(){
        return $this->activity_id;
    }

    public function getUserId(){
        return $this->user_id;
    }

    public function getTimestampCreation(): ?DateTime {
        return $this->timestamp_creation->date_format('Y-d-m');
    }

    public function getType(): ?string{
        return $this->type;
    }   

    public function getSurveyId(): ?int{
        return $this->survey_id;
    }
    public function getTidId(): ?int{
        return $this->tip_id;
    }
    public function getTrainingId(): ?int{
        return $this->training_id;
    }    


    public function setActivityId(int $activity_id): self{
        $this->activity_id = $activity_id;
        return $this;
    }

    public function setUserId(int $user_id): self{
        $this->user_id = $user_id;
        return $this;
    }

    public function setTimestampCreation(DateTime $timestamp_creation): self{
        $this->timestamp_creation = $timestamp_creation;
        return $this;
    }

    public function setType(string $type): self{
        $this->type = $type;
        return $this;
    }

    public function setSurveyId(int $survey_id): self{
        $this->survey_id = $survey_id;
        return $this;
    }

    public function setTidId(int $tip_id): self{
        $this->tip_id = $tip_id;
        return $this;
    }

    public function setTrainingId(int $training_id): self{
        $this->training_id = $training_id;
        return $this;
    }

    public function getUser(): ?User{
        if(is_null($this->user)){
            $this->user = UserRepository::getOne($this->getUserId());
        }
        return $this->user;
    }


}