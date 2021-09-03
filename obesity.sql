DROP DATABASE IF EXISTS `obesitydb`;
CREATE DATABASE IF NOT EXISTS `obesitydb` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `obesitydb`;

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user` (
     `user_id` bigint NOT NULL AUTO_INCREMENT,
     `current_token` varchar(1024) DEFAULT NULL,
     `username` varchar(255) DEFAULT NULL,
--     `email` varchar(320) NOT NULL COLLATE ascii_general_ci UNIQUE,
--     `first_name` varchar(255) NOT NULL,
--     `last_name` varchar(255) NOT NULL,
    `password` varchar(255) DEFAULT NULL,
    `code` varchar(31) NOT NULL
--     `birth_date` date NOT NULL,
--     `date_last_update_pwd` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `enabled` tinyint(1) NOT NULL DEFAULT false,
--     `must_change_pwd` tinyint(1) NOT NULL DEFAULT false,
--     `validated` tinyint(1) NOT NULL DEFAULT false,
    PRIMARY KEY (`user_id`)
    ) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `user_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_settings` (
                                 `setting_id` bigint NOT NULL AUTO_INCREMENT,
                                 `user_id` bigint DEFAULT NULL,
                                 `device_token` varchar(255) DEFAULT NULL,
                                 `locale` char(5) NOT NULL DEFAULT 'fr_FR',
                                 `wakeup` char(5) NOT NULL DEFAULT '08:00',
                                 `sleep` char(5) NOT NULL DEFAULT '22:00',
                                 `avatar` smallint DEFAULT NULL,
                                 `time_code` smallint NOT NULL DEFAULT 0,
                                 `intention_code` VARCHAR(63) DEFAULT NULL NULL,
                                 `intention_text` VARCHAR(511) DEFAULT NULL NULL,
                                 PRIMARY KEY (`setting_id`),
    UNIQUE KEY `UK_d4tg5xqd19ukcwnmfb72s4x77` (`user_id`)
    ) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `survey`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `survey` (
                          `survey_id` bigint NOT NULL AUTO_INCREMENT,
                          `code` varchar(31) NOT NULL,
    `state` smallint NOT NULL DEFAULT 0,
    `from` datetime DEFAULT NULL,
    `to` datetime DEFAULT NULL,
    `started` datetime DEFAULT NULL,
    `ended` datetime DEFAULT NULL,
    `user_id` bigint NOT NULL,
    PRIMARY KEY (`survey_id`),
    KEY `FK51x6iogwvw5n6pa7sl339ltju` (`user_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `answer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `answer` (
    `answer_id` bigint NOT NULL AUTO_INCREMENT,
    `code` varchar(31) NOT NULL,
    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `list` varchar(255) DEFAULT NULL,
    `quantity` smallint DEFAULT NULL,
    `survey_id` bigint NOT NULL,
    PRIMARY KEY (`answer_id`),
    KEY `FK9mw9ejkvxg91xnpxcg6pljbn2` (`survey_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


/* SQLINES DEMO *** cs_client     = @@character_set_client */;
/* SQLINES DEMO *** er_set_client = utf8mb4 */;
CREATE TABLE snooze (
    `snooze_id` bigint NOT NULL AUTO_INCREMENT,
    `start` datetime NOT NULL,
    `end` datetime NOT NULL,
    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `removed_at` datetime DEFAULT NULL,
    `repeat` smallint NOT NULL DEFAULT 0,
    `user_id` bigint NOT NULL,
    PRIMARY KEY (`snooze_id`),
    KEY `FK1az9ejkvxg91xnpxcg6pljbn2` (`user_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE reset_password_token
(
    `token_id` BIGINT AUTO_INCREMENT UNIQUE PRIMARY KEY ,
    `token` VARCHAR(8) NOT NULL,
    `user_id` BIGINT NOT NULL,
    `created_at` DATETIME NOT NULL,
    CONSTRAINT reset_password_token_user_user_id_fk
        FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

ALTER TABLE `obesitydb`.`snooze`
ADD CONSTRAINT `snooze_id_key`
  FOREIGN KEY (`snooze_id`)
  REFERENCES `obesitydb`.`user` (`user_id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE;

ALTER TABLE `obesitydb`.`answer`
ADD CONSTRAINT `survey_id_key`
  FOREIGN KEY (`survey_id`)
  REFERENCES `obesitydb`.`survey` (`survey_id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE;

ALTER TABLE `obesitydb`.`survey`
ADD CONSTRAINT `survey_user_key`
  FOREIGN KEY (`user_id`)
  REFERENCES `obesitydb`.`user` (`user_id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE;

ALTER TABLE `obesitydb`.`user_settings`
ADD CONSTRAINT `setting_user_key`
  FOREIGN KEY (`user_id`)
  REFERENCES `obesitydb`.`user` (`user_id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE;