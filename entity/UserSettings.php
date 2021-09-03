<?php

namespace App\Entity;

use App\Repository\UserRepository;

/**
 * UserSettings
 */
class UserSettings
{
    /**
     * @var int
     */
    private $setting_id;

    /**
     * @var
     */
    private $user_id;

    /**
     * @var
     */
    private $user;

    /**
     * @var int|null
     */
    private $avatar;

    /**
     * @var int
     */
    private $time_code = 0;

    /**
     * @var string
     */
    private $device_token = null;

    /**
     * @var string
     */
    private $locale = "en_GB";

    /**
     * @var string
     */
    private $wakeup = "08:00";

    /**
     * @var string
     */
    private $sleep = "22:00";

    /**
     * @var string|null
     */
    private $intention_code = null;

    /**
     * @var string|null
     */
    private $intention_text = null;

    public function getSettingId(): ?string
    {
        return $this->setting_id;
    }

    public function getAvatar(): ?int
    {
        return $this->avatar;
    }

    public function setAvatar(int $avatar): self
    {
        $this->avatar = $avatar;

        return $this;
    }

    public function getTimeCode(): ?int
    {
        return $this->time_code;
    }

    public function setTimeCode(?int $time_code): self
    {
        $this->time_code = $time_code;

        return $this;
    }

    public function getDeviceToken(): ?string
    {
        return $this->device_token;
    }

    public function setDeviceToken(string $device_token): self
    {
        $this->device_token = $device_token;
        return $this;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): self
    {
        $this->locale = $locale;
        return $this;
    }

    public function getWakeup(): ?string
    {
        return $this->wakeup;
    }

    public function setWakeup(string $wakeup): self
    {
        $this->wakeup = $wakeup;
        return $this;
    }

    public function getSleep(): ?string
    {
        return $this->sleep;
    }

    public function setSleep(string $sleep): self
    {
        $this->sleep = $sleep;
        return $this;
    }

    public function getUser(): ?User
    {
        if (is_null($this->user)) {
            $this->user = UserRepository::getOne($this->getUserId());
        }
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;
        $this->setUserId($user->getUserId());
        return $this;
    }

    public function getUserId(): ?int
    {
        return $this->user_id;
    }

    public function setUserId(int $user_id): self
    {
        $this->user_id = $user_id;
        return $this;
    }

    public function getIntentionCode(): ?string
    {
        return $this->intention_code;
    }

    public function setIntentionCode(?string $code): self
    {
        $this->intention_code = $code;
        return $this;
    }

    public function getIntentionText(): ?string
    {
        return $this->intention_text;
    }

    public function setIntentionText(?string $text): self
    {
        $this->intention_text = $text;
        return $this;
    }

    public static function parse($row): ?UserSettings
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
        if (isset($data['user_id'], $data['time_code'], $data['setting_id'])
        ) {
            $result = new self();
            $result->setting_id = $data['setting_id'];
            $result->setUserId($data['user_id']);
            if (!is_null($data['avatar'])) {
                $result->setAvatar($data['avatar']);
            }
            if (!is_null($data['device_token'])) {
                $result->setDeviceToken($data['device_token']);
            }
            if (!is_null($data['locale'])) {
                $result->setLocale($data['locale']);
            }
            if (!is_null($data['wakeup'])) {
                $result->setWakeup($data['wakeup']);
            }
            if (!is_null($data['sleep'])) {
                $result->setSleep($data['sleep']);
            }
            $result->setTimeCode($data['time_code']);
            $result->intention_code = $data['intention_code'];
            if ($data['intention_text']) {
                $result->intention_text = $data['intention_text'];
            }
            $result->save();
            return $result;
        }
        return null;
    }

    public function serialize()
    {
        return [
            'avatar' => $this->getAvatar(),
            'locale' => $this->getLocale(),
            'time_code' => $this->getTimeCode(),
            'wakeup' => $this->getWakeup(),
            'sleep' => $this->getSleep(),
            'intention_code' => $this->getIntentionCode(),
            'intention_text' => $this->getIntentionText(),
        ];
    }

    private $lastSave = null;

    public function save($id = null)
    {
        if (!is_null($id) && is_null($this->lastSave)) {
            $this->setting_id = $id;
        }
        $this->lastSave = clone $this;
    }

    public function getLastSave(): ?UserSettings
    {
        return $this->lastSave;
    }
}
