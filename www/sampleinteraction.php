<?php

session_start();

include_once "inc/constants.inc.php";
include_once "inc/class.samples.inc.php";

if(!empty($_POST['action'])
&& isset($_SESSION['LoggedIn'])
&& $_SESSION['LoggedIn']==1)
{
    $samples = new DkhSamples();
    switch($_POST['action'])
    {
        case 'checkSampleID':
            if(!empty($_POST['sid']))
                echo json_encode($samples->checkSampleID($_POST['sid']));
            break;
        case 'getSamplesByUid':
            echo json_encode($samples->getSamplesByUid());
            break;
        case 'getSamplesByPid':
            if(!empty($_POST['pid']))
                echo json_encode($samples->getSamplesByPid($_POST['pid']));
            break;
        case 'getSampleinfo':
            if(!empty($_POST['sid']))
                echo json_encode($samples->getSampleinfo($_POST['sid']));
            break;
        case 'setAppreci8':
            if(!empty($_POST['sid']) && isset($_POST['chr']) && isset($_POST['pos']) && isset($_POST['ref']) && isset($_POST['alt'])  && isset($_POST['score']))
                echo json_encode($samples->setAppreci8($_POST['sid'], $_POST['chr'], $_POST['pos'], $_POST['ref'], $_POST['alt'],  $_POST['score']));
            break;
        case 'clearAppreci8':
            if(!empty($_POST['sid']))
                echo json_encode($samples->clearAppreci8($_POST['sid']));
            break;
        case 'getAdditionalTableInfo':
            if(!empty($_POST['gene']))
                echo json_encode($samples->getAdditionalTableInfo($_POST['gene']));
            break;
        //case 'getDiagnosis':
        //    if(!empty($_POST['sid']))
        //        echo json_encode($samples->getDiagnosis($_POST['sid']));
        //    break;
        //case 'getRegionsForReport':
        //    if(!empty($_POST['sid']))
        //        echo json_encode($samples->getRegionsForReport($_POST['sid']));
        //    break;
        //case 'getOverview':
        //    if(!empty($_POST['sid']))
        //        echo json_encode($samples->getOverview($_POST['sid']));
        //    break;
        //case 'getOverviewVariants':
        //    if(!empty($_POST['sid']) && !empty($_POST['overviewselected']) && !empty($_POST['sortlist']))
        //        echo json_encode($samples->getOverviewVariants($_POST['sid'], $_POST['overviewselected'], $_POST['sortlist'])); #Used by downloadcsv.php
        //    break; 
        //case 'getRelevant':
        //    if(!empty($_POST['sid']) && !empty($_POST['relevantfilter']) && !empty($_POST['sortlist']))
        //        echo json_encode($samples->getRelevantVariants($_POST['sid'], $_POST['relevantfilter'], $_POST['sortlist'])); #Used by downloadcsv.php
        //    break;
        //case 'getCoverage':
        //    if(!empty($_POST['sid']))
        //        echo json_encode($samples->getCoverage($_POST['sid']));
        //    break;
        //case 'getCoverageByRegion':
        //    if(!empty($_POST['sid']) && !empty($_POST['region']))
        //        echo json_encode($samples->getCoverageByRegion($_POST['sid'], $_POST['region']));
        //    break;
        //case 'getWithout':
        //    if(!empty($_POST['sid']))
        //        echo json_encode($samples->getRegionsWithoutMutations($_POST['sid']));
        //    break;
        //case 'getStructural':
        //    if(!empty($_POST['sid']))
        //        echo json_encode($samples->getStructuralVariants($_POST['sid']));
        //    break;
        case 'getCanonicalTranscript':
            if(!empty($_POST['gene']))
                echo json_encode($samples->getCanonicalTranscript($_POST['gene']));
            break;
        case 'getGeneOverview':
            if(!empty($_POST['sid']) && !empty($_POST['design']))
                echo json_encode($samples->getGeneOverview($_POST['sid'],$_POST['design']));
            break;
        case 'getAll':
            if(!empty($_POST['sid']) && isset($_POST['version']) && isset($_POST['designID']))
                echo json_encode($samples->getAllVariants($_POST['sid'], $_POST['version'], $_POST['designID']));
            break;
        //case 'getGroundtruth':
        //    if(!empty($_POST['sid']))
        //        echo json_encode($samples->getGroundtruth($_POST['sid']));
        //    break;
        case 'getPdfReports':
            if(!empty($_POST['sid']))
                echo json_encode($samples->getPdfReports($_POST['sid']));
            break;
          case 'getPdfVersion':
            if(!empty($_POST['sid'])&& !empty('version'))
                echo json_encode($samples->getPdfVersion($_POST['sid'], $_POST['version']));
            break;
        //case 'createPdfReport':
        //    if(!empty($_POST['sid']) && !empty($_POST['version']))
        //        echo json_encode($samples->createPdfReport($_POST['sid'], $_POST['version'])); #Used by generatepdf.php
        //    break;
        case 'getCurrentVersion':
            if(!empty($_POST['sid']))
                echo json_encode($samples->getCurrentVersion($_POST['sid']));
            break;
        case 'createSample':
            if(!empty($_POST['pid']) && !empty($_POST['dataarray']))
                echo json_encode($samples->createSample($_POST['pid'], $_POST['dataarray']));
            break;
        case 'updateSample':
            if(!empty($_POST['sid']) && !empty($_POST['diagnosis']) && !empty($_POST['comments']) && !empty($_POST['std']))
                echo json_encode($samples->updateSample($_POST['sid'], $_POST['diagnosis'], $_POST['comments'], $_POST['std']));
            break;
        case 'getCivicInfo':
            if(isset($_POST['variant_id']))
                echo json_encode($samples->getCivicInfo($_POST['variant_id']));
            break;
        case 'getCivicGenesInfo':
            if(!empty($_POST['gene']))
                echo json_encode($samples->getCivicGenesInfo($_POST['gene']));
            break;
        case 'getCivicVariantsInfo':
            if(!empty($_POST['position']) && !empty($_POST['chr']))
                echo json_encode($samples->getCivicVariantsInfo($_POST['position'], $_POST['chr']));
            break;
        case 'getAllRanges':
            if(isset($_POST['sid']) && isset($_POST['design']) && isset($_POST['version']))
                echo json_encode($samples->getAllRanges($_POST['sid'], $_POST['design'], $_POST['version']));
            break;
        case 'getAllSamples':
                echo json_encode($samples->getAllSamples());
            break;
        case 'updateRating':
            if(!empty($_POST['sid']) && isset($_POST['rating']))
                echo json_encode($samples->updateRating($_POST['sid'], $_POST['rating']));
            break;
        case 'getRating':
            if(!empty($_POST['sid']))
                echo json_encode($samples->getRating($_POST['sid']));
            break;
        case 'test':
            if(!empty($_POST['jd'])) {
                $temp =  json_decode($_POST['jd']);
                echo $temp[0] . ' und ' . $temp[1] . ' und ' . $temp[2];
            }
            break;
        case 'removeSample':
            if(!empty($_POST['sid']) && !empty($_POST['pid']))
                echo json_encode($samples->removeSample($_POST['sid'], $_POST['pid']));
            break;
        default:
            header("Location: /");
            break;
    }
}
else
{
    $result = array(
    0    => 0,
    1  => "Please log in",
    );
    echo json_encode($result);
}

?>
