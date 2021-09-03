<?php

namespace App\Repository;
use App\Entity\Answer;
use App\Entity\Survey;
use App\Entity\User;
use App\Utils\Utils;
use PDO;
use PDOException;
use App\Utils\Database;
use DateTime;

class AnswerRepository {
    public static function save(Answer $answer): ?Answer{
        $answerOld = $answer->getLastSave();
        if(is_null($answerOld)){
            //New
            $conn = Database::dbConnection();
            $insert_query = Database::isSqlsrv()?
                "INSERT INTO answer([code],[list],[quantity],[survey_id]) VALUES(:code,:list,:quantity,:survey_id)":
                "INSERT INTO `answer`(`code`,`list`,`quantity`,`survey_id`) VALUES(:code,:list,:quantity,:survey_id)";

            $insert_stmt = $conn->prepare($insert_query);

            // DATA BINDING
            $insert_stmt->bindValue(':code', htmlspecialchars(strip_tags($answer->getCode())), PDO::PARAM_STR);
            $insert_stmt->bindValue(':list', htmlspecialchars(strip_tags($answer->getList())), PDO::PARAM_STR);
            $insert_stmt->bindValue(':quantity', $answer->getQuantity(), PDO::PARAM_INT);
            $insert_stmt->bindValue(':survey_id', $answer->getSurveyId(), PDO::PARAM_INT);

            $insert_stmt->execute();
            $answer->save($conn->lastInsertId());
            return $answer;
        }else{
            $conn = Database::dbConnection();
            try{
                $first = true;
                $query = Database::isSqlsrv()?
                    "UPDATE answer SET ":"UPDATE `answer` SET "
                ;
                $surveyId = ($answer->getSurveyId() == $answerOld->getSurveyId())?null:$answer->getSurveyId();
                $quantity = ($answer->getQuantity() == $answerOld->getQuantity())?null:$answer->getQuantity();

                $list = (strcmp($answer->getList() , $answerOld->getList())==0)?null:$answer->getList();
                $code = (strcmp($answer->getCode() , $answerOld->getCode())==0)?null:$answer->getCode();

                $date = ($answer->getCreatedAt() == $answerOld->getCreatedAt())?null:$answer->getCreatedAt();

                if(!is_null($surveyId)){
                    $query = $query . (Database::isSqlsrv()?"[survey_id] = :survey":"`survey_id` = :survey");
                    $first = false;
                }
                if(!is_null($quantity)){
                    if(!$first){
                        $query = $query . ",";
                    }
                    $query = $query . (Database::isSqlsrv()?"[quantity] = :quantity":"`quantity` = :quantity");
                    $first = false;
                }
                if(!is_null($list)){
                    if(!$first){
                        $query = $query . ",";
                    }
                    $query = $query . (Database::isSqlsrv()?"[list] = :list":"`list` = :list");
                    $first = false;
                }
                if(!is_null($code)){
                    if(!$first){
                        $query = $query . ",";
                    }
                    $query = $query . (Database::isSqlsrv()?"[code] = :code":"`code` = :code");
                    $first = false;
                }
                if(!is_null($date)){
                    if(!$first){
                        $query = $query . ",";
                    }
                    $query = $query . (Database::isSqlsrv()?"[date] = :date":"`date` = :date");
                    $first = false;
                }
                if($first)return $answer;
                $query = $query . (Database::isSqlsrv()?" WHERE [answer_id] = :id":" WHERE `answer_id` = :id");
                $stmt = $conn->prepare($query);
                // DATA BINDING

                if(!is_null($surveyId)){
                    $stmt->bindValue(':survey', $surveyId,PDO::PARAM_INT);
                }
                if(!is_null($quantity)){
                    $stmt->bindValue(':quantity', $quantity,PDO::PARAM_INT);
                }
                if(!is_null($list)){
                    $stmt->bindValue(':list', $list,PDO::PARAM_STR);
                }
                if(!is_null($code)){
                    $stmt->bindValue(':code', $code,PDO::PARAM_STR);
                }
                if(!is_null($date)){
                    $stmt->bindValue(':date', $date,PDO::PARAM_STR);
                }
                $stmt->bindValue(':id', $answer->getAnswerId(), PDO::PARAM_INT);
                $stmt->execute();
            }catch(PDOException $e){
                Utils::log( $e->getMessage());
                return null;
            }
            $answer->save();
            return $answer;
        }
    }
    public static function findByUser(User $user): ?array{
        $conn = Database::dbConnection();
        try{
            $fetch = (Database::isSqlsrv()?"SELECT * FROM answer WHERE [user_id]=:id":"SELECT * FROM `answer` WHERE `user_id`=:id");
            $stmt = $conn->prepare($fetch);
            $stmt->bindValue(':id', $user->getUserId(),PDO::PARAM_STR);
            $stmt->execute();

            if($stmt->rowCount()){
                $result = [];
                while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                    $answer = Answer::parse($row);
                    if(!is_null($answer)){
                        array_push($result, $answer);
                    }
                }
                return $result;
            }
        }catch(PDOException $e){
            Utils::log( $e->getMessage());
            return null;
        }
        return null;
    }
    public static function getOne($id): ?Answer{
        $conn = Database::dbConnection();
        try{
            $fetch = (Database::isSqlsrv()?"SELECT * FROM answer WHERE [answer_id]=:id":"SELECT * FROM `answer` WHERE `answer_id`=:id");
            $stmt = $conn->prepare($fetch);
            $stmt->bindValue(':id', $id,PDO::PARAM_INT);
            $stmt->execute();
            if($stmt->rowCount()){
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                return Answer::parse($row);
            }
        }catch(PDOException $e){
            Utils::log( $e->getMessage());
            return null;
        }
        return null;
    }
    public static function findBySurveyInAndCodeAndCreatedAtAfterAndCreatedAtBefore(array $survey, ?string $type, ?DateTime $after, ?DateTime $before): ?array{

        $conn = Database::dbConnection();
        try{
            $fetch = (Database::isSqlsrv()?"SELECT * FROM answer":"SELECT * FROM `answer`");
            $value = false;
            if(!is_null($survey)){
                $fetch = $fetch . (Database::isSqlsrv()?" WHERE [survey_id] IN (":" WHERE `survey_id` IN (").str_pad('',count($survey)*2-1,'?,').")";
                $value = true;
            }
            if(!is_null($type)){
                if($value){
                    $fetch = $fetch . " AND ";
                }else{
                    $fetch = $fetch . " WHERE ";
                }
                $fetch = $fetch . (Database::isSqlsrv()?"[code] = :code":"`code` = :code");
                $value = true;
            }
            if(!is_null($after)){
                if($value){
                    $fetch = $fetch . " AND ";
                }else{
                    $fetch = $fetch . " WHERE ";
                }
                $fetch = $fetch . (Database::isSqlsrv()?"[created_at] > :start":"`created_at` > :start");
                $value = true;
            }
            if(!is_null($before)){
                if($value){
                    $fetch = $fetch . " AND ";
                }else{
                    $fetch = $fetch . " WHERE ";
                }
                $fetch = $fetch . (Database::isSqlsrv()?"[created_at] < :end":"`created_at` < :end");
//                $value = true;
            }

            $query_stmt = $conn->prepare($fetch);

            if(!is_null($type)){
                $query_stmt->bindValue(':code', $type,PDO::PARAM_STR);
            }
            if(!is_null($after)){
                $query_stmt->bindValue(':start', $after->format("Y-m-d H:i:s"),PDO::PARAM_STR);
            }
            if(!is_null($before)){
                $query_stmt->bindValue(':end', $before->format("Y-m-d H:i:s"),PDO::PARAM_STR);
            }
            if(is_null($survey)) {
                Utils::log($fetch);
                $query_stmt->execute();
            }else{
                $result = [];
                foreach ($survey as $item){
                    if(is_null($item)){
                        array_push($result, 0);
                        continue;
                    }elseif ($item instanceof Survey){
                        array_push($result, $item->getSurveyId());
                    }else{
                        array_push($result, $item);
                    }
                }
                Utils::log($fetch);
                $query_stmt->execute($result);
            }
            if($query_stmt->rowCount()){
                $result = [];
                while($row = $query_stmt->fetch(PDO::FETCH_ASSOC)){
                    $answer = Answer::parse($row);
                    if(!is_null($answer)){
                        array_push($result, $answer);
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
    public static function findBySurveyAndCodeAndCreatedAtAfterAndCreatedAtBefore($survey, ?string $type, ?DateTime $after, ?DateTime $before): ?array{

        if(!is_null($survey)){
            if($survey instanceof Survey){
                $survey = $survey->getSurveyId();
            }else if(!is_int($survey)){
                $survey = null;
            }
        }
        $conn = Database::dbConnection();
        try{
            $fetch = (Database::isSqlsrv()?"SELECT * FROM answer":"SELECT * FROM `answer`");
            $value = false;
            if(!is_null($survey)){
                $fetch = $fetch . (Database::isSqlsrv()?" WHERE [survey_id] = :survey":" WHERE `survey_id` = :survey");
                $value = true;
            }
            if(!is_null($type)){
                if($value){
                    $fetch = $fetch . " AND ";
                }else{
                    $fetch = $fetch . " WHERE ";
                }
                $fetch = $fetch . (Database::isSqlsrv()?"[code] = :code":"`code` = :code");
                $value = true;
            }
            if(!is_null($after)){
                if($value){
                    $fetch = $fetch . " AND ";
                }else{
                    $fetch = $fetch . " WHERE ";
                }
                $fetch = $fetch . (Database::isSqlsrv()?"[created_at] > :start":"`created_at` > :start");
                $value = true;
            }
            if(!is_null($before)){
                if($value){
                    $fetch = $fetch . " AND ";
                }else{
                    $fetch = $fetch . " WHERE ";
                }
                $fetch = $fetch . (Database::isSqlsrv()?"[created_at] < :end":"`created_at` < :end");
//                $value = true;
            }
            $query_stmt = $conn->prepare($fetch);

            if(!is_null($survey)){
                $query_stmt->bindValue(':survey', $survey,PDO::PARAM_INT);
            }
            if(!is_null($type)){
                $query_stmt->bindValue(':code', $type,PDO::PARAM_STR);
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
                    $answer = Answer::parse($row);
                    if(!is_null($answer)){
                        array_push($result, $answer);
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