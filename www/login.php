<?php
    include_once "common/base.php";
    $pageTitle = "Log In";
    include_once "common/header.php";
?>

    <div class="container-fluid">
        <div id="start" class="row row1">
            <div class="col-md-12 column1">

                <?php
    $headline = 'Please log in:';
    $showform = TRUE;
    if(!empty($_SESSION['LoggedIn']) && !empty($_SESSION['Username'])) {
        $showform = FALSE; ?>

                    <p>You are currently <strong>logged in</strong> and will be redirected to the start page.</p>
                    <script type="text/javascript">
                        window.location = "patients.php"; //before that: index.php

                    </script>

                    <?php
    } elseif(!empty($_POST['email']) && !empty($_POST['password'])) {
                    include_once 'inc/class.users.inc.php';
                    $users = new DkhUsers($db);
                    switch($users->accountLogin()) {
                        case 3:
                            echo "<h2>Success! Redirecting to previous page...</h2>";
                            
                            
                            $myString = $_SERVER['HTTP_REFERER'];
                            $findMe   = 'results.php';
                            $pos = strpos($myString, $findMe);
                            if ($pos === false) {
                                echo "<meta http-equiv=\"refresh\" content=\"0;url=/patients.php\"/>";
                            } else {
                                echo "<meta http-equiv=\"refresh\" content=\"0;url=".$_SERVER['HTTP_REFERER']."\"/>";
                            }
                            $showform = FALSE;
                            break;
                        case 2:
                            $headline = 'Your account is not verified yet. Please check your Email for the verification link.';
                            $showform = TRUE;
                            break;
                        case 1:
                            $headline = 'Login failed. Try again:';
                            $showform = TRUE;
                            break;
                        case 0:
                            $headline = 'There was a problem with the database. Please try again:';
                            $showform = TRUE;
                            break;
                    } ?>
                        <?php
    }
    if($showform) { ?>
                            <h2>
                                <?php echo $headline; ?>
                            </h2>
                            <br/>
                            <br/>
                            <form action="login.php" method="post" accept-charset="UTF-8">
                                <input id="email" style="margin-bottom: 15px;" type="text" name="email" size="30" placeholder="Email" maxlength="50" />
                                <br />
                                <input id="password" style="margin-bottom: 15px;" type="password" name="password" size="30" placeholder="Password" maxlength="50" />
                                <br />
                                <input style="clear: left; width: 100px; height: 32px; font-size: 13px;" type="submit" value="Sign In" class="btn btn-default" />
                            </form>
                            <p><a href="password.php">Did you forget your password?</a></p>
                            <?php
    } ?>
            </div>
        </div>
    </div>

    <?php include_once "common/footer.php"; ?>
