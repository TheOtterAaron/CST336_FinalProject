<?php

require "db-con.php";

$sql = "CREATE TABLE BOOK_USERS (
        username varchar (50) NOT NULL PRIMARY KEY,
        password varchar (50) NOT NULL)";

$stmt = $dbCon -> prepare($sql);
$stmt -> execute();

$sql = "INSERT INTO BOOK_USERS
        (username, password)
        VALUES
        (:username, :password)";

$stmt = $dbCon -> prepare($sql);
$stmt -> execute(array(":username" => "gordy", ":password" => hash('sha1','secret')));

echo("Book users created");

?>