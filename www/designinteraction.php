<?php

session_start();

include_once "inc/constants.inc.php";
include_once "inc/class.designs.inc.php";

if(!empty($_POST['action'])
&& isset($_SESSION['LoggedIn'])
&& $_SESSION['LoggedIn']==1)
{
    $designs = new DkhDesigns();
    switch($_POST['action'])
    {
        case 'createDesign':
            if(!empty($_POST['dataarray']))
                echo json_encode($designs->createDesign($_POST['dataarray']));
            break;
        case 'getDesigns':
            echo json_encode($designs->getDesigns());
            break;
        case 'processDesign':
            echo json_encode($designs->processDesign());
            break;
        default:
            header("Location: /");
            break;
    }
}
else
{
    echo json_encode(array(0, 'please log in'));
}

?>
