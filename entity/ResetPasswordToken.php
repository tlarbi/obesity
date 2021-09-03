<?php

namespace App\Entity;

use App\Utils\Utils;

class ResetPasswordToken
{
    /**
     * @var int|null
     */
    private $token_id = null;

    /**
     * @var string
     */
    private $token;

    /**
     * @var int|mixed
     */
    private $user_id;

    /**
     * @var \DateTime
     */
    private $created_at;

    public function __construct(User $user)
    {
        $this->user_id = $user->getUserId();
        $this->token = Utils::generateResetToken();
        $this->created_at = new \DateTime();
    }

    /**
     * @return mixed
     */
    public function getTokenId()
    {
        return $this->token_id;
    }

    /**
     * @param mixed $token_id
     */
    public function setTokenId($token_id): void
    {
        $this->token_id = $token_id;
    }

    /**
     * @return mixed
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param mixed $token
     */
    public function setToken($token): void
    {
        $this->token = $token;
    }

    /**
     * @return mixed
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * @param mixed $user_id
     */
    public function setUserId($user_id): void
    {
        $this->user_id = $user_id;
    }

    /**
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * @param mixed $created_at
     */
    public function setCreatedAt($created_at): void
    {
        $this->created_at = $created_at;
    }

    public static function parse($row)
    {
        if (is_null($row)) {
            return null;
        }
        $data = null;
        if (is_string($row)) {
            $data = json_decode($row, true);
        } elseif (is_array($row)) {
            $data = $row;
        } else {
            return null;
        }

        if (isset($data['token_id'], $data['token'], $data['user_id'], $data['created_at'])) {
            $tokenEntity = new self();
            $tokenEntity->token_id = $data['token_id'];
            $tokenEntity->token = $data['token'];
            $tokenEntity->user_id = $data['user_id'];
            $tokenEntity->created_at = new \DateTime($data['created_at']);

            $tokenEntity->save();
            return $tokenEntity;
        }
        return null;
    }

    public function serialize()
    {
        return json_encode([
            'token_id' => $this->token_id,
            'token' => $this->token,
            'user_id' => $this->user_id,
            'created_at' => $this->created_at->format("Y-m-d H:i:s")
        ]);
    }

    private $lastSave = null;

    public function __clone()
    {
        $this->created_at = clone $this->created_at;
    }

    public function save($id = null): void
    {
        if (!is_null($id) && is_null($this->lastSave)) {
            $this->token_id = $id;
        }
        $this->lastSave = clone $this;
    }

    public function getLastSave(): ?ResetPasswordToken
    {
        return $this->lastSave;
    }
}