<?php
namespace App\Middleware;

use App\Repository\UserRepository;
use App\Utils\JwtHandler;
use App\Entity\User;

class Auth extends JwtHandler{

    protected $headers;
    protected $token;
    public function __construct($headers) {
        parent::__construct();
        $this->headers = $headers;
    }

    public function getCurrentUser(): ?User{
        if(array_key_exists('Authorization',$this->headers) && !empty(trim($this->headers['Authorization']))){
            $this->token = explode(" ", trim($this->headers['Authorization']));
            if(isset($this->token[1]) && !empty(trim($this->token[1]))){
                $data = $this->_jwt_decode_data($this->token[1]);
                if(isset($data['auth']) && isset($data['data']->user_id) && $data['auth']){
                    $user = UserRepository::getOne($data['data']->user_id);
                    if(strcasecmp($user->getCurrentToken(), $this->token[1]) === 0){
                        return $user;
                    }
                }
            }
        }
        return null;
    }
}