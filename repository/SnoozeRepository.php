<?php

namespace App\Repository;
use App\Entity\Snooze;
use App\Entity\Survey;
use App\Entity\User;
use App\Utils\Utils;
use DateTime;
use PDO;
use PDOException;
use App\Utils\Database;

class SnoozeRepository{
    public static function save(Snooze $snooze): ?Snooze{
        $old = $snooze->getLastSave();
        $conn = Database::dbConnection();
        if(is_null($old)){
            //New
            $insert_query = (Database::isSqlsrv()?"INSERT INTO snooze([start],[end],[created_at],[removed_at],[repeat],[user_id]) VALUES(:start,:end,:created_at,:removed_at,:repeat,:user_id)"
                :"INSERT INTO `snooze`(`start`,`end`,`created_at`,`removed_at`,`repeat`,`user_id`) VALUES(:start,:end,:created_at,:removed_at,:repeat,:user_id)");

            $insert_stmt = $conn->prepare($insert_query);

            // DATA BINDING
            $insert_stmt->bindValue(':start', $snooze->getStart()->format("Y-m-d H:i:s"), PDO::PARAM_STR);
            $insert_stmt->bindValue(':end', $snooze->getEnd()->format("Y-m-d H:i:s"), PDO::PARAM_STR);
            $insert_stmt->bindValue(':created_at', $snooze->getCreatedAt()->format("Y-m-d H:i:s"), PDO::PARAM_STR);
            if(is_null($snooze->getRemovedAt())){
                $insert_stmt->bindValue(':removed_at', null, PDO::PARAM_NULL);
            }else{
                $insert_stmt->bindValue(':removed_at', $snooze->getRemovedAt()->format("Y-m-d H:i:s"), PDO::PARAM_STR);
            }
            $insert_stmt->bindValue(':repeat', $snooze->isRepeat(), PDO::PARAM_BOOL);
            $insert_stmt->bindValue(':user_id', $snooze->getUserId(), PDO::PARAM_INT);
            $insert_stmt->execute();
            Utils::log( $insert_stmt->queryString);
            $snooze->save($conn->lastInsertId());
        }else{

            try{
                $first = true;
                $query = (Database::isSqlsrv()?"UPDATE snooze SET ":"UPDATE `snooze` SET ");
                $start = ($snooze->getStart() == $old->getStart())?null : $snooze->getStart()->format("Y-m-d H:i:s");
                $end = ($snooze->getEnd() == $old->getEnd())?null : $snooze->getEnd()->format("Y-m-d H:i:s");
                $created_at = ($snooze->getCreatedAt() == $old->getCreatedAt())?null : $snooze->getCreatedAt()->format("Y-m-d H:i:s");
                $removed_at = ($snooze->getRemovedAt() == $old->getRemovedAt())?null : $snooze->getRemovedAt()->format("Y-m-d H:i:s");
                $repeat = ($snooze->isRepeat() == $old->isRepeat())?null : $snooze->isRepeat();
                $user_id = ($snooze->getUserId() == $old->getUserId())?null:$snooze->getUserId();

                if(!is_null($start)){
                    if(!$first){
                        $query = $query . ",";
                    }
                    $query = $query . (Database::isSqlsrv()?"[start] = :start":"`start` = :start");
                    $first = false;
                }
                if(!is_null($end)){
                    if(!$first){
                        $query = $query . ",";
                    }
                    $query = $query . (Database::isSqlsrv()?"[end] = :end":"`end` = :end");
                    $first = false;
                }
                if(!is_null($created_at)){
                    if(!$first){
                        $query = $query . ",";
                    }
                    $query = $query . (Database::isSqlsrv()?"[created_at] = :created_at":"`created_at` = :created_at");
                    $first = false;
                }
                if(!is_null($removed_at)){
                    if(!$first){
                        $query = $query . ",";
                    }
                    $query = $query . (Database::isSqlsrv()?"[removed_at] = :removed_at":"`removed_at` = :removed_at");
                    $first = false;
                }
                if(!is_null($repeat)){
                    if(!$first){
                        $query = $query . ",";
                    }
                    $query = $query . (Database::isSqlsrv()?"[repeat] = :repeat":"`repeat` = :repeat");
                    $first = false;
                }
                if(!is_null($user_id)){
                    if(!$first){
                        $query = $query . ",";
                    }
                    $query = $query . (Database::isSqlsrv()?"[user_id] = :user_id":"`user_id` = :user_id");
                    $first = false;
                }
                if($first)return $snooze;
                $query = $query . (Database::isSqlsrv()?" WHERE [snooze_id] = :id":" WHERE `snooze_id` = :id");
                $stmt = $conn->prepare($query);
                // DATA BINDING

                if(!is_null($start)){
                    $stmt->bindValue(':start', $start,PDO::PARAM_STR);
                }
                if(!is_null($end)){
                    $stmt->bindValue(':end', $end,PDO::PARAM_STR);
                }
                if(!is_null($created_at)){
                    $stmt->bindValue(':created_at', $created_at,PDO::PARAM_STR);
                }
                if(!is_null($removed_at)){
                    $stmt->bindValue(':removed_at', $removed_at,PDO::PARAM_STR);
                }
                if(!is_null($repeat)){
                    $stmt->bindValue(':repeat', $repeat,PDO::PARAM_STR);
                }
                if(!is_null($user_id)){
                    $stmt->bindValue(':user_id', $user_id,PDO::PARAM_INT);
                }
                $stmt->bindValue(':id', $snooze->getSnoozeId(), PDO::PARAM_INT);
                Utils::log($query);
                $stmt->execute();
            }catch(PDOException $e){
                Utils::log( $e->getMessage());
                return null;
            }
            $snooze->save();
        }
        return $snooze;
    }

