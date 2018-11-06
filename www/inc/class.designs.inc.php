<?php

/**
 * Handles analysis actions within the app
 *
 * PHP version 5
 *
 * @author Sebastian Windau
 *-
 */
class DkhDesigns
{
    /**
     * The database object
     *
     * @var object
     */
    private $_db;

    /**
     * Checks for a database object and creates one if none is found
     *
     * @param object $db
     * @return void
     */
    public function __construct($db=NULL)
    {
        if(is_object($db))
        {
            $this->_db = $db;
        }
        else
        {
            $dsn = "mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8";
            $this->_db = new PDO($dsn, DB_USER, DB_PASS);
        }
    }
    
    /**
     * Stores a new design in the database.
     *
     * @param array dataarray   An array containing Sequencer, Panel, Technique and Remarks
     *
     * @return array    An array containing a status code and status message
     */
    public function createDesign($dataarray) 
    {
        if(isset($_SESSION['UserID']) && isset($_SESSION['LoggedIn']) && $_SESSION['LoggedIn']==1) {
            if(isset($_SESSION['editPermission']) && $_SESSION['editPermission']==1) {
                //TODO: Check ob ein Identischer eintrag bereits in der DB vorhanden ist. Allerdings ist das vielleicht nicht möglich, da die Datei die hochgeladen wird ja unterschiedlich sein könnte, auch wenn die daten in der DB gleich sind.
                if(isset($dataarray)) {
                    $data = json_decode($dataarray);

                    $sql = 'INSERT INTO cfg_LabProfiles (Sequencer, Panel, Technique, Remarks)
                        VALUES (:sequencer, :panel, :technique, :remarks)';
                    try
                    {
                        if($stmt = $this->_db->prepare($sql))
                        $stmt->bindParam(':sequencer', $data[0], PDO::PARAM_STR);
                        $stmt->bindParam(':panel', $data[1], PDO::PARAM_STR);
                        $stmt->bindParam(':technique', $data[2], PDO::PARAM_STR);
                        $stmt->bindParam(':remarks', $data[3], PDO::PARAM_STR);

                        if($stmt->execute())
                        {
                            return array(1, $this->_db->lastInsertId());
                        }
                        else
                        {   
                            return array(0, "Error:"
                                . "Something went wrong while saving the design info.");
                        }
                    }
                    catch(PDOException $e)
                    {
                        return array(0, "Error:"
                            . "Something went wrong while saving the design info.");
                    }
                } else {
                    return array(0, "<h2>Error:</h2>"
                            . "<p>No data array.</p>");
                }
            } else {
                return array(0, "<h2>Error:</h2>"
                    . "<p>You have no edit-Permissions.</p>");
            }
        } else {
            return array(0, "<h2>Error:</h2>"
                    . "<p>It seems like you are not logged in.</p>");
        }
    }
    
    /**
     * Fetches required information of all designs in the Database
     *
     * @return array    An array containing a status code and the query results array or an error message
     */
    public function getDesigns() 
    {
        if(isset($_SESSION['UserID']) && isset($_SESSION['LoggedIn']) && $_SESSION['LoggedIn']==1) {
            $sql = "SELECT DesignID, Sequencer, Panel, Technique, Remarks FROM cfg_LabProfiles WHERE StateCode>0;"; //only fetch successfully uploaded Designs (StateCode>0)
            try
            {
                $stmt = $this->_db->prepare($sql);
                $stmt->execute();
                $rows = $stmt->fetchAll();
                return array(1, $rows);
            }
            catch(PDOException $e)
            {
                return array(0, "<h2>Error:</h2>"
                    . "<p>Could not fetch Designs.</p>");
            }
        } else {
            return array(0, "<h2>Error:</h2>"
                    . "<p>It seems like you are not logged in.</p>");
        }
    }
    
