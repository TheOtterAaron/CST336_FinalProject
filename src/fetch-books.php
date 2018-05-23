<?php

    function FetchBooks($dbCon, $filter = NULL, $debug = false)
    {
        $sql = "SELECT  AUTHOR.AuthorID,
                        AUTHOR.Name as AuthorName,
                        PUBLISHER.Name as PublisherName,
                        BOOK.ISBN,
                        BOOK.Title,
                        BOOK.PublicationDate,
                        BOOK.PageCount,
                        BOOK.Description,
                        BOOK.Price,
                        GROUP_CONCAT(GENRE.Name ORDER BY GENRE.Name ASC SEPARATOR ', ') as Genres
                FROM BOOK
                LEFT JOIN AUTHOR
                ON BOOK.AuthorID = AUTHOR.AuthorId
                LEFT JOIN PUBLISHER
                ON BOOK.PublisherID = PUBLISHER.PublisherID
                LEFT JOIN BOOK_GENRE
                ON BOOK.ISBN = BOOK_GENRE.ISBN
                LEFT JOIN GENRE
                ON BOOK_GENRE.GenreID = GENRE.GenreID";

        if (!is_null($filter))
        {
            $appends = 0;
            foreach ($filter['rules'] as $rule)
            {
                $sql .= $appends == 0 ? " WHERE " : " AND ";
                $appends++;

                if (is_null($rule['maxValue']))
                {
                    $sql .= $rule['field'] . " = :" . $rule['name'];
                }
                else
                {
                    $sql .= $rule['field'] . " BETWEEN :" . $rule['name'] . "Min AND :" . $rule['name'] . "Max";
                }
            }
        }

        $sql .= " GROUP BY BOOK.ISBN";

        if (!is_null($filter))
        {
            $sql .= " ORDER BY " . $filter['sortOn'] . " " . $filter['sortOrder'];
        }

        if ($debug)
        {
            print_r($sql);
        }

        $statement = $dbCon->prepare($sql);

        if (!is_null($filter))
        {
            $bindings = array();
            foreach ($filter['rules'] as $rule)
            {
                if (is_null($rule['maxValue']))
                {
                    $bindings[$rule['name']] = $rule['minValue'];
                }
                else
                {
                    $bindings[$rule['name']."Min"] = $rule['minValue'];
                    $bindings[$rule['name']."Max"] = $rule['maxValue'];
                }
            }

            if ($debug)
            {
                print_r($bindings);
            }

            $statement->execute($bindings);
        }
        else
        {
            $statement->execute();
        }

        return $statement->fetchAll();
    }
    
?>