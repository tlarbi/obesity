<?php


namespace App\Model;
use App\Entity\Snooze;
use DateTime;

class SnoozeFormFields{
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

    private function __construct(){}

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
    public static function parse($var): ?SnoozeFormFields{
        if(is_null($var))return null;
        $data = null;
        if(is_string($var)){
            $data = json_decode($var, true);
        }elseif (is_array($var)){
            $data = $var;
        }else{
            return null;
        }
        if(isset($data['start'])
            && isset($data['end'])){
            $result = new SnoozeFormFields();
            $result->setStart(DateTime::createFromFormat("Y-m-d H:i:s", $data['start']));
            $result->setEnd(DateTime::createFromFormat("Y-m-d H:i:s", $data['end']));
            $result->setRepeat(isset($data['repeat']) && $data['repeat']);
            return $result;
        }
        return null;
    }

}