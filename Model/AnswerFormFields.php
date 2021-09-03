<?php


namespace App\Model;


class AnswerFormFields{

    /**
     * @var string
     */
    private $code;

    /**
     * @var int|null
     */
    private $quantity;
    /**
     * @var string|null
     */
    private $list;

    private function __construct(){}

    public function getCode(): ?string{
        return $this->code;
    }
    public function setCode(string $code): self{
        $this->code = $code;
        return $this;
    }
    public function getQuantity() : ?int{
        return $this->quantity;
    }
    public function setQuantity(int $quantity): self{
        $this->quantity = $quantity;
        return $this;
    }
    public function getList(): ?string{
        return $this->list;
    }
    public function setList(string $list): self{
        $this->list = $list;
        return $this;
    }
    static function parse(array $data) : ?AnswerFormFields{
        $form = new AnswerFormFields();
        $code = AnswerType::getFromCode($data['code']);
        if(is_null($code)){
            return null;
        }
        $form->setCode($code->getCode());
        if($code->hasList()){
            if(isset($data['list'])){
                $form->setList($data['list']);
            }else{
                return null;
            }
        }
        if($code->hasQuantity()){
            if(isset($data['quantity']) /*&& is_int($data->quantity)*/){
                $quantity = $data['quantity'];
                $quantity < 0 ? 0 : ( $quantity > 100  ? 100 : $quantity);
                $form->setQuantity($quantity);
            }else{
                return null;
            }
        }
        return $form;
    }
}