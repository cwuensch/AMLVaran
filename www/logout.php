<?php
    $pageTitle = "Log Out";
    include_once "common/header.php";

    session_start();

    unset($_SESSION['LoggedIn']);
    unset($_SESSION['Username']);
    unset($_SESSION['UserID']);?>

    <div class="container-fluid">
        <div id="start" class="row row1">
            <div class="col-md-12 column1">
                <h2>Logging out...</h2>
                <meta http-equiv='refresh' content='0;index.php'>
            </div>
        </div>
    </div>

    <?php include_once "common/footer.php"; ?>
