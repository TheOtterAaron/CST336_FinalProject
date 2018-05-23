<?php
    
    session_start();
    if(isset($_SESSION['username']))
    {
        header("Location: search.php");
        exit;
    }
    else
    {
        require 'db-con.php';
        require 'authenticate.php';

        if (isset($_POST['username']) && isset($_POST['password']))
        {
            if (!Authenticate($dbCon, $_POST['username'], $_POST['password']))
            {
                $error = "Unrecognized username and password combination.";
            }
            else 
            {
                $_SESSION['username'] = $_POST['username'];

                $sql = "INSERT INTO BOOK_LOG
                        (username, logintime)
                        VALUES
                        (:username, :logintime)";

                $stmt = $dbCon->prepare($sql);
                $stmt->execute(array
                (
                    ":username" => $_SESSION['username'],
                    ":logintime" => date("Y-m-d h:i:s",time())
                ));

                header("Location: search.php");                    
                exit;                    
            }
        }
    }

    require 'header.php';
?>

    <div class="jumbotron jumbotron-fluid jumbotron-login">
        <div class="container">
            <h1>Booknasium</h1>
            <p class="lead">
                <?php
                    if (!empty($error))
                    {
                        echo '<span class="error">' . $error . "</span>";
                    }
                    else
                    {
                        echo "Please Login";
                    }
                ?>
            </p>
            <form class="form-horizontal" role="form" method="post">
                <div class="form-group">
                    <label class="control-label col-sm-2" for="username"><i class="glyphicon glyphicon-user"></i></label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control input-lg" id="username" name="username" placeholder="Username" /><br/>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-sm-2" for="password"><i class="glyphicon glyphicon-lock"></i></label>
                    <div class="col-sm-10">
                        <input type="password" class="form-control input-lg" id="password" name="password" placeholder="Password" /><br/>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-2"></div>
                    <div class="col-sm-10">
                        <button type="submit" class="btn btn-lg btn-warning pull-right">Login</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

	<div class="container">
        <p><small>Username: gordy | Password: secret</small></p>
    </div>

<?php require 'footer.php'; ?>