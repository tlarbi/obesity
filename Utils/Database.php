<?php
namespace App\Utils;
use PDO;
use PDOException;

class Database{
    private static $isSqlsrv = true;
    private static $db_host = '54.234.164.63, 1433';
    private static $db_name = 'obesitydb';
    private static $db_username = 'SA';
    private static $db_password = 'Admin007';

    public static function dbConnection() : ?PDO{
        try{
            if(self::isSqlsrv()){
                $conn = new PDO('sqlsrv:Server='.self::$db_host.';Database='.self::$db_name,self::$db_username,self::$db_password);
            }else{
                $conn = new PDO('mysql:host='.self::$db_host.';dbname='.self::$db_name,self::$db_username,self::$db_password);
            }
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $conn;
        }catch(PDOException $e){
            Utils::log("Connection error ".$e->getMessage());
            exit;
        }
    }
    public static function isSqlsrv():bool{
        return self::$isSqlsrv;
    }
}
