<?php

    function Authenticate($dbCon, $username, $password)
    {
        $sql = "SELECT password
                FROM BOOK_USERS
                WHERE username = :username
                and password = :password";

        $statement = $dbCon->prepare($sql);
        $statement->execute(array
        (
            ":username" => $username,
            ":password" => hash("sha1", $password)
        ));

        $record = $statement->fetch();

        return !empty($record);
    }
    
?>