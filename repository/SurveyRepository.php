<?php

namespace App\Repository;
use App\Entity\Survey;
use App\Entity\User;
use App\Model\SurveyState;
use App\Model\SurveyType;
use App\Utils\Utils;
use DateTime;
use PDO;
use PDOException;
use App\Utils\Database;

class SurveyRepository{
    public static function save(Survey $survey): ?Survey{
        $old = $survey->getLastSave();
        if(is_null($old)){
            //New
            $conn = Database::dbConnection();
            $insert_query = (Database::isSqlsrv()?"INSERT INTO survey([code],[started],[ended],[from],[to],[user_id],[state]) VALUES(:code,:started,:ended,:begin,:end,:user_id,:state)"
                :"INSERT INTO `survey`(`code`,`started`,`ended`,`from`,`to`,`user_id`,`state`) VALUES(:code,:started,:ended,:begin,:end,:user_id,:state)");

            $insert_stmt = $conn->prepare($insert_query);

            // DATA BINDING
            $insert_stmt->bindValue(':code', $survey->getCode(), PDO::PARAM_STR);
            if(is_null($survey->getBegin())){
                $insert_stmt->bindValue(':begin', null, PDO::PARAM_NULL);
            }else{
                $insert_stmt->bindValue(':begin', $survey->getBegin()->format("Y-m-d H:i:s"), PDO::PARAM_STR);
            }

            if(is_null($survey->getStarted())){
                $insert_stmt->bindValue(':started', null, PDO::PARAM_NULL);
            }else{
                $insert_stmt->bindValue(':started', $survey->getStarted()->format("Y-m-d H:i:s"), PDO::PARAM_STR);
            }
            if(is_null($survey->getEnded())){
                $insert_stmt->bindValue(':ended', null, PDO::PARAM_NULL);
            }else{
                $insert_stmt->bindValue(':ended', $survey->getEnded()->format("Y-m-d H:i:s"), PDO::PARAM_STR);
            }

            if(is_null($survey->getEnd())){
                $insert_stmt->bindValue(':end', null, PDO::PARAM_NULL);
            }else{
                $insert_stmt->bindValue(':end', $survey->getEnd()->format("Y-m-d H:i:s"), PDO::PARAM_STR);
            }
            $insert_stmt->bindValue(':state', $survey->getState(), PDO::PARAM_INT);
            $insert_stmt->bindValue(':user_id', $survey->getUserId(), PDO::PARAM_INT);
//            $insert_stmt->bindValue(':tries', $survey->getTries(), PDO::PARAM_INT);

            $insert_stmt->execute();
            Utils::log( $insert_stmt->queryString);
            $survey->save($conn->lastInsertId());
            return $survey;
        }else{
            $conn = Database::dbConnection();
            try{
                $first = true;
                $query = (Database::isSqlsrv()?"UPDATE survey SET ":"UPDATE `survey` SET ");
                $code = ($survey->getCode() == $old->getCode())?null : $survey->getCode();
                $started = ($survey->getStarted() == $old->getStarted())?null : $survey->getStarted()->format("Y-m-d H:i:s");
                $ended = ($survey->getEnded() == $old->getEnded())?null : $survey->getEnded()->format("Y-m-d H:i:s");
                $begin = ($survey->getBegin() == $old->getBegin())?null : $survey->getBegin()->format("Y-m-d H:i:s");
                $end = ($survey->getEnd() == $old->getEnd())?null : $survey->getEnd()->format("Y-m-d H:i:s");
                $user_id = ($survey->getUserId() == $old->getUserId())?null:$survey->getUserId();
                $state = ($survey->getState() == $old->getState())?null:$survey->getState();
//                $tries = ($survey->getTries() == $old->getTries())?null:$survey->getTries();

                if(!is_null($code)){
                    $query = $query . (Database::isSqlsrv()?"[code] = :code":"`code` = :code");
                    $first = false;
                }
                if(!is_null($begin)){
                    if(!$first){
                        $query = $query . ",";
                    }
                    $query = $query . (Database::isSqlsrv()?"[from] = :begin":"`from` = :begin");
                    $first = false;
                }
                if(!is_null($end)){
                    if(!$first){
                        $query = $query . ",";
                    }
                    $query = $query . (Database::isSqlsrv()?"[to] = :end":"`to` = :end");
                    $first = false;
                }
                if(!is_null($started)){
                    if(!$first){
                        $query = $query . ",";
                    }
                    $query = $query . (Database::isSqlsrv()?"[started] = :started":"`started` = :started");
                    $first = false;
                }
                if(!is_null($ended)){
                    if(!$first){
                        $query = $query . ",";
                    }
                    $query = $query . (Database::isSqlsrv()?"[ended] = :ended":"`ended` = :ended");
                    $first = false;
                }
                if(!is_null($user_id)){
                    if(!$first){
                        $query = $query . ",";
                    }
                    $query = $query . (Database::isSqlsrv()?"[user_id] = :user_id":"`user_id` = :user_id");
                    $first = false;
                }
                if(!is_null($state)){
                    if(!$first){
                        $query = $query . ",";
                    }
                    $query = $query . (Database::isSqlsrv()?"[state] = :state":"`state` = :state");
                    $first = false;
                }
//                if(!is_null($tries)){
//                    if(!$first){
//                        $query = $query . ",";
//                    }
//                    $query = $query . (Database::isSqlsrv()?"[tries] = :tries":"`tries` = :tries");
//                    $first = false;
//                }
                if($first)return $survey;
                $query = $query . (Database::isSqlsrv()?" WHERE [survey_id] = :id":" WHERE `survey_id` = :id");
                $stmt = $conn->prepare($query);
                // DATA BINDING

                if(!is_null($code)){
                    $stmt->bindValue(':code', $code,PDO::PARAM_STR);
                }
                if(!is_null($begin)){
                    $stmt->bindValue(':begin', $begin,PDO::PARAM_STR);
                }
                if(!is_null($end)){
                    $stmt->bindValue(':end', $end,PDO::PARAM_STR);
                }
                if(!is_null($started)){
                    $stmt->bindValue(':started', $started,PDO::PARAM_STR);
                }
                if(!is_null($ended)){
                    $stmt->bindValue(':ended', $ended,PDO::PARAM_STR);
                }
                if(!is_null($user_id)){
                    $stmt->bindValue(':user_id', $user_id,PDO::PARAM_INT);
                }
                if(!is_null($state)){
                    $stmt->bindValue(':state', $state,PDO::PARAM_INT);
                }
//                if(!is_null($tries)){
//                    $stmt->bindValue(':tries', $tries,PDO::PARAM_INT);
//                }
                $stmt->bindValue(':id', $survey->getSurveyId(), PDO::PARAM_INT);
                Utils::log($query);
                $stmt->execute();
            }catch(PDOException $e){
                Utils::log( $e->getMessage());
                return null;
            }
            $survey->save();
            return $survey;
        }
    }

