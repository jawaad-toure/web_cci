DROP TABLE IF EXISTS ranking;
DROP TABLE IF EXISTS matches;
DROP TABLE IF EXISTS teams;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
   id INTEGER PRIMARY KEY AUTOINCREMENT,
   email VARCHAR(128),
   password_hash VARCHAR(128),
   UNIQUE (email)
);

CREATE TABLE teams (
   id INTEGER PRIMARY KEY AUTOINCREMENT,
   name VARCHAR(50) NOT NULL
);

CREATE TABLE matches (
   id INTEGER PRIMARY KEY AUTOINCREMENT,
   team0 INTEGER NOT NULL,
   team1 INTEGER NOT NULL,
   score0 INTEGER NOT NULL,
   score1 INTEGER NOT NULL,
   date DATETIME,
   FOREIGN KEY (team0) REFERENCES teams (id),
   FOREIGN KEY (team1) REFERENCES teams (id),
   UNIQUE (team0, team1)
);

CREATE TABLE ranking (
   team_id INTEGER PRIMARY KEY,
   rank INTEGER,
   match_played_count INTEGER,
   match_won_count INTEGER,
   match_lost_count INTEGER,
   draw_count INTEGER,
   goal_for_count INTEGER,
   goal_against_count INTEGER,
   goal_difference INTEGER,
   points INTEGER,
   FOREIGN KEY (team_id) REFERENCES teams (id)
   UNIQUE (rank)
);