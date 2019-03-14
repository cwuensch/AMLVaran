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
        echo $returnstring;

        $samples = new DkhSamples();
        $validsid = $samples->checkSampleID($sid);

        if ($validsid[0] == 1) {
            if ($validsid[1] == true) {
                //pruefe ob die Ordnerstruktur vorhanden ist und erzeuge sie falls noetig
                if (!file_exists('../samples/' . $pid) && !is_dir('../samples/' . $pid)) {
                    mkdir ('../samples/' . $pid); 
//                    chmod('../samples/' . $pid, 0700);
                }
                if (!file_exists('../samples/' . $pid . '/' . $sid) && !is_dir('../samples/' . $pid . '/' . $sid)) {
                    mkdir ('../samples/' . $pid . '/' . $sid); 
//                    chmod('../samples/' . $pid . '/' . $sid, 0700);
                }
                if (!file_exists('../samples/' . $pid . '/' . $sid . '/reports') && !is_dir('../samples/' . $pid . '/' . $sid . '/reports')) {
                    mkdir ('../samples/' . $pid . '/' . $sid . '/reports'); 
//                    chmod('../samples/' . $pid . '/' . $sid . '/reports', 0700);
                }
                if (!file_exists('../samples/' . $pid . '/' . $sid . '/reports/version' . $version) && !is_dir('../samples/' . $pid . '/' . $sid . '/reports/version' . $version)) {
                    mkdir('../samples/' . $pid . '/' . $sid . '/reports/version' . $version); 
//                    chmod('../samples/' . $pid . '/' . $sid . '/reports/version' . $version, 0700);
                }

                $returnstring .= file_put_contents('../samples/' . $pid . '/' . $sid . '/reports/version' . $version . '/report.htm', urldecode($_POST['html']));
                $returnstring .= exec('inc/wkhtmltox/bin/wkhtmltopdf --zoom 0.6 ../samples/' . $pid . '/' . $sid . '/reports/version' . $version . '/report.htm ../samples/' . $pid . '/' . $sid . '/reports/version' . $version . '/report.pdf');

//                chmod('../samples/' . $pid . '/' . $sid . '/reports/version' . $version . '/report.htm', 0666);
//                chmod('../samples/' . $pid . '/' . $sid . '/reports/version' . $version . '/report.pdf', 0666);

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
