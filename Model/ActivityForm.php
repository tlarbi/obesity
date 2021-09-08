<?php


namespace App\Model;

class ActivityForm{
    /**
     * @var ActivityFormFields[]
     */
    private $activity = [];

    private function __construct(){}

    private function addForm(?ActivityFormFields $form): self{
        if(!is_null($form)){
            array_push($this->activity, $form);
        }
        return $this;
    }

    /**
     * @return SnoozeFormFields[]
     */
    public function getForm(): array{
        return $this->activity;
    }

    public static function parse($var): ?ActivityForm{

        if(is_null($var))return null;
        $data = null;
        if(is_string($var)){
            $data = json_decode($var, true);
        }elseif (is_array($var)){
            $data = $var;
        }else{
            return null;
        }
        $result = new ActivityForm();
        $result->addForm(ActivityFormFields::parse($data));

        if(sizeof($result->getForm()) === 0){
            return null;
        }
        return $result;
    }
}