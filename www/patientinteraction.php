<?php

session_start();

include_once "inc/constants.inc.php";
include_once "inc/class.patients.inc.php";

if(!empty($_POST['action'])
&& isset($_SESSION['LoggedIn'])
&& $_SESSION['LoggedIn']==1)
{
    $patients = new DkhPatients();
    switch($_POST['action'])
    {
        case 'getPatients':
            echo json_encode($patients->getPatients());
            break;
        case 'getPatient':
            if(!empty($_POST['pid']))
                echo json_encode($patients->getPatient($_POST['pid']));
            break;
        case 'getPatientBySid':
            if(!empty($_POST['sid']))
                echo json_encode($patients->getPatientBySid($_POST['sid']));
            break;
        case 'updatePatient':
            if(!empty($_POST['pid']) && !empty($_POST['pname']))
                echo json_encode($patients->updatePatient($_POST['pid'],$_POST['pname'],$_POST['pnumber'],$_POST['bd'],$_POST['sex']));
            break;
        case 'createPatient':
            if(!empty($_POST['pname']))
                echo json_encode($patients->createPatient($_POST['pname'],$_POST['pnumber'],$_POST['bd'],$_POST['sex']));
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
