<?php

namespace App\Repository;

use App\Entity\ResetPasswordToken;
use App\Utils\Database;
use App\Utils\Utils;
use PDO;
use PDOException;

class ResetPasswordTokenRepository
{
    public static function save(ResetPasswordToken $token)
    {
        $tokenOld = $token->getLastSave();
        if (is_null($tokenOld)) {
            // The entity is not yet persisted on the database
            $dbConnection = Database::dbConnection();

            // Prepare query
            $insertQuery = Database::isSqlsrv() ?
                "INSERT INTO reset_password_token([token], [user_id], [created_at]) VALUES(:token, :user_id, :created_at)":
                "INSERT INTO `reset_password_token`(`token`, `user_id`, `created_at`) VALUES(:token, :user_id, :created_at)";
            $insertStatement = $dbConnection->prepare($insertQuery);

            // Data binding
            $insertStatement->bindValue(':token', $token->getToken(), PDO::PARAM_STR);
            $insertStatement->bindValue(':user_id', $token->getUserId(), PDO::PARAM_INT);
            $insertStatement->bindValue(':created_at', $token->getCreatedAt()->format('Y-m-d H:i:s'), PDO::PARAM_STR);

            // Execute and save
            try {
                $insertStatement->execute();
                Utils::log($insertStatement->queryString);
                $token->save($dbConnection->lastInsertId());
            } catch (\Exception $e) {
                Utils::log($e->getMessage());
                Utils::log($insertStatement->queryString);
                return null;
            }
            return $token;
        } else {
            // The entity already exists in database
            $dbConnection = Database::dbConnection();

            try {
                $query = (Database::isSqlsrv()?
                    "UPDATE [reset_password_token] SET":
                    "UPDATE `reset_password_token` SET");
                $querySet = [];
                $bindings = [];

                if ($token->getToken() !== $tokenOld->getToken()) {
                    $querySet[] = (Database::isSqlsrv() ? "[token] = :token" : "`token` = :token");
                    $bindings['token'] = [
                        'type' => PDO::PARAM_STR,
                        'value' => $token->getToken(),
                    ];
                }

                if ($token->getUserId() !== $tokenOld->getUserId()) {
                    $querySet[] = (Database::isSqlsrv() ? "[user_id] = :user_id" : "`user_id` = :user_id");
                    $bindings['user_id'] = [
                        'type' => PDO::PARAM_INT,
                        'value' => $token->getUserId(),
                    ];
                }

                if ($token->getCreatedAt() !== $tokenOld->getCreatedAt()) {
                    $querySet[] = (Database::isSqlsrv() ? "[created_at] = :created_at" : "`created_at` = :created_at");
                    $bindings['created_at'] = [
                        'type' => PDO::PARAM_STR,
                        'value' => $token->getCreatedAt()->format('Y-m-d H:i:s'),
                    ];
                }

                if (!empty($bindings)) {
                    $querySet[] = (Database::isSqlsrv() ? "[token_id] = :token_id" : " WHERE `token_id` = :token_id");
                    $bindings['token_id'] = [
                        'type' => PDO::PARAM_INT,
                        'value' => $token->getTokenId(),
                    ];

                    // Data binding
                    $updateStatement = $dbConnection->prepare($query . ' ' . implode(', ', $querySet));
                    foreach ($bindings as $key => $value)
                    {
                        $updateStatement->bindColumn(":$key", $value['value'], $value['type']);
                    }

                    Utils::log($query);
                    $updateStatement->execute();
                }
            } catch (PDOException $e)
            {
                Utils::log($e->getMessage());
                return null;
            }

            $token->save();
            return $token;
        }
    }
}