    /**
     * Returns a list of snoooze objects
     * @return Survey[]
     */
    public static function findByUser($user): ?array{
        if($user instanceof User){
            $user = $user->getUserId();
        }else if(is_numeric($user)){
            $user = (int)$user;
        }else{
            $user = null;
        }
        $conn = Database::dbConnection();
        try{
            $fetch = (Database::isSqlsrv()?"SELECT * FROM snooze WHERE [user_id] = :user":"SELECT * FROM `snooze` WHERE `user_id` = :user");
            $query_stmt = $conn->prepare($fetch);
            $query_stmt->bindValue(':user', $user,PDO::PARAM_INT);
            Utils::log($fetch);
            $query_stmt->execute();
            if($query_stmt->rowCount()){
                $result = [];
                while($row = $query_stmt->fetch(PDO::FETCH_ASSOC)){
                    $val = Snooze::parse($row);
                    if(!is_null($val)){
                        array_push($result, $val);
                    }
                }
                return $result;
            }
            return [];
        }catch(PDOException $e){
            Utils::log( $e->getMessage());
            return null;
        }
    }

    /**
     * @return Snooze
     */
    public static function getOne(int $snooze_id) : ?Snooze{

        $conn = Database::dbConnection();
        try{
            $fetch = (Database::isSqlsrv()?"SELECT * FROM snooze WHERE [snooze_id]=:id"
                :"SELECT * FROM `snooze` WHERE `snooze_id`=:id");
            $stmt = $conn->prepare($fetch);
            $stmt->bindValue(':id', $snooze_id,PDO::PARAM_INT);
            $stmt->execute();
            if($stmt->rowCount()){
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                return Snooze::parse($row);
            }
        }catch(PDOException $e){
            Utils::log( $e->getMessage());
            return null;
        }
        return null;
    }

    /**
     * @return Snooze[]
     */
    public static function getCurrentSnooze(int $user_id) : ?array{
        $conn = Database::dbConnection();
        try{
            $fetch = (Database::isSqlsrv()?"SELECT * FROM snooze WHERE [user_id] = :user AND [start] <= :now AND [end] >= :now2 AND removed_at IS NOT NULL"
                :"SELECT * FROM `snooze` WHERE `user_id` = :user AND `start` <= :now AND `end` >= :now2 AND removed_at <> null");

            $query_stmt = $conn->prepare($fetch);
            $query_stmt->bindValue(':user', $user_id,PDO::PARAM_INT);
            $date = (new DateTime())->format("Y-m-d H:i:s");
            $query_stmt->bindValue(':now', $date,PDO::PARAM_STR);
            $date->setTime(0,0,0,0);
            $query_stmt->bindValue(':now2', $date,PDO::PARAM_STR);
            Utils::log($fetch);
            $query_stmt->execute();
            $result = [];
            if($query_stmt->rowCount()){
                while($row = $query_stmt->fetch(PDO::FETCH_ASSOC)){
                    $val = Snooze::parse($row);
                    if(!is_null($val)){
                        array_push($result, $val);
                    }
                }
            }
            return $result;
        }catch(PDOException $e){
            Utils::log( $e->getMessage());
            return null;
        }
    }

