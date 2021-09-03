USE [obesitydb];

CREATE TABLE "user" (
    [user_id] bigint NOT NULL IDENTITY,
    [current_token] varchar(1024) DEFAULT NULL,
    [code] varchar(31) NOT NULL,
    [username] varchar(255)  DEFAULT NULL,
    [password] varchar(255)  DEFAULT NULL,
    [enabled] smallint NOT NULL DEFAULT 0,
    PRIMARY KEY ([user_id])
    )   ;
CREATE TABLE user_settings (
	[setting_id] bigint NOT NULL IDENTITY,
	[user_id] bigint DEFAULT NULL,
	[device_token] varchar(255) DEFAULT NULL,
	[avatar] smallint DEFAULT NULL,
	[locale] char(5) NOT NULL DEFAULT 'en_GB',
	[wakeup] char(5) NOT NULL DEFAULT '08:00',
	[sleep] char(5) NOT NULL DEFAULT '22:00',
	[time_code] smallint NOT NULL DEFAULT 0,
	[intention_code] VARCHAR(63) DEFAULT NULL,
	[intention_text] VARCHAR(511) DEFAULT NULL,
	PRIMARY KEY ([setting_id]),
    CONSTRAINT [UK_d4tg5xqd19ukcwnmfb72s4x77] UNIQUE  ([user_id])
    )   ;

CREATE TABLE survey (
    [survey_id] bigint NOT NULL IDENTITY,
    [user_id] bigint NOT NULL,
    [code] varchar(31) NOT NULL,
    [from] datetime2(0) DEFAULT NULL,
    [to] datetime2(0) DEFAULT NULL,
    [started] datetime2(0) DEFAULT NULL,
    [ended] datetime2(0) DEFAULT NULL,
    [state] smallint NOT NULL DEFAULT 0,
    PRIMARY KEY ([survey_id])
    )  ;

CREATE INDEX [FK51x6iogwvw5n6pa7sl339ltju] ON survey ([user_id]);
/* SQLINES DEMO *** cs_client     = @@character_set_client */;
/* SQLINES DEMO *** er_set_client = utf8mb4 */;
CREATE TABLE answer (
    [answer_id] bigint NOT NULL IDENTITY,
    [code] varchar(31) NOT NULL,
    [created_at] datetime2(0) NOT NULL DEFAULT GETDATE(),
    [list] varchar(255) DEFAULT NULL,
    [quantity] smallint DEFAULT NULL,
    [survey_id] bigint NOT NULL,
    PRIMARY KEY ([answer_id])
    )  ;

CREATE INDEX [FK9mw9ejkvxg91xnpxcg6pljbn2] ON answer ([survey_id]);

/* SQLINES DEMO *** cs_client     = @@character_set_client */;
/* SQLINES DEMO *** er_set_client = utf8mb4 */;
CREATE TABLE snooze (
    [snooze_id] bigint NOT NULL IDENTITY,
    [start] datetime2(0) NOT NULL,
    [end] datetime2(0) NOT NULL,
    [created_at] datetime2(0) NOT NULL DEFAULT GETDATE(),
    [removed_at] datetime2(0) DEFAULT NULL,
    [repeat] smallint NOT NULL DEFAULT 0,
    [user_id] bigint NOT NULL,
    PRIMARY KEY ([snooze_id])
    )  ;

CREATE INDEX [FK1az9ejkvxg91xnpxcg6pljbn2] ON snooze ([user_id]);

CREATE TABLE reset_password_token
(
    [token_id] BIGINT UNIQUE PRIMARY KEY IDENTITY,
    [token] VARCHAR(8) NOT NULL,
    [user_id] BIGINT NOT NULL,
    [created_at] DATETIME NOT NULL,
    CONSTRAINT reset_password_token_user_user_id_fk
        FOREIGN KEY ([user_id]) REFERENCES [user] ([user_id])
    );

ALTER TABLE [snooze]
ADD CONSTRAINT [snooze_id_key]
  FOREIGN KEY ([snooze_id])
  REFERENCES "user" ([user_id])
  ON DELETE CASCADE
  ON UPDATE CASCADE;

ALTER TABLE [answer]
ADD CONSTRAINT [survey_id_key]
  FOREIGN KEY ([survey_id])
  REFERENCES survey ([survey_id])
  ON DELETE CASCADE
  ON UPDATE CASCADE;

ALTER TABLE [survey]
ADD CONSTRAINT [survey_user_key]
  FOREIGN KEY ([user_id])
  REFERENCES "user" ([user_id])
  ON DELETE CASCADE
  ON UPDATE CASCADE;

ALTER TABLE [user_settings]
ADD CONSTRAINT [setting_user_key]
  FOREIGN KEY ([user_id])
  REFERENCES "user" ([user_id])
  ON DELETE CASCADE
  ON UPDATE CASCADE;