    /**
     * Updates a design in the database to set the State to File Uploaded.
     *
     * @param String $did  The DesignID
     *
     * @return array    An array containing a status code and status message
     */
    public function updateFileUpload($did)
    {
        if(isset($_SESSION['UserID']) && isset($_SESSION['LoggedIn']) && $_SESSION['LoggedIn']==1) {
            if(isset($_SESSION['editPermission']) && $_SESSION['editPermission']==1) {
                $sql = "SELECT DesignID
                    FROM cfg_LabProfiles
                    WHERE DesignID=:did";
                try
                {
                    $stmt = $this->_db->prepare($sql);
                    $stmt->bindParam(':did', $did, PDO::PARAM_INT);
                    $stmt->execute();

                    if($stmt->rowCount()==1) {
                        $sql = 'UPDATE cfg_LabProfiles
                            SET StateCode=1
                            WHERE dESIGNid=:did';
                        try
                        {
                            $stmt = $this->_db->prepare($sql);
                            $stmt->bindParam(':did', $did, PDO::PARAM_INT);
                            $stmt->execute();

                            if($stmt->rowCount() > 0)
                            {
                                return array(1, 'Success');
                            }
                            else
                            {
                                return array(0, "<h2>Error:</h2>"
                                    . "<p>Something went wrong while updating the patient info.</p>");
                            }
                        }
                        catch(PDOException $e)
                        {
                            return array(0, "<h2>Error:</h2>"
                                . "<p>Could not fetch designs.</p>");
                        }
                    } else {
                        return array(0, "<h2>Error:</h2>"
                            . "<p>The requested DesignID was not found in the database</p>");
                    }
                }
                catch(PDOException $e)
                {
                    return array(0, "<h2>Error:</h2>"
                        . "<p>Could not fetch Designs.</p>");
                }
            } else {
                return array(0, "<h2>Error:</h2>"
                    . "<p>You have no edit-Permissions.</p>");
            }
        } else {
            return array(0, "<h2>Error:</h2>"
                    . "<p>It seems like you are not logged in.</p>");
        }
    }
    
    
    public function processDesign($did,$file)
    {
     if(isset($_SESSION['UserID']) && isset($_SESSION['LoggedIn']) && $_SESSION['LoggedIn']==1) {
            if(isset($_SESSION['editPermission']) && $_SESSION['editPermission']==1) {
                
                //Create Array with the necessary data
                //Each array element cotains a array which includes the data for 1 row
                $f = fopen($file, "r");
                $array = [];
                while ( $line = fgets($f, 1000) ) {
                    if(substr($line, 0, 1) === '#'){
                        continue;
                    }
                    $replace_strings = array("chr","\n");
                    $line = str_replace($replace_strings,"",$line);
                    $args = explode("\t",$line);
                    if(count($args) < 4){
                        continue;
                    }
                    array_push($args, ($args[2] - $args[1]+1)); //width
                    
                    array_push($array,$args);
                }
                //print_r($array);
                
                $sql = 'INSERT INTO tgt_Regions (design,chr,start,end,width,gene_name)
                    VALUES (' . $did . ' , :chr, :start, :end, :width, :name)';
                try{
                    $stmt = $this->_db->prepare($sql);
                
                    foreach ($array as $row) {
                        $stmt->bindParam(':chr', $row[0], PDO::PARAM_STR);
                        $stmt->bindParam(':start', $row[1], PDO::PARAM_INT);
                        $stmt->bindParam(':end', $row[2], PDO::PARAM_INT);
                        $stmt->bindParam(':width', $row[4], PDO::PARAM_INT);
                        $stmt->bindParam(':name', $row[3], PDO::PARAM_STR);
                       
                        $stmt->execute();
                        
                        if($stmt->rowCount() < 1){
                            return array(0, "<h2>Error:</h2>"
                                 . "<p>Something went wrong while processing the design.</p>");
                        }
                    }
                    return array(1, 'Success');
                }
                catch(PDOException $e)
                {
                    return array(0, "<h2>Error:</h2>"
                                . "<p>Could not insert the design into the database.</p>");
                }
                print_r($array[1]);
                print "did: " . $did;
                print $sql;
            }
     }
    }
}
