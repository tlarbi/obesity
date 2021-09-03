<?php


namespace App\Model;
use App\Utils\ConfigUtils;
use App\Utils\Utils;
use DateTime;

class ResetPasswordForm{
    /**
     * @var string
     */
    private $email;

    /**
     * @var string|null
     */
    private $key;
    /**
     * @var string|null
     */
    private $password;

    private function __construct(){}

    public function getEmail(): string{
        return $this->email;
    }
    public function setEmail(string $email): self{
        $this->email = $email;
        return $this;
    }
    public function getKey() : ?string{
        return $this->key;
    }
    private function setKey(string $key): self{
        $this->key = $key;
        return $this;
    }
    public function getPassword(): string{
        return $this->password;
    }
    public function setPassword(string $password): self{
        $this->password = $password;
        return $this;
    }
    public static function parse($var): ?ResetPasswordForm{
        if(is_null($var))return null;
        $data = null;
        if(is_string($var)){
            $data = json_decode($var, true);
        }elseif (is_array($var)){
            $data = $var;
        }else{
            Utils::log("Invalid data for ResetPasswordForm parsing!");
            return null;
        }
        if(isset($data['email'])){
            $result = new ResetPasswordForm();
            $result->setEmail($data['email']);
            if(isset($data['key']) && isset($data['password'])){
                $result->setKey($data['key']);
                $result->setPassword($data['password']);
            }
            return $result;
        }else{
            return null;
        }
    }
}