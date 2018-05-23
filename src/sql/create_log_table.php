<?php

require "db_connection.php";

$sql = "CREATE TABLE BOOK_LOG (
        username varchar (50),
        logintime datetime)";

$stmt = $dbConn -> prepare($sql);
$stmt -> execute();

echo("Log table created");

?>