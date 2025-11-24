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


?>
