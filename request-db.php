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

function getRequestById($id)  
{

}

function updateRequest($reqId, $reqDate, $roomNumber, $reqBy, $repairDesc, $reqPriority)
{
    
}

function deleteRequest($reqId)
{

}

?>
