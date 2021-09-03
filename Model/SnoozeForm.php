<?php


namespace App\Model;

class SnoozeForm{
    /**
     * @var SnoozeFormFields[]
     */
    private $snoozes = [];

    private function __construct(){}
    private function addForm(?SnoozeFormFields $form): self{
        if(!is_null($form)){
            array_push($this->snoozes, $form);
        }
        return $this;
    }

    /**
     * @return SnoozeFormFields[]
     */
    public function getForm(): array{
        return $this->snoozes;
    }
    public static function parse($var): ?SnoozeForm{
        if(is_null($var))return null;
        $data = null;
        if(is_string($var)){
            $data = json_decode($var, true);
        }elseif (is_array($var)){
            $data = $var;
        }else{
            return null;
        }
        $result = new SnoozeForm();
        if(isset($data['snoozes'])){
            foreach ($data['snoozes'] as $current){
                $result->addForm(SnoozeFormFields::parse($current));
            }
        }else{
            $result->addForm(SnoozeFormFields::parse($data));
        }
        if(sizeof($result->getForm()) === 0){
            return null;
        }
        return $result;
    }
}