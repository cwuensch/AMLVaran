<?php

// Include database credentials
include_once "../inc/constants.inc.php";

$dsn = "mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8";
$db = new PDO($dsn, DB_USER, DB_PASS);

$SvgWidth = "22px";
$SvgHeight = "12px";
$rectY = "10";
$rectHeight = "12";
$positionX = 0;
$gap1 = 4;
$gap2 = 15;
$boxWidth = 16;
$textWidth1 = 250;
$textWidth2 = 192;
$textWidth3 = 77;

$sqlGetPid = 'Select PatientID FROM samples WHERE SampleID=:sid';

$sqlGetMetadata = 'SELECT Patientname, Patientnumber, Birthdate, Sex, SampleTakeDate, Diagnosis, Comments, design FROM
samples LEFT JOIN patients ON samples.PatientID = patients.PatientID
WHERE SampleID = :sid';

$sqlGetOverview = file_get_contents('/var/amlvaran/www/report/getOverview.sql');

$sqlGetRanges = file_get_contents('/var/amlvaran/www/report/getRanges.sql');

$sqlGetRelevant = file_get_contents('/var/amlvaran/www/report/getRelevant.sql');

$sqlGetDiagnosis = file_get_contents('/var/amlvaran/www/report/getDiagnosis.sql');

try {
    $stmt = $db->prepare($sqlGetPid);
    $stmt->bindParam(':sid', $sid, PDO::PARAM_INT);
    $stmt->execute();
    if($stmt->rowCount()>=1) {
        $pid = $stmt->fetch(PDO::FETCH_ASSOC)['PatientID'];
    } else {
        echo "no matching pid data received";
    }
} catch(PDOException $e) {
    echo "problem with database query"; }

if(isset($pid)){
    try {
        $stmt = $db->prepare($sqlGetMetadata);
        $stmt->bindParam(':sid', $sid, PDO::PARAM_INT);
        $stmt->execute();
        if($stmt->rowCount()==1) {
            $metadata = $stmt->fetch(PDO::FETCH_ASSOC);
            $design = $metadata['design'];
        } else {
            echo "no data received for this sample";
        }
    } catch(PDOException $e) {
        echo "problem with database query";
    }

    if(isset($design)){
        try {
            $stmt = $db->prepare($sqlGetOverview);
            $stmt->bindParam(':sid', $sid, PDO::PARAM_INT);
            $stmt->bindParam(':version', $version, PDO::PARAM_INT);
            $stmt->bindParam(':design', $design, PDO::PARAM_INT);
            $stmt->execute();
            if($stmt->rowCount()>=1) {
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } else {
                echo "no data received for this sample";
            }
        } catch(PDOException $e) {
            echo "problem with database query";
        }
        try {
            $stmt = $db->prepare('SELECT Sequencer, Panel, Technique, Remarks FROM cfg_LabProfiles WHERE DesignID=:design');
            $stmt->bindParam(':design', $design, PDO::PARAM_INT);
            $stmt->execute();
            if($stmt->rowCount()==1) {
                $labdata = $stmt->fetch(PDO::FETCH_ASSOC);
                $sequencer = $labdata['Sequencer'];
                $panel = $labdata['Panel'];
            }
        } catch(PDOException $e) {
            echo "problem with database query";
        }
    }
}

