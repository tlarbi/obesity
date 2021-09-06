<?php
namespace App\Repository;

use App\Entity\Answer;
use App\Utils\Utils;
use PDO;
use PDOException;
use App\Utils\Database;
use DateTime;

class ActivityRepository { 

   public static function getActivity($start_date, $end_date) { 

     $results=[];

     $connexion = Database::dbConnection();
     $query = (Database::isSqlsrv() ? "SELECT * FROM [activity] WHERE [timestamp_creation]>=:start_create AND [timestamp_creation]<=:end_date" : "SELECT * FROM activity WHERE timestamp_creation>=:start_create AND timestamp_creation<=:end_date");


     $statement = $connexion->prepare($query);

     if(!is_null($start_date)) {
        $statement->bindValue(':start_create', $start_date,PDO::PARAM_STR);
        $statement->bindValue(':end_date', $end_date,PDO::PARAM_STR);
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

}