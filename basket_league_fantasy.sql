USE basket_league_fantasy;

CREATE TABLE Account (
	UserID BIGINT UNSIGNED AUTO_INCREMENT,
	Username VARCHAR(30) NOT NULL,
	Password VARCHAR(30) NOT NULL,
	Email VARCHAR(50) NOT NULL,
	Reg_date TIMESTAMP,
	PRIMARY KEY(UserID)
);

CREATE TABLE Team (
	TeamID BIGINT UNSIGNED AUTO_INCREMENT,
	TeamName VARCHAR(30) NOT NULL,
	UserID BIGINT UNSIGNED,
	RemainingMoney DECIMAL(5,2),
	PRIMARY KEY(TeamID),
	FOREIGN KEY(UserID)
	  REFERENCES Account(UserID)
	  ON DELETE CASCADE
);

CREATE TABLE Player (
	PlayerID INT UNSIGNED AUTO_INCREMENT,
	FirstName VARCHAR(30)NOT NULL,
	LastName VARCHAR(30) NOT NULL,
	Price DECIMAL(5,2),
	TeamName VARCHAR(30) NOT NULL,
	Position VARCHAR(10) NOT NULL,
	LastWeekScore DECIMAL(3,1),
	PRIMARY KEY(PlayerID)
);

CREATE TABLE TeamPlayer (
	TeamID BIGINT UNSIGNED,
	PlayerID INT UNSIGNED,
	PurchasePrice DECIMAL(5,2),
	PRIMARY KEY(TeamID, PlayerID),
	FOREIGN KEY(TeamID)
	  REFERENCES Team(TeamID)
	  ON DELETE CASCADE,
	FOREIGN KEY(PlayerID)
	  REFERENCES Player(PlayerID)
	  ON DELETE CASCADE
);

CREATE TABLE League (
	LeagueID BIGINT UNSIGNED AUTO_INCREMENT,
	LeagueName VARCHAR(30) NOT NULL,
	LeaguePassword VARCHAR(30) NOT NULL,
	NumberOfTeams INT UNSIGNED,
	PRIMARY KEY(LeagueID)
);

CREATE TABLE TeamLeague (
	TeamID BIGINT UNSIGNED,
	LeagueID BIGINT UNSIGNED,
	ScoreOnLeague DECIMAL(5,1),
	PRIMARY KEY(TeamID, LeagueID),
	FOREIGN KEY(TeamID)
	  REFERENCES Team(TeamID)
	  ON DELETE CASCADE,
	FOREIGN KEY(LeagueID)
	  REFERENCES League(LeagueID)
	  ON DELETE CASCADE
);