    /**
     * Returns a list of survey objects
     * @param User $user
     * @return Survey[]
     */
    public static function findByUser(User $user): ?array{
        return self::findByUserAndStateAndCodeAndStartAtAfterAndEndAtBefore($user);
    }

    /**
     * Returns a list of survey objects
     * @param User $user
     * @param SurveyType $type
     * @return Survey[]
     */
    public static function findByUserAndType(User $user, SurveyType $type): ?array{
        return self::findByUserAndStateAndCodeAndStartAtAfterAndEndAtBefore($user, null, $type->getCode());
    }

    public static function isFirstOfTheDay(User $user, Survey $survey, ?int $state) : ?bool{
        $conn = Database::dbConnection();
        try{
            $fetch = (Database::isSqlsrv()?"SELECT * FROM survey WHERE [user_id] = :user AND [started] > :begin AND [started] < :end AND [started] IS NOT NULL AND [survey_id] <> :id"
                :"SELECT * FROM `survey` WHERE `user_id` = :user AND `started` > :begin AND `started` < :end AND `started` <> null AND `survey_id` <> :id");

            if(!is_null($state)){
                $fetch = $fetch . " AND ";
                $fetch = $fetch . (Database::isSqlsrv()?"[state] = :state"
                        :"`state` = :state");
            }
            $query_stmt = $conn->prepare($fetch);
            $query_stmt->bindValue(':user', $user->getUserId(),PDO::PARAM_INT);
            $query_stmt->bindValue(':id', $survey->getSurveyId(),PDO::PARAM_INT);
            $date = is_null($survey->getStarted())?new DateTime() : $survey->getStarted();
            $query_stmt->bindValue(':end', ($date)->format("Y-m-d H:i:s"),PDO::PARAM_STR);
            $date->setTime(0,0,0,0);
            $query_stmt->bindValue(':begin', $date->format("Y-m-d H:i:s"),PDO::PARAM_STR);
            if(!is_null($state)){
                $query_stmt->bindValue(':state', $state,PDO::PARAM_STR);
            }
            Utils::log($fetch);
            $query_stmt->execute();
            if($query_stmt->rowCount()){
                return false;
            }
            return true;
        }catch(PDOException $e){
            Utils::log( $e->getMessage());
            return null;
        }
    }
    public static function getOne($id): ?Survey{
        $conn = Database::dbConnection();
        try{
            $fetch = (Database::isSqlsrv()?"SELECT * FROM survey WHERE [survey_id]=:id"
                :"SELECT * FROM `survey` WHERE `survey_id`=:id");
            $stmt = $conn->prepare($fetch);
            $stmt->bindValue(':id', $id,PDO::PARAM_INT);
            $stmt->execute();
            if($stmt->rowCount()){
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                return Survey::parse($row);
            }
        }catch(PDOException $e){
            Utils::log( $e->getMessage());
            return null;
        }
        return null;
    }
    public const IN_RANGE = 0;
    public const FULLY_IN_RANGE = 1;
    public const ENDED_IN_RANGE = 2;
    public const BEGIN_IN_RANGE = 3;
    /**
     * Returns a list of survey objects
     * @param int|User $user
     * @param int|string|SurveyState|null $state
     * @param string|null $type
     * @param DateTime|null $after
     * @param DateTime|null $before
     * @param int $choice
     * @return Survey[]
     */
    public static function findByUserAndStateAndCodeAndStartAtAfterAndEndAtBefore($user, $state = null, ?string $type = null, ?DateTime $after = null, ?DateTime $before = null, int $choice = self::IN_RANGE) : ?array{
        if($user instanceof User){
            $user = $user->getUserId();
        }else if(is_numeric($user)){
            $user = (int)$user;
        }else{
            $user = null;
        }
        if(!is_null($state)){
            if($state instanceof SurveyState){
                $state = $state->getCode();
            }else if(is_numeric($state)){
                $state = (int)$state;
            }else if(is_string($state)){
                $tmp = SurveyState::getFromName($state);
                if(!is_null($tmp)){
                    $state = $tmp->getCode();
                }else{
                    $state = null;
                }
            }else{
                $state = null;
            }
        }
        $conn = Database::dbConnection();
        try{
            $fetch = (Database::isSqlsrv()?"SELECT * FROM survey ":"SELECT * FROM `survey` ");
            $value = false;
            if(!is_null($user)){
                $fetch = $fetch . ( $value ? " AND " : " WHERE " ) . (Database::isSqlsrv()?" [user_id] = :user" : " `user_id` = :user");
                $value = true;
            }
            if(!is_null($state)){
                $fetch = $fetch . ( $value ? " AND " : " WHERE " ) . (Database::isSqlsrv()?" [state] = :state":" `state` = :state");
                $value = true;
            }
            if(!is_null($type)){
                $fetch = $fetch . ( $value ? " AND " : " WHERE " ) . (Database::isSqlsrv()?"[code] = :code":"`code` = :code");
                $value = true;
            }
            if(!is_null($after)){
                $fetch = $fetch . ( $value ? " AND " : " WHERE " );
                $fetch = $fetch . " ( ";
                switch ($choice){
                    case self::BEGIN_IN_RANGE:
                    case self::FULLY_IN_RANGE:
                        $fetch = $fetch . (Database::isSqlsrv()?"[from]":"`from`");
                        break;
                    case self::ENDED_IN_RANGE:
                        $fetch = $fetch . (Database::isSqlsrv()?"[ended]":"`ended`");
                        break;
                    default:
                        $fetch = $fetch . (Database::isSqlsrv()?"[to]":"`to`");
                        break;
                }
                $fetch = $fetch . " > :start";
                if($choice != self::ENDED_IN_RANGE){
                    $fetch = $fetch . (Database::isSqlsrv()?" OR [started] >= :start2":" OR `started` >= :start2");
                }else{
                    $fetch = $fetch . (Database::isSqlsrv()?" OR [ended] >= :start2":" OR `ended` >= :start2");
                }
                $fetch = $fetch . " ) ";
                $value = true;
            }
            if(!is_null($before)){
                $fetch = $fetch . ( $value ? " AND " : " WHERE " );
                $fetch = $fetch . " ( ";
                switch ($choice){
                    case self::FULLY_IN_RANGE:
                        $fetch = $fetch . (Database::isSqlsrv()?"[to]":"`to`");
                        break;
                    case self::ENDED_IN_RANGE:
                        $fetch = $fetch . (Database::isSqlsrv()?"[ended]":"`ended`");
                        break;
                    case self::BEGIN_IN_RANGE:
                    default:
                        $fetch = $fetch . (Database::isSqlsrv()?"[from]":"`from`");
                        break;
                }
                $fetch = $fetch . " <= :end";
                if($choice != self::ENDED_IN_RANGE){
                    $fetch = $fetch . (Database::isSqlsrv()?" OR [started] <= :end2":" OR `started` <= :end2");
                }else{
                    $fetch = $fetch . (Database::isSqlsrv()?" OR [ended] <= :end2":" OR `ended` <= :end2");
                }
                $fetch = $fetch . " ) ";
                $value = true;
            }
            $query_stmt = $conn->prepare($fetch);

            if(!is_null($user)){
                $query_stmt->bindValue(':user', $user,PDO::PARAM_INT);
            }
            if(!is_null($state)){
                $query_stmt->bindValue(':state', $state,PDO::PARAM_INT);
            }
            if(!is_null($type)){
                $query_stmt->bindValue(':code', $type,PDO::PARAM_STR);
            }
            if(!is_null($after)){
                $query_stmt->bindValue(':start', $after->format("Y-m-d H:i:s"),PDO::PARAM_STR);
                $query_stmt->bindValue(':start2', $after->format("Y-m-d H:i:s"),PDO::PARAM_STR);
            }
            if(!is_null($before)){
                $query_stmt->bindValue(':end', $before->format("Y-m-d H:i:s"),PDO::PARAM_STR);
                $query_stmt->bindValue(':end2', $before->format("Y-m-d H:i:s"),PDO::PARAM_STR);
            }
            Utils::log($fetch);
            $query_stmt->execute();
            if($query_stmt->rowCount()){
                $result = [];
                while($row = $query_stmt->fetch(PDO::FETCH_ASSOC)){
                    $val = Survey::parse($row);
                    if(!is_null($val)){
                        $result[] = $val;
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


    public static function getDailyAchievement(User $user): array
    {
        $connection = Database::dbConnection();
        $query = Database::isSqlsrv() ?
            'SELECT COUNT(s.[survey_id]) survey_count, [code] survey_type FROM survey s WHERE s.[ended] >= :min_date AND s.[user_id] = :user_id GROUP BY s.[code]':
            'SELECT COUNT(s.`survey_id`) survey_count, `code` survey_type FROM survey s WHERE s.`ended` >= :min_date AND s.`user_id` = :user_id GROUP BY s.`code`';
        $statement = $connection->prepare($query);

        $statement->bindValue(':min_date', (new DateTime())->format('Y-m-d'), PDO::PARAM_STR);
        $statement->bindValue(':user_id', $user->getUserId(), PDO::PARAM_INT);

        $statement->execute();
        if ($statement->rowCount()) {
            $results = [];
            while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                $results[] = $row;
            }
            return $results;
        }

        return [];
    }

    public static function getWeeklyAchievement(User $user): array
    {
        $connection = Database::dbConnection();
        $query = Database::isSqlsrv() ?
            'SELECT COUNT(s.[survey_id]) survey_count, s.[code] survey_type, FORMAT(s.[ended], \'yyyy-MM-dd\') survey_day FROM survey s WHERE s.[ended] >= :min_date AND s.[user_id] = :user_id GROUP BY s.[code], FORMAT(s.[ended], \'yyyy-MM-dd\')':
            'SELECT COUNT(s.`survey_id`) survey_count, s.`code` survey_type, DATE_FORMAT(s.`ended`, \'%Y-%m-%d\') survey_day FROM survey s WHERE s.`ended` >= :min_date AND s.`user_id` = :user_id GROUP BY s.`code`, survey_day';
        $statement = $connection->prepare($query);

        $statement->bindValue(':min_date', (new DateTime('- 6 days'))->format('Y-m-d'), PDO::PARAM_STR);
        $statement->bindValue(':user_id', $user->getUserId(), PDO::PARAM_INT);

        $statement->execute();
        if ($statement->rowCount()) {
            $results = [];
            while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                $results[] = $row;
            }
            return $results;
        }

        return [];
    }

}