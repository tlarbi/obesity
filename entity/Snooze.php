<?php

namespace App\Entity;

use App\Repository\UserRepository;
use DateTime;

/**
 * Survey
 */
class Snooze{
    /**
     * @var int
     */
    private $snooze_id;
    /**
     * @var boolean
     */
    private $repeat = false;
    /**
     * @var DateTime|null
     */
    private $start;
    /**
     * @var DateTime|null
     */
    private $end;
    /**
     * @var DateTime
     */
    private $created_at;
    /**
     * @var DateTime|null
     */
    private $removed_at;

    /**
     * @var int
     */
    private $user_id;

    private $user;
    public function __construct(){
        $this->created_at = new DateTime();
    }
    public function getSnoozeId(): ?int{
        return $this->snooze_id;
    }

    private function setSnoozeId(?int $id): self{
        $this->snooze_id = $id;
        return $this;
    }
    public function isRepeat(): bool{
        return $this->repeat;
    }

    public function setRepeat(bool $repeat): self{
        $this->repeat = $repeat;
        return $this;
    }
    public function getStart(): DateTime{
        return $this->start;
    }

    public function setStart(DateTime $start): self{
        $this->start = $start;
        return $this;
    }
    public function getEnd(): DateTime{
        return $this->end;
    }

    public function setEnd(DateTime $end): self{
        $this->end = $end;
        return $this;
    }
    public function getCreatedAt(): DateTime{
        return $this->created_at;
    }

    public function setCreatedAt(DateTime $created_at): self{
        $this->created_at = $created_at;
        return $this;
    }
    public function getRemovedAt(): DateTime{
        return $this->removed_at;
    }

    public function setRemovedAt(DateTime $removed_at): self{
        $this->removed_at = $removed_at;
        return $this;
    }

    public function getUser(): ?User{
        if(is_null($this->user)){
            $this->user = UserRepository::getOne($this->getUserId());
        }
        return $this->user;
    }
    public function setUser(User $user): self{
        $this->user_id = $user->getUserId();
        $this->user = $user;
        return $this;
    }
    public function getUserId(): int{
        return $this->user_id;
    }

    public function setUserId(int $user_id): self{
        $this->user_id = $user_id;
        return $this;
    }

    public static function parse($var): ?Snooze{
        if(is_null($var))return null;
        $data = null;
        if(is_string($var)){
            $data = json_decode($var, true);
        }elseif (is_array($var)){
            $data = $var;
        }else{
            return null;
        }
        if(isset($data['snooze_id'])
            && isset($data['repeat'])
            && isset($data['start'])
            && isset($data['end'])
            && isset($data['created_at'])
            && isset($data['user_id'])){
            $result = new Snooze();
            $result->snooze_id = $data['snooze_id'];
            $result->user_id = $data['user_id'];
            $result->setStart(DateTime::createFromFormat("Y-m-d H:i:s", $data['start']));
            $result->setEnd(DateTime::createFromFormat("Y-m-d H:i:s", $data['end']));
            $result->setCreatedAt(DateTime::createFromFormat("Y-m-d H:i:s", $data['created_at']));
            if(isset($data['removed_at'])){
                $result->setRemovedAt(DateTime::createFromFormat("Y-m-d H:i:s", $data['removed_at']));
            }
            $result->setRepeat($data['repeat']);
            $result->save();
            return $result;
        }
        return null;
    }

    public function serialize(){
        return [
            'snooze_id' => $this->getSnoozeId(),
            'repeat' => $this->isRepeat(),
            'start' => $this->getStart()->format("Y-m-d H:i:s"),
            'end' => $this->getEnd()->format("Y-m-d H:i:s"),
            'created_at' => $this->getCreatedAt()->format("Y-m-d H:i:s"),
            'removed_at' => is_null($this->getRemovedAt()) ? null : $this->getRemovedAt()->format("Y-m-d H:i:s"),
        ];
    }

    private $lastSave = null;
    public function __clone(){
        $this->start = clone $this->start;
        $this->end = clone $this->end;
        $this->created_at = clone $this->created_at;
        if(!is_null($this->removed_at))
            $this->removed_at = clone $this->removed_at;
    }
    public function save($id = null){
        if(!is_null($id) && is_null($this->lastSave)){
            $this->setSnoozeId($id);
        }
        $this->lastSave = clone $this;
    }
    public function getLastSave(): ?Snooze{
        return $this->lastSave;
    }

}
