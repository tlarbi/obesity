<?php


namespace App\Model;


class AnswerForm{
    /**
     * @var int
     */
    private $surveyId;
    /**
     * @var array
     */
    private $forms = [];

    private function __construct(){}
    public function getSurveyId(){
        return $this->surveyId;
    }
    public function setSurveyId($surveyId): self{
        $this->surveyId = $surveyId;
        return $this;
    }
    private function addForm(?AnswerFormFields $form): self{
        if(!is_null($form)){
            array_push($this->forms, $form);
        }
        return $this;
    }
    public function getForm(): array{
        return $this->forms;
    }

    public static function parse($var): ?AnswerForm{
        if(is_null($var))return null;
        $data = null;
        if(is_string($var)){
            $data = json_decode($var, true);
        }elseif (is_array($var)){
            $data = $var;
        }else{
            return null;
        }
        if(isset($data['survey_id'])/* && is_int($data->surveyId)*/
            && (isset($data['code']) || isset($data['answers']))){
            $result = new AnswerForm();
            $result->setSurveyId($data['survey_id']);
            if(isset($data['answers'])){
                foreach ($data['answers'] as $current){
                    $result->addForm(AnswerFormFields::Parse($current));
                }
            }else{
                $result->addForm(AnswerFormFields::Parse($data));
            }
            if(sizeof($result->getForm()) === 0){
                return null;
            }
            return $result;
        }
        return null;
    }

}