<?php

    session_start();
    if(isset($_SESSION['username']))
    {
        require 'db-con.php';
        require 'fetch-lists.php';

        if (isset($_POST['added']))
        {
            // Require All Fields
            if (empty($_POST['isbn'])
                || empty($_POST['price'])
                || empty($_POST['genres'])
                || empty($_POST['author'])
                || empty($_POST['publisher'])
                || empty($_POST['published'])
                || empty($_POST['pages'])
                || empty($_POST['title'])
                || empty($_POST['description']))
            {
                $error = "Please provide all fields.";
            }

            $dbCon->beginTransaction();
            
            // Validate ISBN
            if (empty($error))
            {
                $sql = "SELECT count(*)
                        FROM BOOK
                        WHERE isbn = :isbn";
                $statement = $dbCon->prepare($sql);
                $statement->execute(array(":isbn" => $_POST['isbn']));
                $record = $statement->fetch();

                if ($record[0] != 0)
                {
                    $error = "A book with that ISBN already exists.";
                }
            }

            // Get or Create Author ID
            if (empty($error))
            {
                $sql = "SELECT AuthorID
                        FROM AUTHOR
                        WHERE Name = :name";
                $statement = $dbCon->prepare($sql);
                $statement->execute(array(":name" => $_POST['author']));
                $record = $statement->fetch();

                if (!empty($record))
                {
                    $authorId = $record[0];
                }
                else
                {
                    $sql = "INSERT INTO AUTHOR
                            (Name)
                            VALUES
                            (:name)";
                    $statement = $dbCon->prepare($sql);
                    $statement->execute(array(":name" => $_POST['author']));

                    $authorId = $dbCon->lastInsertId();
                }
                
                if (empty($authorId))
                {
                    $error = "An error occurred with the author.";
                }
            }

            // Get or Create Publisher ID
            if (empty($error))
            {
                $sql = "SELECT PublisherID
                        FROM PUBLISHER
                        WHERE Name = :name";
                $statement = $dbCon->prepare($sql);
                $statement->execute(array(":name" => $_POST['publisher']));
                $record = $statement->fetch();

                if (!empty($record))
                {
                    $publisherId = $record[0];
                }
                else
                {
                    $sql = "INSERT INTO PUBLISHER
                            (Name)
                            VALUES
                            (:name)";
                    $statement = $dbCon->prepare($sql);
                    $statement->execute(array(":name" => $_POST['publisher']));

                    $publisherId = $dbCon->lastInsertId();
                }

                if (empty($publisherId))
                {
                    $error = "An error occurred with the publisher.";
                }
            }

            // Insert Book
            if (empty($error))
            {
                $sql = "INSERT INTO BOOK
                        (ISBN, AuthorID, Title, PublisherID, PublicationDate, PageCount, Description, Price)
                        VALUES
                        (:isbn, :authorId, :title, :publisherId, :publicationDate, :pageCount, :description, :price)";
                $statement = $dbCon->prepare($sql);
                $statement->execute(array
                (
                    ":isbn" => $_POST['isbn'],
                    ":authorId" => $authorId,
                    ":title" => $_POST['title'],
                    ":publisherId" => $publisherId,
                    ":publicationDate" => $_POST['published'],
                    ":pageCount" => $_POST['pages'],
                    ":description" => $_POST['description'],
                    ":price" => $_POST['price']
                ));
            }

            // Get or Create Genre IDs
            if (empty($error))
            {
                $genreIds = array();
                $genres = split(",", $_POST['genres']);
                foreach ($genres as $genre)
                {
                    $genre = trim($genre);

                    $sql = "SELECT GenreID
                            FROM GENRE
                            WHERE Name = :name";
                    $statement = $dbCon->prepare($sql);
                    $statement->execute(array(":name" => $genre));
                    $record = $statement->fetch();

                    if(!empty($record))
                    {
                        array_push($genreIds, $record[0]);
                    }
                    else
                    {
                        $sql = "INSERT INTO GENRE
                                (Name)
                                VALUES
                                (:name)";
                        $statement = $dbCon->prepare($sql);
                        $statement->execute(array(":name" => $genre));

                        array_push($genreIds, $dbCon->lastInsertId());
                    }
                }
                
                if (empty($genreIds))
                {
                    $error - "An error occurred with the genres.";
                }
            }

            // Insert Genres
            if (empty($error))
            {
                foreach ($genreIds as $genreId)
                {
                    $sql = "INSERT INTO BOOK_GENRE
                            (ISBN, GenreID)
                            VALUES
                            (:isbn, :genreId)";
                    $statement = $dbCon->prepare($sql);
                    $statement->execute(array
                    (
                        ":isbn" => $_POST['isbn'],
                        ":genreId" => $genreId
                    ));
                }
            }

            // Commit or Roll Back
            if (empty($error))
            {
                $dbCon->commit();
                $result = $_POST['title'] . " by " . $_POST['author'] . " successfully added!";
            }
            else
            {
                $dbCon->rollBack();
            }
        }

        // Load Lists
        $genreList = FetchGenreList($dbCon);
        $publisherList = FetchPublisherList($dbCon);
        $authorList = FetchAuthorList($dbCon);
    }
    else
    {
        header("Location: index.php");
        exit;
    }

    require 'header.php';
    require 'menu.php';
