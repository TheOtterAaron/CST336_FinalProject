<?php

    session_start();
    if(isset($_SESSION['username']))
    {
        require 'db-con.php';
        require 'authenticate.php';

        if(isset($_POST['currentpassword']))
        {
            if (!Authenticate($dbCon, $_SESSION['username'], $_POST['currentpassword']))
            {
                    $error = "Incorrect current password.";
            }
            else
            {
                if($_POST['newpassword1'] != $_POST['newpassword2'])
                {
                    $error = "New passwords do not match.";
                }
                else
                {
                    $sql = "UPDATE BOOK_USERS
                            SET password = :password
                            WHERE username = :username";

                    $statement = $dbCon->prepare($sql);
                    $statement->execute(array
                    (
                        ":password" => hash("sha1", $_POST['newpassword1']),
                        ":username" => $_SESSION['username']
                    ));

                    $result = "Password updated!";
                }
            }
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
            <h1>Update Password</h1>
            <p class="lead">Maybe You Should Write it Down...</p>
        </div>
    </div>

	<div class="container container-password">
        <form class="form-horizontal" role="form" method="post">
            <div class="form-group">
                <label class="control-label col-sm-4" for="currentpassword">Current Password</label>
                <div class="col-sm-8">
                    <input type="password" class="form-control" name="currentpassword" id="currentpassword" />
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-sm-4" for="newpassword1">New Password</label>
                <div class="col-sm-8">
                    <input type="password" class="form-control" name="newpassword1" id="newpassword1" />
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-sm-4" for="newpassword2">Repeat Password</label>
                <div class="col-sm-8">
                    <input type="password" class="form-control" name="newpassword2" id="newpassword2" />
                </div>
            </div>
            <div class="form-group">
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
                <input type="submit" class="btn btn-warning pull-right" value="Update Password" />
            </div>
        </form>
    </div>

<?php require 'footer.php'; ?>