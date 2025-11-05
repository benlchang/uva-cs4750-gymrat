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

function getAllRequests()
{

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
