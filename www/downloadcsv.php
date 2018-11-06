<?php
session_start();

include_once "inc/constants.inc.php";
include_once "inc/class.samples.inc.php";

if(isset($_SESSION['LoggedIn']) && $_SESSION['LoggedIn']==1 && isset($_GET['chosentab']))
{
    $headernames = array('region_name', 'region_type', 'REPLACE(chr, "X", "24")+0', 'pos', 'ref', 'alt', 'strand', 'mut_type', 'function', 'Quality', 'QualScore', 'GT', 'AD', 'DP', 'CLNSIG', 'CLNDBN', 'CLNREVSTAT', 'CosmicID', 'CosmicSites', 'NrHaemato', 'NrCivic', 'NrSamples');
    switch($_GET['chosentab']) {
        case 'all':
            if(isset($_GET['sid']) && isset($_GET['sortList']) && isset($_GET['selectedcolumns']) && isset($_GET['allselected']) && isset($_GET['allfilter'])) {
                $selectedcolumns = json_decode($_GET['selectedcolumns']);
                $sortList = json_decode($_GET['sortList']);

                for($i = 0; $i < sizeof($sortList); $i++){
                    $sortList[$i][0] = $headernames[$sortList[$i][0]];
                    if($sortList[$i][1] == 0) {
                        $sortList[$i][1] = 'ASC';
                    } else {
                        $sortList[$i][1] = 'DESC';
                    }
                }

                $samples = new DkhSamples();

                $return = $samples->getAllVariants($_GET['sid'],$_GET['allselected'],$_GET['allfilter'],json_encode($sortList));
            }
            break;


        case 'relevant':
            if(isset($_GET['sid']) && isset($_GET['sortList']) && isset($_GET['selectedcolumns']) && isset($_GET['relevantfilter'])) {
                $selectedcolumns = json_decode($_GET['selectedcolumns']);
                $sortList = json_decode($_GET['sortList']);

                for($i = 0; $i < sizeof($sortList); $i++){
                    $sortList[$i][0] = $headernames[$sortList[$i][0]];
                    if($sortList[$i][1] == 0) {
                        $sortList[$i][1] = 'ASC';
                    } else {
                        $sortList[$i][1] = 'DESC';
                    }
                }

                $samples = new DkhSamples();

                $return = $samples->getRelevantVariants($_GET['sid'],$_GET['relevantfilter'],json_encode($sortList));
            }
            break;


        case 'overview':
            if(isset($_GET['sid']) && isset($_GET['sortList']) && isset($_GET['selectedcolumns']) && isset($_GET['overviewselected'])) {
                $selectedcolumns = json_decode($_GET['selectedcolumns']);
                $sortList = json_decode($_GET['sortList']);

                for($i = 0; $i < sizeof($sortList); $i++){
                    $sortList[$i][0] = $headernames[$sortList[$i][0]];
                    if($sortList[$i][1] == 0) {
                        $sortList[$i][1] = 'ASC';
                    } else {
                        $sortList[$i][1] = 'DESC';
                    }
                }

                $samples = new DkhSamples();

                $return = $samples->getOverviewVariants($_GET['sid'],$_GET['overviewselected'],json_encode($sortList));
            }
            break;
    }

    if($return[0] == 1) {
                    $rows = $return[1];
                    $headers = array_keys($rows[0]);
                    $maxcolumn = 0;

                    $headers2 = array();

                    for($i = 0; $i < sizeof($headers); $i++){
                        if($selectedcolumns[$i] == 1) {
                            array_push($headers2, $headers[$i]);
                        }
                    }

                    $rows2 = array();
                    $temprow = array();

                    for ($i = 0; $i < sizeof($rows); $i++) {
                        $temprow = array();
                        for ($j = 0; $j < sizeof($headers); $j++) {
                            if($selectedcolumns[$j] == 1) {
                                //value replacements needed
                                array_push($temprow, $rows[$i][$headers[$j]]);
                            }
                        }
                        array_push($rows2, $temprow);
                    }

                    foreach($headers2 as &$header) {
                        switch($header) {
                            case 'region_name':
                                $header = 'Gene/Region';
                                break;
                            case 'region_type':
                                $header = 'Region type';
                                break;
                            case 'mut_type':
                                $header = 'Mutation type';
                                break;
                            case 'function':
                                $header = 'Function';
                                break;
                            case 'GT':
                                $header = 'NrAlleles';
                                break;
                            case 'CLNSIG':
                                $header = 'clinvar Significance';
                                break;
                            case 'CLNDBN':
                                $header = 'clinvar Disease';
                                break;
                            case 'CLNREVSTAT':
                                $header = 'Review Status';
                                break;
                            case 'NrHaemato':
                                $header = 'Cosmic NrHaemato';
                                break;
                            default:
                                break;
                        }
                    }

                    /******* Offer CSV File for download as seen in http://code.stephenmorley.org/php/creating-downloadable-csv-files/ *******/

                    // output headers so that the file is downloaded rather than displayed
                    header('Content-Type: text/csv; charset=utf-8');
                    header('Content-Disposition: attachment; filename=data.csv');

                    // create a file pointer connected to the output stream
                    $output = fopen('php://output', 'w');

                    // output the column headings
                    fputcsv($output, $headers2, ';', '"');

                    // loop over the rows, outputting them
                    foreach($rows2 as $row) {
                        fputcsv($output, $row, ';', '"');
                    }

                }
}
?>
