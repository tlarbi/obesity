CREATE TABLE [activity](
	[activity_id] [int] IDENTITY(1,1) NOT NULL,
	[user_id] [int] NULL,
	[timestamp_creation] [date] NOT NULL,
	[type] [varchar](max) NULL,
	[survey_id] [int] NULL,
	[tip_id] [int] NULL,
	[training_id] [int] NULL
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]