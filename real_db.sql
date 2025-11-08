CREATE OR REPLACE TABLE USERS(
	USER_ID INT AUTO_INCREMENT,
	F_NAME VARCHAR(50),
	L_NAME VARCHAR(50),
	COMP_ID VARCHAR(7),
	PASSWORD VARCHAR(255),
	YEAR INT,
	PRIMARY KEY (USER_ID)
);

CREATE OR REPLACE TABLE EXERCISE_TYPE (
	EXERCISE_ID INT AUTO_INCREMENT,
	EXERCISE_NAME VARCHAR(100),
	PRIMARY KEY (EXERCISE_ID)
);

CREATE OR REPLACE TABLE EXERCISE_INSTANCE (
	INSTANCE_ID INT AUTO_INCREMENT,
	EXERCISE_ID INT,
	DATE DATETIME,
	PRIMARY KEY(INSTANCE_ID),
	FOREIGN KEY (EXERCISE_ID) REFERENCES EXERCISE_TYPE(EXERCISE_ID)
);

CREATE OR REPLACE TABLE WORKOUT_SESSION (
	SESSION_ID INT AUTO_INCREMENT,
	DATE DATETIME,
	LOCATION VARCHAR(120),
	PRIMARY KEY (SESSION_ID)
);

CREATE OR REPLACE TABLE HAS (
	INSTANCE_ID INT,
	SESSION_ID INT,
	PRIMARY KEY (INSTANCE_ID, SESSION_ID),
	FOREIGN KEY (INSTANCE_ID) REFERENCES EXERCISE_INSTANCE(INSTANCE_ID),
	FOREIGN KEY(SESSION_ID) REFERENCES WORKOUT_SESSION(SESSION_ID)
);

CREATE OR REPLACE TABLE WORKOUT_GROUP (
	GROUP_ID INT AUTO_INCREMENT,
	CREATOR_ID INT,
	GROUP_NAME VARCHAR(100),
	NUMBER_OF_MEMBERS INT,
	PRIMARY KEY (GROUP_ID),
	FOREIGN KEY (CREATOR_ID) REFERENCES USERS(USER_ID)
);

-- ADVANCED SQL CHECK
CREATE OR REPLACE TABLE STRENGTH (
	INSTANCE_ID INT,
	WEIGHT FLOAT CHECK (WEIGHT > 0),
	SETS INT CHECK (SETS > 0),
	REPS INT CHECK (REPS > 0),
	PRIMARY KEY(INSTANCE_ID)
);

CREATE OR REPLACE TABLE CARDIO (
	INSTANCE_ID INT,
	DISTANCE FLOAT,
	DURATION FLOAT,
	PRIMARY KEY(INSTANCE_ID)
);

CREATE OR REPLACE TABLE FRIENDSHIP (
	USER_ID1 INT,
	USER_ID2 INT,
	PRIMARY KEY (USER_ID1, USER_ID2),
	FOREIGN KEY (USER_ID1) REFERENCES USERS(USER_ID),
	FOREIGN KEY (USER_ID2) REFERENCES USERS(USER_ID)
);

CREATE OR REPLACE TABLE JOINS (
	USER_ID INT,
	GROUP_ID INT,
	PRIMARY KEY (USER_ID, GROUP_ID),
	FOREIGN KEY (GROUP_ID) REFERENCES WORKOUT_GROUP(GROUP_ID),
	FOREIGN KEY (USER_ID) REFERENCES USERS(USER_ID)
);

CREATE OR REPLACE TABLE LOG_WORKOUT (
	USER_ID INT,
	SESSION_ID INT,
	PRIMARY KEY(USER_ID, SESSION_ID),
	FOREIGN KEY (USER_ID) REFERENCES USERS(USER_ID),
	FOREIGN KEY (SESSION_ID) REFERENCES WORKOUT_SESSION(SESSION_ID)
);


-- INSERT USER DATA
INSERT INTO users (f_name, l_name, comp_id, password, year) VALUES ("Alston", "Hou", "epe9ev", "alston123", 2026);
INSERT INTO users (f_name, l_name, comp_id, password, year) VALUES ("Adnan", "Murtaza", "enn6qy", "adnan123", 2026);
INSERT INTO users (f_name, l_name, comp_id, password, year) VALUES ("Ben", "Chang", "bnw6yx", "ben123", 2026);
INSERT INTO users (f_name, l_name, comp_id, password, year) VALUES ("Kenny", "Nguyen", "vrv6sf", "kenny123", 2026);

