<?php


namespace App\Service;

use App\Entity\Snooze;
use App\Model\ListSnoozeForm;
use App\Model\SnoozeForm;
use App\Repository\SnoozeRepository;
use App\Utils\ConfigUtils;
use DateTime;

class SnoozeService{

    /**
     * @param ListSnoozeForm $form
     * @return Snooze[]
     */
    public static function listSnooze(ListSnoozeForm $form) : ?array{
        $user = UserService::getCurrentUser();
        if(is_null($user))return null;
        return SnoozeRepository::findByUserAndTypeAndStartAtAfterAndEndAtBefore($user->getUserId(), $form->getRepeat(), $form->getStartDate(), $form->getEndDate(), SnoozeRepository::IN_RANGE);
    }

    /**
     * @param int $user_id
     * @return Snooze[]
     */
    public static function listSnoozeUser(int $user_id) : ?array{
        return SnoozeRepository::findByUser($user_id);
    }

    /**
     * @param SnoozeForm $form
     * @return Snooze[]
     */
    public static function createSnooze(SnoozeForm $form): ?array{
        $user = UserService::getCurrentUser();
        if(is_null($user))return null;
        $result = [];
        foreach ($form->getForm() as $fields){
            $snooze = new Snooze();
            $snooze->setRepeat($fields->isRepeat());
            $snooze->setStart($fields->getStart());
            $snooze->setEnd($fields->getEnd());
            $snooze->setUser($user);
            $snooze = SnoozeRepository::save($snooze);
            if(!is_null($snooze)){
                array_push($result, $snooze);
            }
        }
        if(empty($result)){
            return null;
        }
        return $result;
    }

    /**
     * @return Snooze
     */
    public static function currentSnooze() : ?Snooze{
        $user = UserService::getCurrentUser();
        if(is_null($user))return null;
        $result = self::currentSnoozeUser($user->getUserId());
        if(is_null($result)){
            return null;
        }
        $last = null;
        foreach ($result as $current){
            if(is_null($last) || $last->getEnd() < $current->getEnd() ){
                $last = $current;
            }
        }
        return $last;
    }

    /**
     * @param int $user_id
     * @return Snooze[]
     */
    public static function currentSnoozeUser(int $user_id) : ?array{
        return SnoozeRepository::getCurrentSnooze($user_id);
    }
    public static function removeSnooze(int $snooze_id) : bool{
        $user = UserService::getCurrentUser();
        if(is_null($user))return false;
        $snooze = SnoozeRepository::getOne($snooze_id);
        if(is_null($snooze))return false;
        if($snooze->getUserId() !== $user->getUserId())return false;
        $now = new \DateTime();
        if($snooze->isRepeat()){
            $snooze->setRemovedAt($now);
            SnoozeRepository::save($snooze);
        }else{
            if($snooze->getStart() >= $now){
                SnoozeRepository::remove($snooze);
            }else if($snooze->getEnd() >= $now){
                $snooze->setEnd($now);
                SnoozeRepository::save($snooze);
            }else{
                return false;
            }
        }
        return true;
    }

    public static function cleanSnooze(){

        $old = new DateTime();
        $old->modify("- ".ConfigUtils::getInt("SNOOZE_DURATION")." days");
        SnoozeRepository::removeBefore($old);

        $start = new DateTime();
        $start->modify("- 1 day");
        $end = new DateTime();
        $result = SnoozeRepository::findByUserAndTypeAndStartAtAfterAndEndAtBefore(null, true, $start, $end, SnoozeRepository::END_IN_RANGE);
        foreach($result as $row){
            if(!is_null($row->getRemovedAt()))continue;
            $row->setStart($row->getStart()->modify("+ 7 days"));
            $row->setEnd($row->getEnd()->modify("+ 7 days"));
            SnoozeRepository::save($row);
        }
    }
}