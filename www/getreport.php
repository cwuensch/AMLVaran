<?php

session_start();

include_once "inc/constants.inc.php";
include_once "inc/class.samples.inc.php";

if(isset($_POST['sid']) && isset($_POST['version']) && isset($_SESSION['LoggedIn']) && $_SESSION['LoggedIn']==1)
{
    $samples = new DkhSamples();
    $sid = $_POST['sid'];
    $version = $_POST['version'];
    if(isset($_POST['pdf']) && $_POST['pdf'] == 'true') {
        $pdf = true;
    } else{
        $pdf = false;
    }
    $validsid = $samples->checkSampleID($sid);

    if ($validsid[0] == 1) {
        if ($validsid[1] == true) {

            include_once "report/generatereport.php";

        } else {
            echo "The sid does not match with the currently logged in user";
        }
    } else {
        echo "Error while checking sid";
    }
}
else
{
    echo "Please log in";
}

?>