?>

    <div class="jumbotron jumbotron-fluid jumbotron-banner">
        <div class="container">
            <h1>Add a Book</h1>
            <p class="lead">To The Online Exchange</p>
        </div>
    </div>

    <div class="container container-detail">
        <form class="form-horizontal" role="form" method="post">
            <div class="row">
                <div class="col-sm-4 col-glance">
                    <div class="formgroup">
                        <label for="isbn" class="col-sm-4 control-label">ISBN</label>
                        <div class="col-sm-8">
                            <input class="form-control" type="text" name="isbn" id="isbn">
                        </div>
                    </div>
                    <div class="formgroup">
                        <label for="price" class="col-sm-4 control-label">Price</label>
                        <div class="col-sm-8">
                            <input class="form-control" type="number" name="price" id="price" step="0.01" min="0">
                        </div>
                    </div>
                    <div class="formgroup">
                        <label for="genre" class="col-sm-4 control-label">Genres</label>
                        <div class="col-sm-8">
                            <input class="form-control" type="text" name="genres" id="genres" list="genreList">
                        </div>
                        <datalist id="genreList">
                            <?php
                                foreach ($genreList as $genre)
                                {
                                    echo "<option value='" . $genre['Name'] . "'>";
                                }
                            ?>
                        </datalist>
                    </div>
                    <div class="formgroup">
                        <label for="author" class="col-sm-4 control-label">Author</label>
                        <div class="col-sm-8">
                            <input class="form-control" type="text" name="author" id="author" list="authorList">
                        </div>
                        <datalist id="authorList">
                            <?php
                                foreach ($authorList as $author)
                                {
                                    echo "<option value='" . $author['Name'] . "'>";
                                }
                            ?>
                        </datalist>
                    </div>
                    <div class="formgroup">
                        <label for="publisher" class="col-sm-4 control-label">Publisher</label>
                        <div class="col-sm-8">
                            <input class="form-control" type="text" name="publisher" id="publisher" list="publisherList">
                        </div>
                        <datalist id="publisherList">
                            <?php
                                foreach ($publisherList as $publisher)
                                {
                                    echo "<option value='" . $publisher['Name'] . "'>";
                                }
                            ?>
                        </datalist>
                    </div>
                    <div class="formgroup">
                        <label for="published" class="col-sm-4 control-label">Published</label>
                        <div class="col-sm-8">
                            <input class="form-control" type="date" name="published" id="published">
                        </div>
                    </div>
                    <div class="formgroup">
                        <label for="pages" class="col-sm-4 control-label">Pages</label>
                        <div class="col-sm-8">
                            <input class="form-control" type="number" name="pages" id="pages" min="0">
                        </div>
                    </div>
                </div>
                <div class="col-sm-8">
                    <input class="form-control input-lg input-bottom-border" type="text" name="title" placeholder="Title"><br/>
                    <textarea class="form-control" rows="15" name="description" placeholder="Description"></textarea>
                </div>
            </div>
            <div class="row text-right">
                <?php
                    if (!empty($error))
                    {
                        echo "<span class='error'>" . $error . "</span>";
                    }
                    elseif (!empty($result))
                    {
                        echo $result;
                    }
                ?>
                <button type="submit" class="btn btn-warning btn-large" name="added" value="true">Add Book</button>
                <button type="reset" class="btn btn-default btn-large">Start Over</button>
            </div>
        </form>
    </div>

<?php require 'footer.php'; ?>