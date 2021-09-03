<?php


namespace App\Model;

class RegisterForm{
    /**
     * @var string
     */
    private $code;

    /**
     * @var string
     */
    private $username;
    /**
     * @var string
     */
    private $password;

    private function __construct(){}

    public function getCode() : string{
        return $this->code;
    }
    public function setCode(string $code): self{
        $this->code = $code;
        return $this;
    }

    public function getUsername() : string{
        return $this->username;
    }
    public function setUsername(string $username): self{
        $this->username = $username;
        return $this;
    }
    public function getPassword() : string{
        return $this->password;
    }
    public function setPassword(string $password): self{
        $this->password = $password;
        return $this;
    }
    public static function parse($var){
        if(is_null($var))return null;
        $data = null;
        if(is_string($var)){
            $data = json_decode($var, true);
        }elseif (is_array($var)){
            $data = $var;
        }else{
            return null;
        }
        if(isset($data['username'])
            && isset($data['password'])
            && isset($data['code'])){
            $username = trim($data['username']);
            $password = trim($data['password']);
            $code = trim($data['code']);
            $result = new RegisterForm();
            $result->setCode($code);
            $result->setPassword($password);
            $result->setUsername($username);
            return $result;
        }
        return null;
    }
}