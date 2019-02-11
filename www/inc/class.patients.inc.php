<?php

/**
 * Handles analysis actions within the app
 *
 * PHP version 5
 *
 * @author Jan Stenner
 *
 */
class DkhPatients
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
     * Fetches all patients' info of the currently logged in user
     *
     * @return array    An array containing a status code and the query results array or an error message
     */
    public function getPatients()
    {
        if(isset($_SESSION['UserID']) && isset($_SESSION['LoggedIn']) && $_SESSION['LoggedIn']==1) {
            $sql = "SELECT Patientname, patients.PatientID, Birthdate, Patientnumber, patients.Created, Sex, count(samples.SampleID) as Samplecount, max(samples.Created) as Newestsample
                FROM patients left join samples ON patients.PatientID = samples.PatientID
                WHERE patients.UserID=:uid
                GROUP BY patients.PatientID
                ORDER BY patients.Created, patients.Patientname";//korrekte Sortierung der Zahlen (aktuell: Sample 1, 10, 2) nur möglich wenn zuvor nach Laenge sortiert wird: "LENGTH(patients.Patientname)". ABER: dann kommen kuerzere Namen zuerst
            try
            {
                $stmt = $this->_db->prepare($sql);
                $stmt->bindParam(':uid', $_SESSION['UserID'], PDO::PARAM_INT);
                $stmt->execute();
                $rows = $stmt->fetchAll();
                return array(1, $rows);
            }
            catch(PDOException $e)
            {
                return array(0, "Could not fetch Patients.");
            }
        } else {
            return array(0, ">It seems like you are not logged in.");
        }
    }

    /**
     * Fetches info of the patient with the PatientID $pid
     *
     * @param int $pid  The PatientID
     *
     * @return array    An array containing a status code and the query results array or an error message
     */
    public function getPatient($pid)
    {
        if(isset($_SESSION['UserID']) && isset($_SESSION['LoggedIn']) && $_SESSION['LoggedIn']==1) {
            $sql = "SELECT Patientname, PatientID, Birthdate, Patientnumber, Created, Sex
                FROM patients
                WHERE UserID=:uid
                AND PatientID=:pid
                LIMIT 1";
            try
            {
                $stmt = $this->_db->prepare($sql);
                $stmt->bindParam(':uid', $_SESSION['UserID'], PDO::PARAM_INT);
                $stmt->bindParam(':pid', $pid, PDO::PARAM_INT);
                $stmt->execute();

                if($stmt->rowCount()==1)
                {
                    $row = $stmt->fetch();
                    return array(1, $row);
                } else {
                    return array(0, "No patient found.");//TODO
                }
            }
            catch(PDOException $e)
            {
                return array(0, "Could not fetch patient.");
            }
        } else {
            return array(0, "It seems like you are not logged in.");
        }
    }

    /**
     * Fetches info of the patient corresponding to the SampleID $sid
     *
     * @param int $sid  The SampleID
     *
     * @return array    An array containing a status code and the query results array or an error message
     */
    public function getPatientBySid($sid)
    {
        if(isset($_SESSION['UserID']) && isset($_SESSION['LoggedIn']) && $_SESSION['LoggedIn']==1) {
            $sql = "SELECT Patientname, patients.PatientID, Birthdate, Patientnumber, patients.Created, Sex
                FROM patients LEFT JOIN samples ON samples.PatientID = patients.PatientID
                WHERE patients.UserID=:uid
                AND SampleID=:sid
                LIMIT 1";
            try
            {
                $stmt = $this->_db->prepare($sql);
                $stmt->bindParam(':uid', $_SESSION['UserID'], PDO::PARAM_INT);
                $stmt->bindParam(':sid', $sid, PDO::PARAM_INT);
                $stmt->execute();

                if($stmt->rowCount()==1)
                {
                    $row = $stmt->fetch();
                    return array(1, $row);
                } else {
                    return array(0, "The requested patient does not exist or you are not allowed to access this patient.");//TODO
                }
            }
            catch(PDOException $e)
            {
                return array(0, "Could not fetch patient.");
            }
        } else {
            return array(0, "It seems like you are not logged in.");
        }
    }

    /**
     * Updates the patient with the PatientID $pid
     *
     * @param int $pid  The PatientID
     * @param String $pname  The Patientname
     * @param String $pnumber  The Patientnumber
     * @param String $bd  The Birthday
     * @param String $sex  The Sex
     *
     * @return array    An array containing a status code and the query results array or an error message
     */
    public function updatePatient($pid, $pname, $pnumber, $bd, $sex)
    {
        if(isset($_SESSION['UserID']) && isset($_SESSION['LoggedIn']) && $_SESSION['LoggedIn']==1) {
            if(isset($_SESSION['editPermission']) && $_SESSION['editPermission']==1) {
                $sql = "UPDATE patients
                    SET Patientname=:pname, Patientnumber=:pnumber, Birthdate=DATE(:bd), Sex=:sex
                    WHERE UserID=:uid
                    AND PatientID=:pid";
                try
                {
                    $stmt = $this->_db->prepare($sql);
                    $stmt->bindParam(':uid', $_SESSION['UserID'], PDO::PARAM_INT);
                    $stmt->bindParam(':pid', $pid, PDO::PARAM_INT);
                    $stmt->bindParam(':pname', $pname, PDO::PARAM_STR);
                    $stmt->bindParam(':pnumber', $pnumber, PDO::PARAM_STR);
                    if (empty($bd) || $bd == ' ')
                      $stmt->bindValue(':bd', NULL, PDO::PARAM_STR);
                    else
                      $stmt->bindParam(':bd', $bd, PDO::PARAM_STR);
                    $stmt->bindParam(':sex', $sex, PDO::PARAM_STR);
                    $stmt->execute();

                    if($stmt->rowCount() > 0)
                    {
                        return array(1, 'Success');
                    }
                    else
                    {
                        return array(0, "Something went wrong while updating the patient info.");
                    }
                }
                catch(PDOException $e)
                {
                    return array(0, "Something went wrong while updating the patient info.");
                }
            } else {
                return array(0, "You have no edit-Permissions.");
            }
        } else {
            return array(0, "It seems like you are not logged in.");
        }
    }

    /**
     * Stores a new patient in the database.
     *
     * @param String $pname  The Patientname
     * @param String $pnumber  The Patientnumber
     * @param String $bd  The Birthday
     * @param String $sex  The Sex
     *
     * @return array    An array containing a status code and status message
     */
    public function createPatient($pname, $pnumber, $bd, $sex)
    {   
        if(isset($_SESSION['UserID']) && isset($_SESSION['LoggedIn']) && $_SESSION['LoggedIn']==1) {
            if(isset($_SESSION['editPermission']) && $_SESSION['editPermission']==1) {
                $sql = 'INSERT INTO patients (UserID, Patientname, Patientnumber, Birthdate, Sex, Created)
                    VALUES (:uid, :pname, :pnumber, DATE(:bd), :sex, NOW())';
                try
                {
                    $stmt = $this->_db->prepare($sql);
                    $stmt->bindParam(':uid', $_SESSION['UserID'], PDO::PARAM_INT);
                    $stmt->bindParam(':pname', $pname, PDO::PARAM_STR);
                    $stmt->bindParam(':pnumber', $pnumber, PDO::PARAM_STR);
                    if (empty($bd) || $bd == ' ')
                      $stmt->bindValue(':bd', NULL, PDO::PARAM_STR);
                    else
                      $stmt->bindParam(':bd', $bd, PDO::PARAM_STR);
                    $stmt->bindParam(':sex', $sex, PDO::PARAM_STR);
                    
                    if($stmt->execute())
                    {
                        return array(1, $this->_db->lastInsertId());
                    }
                    else
                    {
                        return array(0, "Something went wrong while saving the patient info[1].");
                    }
                }
                catch(PDOException $e)
                {
                    return array(0, "Something went wrong while saving the patient info[2].");
                }
            } else {
                return array(0, "You have no edit-Permissions.");
            }
        } else {
            return array(0, "It seems like you are not logged in.");
        }
    }
}
