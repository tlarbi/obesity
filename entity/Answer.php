<?php

namespace App\Entity;


use App\Repository\SurveyRepository;
use DateTime;
/**
 * Answer
 */
class Answer{
    /**
     * @var int
     */
    private $answer_id;

    /**
     * @var string
     */
    private $code;

    /**
     * @var DateTime|null
     */
    private $created_at;

    /**
     * @var string|null
     */
    private $list;

    /**
     * @var int|null
     */
    private $quantity;

    /**
     * @ORM\ManyToOne(targetEntity="Survey")
     * @ORM\JoinColumn(name="survey_id", referencedColumnName="survey_id")
     */
    private $survey_id;
    private $survey;
    public function __construct(){
        $this->created_at = new DateTime();
    }

    public function getAnswerId(): ?string{
        return $this->answer_id;
    }

    public function getCode(): ?string{
        return $this->code;
    }

    public function setCode(string $code): self{
        $this->code = $code;

        return $this;
    }

    public function getCreatedAt(): ?DateTime{
        return $this->created_at;
    }

    public function setCreatedAt(?DateTime $created_at): self{
        $this->created_at = $created_at;

        return $this;
    }

    public function getList(): ?string{
        return $this->list;
    }

    public function setList(?string $list): self{
        $this->list = $list;

        return $this;
    }

    public function getQuantity(): ?int{
        return $this->quantity;
    }

    public function setQuantity(?int $quantity): self{
        $this->quantity = $quantity;

        return $this;
    }

    public function getSurveyId(): ?int{
        return $this->survey_id;
    }

    public function setSurveyId(int $survey_id): self{
        $this->survey_id = $survey_id;

        return $this;
    }
    public function getSurvey(): ?Survey{
        if(is_null($this->survey)){
            $this->survey = SurveyRepository::getOne($this->getSurveyId());
        }
        return $this->survey;
    }

    public function setSurvey(Survey $survey): self{
        $this->setSurveyId($survey->getSurveyId());
        $this->survey = $survey;
        return $this;
    }

    public static function parse($var): ?Answer{
        if(is_null($var))return null;
        $data = null;
        if(is_string($var)){
            $data = json_decode($var, true);
        }elseif (is_array($var)){
            $data = $var;
        }else{
            return null;
        }

        if(isset($data['answer_id']) /*&& is_int($data['answer_id'])*/
            && isset($data['survey_id']) /*&& is_int($data['survey_id'])*/
            && isset($data['code'])){
            $result = new Answer();
            $result->answer_id = $data['answer_id'];
            $result->survey_id = $data['survey_id'];
            $result->setCode($data['code']);
            if(isset($data['quantity']) /*&& is_int($data['quantity'])*/){
                $result->setQuantity($data['quantity']);
            }
            if(isset($data['list'])){
                $result->setList($data['list']);
            }
            if(isset($data['created_at'])){
                $result->setCreatedAt(DateTime::createFromFormat("Y-m-d H:i:s", $data['created_at']));
            }
            $result->save();
            return $result;
        }
        return null;
    }

    public function serialize(){
        return [
            'answer_id' => $this->getAnswerId(),
            'code' => $this->getCode(),
            'created_at' => $this->getCreatedAt()->format("Y-m-d H:i:s"),
            'list' => $this->getList(),
            'quantity' => $this->getQuantity(),
            'survey_id' => $this->getSurveyId(),
        ];
    }
    private $lastSave = null;
    public function __clone(){
        $this->created_at = clone $this->created_at;
    }
    public function save($id = null){
        if(!is_null($id) && is_null($this->lastSave)){
            $this->answer_id=$id;
        }
        $this->lastSave = clone $this;
    }
    public function getLastSave(): ?Answer{
        return $this->lastSave;
    }
}
