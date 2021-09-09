<?php
namespace App\Repository;

use App\Entity\Answer;
use App\Utils\Utils;
use App\Entity\User;
use App\Entity\Activity;
use PDO;
use PDOException;
use App\Utils\Database;
use DateTime;

class ActivityRepository { 

   public static function getActivity(User $user, $start_date, $end_date) { 

     $results=[];

     $connexion = Database::dbConnection();
     $query = (Database::isSqlsrv() ? "SELECT * FROM [activity] WHERE [user_id]=:user_id AND [timestamp_creation]>=:start_create AND [timestamp_creation]<=:end_date" : "SELECT * FROM activity WHERE `user_id`=:user_id AND timestamp_creation>=:start_create AND timestamp_creation<=:end_date");


     $statement = $connexion->prepare($query);

     
     if(!is_null($start_date)) {
        $statement->bindValue(':start_create', $start_date,PDO::PARAM_STR);
        $statement->bindValue(':end_date', $end_date,PDO::PARAM_STR);
     }

     if(!is_null($user)){
        $statement->bindValue(':user_id', $user->getUserId(),PDO::PARAM_STR);
     }

     $statement->execute();

     if($statement->rowCount()){
       $results = [];
       while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
         $results[] = $row;
       }
       return $results;
     }

    return $results;
   }


   public static function getLastActivityByUser(User $user) {
     $results=[];

      $connexion = Database::dbConnection();
      $query = (Database::isSqlsrv() ? "SELECT TOP 1 * FROM activity WHERE [user_id]=:user_id ORDER BY timestamp_creation DESC" : "SELECT TOP 1 * FROM activity WHERE `user_id`=:user_id ORDER BY timestamp_creation DESC");

      $statement = $connexion->prepare($query);

 
      if(!is_null($user)){
        $statement->bindValue(':user_id', $user->getUserId(),PDO::PARAM_STR);
     }

      $statement->execute();

      if($statement->rowCount()){
        $results = [];
        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
          $results[] = $row;
        }
        return $results;
      }
 
     return $results;      

   }


   public static function insertActivity($form) {


     $connexion = Database::dbConnection();
    
     $query = (Database::isSqlsrv()?"INSERT INTO activity ([activity_id],[user_id], [timestamp_creation], [type], [survey_id], [tip_id], [training_id]) VALUES(:activity_id,:user_id,:timestamp_creation,:type,:survey_id,:tip_id,:training_id)"
        :"INSERT INTO `activity` (`activity_id`,`user_id`, `timestamp_creation`, `type`, `survey_id`, `tip_id`, `training_id`) VALUES(:activity_id,:user_id,:timestamp_creation,:type,:survey_id,:tip_id,:training_id)");

     $statement = $connexion->prepare($query);  


     try {

        if(isset($form['activity_id'])) {
            $statement->bindValue(':activity_id', $form['activity_id'], PDO::PARAM_BOOL);
        }

        if(isset($form['user_id'])) {
          $statement->bindValue(':user_id', $form['user_id'], PDO::PARAM_BOOL);
        }  

        if(isset($form['timestamp_creation'])) {
          $statement->bindValue(':timestamp_creation', $form['timestamp_creation'], PDO::PARAM_BOOL);
        }  

        if(isset($form['type'])) {
          $statement->bindValue(':type', $form['type'], PDO::PARAM_BOOL);
        }  

        if(isset($form['survey_id'])) {
          $statement->bindValue(':survey_id', $form['survey_id'], PDO::PARAM_BOOL);
        }  

        if(isset($form['tip_id'])) {
         $statement->bindValue(':tip_id', $form['tip_id'], PDO::PARAM_BOOL);
        }  

        if(isset($form['training_id'])) {
         $statement->bindValue(':training_id', $form['training_id'], PDO::PARAM_BOOL);
        }  

        $statement->execute();
        return true;

     } catch(PDOException $e) {
        Utils::log( $e->getMessage());
        return null;
    }

   }

}