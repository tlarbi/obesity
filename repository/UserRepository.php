<?php

namespace App\Repository;

use App\Entity\User;
use App\Utils\ConfigUtils;
use App\Utils\Utils;
use PDO;
use PDOException;
use App\Utils\Database;

class UserRepository
{
    public static function save(User $user): ?User
    {
        $old = $user->getLastSave();
        $conn = Database::dbConnection();
        try {
            //TODO for all allow null as value
            $first = true;
            $query = (Database::isSqlsrv() ? "UPDATE [user] SET "
                : "UPDATE `user` SET ");
            if (is_null($old)) {
                $current_token = $user->getCurrentToken();
                $username = $user->getUsername();
                $password = $user->getPassword();
                $enabled = $user->getEnabled();
            } else {
                $current_token = (strcmp($user->getCurrentToken(), $old->getCurrentToken()) === 0) ? null : $user->getCurrentToken();
                $username = (strcmp($user->getUsername(), $old->getUsername()) === 0) ? null : $user->getUsername();
                $password = (strcmp($user->getPassword(), $old->getPassword()) === 0) ? null : $user->getPassword();
                $enabled = ($user->getEnabled() == $old->getEnabled()) ? null : $user->getEnabled();
            }

            if (!is_null($current_token)) {
                if (!$first) {
                    $query = $query . ",";
                }
                $query = $query . (Database::isSqlsrv() ? "[current_token] = :current_token" : "`current_token` = :current_token");
                $first = false;
            }
            if (!is_null($username)) {
                if (!$first) {
                    $query = $query . ",";
                }
                $query = $query . (Database::isSqlsrv() ? "[username] = :username" : "`username` = :username");
                $first = false;
            }
            if (!is_null($password)) {
                if (!$first) {
                    $query = $query . ",";
                }
                $query = $query . (Database::isSqlsrv() ? "[password] = :password" : "`password` = :password");
                $first = false;
            }
            if (!is_null($enabled)) {
                if (!$first) {
                    $query = $query . ",";
                }
                $query = $query . (Database::isSqlsrv() ? "[enabled] = :enabled" : "`enabled` = :enabled");
                $first = false;
            }
            if ($first) return $user;
            $query = $query . (Database::isSqlsrv() ? " WHERE [user_id] = :id" : " WHERE `user_id` = :id");
            Utils::log($query);
            $stmt = $conn->prepare($query);
            // DATA BINDING

            if (!is_null($current_token)) {
                $stmt->bindValue(':current_token', $current_token, PDO::PARAM_STR);
            }
            if (!is_null($username)) {
                $stmt->bindValue(':username', $username, PDO::PARAM_STR);
            }
            if (!is_null($password)) {
                $stmt->bindValue(':password', $password, PDO::PARAM_STR);
            }
            if (!is_null($enabled)) {
                $stmt->bindValue(':enabled', $enabled, PDO::PARAM_BOOL);
            }
            $stmt->bindValue(':id', $user->getUserId(), PDO::PARAM_INT);
            $stmt->execute();
        } catch (PDOException $e) {
            Utils::log($e->getMessage());
            return null;
        }
        $user->save();
        return $user;
    }

    public static function remove(User $user): void
    {
        $conn = Database::dbConnection();
        try {
            $fetch_user_by_email = (Database::isSqlsrv() ? "DELETE FROM [user] WHERE [user_id]=:id" : "DELETE FROM `user` WHERE `user_id`=:id");
            $query_stmt = $conn->prepare($fetch_user_by_email);
            $query_stmt->bindValue(':id', $user->getUserId(), PDO::PARAM_STR);
            $query_stmt->execute();
        } catch (PDOException $e) {
            Utils::log($e->getMessage());
        }
    }

    public static function getOne($id): ?User
    {
        return User::parse(self::getOneRaw($id));
    }

    public static function canRegister($id)
    {
        $tmp = self::getOneRawCode($id);
        if (is_null($tmp) || isset($tmp["username"])) {
            return -1;
        }
        return $tmp["user_id"];
    }

    private static function getOneRaw($id): ?array
    {
        $conn = Database::dbConnection();
        try {
            $fetch_user_by_email = (Database::isSqlsrv() ? "SELECT * FROM [user] WHERE [user_id]=:id" : "SELECT * FROM `user` WHERE `user_id`=:id");
            $query_stmt = $conn->prepare($fetch_user_by_email);
            $query_stmt->bindValue(':id', $id, PDO::PARAM_INT);
            Utils::log($query_stmt->queryString);
            $query_stmt->execute();

            if ($query_stmt->rowCount()) {
                $row = $query_stmt->fetch(PDO::FETCH_ASSOC);
                return $row;
            }
        } catch (PDOException $e) {
            Utils::log($e->getMessage());
            return null;
        }
        return null;
    }

