<?php
    //include("pdftest/mpdf/mpdf.php");

session_start();

include_once "inc/constants.inc.php";
include_once "inc/class.samples.inc.php";

if(isset($_SESSION['LoggedIn']) && $_SESSION['LoggedIn']==1) {
    if(isset($_POST['html']) && isset($_POST['sid']) && isset($_POST['pid']) && isset($_POST['version'])) {
        /*$mpdf = new mPDF('c','A4');

        $stylesheet = file_get_contents('common/stylesheets/reportstylesheet.css');
        $mpdf->WriteHTML($stylesheet,1);

        $mpdf->WriteHTML($_POST['html'],2);
        $mpdf->Output('pdftest/pdftest.pdf','F');
        exit;*/

        $sid = $_POST['sid'];
        $pid = $_POST['pid'];
        $version = $_POST['version'];
        $returnstring = '';

        $samples = new DkhSamples();
        $validsid = $samples->checkSampleID($sid);

        if ($validsid[0] == 1) {
            if ($validsid[1] == true) {
                //pruefe ob die Ordnerstruktur vorhanden ist und erzeuge sie falls noetig
                if (!file_exists('/var/amlvaran/samples/' . $pid) && !is_dir('/var/amlvaran/samples/' . $pid)) {
                    mkdir ('/var/amlvaran/samples/' . $pid); 
                    chgrp('/var/amlvaran/samples/' . $pid, 'amlvaran');
                    chmod('/var/amlvaran/samples/' . $pid, 0775);
                }
                if (!file_exists('/var/amlvaran/samples/' . $pid . '/' . $sid) && !is_dir('/var/amlvaran/samples/' . $pid . '/' . $sid)) {
                    mkdir ('/var/amlvaran/samples/' . $pid . '/' . $sid); 
                    chgrp('/var/amlvaran/samples/' . $pid . '/' . $sid, 'amlvaran');
                    chmod('/var/amlvaran/samples/' . $pid . '/' . $sid, 0775);
                }
                if (!file_exists('/var/amlvaran/samples/' . $pid . '/' . $sid . '/reports') && !is_dir('/var/amlvaran/samples/' . $pid . '/' . $sid . '/reports')) {
                    mkdir ('/var/amlvaran/samples/' . $pid . '/' . $sid . '/reports'); 
                    chgrp('/var/amlvaran/samples/' . $pid . '/' . $sid . '/reports', 'amlvaran');
                    chmod('/var/amlvaran/samples/' . $pid . '/' . $sid . '/reports', 0775);
                }
                if (!file_exists('/var/amlvaran/samples/' . $pid . '/' . $sid . '/reports/version' . $version) && !is_dir('/var/amlvaran/samples/' . $pid . '/' . $sid . '/reports/version' . $version)) {
                    mkdir('/var/amlvaran/samples/' . $pid . '/' . $sid . '/reports/version' . $version); 
                    chgrp('/var/amlvaran/samples/' . $pid . '/' . $sid . '/reports/version' . $version, 'amlvaran');
                    chmod('/var/amlvaran/samples/' . $pid . '/' . $sid . '/reports/version' . $version, 0775);
                }

                $returnstring .= file_put_contents('../samples/' . $pid . '/' . $sid . '/reports/version' . $version . '/report.htm', $_POST['html']);

                $returnstring .= exec('inc/wkhtmltox/bin/wkhtmltopdf --zoom 0.6 ../samples/' . $pid . '/' . $sid . '/reports/version' . $version . '/report.htm ../samples/' . $pid . '/' . $sid . '/reports/version' . $version . '/report.pdf');

                chgrp('../samples/' . $pid . '/' . $sid . '/reports/version' . $version . '/report.htm', 'amlvaran');
                chmod('../samples/' . $pid . '/' . $sid . '/reports/version' . $version . '/report.htm', 0664);
                chgrp('../samples/' . $pid . '/' . $sid . '/reports/version' . $version . '/report.pdf', 'amlvaran');
                chmod('../samples/' . $pid . '/' . $sid . '/reports/version' . $version . '/report.pdf', 0664);

                $samples->createPdfReport($sid, $version);

                echo json_encode(array(1, $returnstring));
            } else {
                echo json_encode(array(0, "The sid does not match with the currently logged in user"));
            }
        } else {
            echo json_encode(array(0, "Error while checking sid"));
        }
    } else {
        echo json_encode(array(0, 'no sufficient data'));
    }
}   else {
    echo json_encode(array(0, 'please log in!'));
}

?>