-- INSERT EXERCISE TYPES
INSERT INTO exercise_type (EXERCISE_NAME) VALUES ("Pull Ups");
INSERT INTO exercise_type (EXERCISE_NAME) VALUES ("Bench Press");
INSERT INTO exercise_type (EXERCISE_NAME) VALUES ("Running");
INSERT INTO exercise_type (EXERCISE_NAME) VALUES ("Swimming");

-- INSERT EXERCISE INSTANCES
INSERT INTO exercise_instance (exercise_id, `date`) VALUES (1, '2025-10-26 22:05:00');
INSERT INTO exercise_instance (exercise_id, `date`) VALUES (2, '2025-10-26 22:10:00');
INSERT INTO exercise_instance (exercise_id, `date`) VALUES (1, '2025-10-24 15:05:00');
INSERT INTO exercise_instance (exercise_id, `date`) VALUES (2, '2025-10-24 15:10:00');
INSERT INTO exercise_instance (exercise_id, `date`) VALUES (2, '2025-10-23 14:05:00');
INSERT INTO exercise_instance (exercise_id, `date`) VALUES (2, '2025-10-21 13:05:00');
INSERT INTO exercise_instance (exercise_id, `date`) VALUES (4, '2025-10-23 14:10:00');
INSERT INTO exercise_instance (exercise_id, `date`) VALUES (3, '2025-10-21 13:10:00');

-- INSERT STRENGTH INSTANCES
INSERT INTO strength (instance_id, weight, sets, reps) VALUES (1, 25, 3, 10);
INSERT INTO strength (instance_id, weight, sets, reps) VALUES (2, 225, 3, 5);
INSERT INTO strength (instance_id, weight, sets, reps) VALUES (3, 10, 3, 6);
INSERT INTO strength (instance_id, weight, sets, reps) VALUES (4, 135, 3, 5);
INSERT INTO strength (instance_id, weight, sets, reps) VALUES (5, 135, 3, 5);
INSERT INTO strength (instance_id, weight, sets, reps) VALUES (6, 200, 4, 5);

-- INSERT CARDIO INSTANCES
INSERT INTO cardio (instance_id, distance, duration) VALUES (7, 100, 59);
INSERT INTO cardio (instance_id, distance, duration) VALUES (8, 1000, 300);

-- INSERT WORKOUT SESSIONS
INSERT INTO workout_session VALUES (1, '2025-10-26 22:00:00', "AFC");
INSERT INTO workout_session VALUES (2, '2025-10-24 15:00:00', "SRC");
INSERT INTO workout_session VALUES (3, '2025-10-23 14:00:00', "MEM");
INSERT INTO workout_session VALUES (4, '2025-10-21 13:00:00', "AFC");

-- INSERT INSTANCES INTO WORKOUT SESSIONS
INSERT INTO has (instance_id, session_id) VALUES (1, 1);
INSERT INTO has (instance_id, session_id) VALUES (2, 1);
INSERT INTO has (instance_id, session_id) VALUES (3, 2);
INSERT INTO has (instance_id, session_id) VALUES (4, 2);
INSERT INTO has (instance_id, session_id) VALUES (5, 3);
INSERT INTO has (instance_id, session_id) VALUES (6, 4);
INSERT INTO has (instance_id, session_id) VALUES (7, 3);
INSERT INTO has (instance_id, session_id) VALUES (8, 4);

-- INSERT WORKOUT GROUPS
INSERT INTO workout_group (group_id, creator_id, group_name) VALUES (1, 1, "Killer Abs");

-- INSERT INTO FRIENDSHIPS
INSERT INTO friendship (user_id1, user_id2) VALUES (1, 2);
INSERT INTO friendship (user_id1, user_id2) VALUES (2, 1);
INSERT INTO friendship (user_id1, user_id2) VALUES (3, 4);
INSERT INTO friendship (user_id1, user_id2) VALUES (4, 3);

-- INSERT INTO JOINS
INSERT INTO joins (user_id, group_id) VALUES (1, 1);
INSERT INTO joins (user_id, group_id) VALUES (2, 1);
INSERT INTO joins (user_id, group_id) VALUES (3, 1);
INSERT INTO joins (user_id, group_id) VALUES (4, 1);


-- INSERT INTO LOG_WORKOUT
INSERT INTO log_workout (user_id, SESSION_ID) VALUES (1, 1);
INSERT INTO log_workout (user_id, SESSION_ID) VALUES (2, 2);
INSERT INTO log_workout (user_id, SESSION_ID) VALUES (3, 3);
INSERT INTO log_workout (user_id, SESSION_ID) VALUES (4, 4);
