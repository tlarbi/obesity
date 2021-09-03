<?php

namespace App\Entity;


use App\Repository\UserSettingsRepository;
use DateTime;
/**
 * User
 */
class User{
    /**
     * @var int
     */
    private $user_id;
//
//    /**
//     * @var DateTime
//     */
//    private $birth_date;

    /**
     * @var string|null
     */
    private $username;

    /**
     * @var string|null
     */
    private $current_token;
//
//    /**
//     * @var DateTime
//     */
//    private $date_last_update_pwd;
//
//    /**
//     * @var string
//     */
//    private $email;

//
//    /**
//     * @var string
//     */
//    private $first_name;
//
//    /**
//     * @var string
//     */
//    private $last_name;


    /**
     * @var string|null
     */
    private $password;

    /**
     * @var boolean
     */
    private $enabled = false;
//    /**
//     * @var boolean
//     */
//    private $must_change_pwd = false;
//    /**
//     * @var boolean
//     */
//    private $validated = false;

    private $user_settings;

    public function __construct($id = null){
        if(!is_null($id)){
            $this->user_id = $id;
        }
    }

    public function getUserId(){
        return $this->user_id;
    }
//    public function getBirthDate(): DateTime{
//        return $this->birth_date;
//    }
//    public function setBirthDate(DateTime $birth_date): self{
//        $this->birth_date = $birth_date;
//        return $this;
//    }
    public function getCurrentToken(): ?string{
        return $this->current_token;
    }
    public function setCurrentToken(?string $current_token): self{
        $this->current_token = $current_token;
        return $this;
    }
//    public function getDateLastUpdatePwd(): DateTime{
//        return $this->date_last_update_pwd;
//    }
//    public function setDateLastUpdatePwd(DateTime $date_last_update_pwd): self{
//        $this->date_last_update_pwd = $date_last_update_pwd;
//        return $this;
//    }
//    public function getEmail(): ?string{
//        return $this->email;
//    }
//    public function setEmail(?string $email): self{
//        $this->email = $email;
//        return $this;
//    }
    public function getEnabled(): ?bool{
        return $this->enabled;
    }
    public function setEnabled(bool $enabled): self{
        $this->enabled = $enabled;
        return $this;
    }
    public function getUsername(): ?string{
        return $this->username;
    }
    public function setUsername(string $username): self{
        $this->username = $username;
        return $this;
    }
//    public function getFirstName(): ?string{
//        return $this->first_name;
//    }
//    public function setFirstName(string $first_name): self{
//        $this->first_name = $first_name;
//        return $this;
//    }
//    public function getLastName(): ?string{
//        return $this->last_name;
//    }
//    public function setLastName(string $last_name): self{
//        $this->last_name = $last_name;
//        return $this;
//    }
//    public function getMustChangePwd(): bool{
//        return $this->must_change_pwd;
//    }
//    public function setMustChangePwd(bool $must_change_pwd): self{
//        $this->must_change_pwd = $must_change_pwd;
//        return $this;
//    }
    public function getPassword(): ?string{
        return $this->password;
    }
    public function setPassword(?string $password): self{
        $this->password = $password;
        return $this;
    }
//    public function getValidated(): bool{
//        return $this->validated;
//    }
//    public function setValidated(bool $validated): self{
//        $this->validated = $validated;
//        return $this;
//    }
    public function getUserSettings(): UserSettings{
        if(is_null($this->user_settings)){
            $this->user_settings = UserSettingsRepository::getByUser($this);
        }
        return $this->user_settings;
    }
    public function setUserSettings(UserSettings $user_settings): self{
        $this->user_settings = $user_settings;
        return $this;
    }
    public function eraseCredentials(): void{
        $pattern = "*****";
        $this->setUsername($pattern);
//		$date = new DateTime();
//		$date->setDate(0,0,0);
//		$this->setFirstName($pattern);
//        $this->setLastName($pattern);
//
//        $this->setBirthDate($date);
//        $this->setEmail($this->getUserId() . $pattern . "@" . $pattern . ".com");
        $this->setPassword($pattern);
        $this->setEnabled(false);
    }
//
//    public function getUsername(){
//        return $this->email;
//    }
    public static function parse($var): ?User{
        if(is_null($var))return null;
        $data = null;
        if(is_string($var)){
            $data = json_decode($var, true);
        }elseif (is_array($var)){
            $data = $var;
        }else{
            return null;
        }
//        echo json_encode($data);
        if(isset($data['user_id']) /*&& is_int($data->user_id)*/
            && isset($data['username'])
//            && isset($data['birth_date'])
//            && isset($data['email'])
//            && isset($data['date_last_update_pwd'])
//            && isset($data['first_name'])
//            && isset($data['last_name'])
            && isset($data['password'])
        ){
            $result = new User();
            $result->user_id = $data['user_id'];
//            $result->setBirthDate(DateTime::createFromFormat("Y-m-d", $data['birth_date']));
            if(isset($data['current_token'])){
                $result->setCurrentToken($data['current_token']);
            }
//            $result->setDateLastUpdatePwd(DateTime::createFromFormat("Y-m-d H:i:s", $data['date_last_update_pwd']));
//            $result->setEmail($data['email']);
//            $result->setFirstName($data['first_name']);
//            $result->setLastName($data['last_name']);
            $result->setUsername($data['username']);
            $result->setPassword($data['password']);
            $result->setEnabled(isset($data['enabled']) && $data['enabled']);
//            $result->setMustChangePwd(isset($data['must_change_pwd']) && $data['must_change_pwd']);
//            $result->setValidated(isset($data['validated']) && $data['validated']);
            $result->save();
            return $result;
        }
        return null;
    }

    public function serialize(){
        return [
//            'email' => $this->getEmail(),
//            'first_name' => $this->getFirstName(),
//            'last_name' => $this->getLastName(),
//            'birth_date' => $this->getBirthDate()->format("Y-m-d"),
//            'date_last_update_pwd' => $this->getDateLastUpdatePwd()->format("Y-m-d H:i:s"),
//            'must_change_pwd' => $this->getMustChangePwd(),
            'username' => $this->getUsername(),
            'enabled' => $this->getEnabled(),
//            'validated' => $this->getValidated(),
            'user_settings' => $this->getUserSettings()->serialize(),
        ];
    }
    private $lastSave = null;
    public function __clone(){
//        $this->birth_date = clone $this->birth_date;
//        $this->date_last_update_pwd = clone $this->date_last_update_pwd;
    }
    public function save($id = null){
        if(!is_null($id) && is_null($this->lastSave)){
            $this->user_id=$id;
        }
        $this->lastSave = clone $this;
    }
    public function getLastSave(): ?User{
        return $this->lastSave;
    }
}
