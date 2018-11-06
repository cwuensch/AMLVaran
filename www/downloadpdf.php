<?php
session_start();

include_once "inc/constants.inc.php";
include_once "inc/class.samples.inc.php";

if(isset($_SESSION['LoggedIn']) && $_SESSION['LoggedIn']==1) {
    if(isset($_GET['sid']) && isset($_GET['pid']) && isset($_GET['version'])) {

        $sid = $_GET['sid'];
        $pid = $_GET['pid'];
        $version = $_GET['version'];

        $samples = new DkhSamples();
        $validsid = $samples->checkSampleID($sid);

        if ($validsid[0] == 1) {
            if ($validsid[1] == true) {

                $file = '/var/amlvaran/samples/' . $pid . '/' . $sid . '/reports/version' . $version . '/report.pdf';
                if (file_exists($file)) {
                    header('Content-Description: File Transfer');
                    header('Content-Type: application/octet-stream');
                    header('Content-Disposition: attachment; filename="report' . $pid . $sid . $version . '.pdf"');
                    header('Expires: 0');
                    header('Cache-Control: must-revalidate');
                    header('Pragma: public');
                    header('Content-Length: ' . filesize($file));
                    readfile($file);
                    exit;
                }
            } else {
                echo "The sid does not math with the currently logged in user";
            }
        } else {
            echo "Error while checking sid";
        }
    } else {
        echo 'no sufficient data';
    }
}   else {
    echo 'please log in!';
}
?>