    public static function remove(Snooze $snooze){
        self::removeById($snooze);
    }
    private static function removeById(int $id){

        $conn = Database::dbConnection();
        try{
            $fetch = (Database::isSqlsrv()?"DELETE FROM snooze WHERE [snooze_id]=:id":"DELETE FROM `snooze` WHERE `snooze_id`=:id");
            $query_stmt = $conn->prepare($fetch);
            $query_stmt->bindValue(':id', $id,PDO::PARAM_INT);
            $query_stmt->execute();
        }catch(PDOException $e){
            error_log( $e->getMessage());
            return null;
        }
    }

    public static function removeBefore(DateTime $old){
        $conn = Database::dbConnection();
        try{
            $fetch = (Database::isSqlsrv()?"DELETE FROM snooze WHERE ( [repeat] = true AND [removed_at] <= :old ) OR ([repeat] = false AND [end] <= :old2)"
                :"DELETE FROM snooze WHERE ( `repeat` = true AND `removed_at` <= :old ) OR (`repeat` = false AND `end` <= :old2)");

            $query_stmt = $conn->prepare($fetch);
            $date = ($old)->format("Y-m-d H:i:s");
            $query_stmt->bindValue(':old', $date,PDO::PARAM_STR);
            $query_stmt->bindValue(':old2', $date,PDO::PARAM_STR);
            Utils::log($fetch);
            $query_stmt->execute();
        }catch(PDOException $e){
            Utils::log( $e->getMessage());
        }
    }

    public const IN_RANGE = 0;
    public const END_IN_RANGE = 1;

    /**
     * Returns a list of survey objects
     * @param int|User $user
     * @param bool|null $type
     * @param DateTime|null $after
     * @param DateTime|null $before
     * @param int $choice
     * @return Snooze[]
     */
    public static function findByUserAndTypeAndStartAtAfterAndEndAtBefore($user, ?bool $type = null, ?DateTime $after = null, ?DateTime $before = null, int $choice = self::IN_RANGE) : ?array{
        if($user instanceof User){
            $user = $user->getUserId();
        }else if(is_numeric($user)){
            $user = (int)$user;
        }else{
            $user = null;
        }
        $conn = Database::dbConnection();
        try{
            $fetch = (Database::isSqlsrv()?"SELECT * FROM snooze ":"SELECT * FROM `snooze` ");
            $value = false;
            if(!is_null($user)){
                $fetch = $fetch . ( $value ? " AND " : " WHERE " ) . (Database::isSqlsrv()?" [user_id] = :user" : " `user_id` = :user");
                $value = true;
            }
            if(!is_null($type)){
                $fetch = $fetch . ( $value ? " AND " : " WHERE " ) . (Database::isSqlsrv()?" [snooze] = :snooze":" `snooze` = :snooze");
                $value = true;
            }
            if(!is_null($after)){
                $fetch = $fetch . ( $value ? " AND " : " WHERE " );
                $fetch = $fetch . " ( ";
                switch ($choice){
                    default:
                        $fetch = $fetch . (Database::isSqlsrv()?"[end]":"`end`");
                        break;
                }
                $fetch = $fetch . " > :start";

                $fetch = $fetch . " ) ";
                $value = true;
            }
            if(!is_null($before)){
                $fetch = $fetch . ( $value ? " AND " : " WHERE " );
                $fetch = $fetch . " ( ";
                switch ($choice){
                    case self::END_IN_RANGE:
                        $fetch = $fetch . (Database::isSqlsrv()?"[end]":"`end`");
                        break;
                    default:
                        $fetch = $fetch . (Database::isSqlsrv()?"[start]":"`start`");
                        break;
                }
                $fetch = $fetch . " <= :end";
                $fetch = $fetch . " ) ";
                $value = true;
            }
            $query_stmt = $conn->prepare($fetch);

            if(!is_null($user)){
                $query_stmt->bindValue(':user', $user,PDO::PARAM_INT);
            }
            if(!is_null($type)){
                $query_stmt->bindValue(':snooze', $type,PDO::PARAM_INT);
            }
            if(!is_null($after)){
                $query_stmt->bindValue(':start', $after->format("Y-m-d H:i:s"),PDO::PARAM_STR);
            }
            if(!is_null($before)){
                $query_stmt->bindValue(':end', $before->format("Y-m-d H:i:s"),PDO::PARAM_STR);
            }
            Utils::log($fetch);
            $query_stmt->execute();
            if($query_stmt->rowCount()){
                $result = [];
                while($row = $query_stmt->fetch(PDO::FETCH_ASSOC)){
                    $val = Snooze::parse($row);
                    if(!is_null($val)){
                        array_push($result, $val);
                    }
                }
                return $result;
            }
            return [];
        }catch(PDOException $e){
            Utils::log( $e->getMessage());
            return null;
        }
    }
}