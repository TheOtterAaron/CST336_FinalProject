<?php

    function FetchGenreList($dbCon)
    {
        $sql = "SELECT GenreID as ID, Name
                FROM GENRE
                ORDER BY Name ASC";

        $statement = $dbCon->prepare($sql);
        $statement->execute();
        return $statement->fetchAll();
    }

    function FetchPublisherList($dbCon)
    {
        $sql = "SELECT PublisherID as ID, Name
                FROM PUBLISHER
                ORDER BY Name ASC";

        $statement = $dbCon->prepare($sql);
        $statement->execute();
        return $statement->fetchAll();
    }

    function FetchAuthorList($dbCon)
    {
        $sql = "SELECT AuthorID as ID, Name
                FROM AUTHOR
                ORDER BY Name ASC";

        $statement = $dbCon->prepare($sql);
        $statement->execute();
        return $statement->fetchAll();
    }
    
?>