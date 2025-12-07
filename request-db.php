<?php
function getAccount($computingId, $password)
{
    global $db;

    $query = "SELECT USER_ID, `PASSWORD` AS stored_pw FROM USERS WHERE COMP_ID = :computingId";

    try {
        $stmt = $db->prepare($query);
        $stmt->bindValue(':computingId', $computingId);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        if (!$row) {
            return null;
        }

        $stored = $row['stored_pw'];

        // If stored value is a hash (password_verify will handle it)
        if (password_verify($password, $stored)) {
            // Optional: rehash if algorithm/settings changed
            if (password_needs_rehash($stored, PASSWORD_DEFAULT)) {
                $newHash = password_hash($password, PASSWORD_DEFAULT);
                $u = $db->prepare("UPDATE USERS SET `PASSWORD` = :newHash WHERE USER_ID = :uid");
                $u->bindValue(':newHash', $newHash);
                $u->bindValue(':uid', $row['USER_ID'], PDO::PARAM_INT);
                $u->execute();
                $u->closeCursor();
            }
            return $row['USER_ID'];
        }

        // Backwards-compatible: if DB stored plaintext, allow login and upgrade to hashed
        if ($stored === $password) {
            $newHash = password_hash($password, PASSWORD_DEFAULT);
            $u = $db->prepare("UPDATE USERS SET `PASSWORD` = :newHash WHERE USER_ID = :uid");
            $u->bindValue(':newHash', $newHash);
            $u->bindValue(':uid', $row['USER_ID'], PDO::PARAM_INT);
            $u->execute();
            $u->closeCursor();
            return $row['USER_ID'];
        }

        return null;
    }
    catch (PDOException $e) {
        // In dev show message; in production log it instead
        echo $e->getMessage();
        return null;
    }
}

function getNameByID($user_id)
{
    global $db;
    $query = "SELECT F_NAME as user_name FROM USERS WHERE USER_ID = :user_id"; 
    // OR SELECT user_full_name as user_name ... depending on your table

    $statement = $db->prepare($query);
    $statement->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $statement->execute();
    
    // Return the associative array row
    $result = $statement->fetch(PDO::FETCH_ASSOC); 
    $statement->closeCursor();
    
    // The key 'user_name' is what is accessed in dashboard.php
    return $result;
}

