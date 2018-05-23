<?php

    session_start();
    if(isset($_SESSION['username']))
    {
        require 'db-con.php';
        require 'build-filter.php';
        require 'fetch-books.php';

        if (isset($_GET['isbn']))
        {
            $isbn = $_GET['isbn'];
            
            $filter = Filter();
            AddFilterRule($filter, "isbn", "BOOK.ISBN", $isbn);

            $book = FetchBooks($dbCon, $filter);
            $book = $book[0];

            $sql = "SELECT  COUNT(*) as count,
                            TRUNCATE(AVG(price), 2) as avgPrice
                    FROM BOOK
                    WHERE AuthorID = :authorId";
            $statement = $dbCon->prepare($sql);
            $statement->execute(array(':authorId' => $book['AuthorID']));
            $similar = $statement->fetch();
        }
        else
        {
            header("Location: search.php");
            exit();
        }
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
            <h1><?php echo $book['Title']; ?></h1>
            <p class="lead"><?php echo $book['AuthorName']; ?></p>
        </div>
    </div>

    <div class="container container-detail">
        <div class="row">
            <div class="col-sm-4 col-glance">
                <h3>$<?php echo $book['Price']; ?></h3>
                <h4><?php echo $book['Genres']; ?></h4>
                <h4>
                    <?php echo $book['AuthorName']; ?><br/>
                    <small>This author has <b><a href="search.php?author=<?php echo $book['AuthorID']; ?>"><?php echo $similar['count']; ?> book(s)</a></b> averaging $<?php echo $similar['avgPrice']; ?></small>
                </h4>
                <h4>
                    Published by <?php echo $book['PublisherName']; ?><br/>
                </h4>
                <h4><?php echo $book['PublicationDate']; ?></h4>
                <h4><?php echo $book['PageCount']; ?> Pages</h4>
            </div>
            <div class="col-sm-8">
                <h2>About</h2>
                <p>From Amazon.com:</p>
                <p><?php echo $book['Description']; ?></p>
            </div>
        </div>
    </div>

<?php require 'footer.php'; ?>