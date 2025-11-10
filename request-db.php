<?php
function getAccount($computingId, $password)
{
    global $db;

    $query = "SELECT USER_ID FROM USERS WHERE COMP_ID = :computingId AND PASSWORD = :password";

    try {
        $statement = $db->prepare($query);

        $statement->bindValue(':computingId', $computingId);
        $statement->bindValue(':password', $password); 

        $statement->execute();

        $result = $statement->fetchColumn(); 

        $statement->closeCursor();

        return $result; 

    }
    catch (PDOException $e) 
    {
        echo $e->getMessage();

        // if there is a specific SQL-related error message
        //    echo "generic message (don't reveal SQL-specific message)";
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
    $query = "
        INSERT INTO USERS (COMP_ID, `PASSWORD`, F_NAME, L_NAME, YEAR)
        SELECT :computingId, :password, :f_name, :l_name, :year
        FROM DUAL
        WHERE NOT EXISTS (
            SELECT 1 FROM USERS WHERE COMP_ID = :computingId
        )
    ";    
    try {
        // bad way
        $stmt = $db->prepare($query);

        $stmt->bindParam(':computingId', $computingId);
        $stmt->bindParam(':password', $password);
        $stmt->bindParam(':f_name', $f_name);
        $stmt->bindParam(':l_name', $l_name);
        $stmt->bindParam(':year', $year);

        $stmt->execute();

        $rowsInserted = $stmt->rowCount();

        $stmt->closeCursor();
        
        if ($rowsInserted > 0) {
            return 1;
        }
    }
    catch (PDOException $e) 
    {
        echo $e->getMessage();

      
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