function createAccount($computingId, $password, $f_name, $l_name, $year)
{
    global $db;

    try {
        // existence check
        $check = $db->prepare("SELECT 1 FROM USERS WHERE COMP_ID = :computingId");
        $check->bindValue(':computingId', $computingId);
        $check->execute();
        if ($check->fetch()) {
            return 0; // already exists
        }

        $hashed = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $db->prepare("
            INSERT INTO USERS (COMP_ID, `PASSWORD`, F_NAME, L_NAME, YEAR)
            VALUES (:computingId, :password, :f_name, :l_name, :year)
        ");

        $stmt->bindParam(':computingId', $computingId);
        $stmt->bindParam(':password', $hashed);
        $stmt->bindParam(':f_name', $f_name);
        $stmt->bindParam(':l_name', $l_name);
        $stmt->bindParam(':year', $year);

        $stmt->execute();
        $rowsInserted = $stmt->rowCount();
        $stmt->closeCursor();

        return ($rowsInserted > 0) ? 1 : 0;
    }
    catch (PDOException $e) {
        echo $e->getMessage();
        return 0;
    }
}

function getExerciseByName($name)
{
    global $db;
    $query = "SELECT * FROM EXERCISE_TYPE WHERE EXERCISE_NAME LIKE :name";

    $statement = $db->prepare($query);
    $statement->bindValue(':name', "%" . $name . "%");
    $statement->execute();
    
    // Return the associative array row
    $result = $statement->fetchAll(PDO::FETCH_ASSOC); 
    $statement->closeCursor();
    
    return $result;
}

function createExercise($exercise_name)
{
    global $db;
    $formatted_name = ucwords(strtolower(trim($exercise_name)));
    try {
        $check = $db->prepare("SELECT 1 FROM EXERCISE_TYPE WHERE EXERCISE_NAME Like :formatted_name");
        $check->bindValue(':formatted_name', $formatted_name);
        $check->execute();
        if ($check->fetch()) {
            return 0; // already exists
        }
        $stmt = $db->prepare("
            INSERT INTO EXERCISE_TYPE (EXERCISE_NAME)
            VALUES (:formatted_name)
        ");

        $stmt->bindParam(':formatted_name', $formatted_name);

        $stmt->execute();
        $rowsInserted = $stmt->rowCount();
        $stmt->closeCursor();

        return ($rowsInserted > 0) ? 1 : 0;
    }
    catch (PDOException $e) {
        echo $e->getMessage();
        return 0;
    }
}

function createStrengthExerciseInstance($exercise_name, $date, $sets, $reps, $weight) {
    global $db;
    try {
        $query = $db->prepare("
            INSERT INTO EXERCISE_INSTANCE (EXERCISE_ID, DATE)
            SELECT ET.EXERCISE_ID, :date
            FROM EXERCISE_TYPE ET
            WHERE ET.EXERCISE_NAME LIKE :exercise_name
        ");
        $query->bindParam(':exercise_name', $exercise_name);
        $query->bindParam(':date', $date);
        $query->execute();

        //The command lastInsertId() was identified by gemini
        $instance_id = $db->lastInsertId();

        $query->closeCursor();
        
        

        $query2 = $db->prepare("
            INSERT INTO STRENGTH (INSTANCE_ID, WEIGHT, SETS, REPS)
            VALUES (:instance_id, :weight, :sets, :reps)
            ");
        $query2->bindParam(':instance_id', $instance_id);
        $query2->bindParam(':weight', $weight);
        $query2->bindParam(':sets', $sets);
        $query2->bindParam(':reps', $reps);
        $query2->execute();
        $query2->closeCursor();
        return $instance_id;
    }
    catch (PDOException $e) {
        echo $e->getMessage();
        return 0;
    }
}

function createCardioExerciseInstance($exercise_name, $date, $distance, $duration) {
    global $db;
    try {
        $query = $db->prepare("
            INSERT INTO EXERCISE_INSTANCE (EXERCISE_ID, DATE)
            SELECT ET.EXERCISE_ID, :date
            FROM EXERCISE_TYPE ET
            WHERE ET.EXERCISE_NAME LIKE :exercise_name
        ");
        $query->bindParam(':exercise_name', $exercise_name);
        $query->bindParam(':date', $date);
        $query->execute();

        //The command lastInsertId() was identified by gemini
        $instance_id = $db->lastInsertId();

        $query->closeCursor();
        
        

        $query2 = $db->prepare("
            INSERT INTO CARDIO (INSTANCE_ID, DISTANCE, DURATION)
            VALUES (:instance_id, :distance, :duration)
            ");
        $query2->bindParam(':instance_id', $instance_id);
        $query2->bindParam(':distance', $distance);
        $query2->bindParam(':duration', $duration);
        $query2->execute();
        $query2->closeCursor();
        return $instance_id;
    }
    catch (PDOException $e) {
        echo $e->getMessage();
        return 0;
    }
}

function logWorkout($date, $location, $instance_list, $user_id) {
    global $db;
    try {
        $query = $db->prepare("
            INSERT INTO WORKOUT_SESSION (DATE, LOCATION)
            VALUES (:date, :location)
        ");
        $query->bindParam(':date', $date);
        $query->bindParam(':location', $location);
        $query->execute();

        //The command lastInsertId() was identified by gemini
        $session_id = $db->lastInsertId();

        $query->closeCursor();

        foreach ($instance_list as $instance_id) {
            $query2 = $db->prepare("
                INSERT INTO HAS (INSTANCE_ID, SESSION_ID)
                VALUES (:instance_id, :session_id)
                ");
            $query2->bindParam(':session_id', $session_id);
            $query2->bindParam('instance_id', $instance_id);
            $query2->execute();
            $query2->closeCursor();
        }
        $query = $db->prepare("
            INSERT INTO LOG_WORKOUT (USER_ID, SESSION_ID)
            VALUES (:user_id, :session_id)
        ");
        $query->bindParam(':user_id', $user_id);
        $query->bindParam(':session_id', $session_id);
        $query->execute();
        $query->closeCursor();

        return;
    }
    catch (PDOException $e) {
        echo $e->getMessage();
        return 0;
    }
}

function getUserGroups($user_id) {
    global $db;
    $query = "
        SELECT g.GROUP_ID AS id, g.GROUP_NAME AS name
        FROM JOINS j
        INNER JOIN WORKOUT_GROUP g ON j.GROUP_ID = g.GROUP_ID
        WHERE j.USER_ID = :user_id
    ";
    $statement = $db->prepare($query);
    $statement->bindParam(':user_id', $user_id);
    $statement->execute();
    
    // Return the associative array row
    $result = $statement->fetchAll(PDO::FETCH_ASSOC); 
    $statement->closeCursor();
    
    return $result;
}

function createNewGroup($group_name, $creator_id) {
    global $db;
    try {
        $query = $db->prepare("
            INSERT INTO WORKOUT_GROUP (GROUP_NAME, CREATOR_ID, NUMBER_OF_MEMBERS)
            VALUES (:group_name, :creator_id, :num_mems)
        ");
        $query->bindParam(':group_name', $group_name);
        $query->bindParam(':creator_id', $creator_id);
        $temp = 1;
        $query->bindParam(':num_mems', $temp);
        $query->execute();

        //The command lastInsertId() was identified by gemini
        $group_id = $db->lastInsertId();

        $query->closeCursor();

        $query = $db->prepare("
            INSERT INTO JOINS (USER_ID, GROUP_ID)
            VALUES (:user_id, :group_id)
        ");
        $query->bindParam(':user_id', $creator_id);
        $query->bindParam(':group_id', $group_id);
        $query->execute();
        $query->closeCursor();

        return $group_id;
    }
    catch (PDOException $e) {
        echo $e->getMessage();
        return 0;
    }
}

function addGroupMember($group_id, $comp_id) {
    global $db;

    // Convert computing ID to USER_ID
    $member_id = getUserIdFromCompId($comp_id);

    if ($member_id === null) {
        return "Error: No user found with computing ID '$comp_id'.";
    }

    try {
        // Check if already in group
        $check = $db->prepare("
            SELECT * FROM JOINS
            WHERE USER_ID = :member_id AND GROUP_ID = :group_id
        ");
        $check->bindParam(':member_id', $member_id);
        $check->bindParam(':group_id', $group_id);
        $check->execute();

        if ($check->fetch()) {
            return "User '$comp_id' is already in this group.";
        }

        // Insert into JOINS
        $insert = $db->prepare("
            INSERT INTO JOINS (USER_ID, GROUP_ID)
            VALUES (:member_id, :group_id)
        ");
        $insert->bindParam(':member_id', $member_id);
        $insert->bindParam(':group_id', $group_id);
        $insert->execute();

        // Increment member count
        $update = $db->prepare("
            UPDATE WORKOUT_GROUP
            SET NUMBER_OF_MEMBERS = NUMBER_OF_MEMBERS + 1
            WHERE GROUP_ID = :group_id
        ");
        $update->bindParam(':group_id', $group_id);
        $update->execute();

        return "Successfully added '$comp_id' to group.";
    }
    catch (PDOException $e) {
        return "DB Error: " . $e->getMessage();
    }
}

function removeGroupMember($group_id, $comp_id) {
    global $db;

    // Convert computing ID to USER_ID
    $member_id = getUserIdFromCompId($comp_id);

    if ($member_id === null) {
        return "Error: No user found with computing ID '$comp_id'.";
    }

    try {
        // Check if they are in the group
        $check = $db->prepare("
            SELECT * FROM JOINS
            WHERE USER_ID = :member_id AND GROUP_ID = :group_id
        ");
        $check->bindParam(':member_id', $member_id);
        $check->bindParam(':group_id', $group_id);
        $check->execute();

        if (!$check->fetch()) {
            return "User '$comp_id' is not in this group.";
        }

        // Remove from JOINS
        $delete = $db->prepare("
            DELETE FROM JOINS 
            WHERE USER_ID = :member_id AND GROUP_ID = :group_id
        ");
        $delete->bindParam(':member_id', $member_id);
        $delete->bindParam(':group_id', $group_id);
        $delete->execute();

        // Decrement count
        $update = $db->prepare("
            UPDATE WORKOUT_GROUP
            SET NUMBER_OF_MEMBERS = NUMBER_OF_MEMBERS - 1
            WHERE GROUP_ID = :group_id
        ");
        $update->bindParam(':group_id', $group_id);
        $update->execute();

        return "Successfully removed '$comp_id' from group.";
    }
    catch (PDOException $e) {
        return "DB Error: " . $e->getMessage();
    }
}

function getUserIdFromCompId($comp_id) {
    global $db;
    $stmt = $db->prepare("SELECT USER_ID FROM USERS WHERE COMP_ID = :comp_id");
    $stmt->bindValue(':comp_id', $comp_id);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? $row['USER_ID'] : null;
}

function getNumWorkoutsFromID($user_id) {
    global $db;
    $stmt = $db->prepare("
        SELECT COUNT(*) AS workout_count
        FROM LOG_WORKOUT
        WHERE USER_ID = :user_id
    ");

    $stmt->bindValue(':user_id', $user_id);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row ? (int)$row['workout_count'] : 0;
}

function getUsersInGroup($group_id) {
    global $db;

    $query = "
        SELECT U.USER_ID, U.F_NAME, U.L_NAME
        FROM JOINS J
        INNER JOIN USERS U ON J.USER_ID = U.USER_ID
        WHERE J.GROUP_ID = :group_id
    ";

    $stmt = $db->prepare($query);
    $stmt->bindValue(':group_id', $group_id);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getGroupNameFromID($group_id) {
    global $db;

    $query = "
        SELECT GROUP_NAME
        FROM WORKOUT_GROUP
        WHERE GROUP_ID = :group_id
    ";

    $stmt = $db->prepare($query);
    $stmt->bindValue(':group_id', $group_id);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? $row['GROUP_NAME'] : null;
}
function addFriend($user_id, $friendCompId) {
    global $db;

    $query = "SELECT USER_ID FROM USERS WHERE COMP_ID = :compid";
    $stmt = $db->prepare($query);
    $stmt->bindValue(':compid', $friendCompId);
    $stmt->execute();
    $friend = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$friend) return false;

    $friend_id = $friend['USER_ID'];

    $insert = $db->prepare("INSERT IGNORE INTO FRIENDSHIP(USER_ID1, USER_ID2) VALUES (:u, :f)");
    $insert->bindValue(':u', $user_id);
    $insert->bindValue(':f', $friend_id);
    $insert->execute();

    $insert = $db->prepare("INSERT IGNORE INTO FRIENDSHIP(USER_ID1, USER_ID2) VALUES (:f, :u)");
    $insert->bindValue(':u', $user_id);
    $insert->bindValue(':f', $friend_id);
    return $insert->execute();
}

function getWorkoutByDate($user_id, $date) {
    global $db;

    $query = "
        SELECT 
            et.EXERCISE_NAME AS exercise_name,
            s.SETS,
            s.REPS,
            s.WEIGHT,
            c.DURATION,
            c.DISTANCE
        FROM LOG_WORKOUT lw
        JOIN WORKOUT_SESSION ws ON ws.SESSION_ID = lw.SESSION_ID
        JOIN HAS h ON h.SESSION_ID = ws.SESSION_ID
        JOIN EXERCISE_INSTANCE ei ON ei.INSTANCE_ID = h.INSTANCE_ID
        JOIN EXERCISE_TYPE et ON et.EXERCISE_ID = ei.EXERCISE_ID
        LEFT JOIN STRENGTH s ON s.INSTANCE_ID = ei.INSTANCE_ID
        LEFT JOIN CARDIO c ON c.INSTANCE_ID = ei.INSTANCE_ID
        WHERE lw.USER_ID = :uid 
          AND DATE(ws.DATE) = :d
    ";

    $stmt = $db->prepare($query);
    $stmt->bindValue(':uid', $user_id);
    $stmt->bindValue(':d', $date);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getLeaderboard($user_id) {
    global $db;

    $query = "
        SELECT NAME, SCORE
        FROM (
            SELECT 
                CONCAT(U.F_NAME, ' ', U.L_NAME) AS NAME,
                COUNT(L.SESSION_ID) AS SCORE
            FROM USERS U
            LEFT JOIN LOG_WORKOUT L ON L.USER_ID = U.USER_ID
            WHERE U.USER_ID = :uid
            
            UNION ALL
            
            SELECT
                CONCAT(U.F_NAME, ' ', U.L_NAME) AS NAME,
                COUNT(L.SESSION_ID) AS SCORE
            FROM FRIENDSHIP F
            JOIN USERS U ON F.USER_ID2 = U.USER_ID
            LEFT JOIN LOG_WORKOUT L ON L.USER_ID = U.USER_ID
            WHERE F.USER_ID1 = :uid
            GROUP BY U.F_NAME, U.L_NAME
        ) AS LB
        ORDER BY SCORE DESC, NAME ASC;
    ";

    $stmt = $db->prepare($query);
    $stmt->bindValue(':uid', $user_id);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}



?>
