<?php

    session_start();
    if(isset($_SESSION['username']))
    {
        require 'db-con.php';
        require 'build-filter.php';
        require 'fetch-books.php';    
        require 'fetch-lists.php';

        // BUILD FILTER
        $filter = Filter();

        if (!empty($_GET['genre']))
        {
            AddFilterRule($filter, "genre", "BOOK_GENRE.GenreID", $_GET['genre']);
        }

        if (!empty($_GET['publisher']))
        {
            AddFilterRule($filter, "publisher", "BOOK.PublisherID", $_GET['publisher']);
        }

        if (!empty($_GET['author']))
        {
            AddFilterRule($filter, "author", "BOOK.AuthorID", $_GET['author']);
        }

        if (!empty($_GET['publishStart']) || !empty($_GET['publishEnd']))
        {
            AddFilterRule(
                $filter,
                "publishDate",
                "BOOK.PublicationDate",
                empty($_GET['publishStart']) ? "0001-01-01" : $_GET['publishStart'],
                empty($_GET['publishEnd']) ? "3000-01-01" : $_GET['publishEnd']);
        }

        if (!empty($_GET['pagesMin']) || !empty($_GET['pagesMax']))
        {
            AddFilterRule(
                $filter,
                "pages",
                "BOOK.PageCount",
                empty($_GET['pagesMin']) ? 0 : $_GET['pagesMin'],
                empty($_GET['pagesMax']) ? 1000000 : $_GET['pagesMax']);
        }

        if (!empty($_GET['sort']) && !empty($_GET['order']))
        {
            switch (strtolower($_GET['sort']))
            {
                case "genre" : $sortField = "Genres";
                    break;
                case "publisher" : $sortField = "PUBLISHER.Name";
                    break;
                case "author" : $sortField = "AUTHOR.Name";
                    break;
                case "published" : $sortField = "BOOK.PublicationDate";
                    break;
                case "pages" : $sortField = "BOOK.PageCount";
                    break;
                default : $sortField = "BOOK.Title";
            }

            $sortOrder = strtolower($_GET['order']) == "desc" ? "DESC" : "ASC";
            
            SetFilterSort($filter, $sortField, $sortOrder);
        }

        // FETCH DATA
        $books = FetchBooks($dbCon, $filter);

        $genreList = FetchGenreList($dbCon);
        $publisherList = FetchPublisherList($dbCon);
        $authorList = FetchAuthorList($dbCon);

        // STRIP SORT AND ORDER FROM QUERY STRING
        parse_str($_SERVER['QUERY_STRING'], $query);
        if (isset($query['sort']))
        {
            unset($query['sort']);
        }
        if (isset($query['order']))
        {
            unset($query['order']);
        }
        $queryString = http_build_query($query);

        // FILTER HELPER FUNCTIONS
        function IsSortField($field)
        {
            $curField = empty($_GET['sort']) ? "title" : strtolower($_GET['sort']);
            return $field == $curField;
        }

        function IsSortAsc()
        {
            return (empty($_GET['order']) ? "asc" : strtolower($_GET['order'])) != "desc";
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
            <h1>Search</h1>
            <p class="lead">The Online Book Exchange</p>
        </div>
    </div>

	<div class="container-fluid container-filter">
		<form class="form-horizontal" role="form" method="get">
            <div class="row">
                <div class="col-md-5">
                    <div class="form-group">
                        <div class="col-md-4">
                            <select class="form-control input-sm" name="genre">
                                <option value="" selected>Genre</option>
                                <?php
                                    foreach ($genreList as $genre)
                                    {
                                        $selected = "";
                                        if (isset($_GET['genre']))
                                        {
                                            $selected = $_GET['genre'] == $genre['ID'] ? " selected" : "";
                                        }
                                        echo "<option value='" . $genre['ID'] . "'" . $selected . ">" . $genre['Name'] . "</option>";
                                    }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <select class="form-control input-sm" name="publisher">
                                <option value="" selected>Publisher</option>
                                <?php
                                    foreach ($publisherList as $publisher)
                                    {
                                        $selected = "";
                                        if (isset($_GET['publisher']))
                                        {
                                            $selected = $_GET['publisher'] == $publisher['ID'] ? " selected" : "";
                                        }
                                        echo "<option value='" . $publisher['ID'] . "'" . $selected . ">" . $publisher['Name'] . "</option>";
                                    }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <select class="form-control input-sm" name="author">
                                <option value="" selected>Author</option>
                                <?php
                                    foreach ($authorList as $author)
                                    {
                                        $selected = "";
                                        if (isset($_GET['author']))
                                        {
                                            $selected = $_GET['author'] == $author['ID'] ? " selected" : "";
                                        }
                                        echo "<option value='" . $author['ID'] . "'" . $selected . ">" . $author['Name'] . "</option>";
                                    }
                                ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="publishStart" class="col-md-2 control-label input-sm">Published</label>
                        <div class="col-md-5">
                            <input class="form-control input-sm" type="date" name="publishStart" id="publishStart" value="<?php if (!empty($_GET['publishStart'])) { echo $_GET['publishStart']; } ?>">
                        </div>
                        <div class="col-md-5">
                            <input class="form-control input-sm" type="date" name="publishEnd" value="<?php if (!empty($_GET['publishEnd'])) { echo $_GET['publishEnd']; } ?>">
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="pagesMin" class="col-md-2 control-label input-sm">Pages</label>
                        <div class="col-md-3">
                            <input class="form-control input-sm" type="number" name="pagesMin" id="pagesMin" min="0" value="<?php if (!empty($_GET['pagesMin'])) { echo $_GET['pagesMin']; } ?>">
                        </div>
                        <div class="col-md-3">
                            <input class="form-control input-sm" type="number" name="pagesMax" min="0"  value="<?php if (!empty($_GET['pagesMax'])) { echo $_GET['pagesMax']; } ?>">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-small btn-warning"><i class="glyphicon glyphicon-search"></i></button>
                        </div>
                        <div class="col-md-2">
                            <a href="search.php" class="btn btn-small btn-default"><i class="glyphicon glyphicon-trash"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
	</div>

    <div class="container-fluid container-results">
        <table class="table table-striped">
            <tr>
                <td>
                    <?php
                        $href = $queryString
                                ."&sort=title&order="
                                .(IsSortField("title") && IsSortAsc() ? "desc" : "asc");
                    ?>
                    <a href="search.php?<?php echo $href; ?>">
                        <b>Title</b>
                        <?php
                            if (IsSortField("title") && IsSortAsc())
                            {
                                echo "<i class='glyphicon glyphicon-triangle-top'></i>";
                            }
                            elseif (IsSortField("title"))
                            {
                                echo "<i class='glyphicon glyphicon-triangle-bottom'></i>";
                            }
                        ?>
                    </a>
                </td>
                <td>
                    <?php
                        $href = $queryString
                                ."&sort=genre&order="
                                .(IsSortField("genre") && IsSortAsc() ? "desc" : "asc");
                    ?>
                    <a href="search.php?<?php echo $href; ?>">
                        <b>Genre</b>
                        <?php
                            if (IsSortField("genre") && IsSortAsc())
                            {
                                echo "<i class='glyphicon glyphicon-triangle-top'></i>";
                            }
                            elseif (IsSortField("genre"))
                            {
                                echo "<i class='glyphicon glyphicon-triangle-bottom'></i>";
                            }
                        ?>
                    </a>
                </td>
                <td>
                    <?php
                        $href = $queryString
                                ."&sort=publisher&order="
                                .(IsSortField("publisher") && IsSortAsc() ? "desc" : "asc");
                    ?>
                    <a href="search.php?<?php echo $href; ?>">
                        <b>Publisher</b>
                        <?php
                            if (IsSortField("publisher") && IsSortAsc())
                            {
                                echo "<i class='glyphicon glyphicon-triangle-top'></i>";
                            }
                            elseif (IsSortField("publisher"))
                            {
                                echo "<i class='glyphicon glyphicon-triangle-bottom'></i>";
                            }
                        ?>
                    </a>
                </td>
                <td>
                    <?php
                        $href = $queryString
                                ."&sort=author&order="
                                .(IsSortField("author") && IsSortAsc() ? "desc" : "asc");
                    ?>
                    <a href="search.php?<?php echo $href; ?>">
                        <b>Author</b>
                        <?php
                            if (IsSortField("author") && IsSortAsc())
                            {
                                echo "<i class='glyphicon glyphicon-triangle-top'></i>";
                            }
                            elseif (IsSortField("author"))
                            {
                                echo "<i class='glyphicon glyphicon-triangle-bottom'></i>";
                            }
                        ?>
                    </a>
                </td>
                <td>
                    <?php
                        $href = $queryString
                                ."&sort=published&order="
                                .(IsSortField("published") && IsSortAsc() ? "desc" : "asc");
                    ?>
                    <a href="search.php?<?php echo $href; ?>">
                        <b>Published</b>
                        <?php
                            if (IsSortField("published") && IsSortAsc())
                            {
                                echo "<i class='glyphicon glyphicon-triangle-top'></i>";
                            }
                            elseif (IsSortField("published"))
                            {
                                echo "<i class='glyphicon glyphicon-triangle-bottom'></i>";
                            }
                        ?>
                    </a>
                </td>
                <td>
                    <?php
                        $href = $queryString
                                ."&sort=pages&order="
                                .(IsSortField("pages") && IsSortAsc() ? "desc" : "asc");
                    ?>
                    <a href="search.php?<?php echo $href; ?>">
                        <b>Pages</b>
                        <?php
                            if (IsSortField("pages") && IsSortAsc())
                            {
                                echo "<i class='glyphicon glyphicon-triangle-top'></i>";
                            }
                            elseif (IsSortField("pages"))
                            {
                                echo "<i class='glyphicon glyphicon-triangle-bottom'></i>";
                            }
                        ?>
                    </a>
                </td>
            </tr>
            <?php
                foreach ($books as $book)
                {
                ?>
                    <tr>
                        <td><a href="detail.php?isbn=<?php echo $book['ISBN']; ?>"><?php echo $book['Title']; ?></a></td>
                        <td><?php echo $book['Genres']; ?></td>
                        <td><?php echo $book['PublisherName']; ?></td>
                        <td><?php echo $book['AuthorName']; ?></td>
                        <td><?php echo $book['PublicationDate']; ?></td>
                        <td><?php echo $book['PageCount']; ?></td>
                    </tr>
                <?php
                }
            ?>
        </table>
    </div>

<?php require 'footer.php'; ?>