if(isset($rows)){
    if($pdf == true) echo '<!DOCTYPE HTML>
    <head>
      <meta charset="utf-8"/>
      <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
      <meta name="viewport" content="width=device-width, initial-scale=1"/>
      <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags-->
      <title>AML VARAN | <?php echo $pageTitle ?></title>
      <link href="/var/amlvaran/www/common/stylesheets/reportstylesheet.css" rel="stylesheet"/>
    </head>
    <p class="head3">Clinical Variant Report</p>
    <p class="warningpdf">This is not a medical product. Use only for research purposes!</p>';

    if($pdf == true) {
        if(isset($metadata)) {
            echo '<div style="width: 500px; float: right;"><b>Sample:</b><br/><table><tr><td style="width:130px">Sample taken:</td><td>' . $metadata['SampleTakeDate'] . '</td></tr><tr><td>Diagnosis:</td><td>' . $metadata['Diagnosis'] . '</td></tr><tr><td>Comments:</td><td>' . $metadata['Comments'] . '</td></tr></table><br/><br/></div>';
            echo '<div style="width: 500px;"><b>Patient:</b><br/><table><tr><td style="width:130px">Name:</td><td>' . $metadata['Patientname'] . '</td></tr><tr><td>Birth date:</td><td>' . $metadata['Birthdate'] . '</td></tr><tr><td>Patient number:</td><td>' . $metadata['Patientnumber'] . '</td></tr><tr><td>Sex:</td><td>' . $metadata['Sex'] . '</td></tr></table><br/><br/></div>';
        }
    }

    if($pdf == true) {
        echo '<p class="head1pdf">Known Mutations</p>
        <p class="head2pdf">Mutations within the following regions are known to have therapeutical consequences.</p>';
    } else {
        echo '<p class="head1">Known Mutations</p>
        <p class="head2">Mutations within the following regions are known to have therapeutical consequences.</p>';
    }

    echo '<div class="overview"><div style="overflow:hidden;"><p style="margin: 10px 0px 0px; color: #555;">';
    echo '<svg width=' . $SvgWidth . ' height=' . $SvgHeight . ' xmlns="http://www.w3.org/2000/svg">';
    echo '<rect x="0%" ' . $rectY . ' height=' . $rectHeight . ' rx="3" ry="3" width="100%" style="fill: #c61213"/></svg>';
    echo ' Mutations found</p>';
    
    $overviewstate = 0;
    foreach ($rows as $row) {
        if($row['NrMutations'] >= 1) {
            echo '<div class="hotspotsbox mutationsfound" id="hotspotsbox' . $row['MutationID'] . '" style="float:left;">' . $row['name'] . '</div>';
        } else {
            if($row['isBadCovered'] == 1) {
                if($overviewstate <= 1) {
                    echo '</div><div style="overflow:hidden;"><p style="margin: 10px 0px 0px; color: #555;">';
                    echo '<svg width=' . $SvgWidth . ' height=' . $SvgHeight . ' xmlns="http://www.w3.org/2000/svg">';
                    echo '<rect x="0%" ' . $rectY . ' height=' . $rectHeight . ' rx="3" ry="3" width="100%" style="fill: #dbab21"/></svg>';
                    echo ' No mutations found, but insufficient coverage</p>';
                    $overviewstate = 2;
                }
                echo '<div class="hotspotsbox badcovered" id="hotspotsbox' . $row['MutationID'] . '" style="float:left;">' . $row['name'] . '</div>';
            } else {
                if($overviewstate == 0) {
                    echo '</div><div style="overflow:hidden;"><p style="margin: 10px 0px 0px; color: #555;">';
                    echo '<svg width=' . $SvgWidth . ' height=' . $SvgHeight . ' xmlns="http://www.w3.org/2000/svg">';
                    echo '<rect x="0%" ' . $rectY . ' height=' . $rectHeight . ' rx="3" ry="3" width="100%" style="fill: #43ae44"/></svg>';
                    echo ' No (relevant) mutations found</p>';
                    $overviewstate = 1;
                }
                echo '<div class="hotspotsbox nomutations" id="hotspotsbox' . $row['MutationID'] . '" style="float:left;">' . $row['name'] . '</div>';
            }
        }
    }
    echo '</div></div>';

    $stmtRanges = $db->prepare($sqlGetRanges);
    $stmtVariants = $db->prepare($sqlGetRelevant);
    foreach ($rows as $row) {

        if($row['NrMutations'] >= 1) {
            try {
                $stmtVariants->bindParam(':sid', $sid, PDO::PARAM_INT);
                $stmtVariants->bindParam(':version', $version, PDO::PARAM_INT);
                $stmtVariants->bindParam(':mid', $row['MutationID'], PDO::PARAM_INT);
                $stmtVariants->execute();
                if($stmtVariants->rowCount()>=1) {
                    $rowsRelevant = $stmtVariants->fetchAll(PDO::FETCH_ASSOC);
                } else {
                    echo 'Error: No mutations received';
                    $rowsRelevant = [];
                }
            }  catch(PDOException $e) {
                echo 'Error: problem with database query';
                $rowsRelevant = [];
            }
        } else {
            $rowsRelevant = [];
        }

        try {            
            $stmtRanges->bindParam(':sid', $sid, PDO::PARAM_INT);
            $stmtRanges->bindParam(':version', $version, PDO::PARAM_INT);
            $stmtRanges->bindParam(':design', $design, PDO::PARAM_INT);
            $stmtRanges->bindParam(':mid', $row['MutationID'], PDO::PARAM_INT);
            $stmtRanges->execute();
            if($stmtRanges->rowCount()>=1) {
                $targets = $stmtRanges->fetchAll(PDO::FETCH_ASSOC);
            } else {
                echo 'Error: No ranges received';
                $targets = [];
            }
        }  catch(PDOException $e) {
            echo 'Error: problem with database query';
            $targets = [];
        }

        if($pdf == true) {
            echo '<table class="reporttable pdfwrapper" id="reporttable' . $row['MutationID'] . '"><tr><td rowspan="6" class="firstcolumn firstcolumnpdf">';
        } else {
            echo '<table class="reporttable" id="reporttable' . $row['MutationID'] . '"><tr><td rowspan="6" class="firstcolumn">';
        }


        if($row['NrMutations'] >= 1) {
            echo '<div class="hotspotsboxinside mutationsfound" id="hotspotsboxinside' . $row['MutationID'] . '">' . $row['name'] . '</div><p class="circlelabel mutationsfound">Mutations found in this region</p></td><td><p class="reporttableheadline">Considered Region</p></td></tr>';
        } else {
            if($row['isBadCovered'] == 1) {
                echo '<div class="hotspotsboxinside badcovered" id="hotspotsboxinside' . $row['MutationID'] . '">' . $row['name'] . '</div><p class="circlelabel badcovered">No mutations but low coverage in this region</p></td><td><p class="reporttableheadline">Considered Region</p></td></tr>';
            } else {
                echo '<div class="hotspotsboxinside nomutations" id="hotspotsboxinside' . $row['MutationID'] . '">' . $row['name'] . '</div><p class="circlelabel nomutations">No mutations found in this region</p></td><td><p class="reporttableheadline">Considered Region</p></td></tr>';
            }
        }

        echo '<tr>
            <td class="reporttablecontent">';

        include('getimage.php');

        echo '
            </td>
        </tr>
        <tr>
            <td><p class="reporttableheadline">Region evidence</p></td>
        </tr>
        <tr>
            <td class="reporttablecontent"><p>' . $row['MutText'] . '</p><a href="' . $row['MutRef'] . '">' . $row['MutRef'] . '</a></td>
        </tr>
        <tr>
            <td><p class="reporttableheadline">Mutations found</p></td>
        </tr>
        <tr>
            <td class="reporttablecontent">';

        if(sizeof($rowsRelevant) > 0) {
            if($pdf == true) {
                echo '<table class="variantstable tablepdf">';
            } else {
                echo '<table class="variantstable">';
            }
/*            echo '<thead>
                <tr>
                    <th>Color</th>
                    <th title="ID in the dbSNP-Database">dbSNP</th>
                    <th title="Type of mutation">type</th>
                    <th title="Rating by appreci8">Cat</th>
                    <th>transcript</th>
                    <th>exon</th>
                    <th>codon</th>
                    <th>protein</th>
                    <th title="Number Reads with mutation">NrRd</th>
                    <th title="Coverage">Cvg</th>
                    <th title="Variant Allelic Frequency">Freq</th>
                    <th "1000 Genomes Allelic Frequency">1000G</th>
                    <th "Provean score">Provean</th>
                    <th "ClinVar database significance rating">Clinvar</th>
                    <th "Occurances with haematopoetic_and_lymphoid_tissue in COSMIC database">Cosmic</th>
                </tr>
            </thead>
            <tbody>'; */

            $variantcolors = ['#1DFFF5', '#D33EFF', '#F5FF17', '#2CFF3F', '#277AFF'];
            $coloriterator = 0;
            
            // Table header
            print("<thead>\n");
            print('  <tr>');
            print("<th>Color</th>");
            foreach (array_keys($rowsRelevant[0]) as $name)
                print("<th>$name</th>");
            print("</tr>\n");
            print("</thead>\n<tbody>\n");
            
            foreach ($rowsRelevant as $row)
            {
                print('  <tr>');
                print("<td><div class='smallcircle' style='background-color: $variantcolors[$coloriterator];' ></div></td>");
//                for ($i = 0; $i < count($row); $i++)
                $i = 0;
                foreach ($row as $val)
                {
//                    $val = $row[$i];
                
/*                    //Replace number in CLNSIG with exact description
                    $rowRelevant['CLNSIG'] = str_replace('0', 'uncertain significance', $rowRelevant['CLNSIG']);
                    $rowRelevant['CLNSIG'] = str_replace('1', 'not provided', $rowRelevant['CLNSIG']);
                    $rowRelevant['CLNSIG'] = str_replace('2', 'benign', $rowRelevant['CLNSIG']);
                    $rowRelevant['CLNSIG'] = str_replace('3', 'likely benign', $rowRelevant['CLNSIG']);
                    $rowRelevant['CLNSIG'] = str_replace('4', 'likely pathogenic', $rowRelevant['CLNSIG']);
                    $rowRelevant['CLNSIG'] = str_replace('5', 'pathogenic', $rowRelevant['CLNSIG']);
                    $rowRelevant['CLNSIG'] = str_replace('6', 'drug response', $rowRelevant['CLNSIG']);
                    $rowRelevant['CLNSIG'] = str_replace('7', 'histocompatibility', $rowRelevant['CLNSIG']); */

//                    if (array_keys($row)[$i] == 'dbSNP')
//                        $val2 = "<a href='http://www.ncbi.nlm.nih.gov/SNP/snp_ref.cgi?searchType=adhoc_search&type=rs&rs=$val>$val</a>";
//                    else
                        $val2 = str_replace(',', '<br>', $val);
                    print("<td>$val2</td>");
                    $i++;
                }
            
                $coloriterator += 1;
                $coloriterator = $coloriterator % 5;
            }            
            
            foreach($rowsRelevant as $rowRelevant) {
                //Replace | and , from CLNSIG; exon; cordon and protein and replace it with <br/>
                $rowRelevant['CLNSIG'] = str_replace('|', '<br/>', $rowRelevant['CLNSIG']);
                $rowRelevant['transcript'] = str_replace(',', '<br/>', $rowRelevant['transcript']);
                $rowRelevant['exon'] = str_replace(',', '<br/>', $rowRelevant['exon']);
                $rowRelevant['codon'] = str_replace(',', '<br/>', $rowRelevant['codon']);
                $rowRelevant['protein'] = str_replace(',', '<br/>', $rowRelevant['protein']);
            }

            echo '</tbody>
            </table>';

        } else {
            echo 'No Mutations';
        }

        echo '</td>
        </tr>
    </table>';
    }

    echo '<p style="color:#aaa">Coverage color-coding:</p>';
    echo '<svg width="1250px" height="20px" xmlns="http://www.w3.org/2000/svg">';

    echo '<rect x="' . $positionX . '" y="1" height="16" rx="3" ry="3" width="16" style="fill: #22670B"/>';
    $positionX += $gap1 + $boxWidth;
    echo '<text x="' . $positionX . '" y="16" height="16" width="10%" font-size="10">Full region meets coverage threshold (default: 20 reads)</text>';
    $positionX += $gap2 + $textWidth1;
    
    echo '<rect x="' . $positionX . '" y="1" height="16" rx="3" ry="3" width="16" style="fill: #7EB838"/>';
    $positionX += $gap1 + $boxWidth;
    echo '<text x="' . $positionX . '" y="16" height="16" width="10%" font-size="10">>=99% of region meets coverage threshold</text>';
    $positionX += $gap2 + $textWidth2;

    echo '<rect x="' . $positionX . '" y="1" height="16" rx="3" ry="3" width="16" style="fill: #B8CF3E"/>';
    $positionX += $gap1 + $boxWidth;
    echo '<text x="' . $positionX . '" y="16" height="16" width="10%" font-size="10">>=98% coverage</text>';
    $positionX += $gap2 + $textWidth3;

    echo '<rect x="' . $positionX . '" y="1" height="16" rx="3" ry="3" width="16" style="fill: #E3BF3D"/>';
    $positionX += $gap1 + $boxWidth;
    echo '<text x="' . $positionX . '" y="16" height="16" width="10%" font-size="10">>=97% coverage</text>';
    $positionX += $gap2 + $textWidth3;

    echo '<rect x="' . $positionX . '" y="1" height="16" rx="3" ry="3" width="16" style="fill: #DB7432"/>';
    $positionX += $gap1 + $boxWidth;
    echo '<text x="' . $positionX . '" y="16" height="16" width="10%" font-size="10">>=96% coverage</text>';
    $positionX += $gap2 + $textWidth3;

    echo '<rect x="' . $positionX . '" y="1" height="16" rx="3" ry="3" width="16" style="fill: #B04925"/>';
    $positionX += $gap1 + $boxWidth;
    echo '<text x="' . $positionX . '" y="16" height="16" width="10%" font-size="10">>=95% coverage</text>';
    $positionX += $gap2 + $textWidth3;

    echo '<rect x="' . $positionX . '" y="1" height="16" rx="3" ry="3" width="16" style="fill: #902B1D"/>';
    $positionX += $gap1 + $boxWidth;
    echo '<text x="' . $positionX . '" y="16" height="16" width="10%" font-size="10">>=94% coverage</text>';
    $positionX += $gap2 + $textWidth3;

    echo '<rect x="' . $positionX . '" y="1" height="16" rx="3" ry="3" width="16" style="fill: #90191C"/>';
    $positionX += $gap1 + $boxWidth;
    echo '<text x="' . $positionX . '" y="16" height="16" width="10%" font-size="10"><94% coverage</text>';
    $positionX += $gap2 + $textWidth3;

    echo '</svg>';

    try {
        $stmt = $db->prepare($sqlGetDiagnosis);
        $stmt->bindParam(':sid', $sid, PDO::PARAM_INT);
        $stmt->bindParam(':version', $version, PDO::PARAM_INT);
        $stmt->bindParam(':design', $design, PDO::PARAM_INT);
        $stmt->execute();
        $diagnosis = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }  catch(PDOException $e) {
        echo 'Error: problem with database query';
        $targets = [];
    }

    echo '<div class="diagnosispanel"><head4>Possible interpretation</head4><table style="font-size:14px;">';

    foreach($diagnosis as $item) {
        echo '<tr><td style="width:400;">';

        switch($item['Color']) {
            case '1':
                echo '<b aria-hidden="true" data-toggle="tooltip" data-placement="top" title="' . $item['Rule'] . '" style="color:green;">' . $item['Prognosis'] . '</b>';
                break;
            case '2':
                echo '<b aria-hidden="true" data-toggle="tooltip" data-placement="top" title="' . $item['Rule'] . '" style="color:orange;">' . $item['Prognosis'] . '</b>';
                break;
            case '0':
                echo '<b aria-hidden="true" data-toggle="tooltip" data-placement="top" title="' . $item['Rule'] . '" style="color:red;">' . $item['Prognosis'] . '</b>';
                break;
            default:
                echo '<b aria-hidden="true" data-toggle="tooltip" data-placement="top" title="' . $item['Rule'] . '">' . $item['Prognosis'] . '</b>';
        }

        echo '</td><td>Source: ';
        if($item['URL'] != null) echo '<a href="' . $item['URL'] . '" target="_blank">';
        echo $item['Source'];
        if($item['URL'] != null) echo '</a>';
        echo '</td></tr>';
    }

    echo '</table></div>';

    echo '<p style="color:#aaa">This sample was sequenced with lab design ' . $design . ' (Sequencer: ' . $sequencer . ', Panel: ' . $panel . ').<br>
    The analysis and report generation was generated on ' . date(DATE_RFC822) . ' with AMLVaran configuration version ' . $version . '.<br>
    Detailed information about the processing steps can be obtained from <a href="http://amlvaran.uni-muenster.de/doc/Version' . $version . '.pdf">http://amlvaran.uni-muenster.de/doc/Version' . $version . '.pdf.</p>';
    
//    echo '<p style="color:#aaa">The analysis was performed on 2016-01-17 with our AML configuration 1, consisting of:<br>
//Design: Targeted NGS, Illumina MySeq with Haloplex custom panel “IMI-v1”.<br>
//Analysis: based on BWA-mem 0.7.12, GATK 3.30 and VariantTools 2.7 (pipeline version v0).<br>
//Interpretation: based on clinVar database from 2015-09-29 and custom hotspot definitions v1.</p>';
}

?>