    private static function getOneRawCode($code): ?array
    {
        $conn = Database::dbConnection();
        try {
            $fetch_user_by_email = (Database::isSqlsrv() ? "SELECT * FROM [user] WHERE [code]=:code" : "SELECT * FROM `user` WHERE `code`=:code");
            $query_stmt = $conn->prepare($fetch_user_by_email);
            $query_stmt->bindValue(':code', $code, PDO::PARAM_INT);
            Utils::log($query_stmt->queryString);
            $query_stmt->execute();

            if ($query_stmt->rowCount()) {
                $row = $query_stmt->fetch(PDO::FETCH_ASSOC);
                return $row;
            }
        } catch (PDOException $e) {
            Utils::log($e->getMessage());
            return null;
        }
        return null;
    }

    public static function getPage(int $page): ?array
    {
        $conn = Database::dbConnection();
        try {
            if ($page < 1) $page = 1;
            $offset = ($page - 1) * 50;
            $end = ($page * 50) + 1;
            //TODO this cause issue if 50 user was deleted but work on all sql server
            $fetch_user_by_email = (Database::isSqlsrv() ? "SELECT * FROM [user] WHERE [user_id] > :begin AND [user_id] < :end" : "SELECT * FROM `user` WHERE `user_id` > :begin AND `user_id` < :end");
            $query_stmt = $conn->prepare($fetch_user_by_email);
            $query_stmt->bindValue(':begin', $offset, PDO::PARAM_INT);
            $query_stmt->bindValue(':end', $end, PDO::PARAM_INT);
            Utils::log($query_stmt->queryString);
            $query_stmt->execute();

            if ($query_stmt->rowCount()) {
                $result = [];
                while ($row = $query_stmt->fetch(PDO::FETCH_ASSOC)) {
                    $answer = User::parse($row);
                    if (!is_null($answer)) {
                        array_push($result, $answer);
                    }
                }
                return $result;
            }
            return [];
        } catch (PDOException $e) {
            Utils::log($e->getMessage());
//            $returnData = msg(0,500,$e->getMessage());
        }
        return null;
    }

    public static function findByUsername(string $username): ?User
    {
        $conn = Database::dbConnection();
        try {
            $fetch_user_by_email = (Database::isSqlsrv() ? "SELECT * FROM [user] WHERE [username]=:username" : "SELECT * FROM `user` WHERE `username`=:username");
            $query_stmt = $conn->prepare($fetch_user_by_email);
            $query_stmt->bindValue(':username', $username, PDO::PARAM_STR);
            $query_stmt->execute();

            // IF THE USER IS FOUNDED BY EMAIL
            if ($query_stmt->rowCount()) {
                $row = $query_stmt->fetch(PDO::FETCH_ASSOC);
                return User::parse($row);
            }
        } catch (PDOException $e) {
            Utils::log($e->getMessage());
            return null;
        }
        return null;
    }

    public static function findByResetToken(string $token, string $username): ?User
    {
        $conn = Database::dbConnection();
        try {
            $query = (Database::isSqlsrv() ?
                "SELECT * FROM [user] u INNER JOIN [reset_password_token] rp ON u.user_id = rp.user_id WHERE u.username = :username AND rp.token = :token AND rp.created_at >= :date" :
                "SELECT * FROM `user` u INNER JOIN `reset_password_token` rp ON u.user_id = rp.user_id WHERE u.username = :username AND `rp`.token = :token AND `rp`.created_at >= :date");
            $query_stmt = $conn->prepare($query);
            $query_stmt->bindValue(':username', $username, PDO::PARAM_STR);
            $query_stmt->bindValue(':token', $token, PDO::PARAM_STR);
            $query_stmt->bindValue(':date', (new \DateTime('- ' . ConfigUtils::getProperty('RESET_TOKEN_VALIDITY')))->format('Y-m-d H:i:s'), PDO::PARAM_STR);
            $query_stmt->execute();

            if ($query_stmt->rowCount()) {
                $row = $query_stmt->fetch(PDO::FETCH_ASSOC);
                return User::parse($row);
            }
        } catch (PDOException $e) {
            Utils::log($e->getMessage());
            return null;
        }
        return null;
    }
}