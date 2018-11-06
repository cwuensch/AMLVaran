<?php
    include_once "common/base.php";
    $pageTitle = "Verify Your Account";
    include_once "common/header.php";

    if(isset($_GET['v']) && isset($_GET['e']))
    {
        include_once "inc/class.users.inc.php";
        $users = new DkhUsers($db);
        $ret = $users->verifyAccount();
    }
    else
    {
        header("Location: register.php");
        exit;
    }
    ?>

    <div class="container-fluid">
        <div id="start" class="row row1">
            <div class="col-md-12 column1">
                <?php if(isset($ret[0])) {
            echo isset($ret[1]) ? $ret[1] : NULL;
        } else {
            echo '<meta http-equiv="refresh" content="0;index.php">';
        } ?>
            </div>
        </div>
    </div>

    <?php
    if(isset($ret[0]) && $ret[0] == 5) {
        echo '<meta http-equiv="refresh" content="3;index.php">';
    }
    include_once 'common/footer.php';
?>
