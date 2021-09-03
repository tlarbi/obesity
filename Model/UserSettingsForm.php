<?php


namespace App\Model;


class UserSettingsForm
{
    /**
     * @var int
     */
    private $avatar;

    /**
     * @var int
     */
    private $time_code;

    /**
     * @var string
     */
    private $device_token;

    /**
     * @var string
     */
    private $locale;

    /**
     * @var string
     */
    private $wakeup;

    /**
     * @var string
     */
    private $sleep;

    /**
     * @var string
     */
    private $intention_code;

    /**
     * @var string
     */
    private $intention_text;

    private function __construct()
    {
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

    public function setTimeCode(int $time_code): self
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

    /**
     * @return string
     */
    public function getIntentionCode(): ?string
    {
        return $this->intention_code;
    }

    /**
     * @param string $intention_code
     */
    public function setIntentionCode(string $intention_code): self
    {
        $this->intention_code = $intention_code;
        return $this;
    }

    /**
     * @return string
     */
    public function getIntentionText(): ?string
    {
        return $this->intention_text;
    }

    /**
     * @param string $intention_text
     * @return \App\Model\UserSettingsForm
     */
    public function setIntentionText(string $intention_text): self
    {
        $this->intention_text = $intention_text;
        return $this;
    }

    public static function parse(string $encodedJson): ?UserSettingsForm
    {
        $userSettingsForm = new UserSettingsForm();
        $change = false;

        $json = json_decode($encodedJson, true);
        if (isset($json['avatar']) && self::isByte($json['avatar'])) {
            $userSettingsForm->setAvatar($json['avatar']);
            $change = true;
        }

        if (isset($json['time_code']) && self::isByte($json['time_code'])) {
            $userSettingsForm->setTimeCode($json['time_code']);
            $change = true;
        }

        if (isset($json['device_token'])) {
            $userSettingsForm->setDeviceToken($json['device_token']);
            $change = true;
        }

        if (isset($json['locale'])) {
            $userSettingsForm->setLocale($json['locale']);
            $change = true;
        }

        if (isset($json['wakeup'])) {
            $userSettingsForm->setWakeup($json['wakeup']);
            $change = true;
        }

        if (isset($json['sleep'])) {
            $userSettingsForm->setSleep($json['sleep']);
            $change = true;
        }

        if (isset($json['intention_code'])) {
            $userSettingsForm->setIntentionCode($json['intention_code']);
            $change = true;
        }

        if (isset($json['intention_text'])) {
            $userSettingsForm->setIntentionText($json['intention_text']);
            $change = true;
        }

        return $change ? $userSettingsForm : null;
    }

    private static function isByte($var): bool
    {
        return is_numeric($var) && $var >= 0 && $var < 128;
    }

}