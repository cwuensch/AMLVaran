<?php
    include_once "common/base.php";
    include_once "inc/class.samples.inc.php";
    include_once "inc/class.designs.inc.php";

if(isset($_SESSION['LoggedIn']) && isset($_SESSION['Username']) && $_SESSION['LoggedIn']==1) {
    if(isset($_POST['pid']) && isset($_POST['sid'])) { //SampleUpload
        
        define("UPLOAD_DIR", '../samples/' . $_POST['pid'] . '/' . $_POST['sid'] . '/');
        
        if (!empty($_FILES["fileUpload"])) {
            $myFile = $_FILES["fileUpload"];

            if ($myFile["error"] !== UPLOAD_ERR_OK) {
                echo "<h2>An error occurred.</h2>";
                exit;
            }

            
            $upload_file_name = pathinfo($myFile['name'], PATHINFO_FILENAME);
            $extension = end((explode(".", $myFile["name"])));
            switch($extension)
            {
                case "fastq":
                    $name = $upload_file_name . "." . $extension;
                    $state_code = 0;
                    break;
                case "vcf":
                    $name = 'Variants_raw.' . $extension;
                    $state_code = 50;
                    break;
                case "bam": 
                    $name = 'Sample.' . $extension;
                    $state_code = 20;
                    break;
                    
                case NULL: // Handle no file extension
                    break;
            }

            if(!file_exists('../samples/' . $_POST['pid'] . '/')) {
                mkdir('../samples/' . $_POST['pid'] . '/', 0777, true);
//                chgrp('../samples/' . $_POST['pid'] . '/','amlvaran');
                chmod('../samples/' . $_POST['pid'] . '/',0777);
            }

            if (!file_exists(UPLOAD_DIR)){
                mkdir(UPLOAD_DIR, 0777, true);
//                chgrp(UPLOAD_DIR,'amlvaran');
                chmod(UPLOAD_DIR,0777);
            }

            // preserve file from temporary directory
            $success = move_uploaded_file($myFile["tmp_name"], UPLOAD_DIR . $name);
            if (!$success) {
                echo "<h2>Unable to save file.</h2>";
                exit;
            }

            // set proper permissions on the new file
            chmod(UPLOAD_DIR . $name, 0644);

            $samples = new DkhSamples();
            $ret = $samples->updateFileUpload($_POST['sid'], $state_code);

            if($ret[0] == 1) {
                echo "Success.";
            }


        } else {
            echo "<h2>No file uploaded.</h2>";
        }

    } elseif(isset($_POST['did'])) { //DesignUpload
        
        define("UPLOAD_DIR", '../designs/' . $_POST['did'] . '/');

        if (!empty($_FILES["fileUpload"])) {
            $myFile = $_FILES["fileUpload"];

            if ($myFile["error"] !== UPLOAD_ERR_OK) {
                echo "<h2>An error occurred.</h2>";
                exit;
            }

            $name = 'upload.txt';            

            if(!file_exists('../designs/' . $_POST['did'] . '/')) {
                mkdir('../designs/' . $_POST['did'] . '/', 0777, true);
//                chgrp('../designs/' . $_POST['did'] . '/','amlvaran');
                chmod('../designs/' . $_POST['did'] . '/',0777);
            }

            if (!file_exists(UPLOAD_DIR)){
                mkdir(UPLOAD_DIR, 0777, true);
//                chgrp(UPLOAD_DIR,'amlvaran');
                chmod(UPLOAD_DIR,0777);
            }

            // preserve file from temporary directory
            $success = move_uploaded_file($myFile["tmp_name"], UPLOAD_DIR . $name);
            if (!$success) {
                echo "<h2>Unable to save file.</h2>";
                exit;
            }

            // set proper permissions on the new file
            chmod(UPLOAD_DIR . $name, 0644);

            $designs = new DkhDesigns();
            
            $proc= $designs->processDesign($_POST['did'],UPLOAD_DIR . $name);
            
            if($proc[0] ==1){
                $ret = $designs->updateFileUpload($_POST['did']);  
                if($ret[0] == 1 && $proc[0]==1) {
                    echo "Success.";
                }
            }
            


        } else {
            echo "<h2>No file uploaded.</h2>";
        }

        
    }
}
