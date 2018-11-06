<?php

session_start();

include_once "inc/constants.inc.php";
include_once "inc/class.users.inc.php";

if(!empty($_POST['action'])
&& isset($_SESSION['LoggedIn'])
&& $_SESSION['LoggedIn']==1)
{
    $users = new DkhUsers();
    switch($_POST['action'])
    {
        case 'createAccount':
            if(!empty($_POST['email']) && !empty($_POST['password']))
                echo json_encode($users->createAccount($_POST['email'], $_POST['password']));
            break;
        case 'createMissingSettings':
            if(!empty($_POST['regionFilterDefault']) && !empty($_POST['typeFilterDefault']) && !empty($_POST['exclusionFiltersDefault']) && !empty($_POST['selectedColumnsDefault']) && !empty($_POST['sortListDefault']) &&       !empty($_POST["columnWidthDefault"]))
                echo json_encode($users->createMissingSettings($_POST['regionFilterDefault'], $_POST['typeFilterDefault'], $_POST['exclusionFiltersDefault'], $_POST['selectedColumnsDefault'], $_POST['sortListDefault'], $_POST['columnWidthDefault']));
            break;
        case 'changeEmail':
            if(!empty($_POST['email']))
                echo json_encode($users->changeEmail($_POST['email']));
            break;
        case 'changePassword':
            if(!empty($_POST['newPassword']) && !empty($_POST['password']))
                echo json_encode($users->changePassword($_POST['newPassword'], $_POST['password']));
            break;
        case 'saveSettings':
            if(isset($_POST['regionFilter']) 
               && isset($_POST['typeFilter']) 
               && isset($_POST['exclusionFilters']) 
               && isset($_POST['selectedColumns']) 
               && isset($_POST['sortList']))
                echo json_encode($users->saveSettings($_POST['regionFilter'], $_POST['typeFilter'], $_POST['exclusionFilters'], $_POST['selectedColumns'], $_POST['sortList'], $_POST['columnWidth']));
            break;
        case 'loadSettings':
            echo json_encode($users->loadSettings());
            break;
        default:
            header("Location: /");
            break;
    }
}
else
{
    $users = new DkhUsers();
    switch($_POST['action'])
    {
        case 'createAccount':
            if(!empty($_POST['email']) && !empty($_POST['password']))
                echo json_encode($users->createAccount($_POST['email'], $_POST['password']));
            break;
        case 'checkEmail':
            if(!empty($_POST['email']))
                echo json_encode($users->checkEmail($_POST['email']));
            break;
        case 'sendPasswordLink':
            if(!empty($_POST['email']))
                echo json_encode($users->sendPasswordLink($_POST['email']));
            break;
        case 'updateForgottenPassword':
            if(!empty($_POST['v']) && !empty($_POST['e']) && !empty($_POST['password']))
                echo json_encode($users->updateForgottenPassword($_POST['v'], $_POST['e'], $_POST['password']));
            break;
        case 'validateNewEmail':
            if(!empty($_POST['v']) && !empty($_POST['e']))
                echo json_encode($users->validateNewEmail($_POST['v'], $_POST['e']));
            break;
        default:
            echo json_encode(array(0, 'please log in'));
            break;
    }
}

?>
