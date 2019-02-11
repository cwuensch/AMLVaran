<?php

/**
 * Handles analysis actions within the app
 *
 * PHP version 5
 *
 * @author Jan Stenner
 *
 */
class DkhSamples
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
     *
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
            ini_set('max_execution_time', 0);            
        }
    }

    /**
     * Checks if the provided SampleID belongs to the logged in User
     *
     * @param int $sid  The SampleID
     *
     * @return array     An array containing a status code and the results boolean or an error message
     */
    public function checkSampleID($sid) {
        if(isset($_SESSION['UserID']) && isset($_SESSION['LoggedIn']) && $_SESSION['LoggedIn']==1) {
            $sql = "SELECT SampleID
                FROM samples left join patients ON samples.PatientID = patients.PatientID
                WHERE patients.UserID=:uid AND SampleID =:sid";
            try
            {
                $stmt = $this->_db->prepare($sql);
                $stmt->bindParam(':uid', $_SESSION['UserID'], PDO::PARAM_INT);
                $stmt->bindParam(':sid', $sid, PDO::PARAM_INT);
                $stmt->execute();

                if($stmt->rowCount()==1) {
                    return array(1, true);
                } else {
                    return array(1, false);
                }
            }
            catch(PDOException $e)
            {
                return array(0, "Could not fetch Samples.");
            }
        } else {
            return array(0, "It seems like you are not logged in.");
        }
    }

    /**
     * Fetches all Analyses of Patients of the currently logged in user
     *
     * @return array    An array containing a status code and the query results array or an error message
     */
    public function getSamplesByUid()
    {
        if(isset($_SESSION['UserID']) && isset($_SESSION['LoggedIn']) && $_SESSION['LoggedIn']==1) {
            $sql = "SELECT SampleID, Patientname, Samplename, samples.Created, SampleTakeDate, Diagnosis, StateCode, patients.PatientID, Worker
                FROM samples left join patients ON samples.PatientID = patients.PatientID
                WHERE patients.UserID=:uid
                ORDER BY samples.Created DESC, Patientname";
            try
            {
                $stmt = $this->_db->prepare($sql);
                $stmt->bindParam(':uid', $_SESSION['UserID'], PDO::PARAM_INT);
                $stmt->execute();
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                return array(1, $rows);
            }
            catch(PDOException $e)
            {
                return array(0, "Could not fetch Samples.");
            }
        } else {
            return array(0, "It seems like you are not logged in.");
        }
    }

    /**
     * Fetches all Analyses of Patients with the PatientID $pid and the UserID of the currently logged in user
     *
     * @param int $pid  The PatientID
     *
     * @return array    An array containing a status code and the query results array or an error message
     */
    public function getSamplesByPid($pid)
    {
        if(isset($_SESSION['UserID']) && isset($_SESSION['LoggedIn']) && $_SESSION['LoggedIn']==1) {
            $sql = "SELECT SampleID, SampleTakeDate, StateCode
                FROM samples left join patients ON samples.PatientID = patients.PatientID
                WHERE patients.UserID=:uid
                AND samples.PatientID=:pid";
            try
            {
                $stmt = $this->_db->prepare($sql);
                $stmt->bindParam(':uid', $_SESSION['UserID'], PDO::PARAM_INT);
                $stmt->bindParam(':pid', $pid, PDO::PARAM_INT);
                $stmt->execute();
                $rows = $stmt->fetchAll();
                return array(1, $rows);
            }
            catch(PDOException $e)
            {
                return array(0, 
                    "Could not fetch Samples.");
            }
        } else {
            return array(0, 
                    "It seems like you are not logged in.");
        }
    }

    /**
     * Fetches required information of sample with the SampleID $sid and the UserID of the currently logged in user
     *
     * @param int $sid  The SampleID
     *
     * @return array    An array containing a status code and the query results array or an error message
     */
    public function getSampleinfo($sid)
    {
        if(isset($_SESSION['UserID']) && isset($_SESSION['LoggedIn']) && $_SESSION['LoggedIn']==1) {
            $sql = "SELECT Comments, samples.Created, SampleTakeDate, Diagnosis, design
                FROM samples left join patients ON samples.PatientID = patients.PatientID
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
                    if($stmt->rowCount() > 0){
                        return array(1, $row);
                    } else {
                        return array(0, 
                            "Could not get the required sample info.");
                    }
                }
                else
                {
                    return array(0, 
                        "No samples found.");
                }
            }
            catch(PDOException $e)
            {
                return array(0, 
                    "Could not fetch samples.");
            }
        } else {
            return array(0, 
                    "It seems like you are not logged in.");
        }
    }

    /**
     * Fetches the relevant variants table of the sample with SampleID $sid
     *
     * @param int $sid  The SampleID
     *
     * @param array $relevantfilter  array of the filter options
     *
     * @param array $sortlist  array of the sort settings
     *
     * @return array    An array containing a status code and the query results array or an error message
     */
    /*public function getRelevantVariants($sid,$relevantfilter,$sortlist) #Used by downloadcsv.php
    {
        if(isset($_SESSION['UserID']) && isset($_SESSION['LoggedIn']) && $_SESSION['LoggedIn']==1) {
            $check = $this->checkSampleID($sid);
            if($check[0] == 1) {
                if($check[1] == true) {

                    if(isset($relevantfilter)) {

                        $filterarray =  json_decode($relevantfilter);
                        if(!($filterarray[0] == 0 && $filterarray[1] == 0 && $filterarray[2] == 0)) {
                            if(isset($sortlist)) {
                                $sortarray =  json_decode($sortlist);

                                $sql = 'SELECT region_name, region_type, chr, pos, ref, alt, strand, mut_type, function, Quality, FORMAT((1-POW(10,(-Quality/10)))*100,4) AS QualScore, GT, AD, DP, (AD/DP) as AF, ID as "dbSNP ID", CLNSIG, CLNDBN, CLNREVSTAT, GROUP_CONCAT(DISTINCT CosmicID) AS CosmicID, GROUP_CONCAT(Site_Counts ORDER BY nr DESC) AS CosmicSites, MAX(NrHaemato2) AS NrHaemato,
        (SELECT COUNT(*) FROM db_civicEvidence WHERE region_name LIKE gene AND function LIKE CONCAT("%:p.", variant, "%")) AS NrCivic,
(SELECT COUNT(DISTINCT SampleID) FROM Variants WHERE vc.chr=Variants.chr AND vc.pos=Variants.pos AND vc.ref=Variants.ref and vc.alt=Variants.alt) AS NrSamples,
MIN(MutationID) AS inHotspot FROM
          (SELECT region_name, region_type, chr, pos, ref, alt, strand, mut_type, function, Quality, GT, AD, DP, ID, CLNSIG, CLNDBN, CLNREVSTAT, GROUP_CONCAT(DISTINCT CosmicID) AS CosmicID, CONCAT(Primary_Site, " (", COUNT(DISTINCT ID_Sample), ")") AS Site_Counts, COUNT(DISTINCT ID_Sample) AS nr, SUM(IF(Primary_Site="haematopoietic_and_lymphoid_tissue", 1, 0)) AS NrHaemato2, MutationID FROM
            (SELECT region_name, region_type, v.chr, v.pos, v.ref, v.alt, CosmicCoding.strand, v.mut_type, function, Quality, GT, AD, DP, ID, CLNSIG, CLNDBN, CLNREVSTAT, GROUP_CONCAT(DISTINCT CosmicCoding.Mutation_ID) AS CosmicID, ID_Sample, Primary_Site, MutationID FROM
                      (SELECT * FROM Variants WHERE SampleID=:sid) AS v
                      LEFT JOIN tgt_KnownMutations ON v.chr=tgt_KnownMutations.chr AND v.pos BETWEEN tgt_KnownMutations.start AND tgt_KnownMutations.end AND (v.mut_type=tgt_KnownMutations.mut_type OR tgt_KnownMutations.mut_type IS NULL)
                      LEFT JOIN clinvar ON v.chr=clinvar.chr AND v.pos=clinvar.pos AND v.ref=clinvar.ref AND v.alt=clinvar.alt
                      LEFT JOIN CosmicCoding ON v.chr=CosmicCoding.chr AND v.pos=CosmicCoding.pos AND v.ref=CosmicCoding.ref AND v.alt LIKE CosmicCoding.alt
                      LEFT JOIN CosmicDetails ON CosmicCoding.Mutation_ID=CosmicDetails.Mutation_ID
                    GROUP BY v.chr, v.pos, v.ref, v.alt, ID_Sample) as sc
                  GROUP BY chr, pos, ref, alt, Primary_Site) as vc
                GROUP BY chr, pos, ref, alt
                                        HAVING ';
                                if($filterarray[0] == 1) {
                                    $sql .= '(mut_type="duplication" OR mut_type="deletion") ';
                                    if($filterarray[1] == 1 || $filterarray[2] == 1) $sql .= 'OR ';
                                }
                                if($filterarray[1] == 1) {
                                    $sql .= '(CLNSIG LIKE "%4%" OR CLNSIG LIKE "%5%") ';
                                    if($filterarray[2] == 1) $sql .= 'OR ';
                                }
                                if($filterarray[2] == 1) $sql .= '(NrHaemato>1) ';

                                if(sizeof($sortarray) > 0) {
                                    $sql .= 'ORDER BY ' . $sortarray[0][0] . ' ' . $sortarray[0][1];
                                    for ($i = 1; $i < sizeof($sortarray); $i++) {
                                        $sql .= ', ' . $sortarray[$i][0] . ' ' . $sortarray[$i][1];
                                    }
                                } else {
                                    $sql .= 'ORDER BY region_name';
                                }

                                try
                                {
                                    $stmt = $this->_db->prepare($sql);
                                    $stmt->bindParam(':sid', $sid, PDO::PARAM_INT);
                                    $stmt->execute();
                                    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                    return array(1, $rows);
                                }
                                catch(PDOException $e)
                                {
                                    return array(0, 
                                        "Could not fetch relevant variants.");
                                }
                            } else {
                                return array(0, 
                                    "No sort information.");
                            }
                        } else {
                            return array(1, array());
                        }
                    } else {
                        return array(0, 
                                "No filter information.");
                    }

                } else {
                    return array(0, 
                        "The requested SampleID does not belong to the logged in user.");
                }
            } else {
                return $check;
            }
        } else {
            return array(0, 
                    "It seems like you are not logged in.");
        }
    }*/

    /**
     * Fetches the coverage analysis table of the sample with SampleID $sid
     *
     * @param int $sid  The SampleID
     *
     * @return array    An array containing a status code and the query results array or an error message
     */
    /*public function getCoverage($sid)
    {
        if(isset($_SESSION['UserID']) && isset($_SESSION['LoggedIn']) && $_SESSION['LoggedIn']==1) {
            $check = $this->checkSampleID($sid);
            if($check[0] == 1) {
                if($check[1] == true) {

                    $sql = 'SELECT hgnc_gene, tgt_Regions.chr, tgt_Genes.start, tgt_Genes.end,
                            COUNT(*) as NrRegions,
                            SUM(tgt_Regions.width) as width,
                            (SELECT min(cvg) FROM Coverage WHERE SampleID=:sid AND tgt_Regions.design=1 AND Coverage.chr=tgt_Regions.chr AND pos BETWEEN tgt_Regions.start AND tgt_Regions.end) as min_cvg,
                            (SELECT COUNT(*) FROM Coverage WHERE SampleID=:sid AND tgt_Regions.design=1 AND Coverage.chr=tgt_Regions.chr AND pos BETWEEN tgt_Regions.start AND tgt_Regions.end AND cvg<20) as nr_less20,
                            (SELECT COUNT(*) FROM Coverage WHERE SampleID=:sid AND tgt_Regions.design=1 AND Coverage.chr=tgt_Regions.chr AND pos BETWEEN tgt_Regions.start AND tgt_Regions.end AND cvg<100) as nr_less100
                            FROM tgt_Regions LEFT JOIN tgt_Genes ON tgt_Regions.design=tgt_Genes.design AND tgt_Regions.chr=tgt_Genes.chr AND tgt_Regions.start >= tgt_Genes.start AND tgt_Regions.end <= tgt_Genes.end
                            WHERE tgt_Regions.design=1 AND hgnc_gene IS NOT NULL
                            GROUP BY hgnc_gene
                            HAVING min_cvg IS NOT NULL
                            ORDER BY hgnc_gene';
                    try
                    {
                        $stmt = $this->_db->prepare($sql);
                        $stmt->bindParam(':sid', $sid, PDO::PARAM_INT);
                        $stmt->execute();
                        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        return array(1, $rows);
                    }
                    catch(PDOException $e)
                    {
                        return array(0, 
                            "Could not fetch relevant variants.");
                    }

                } else {
                    return array(0, 
                        "The requested SampleID does not belong to the logged in user.");
                }
            } else {
                return $check;
            }
        } else {
            return array(0, 
                    "It seems like you are not logged in.");
        }
    }*/

    /**
     * Fetches the coverage analysis table of the sample with SampleID $sid for a specified region $region
     *
     * @param int $sid  The SampleID
     *
     * @param String $region  The region
     *
     * @return array    An array containing a status code and the query results array or an error message
     */
    /*public function getCoverageByRegion($sid, $region)
    {
        if(isset($_SESSION['UserID']) && isset($_SESSION['LoggedIn']) && $_SESSION['LoggedIn']==1) {
            $check = $this->checkSampleID($sid);
            if($check[0] == 1) {
                if($check[1] == true) {

                    $sql = 'SELECT hgnc_gene, r.chr, start, end, width, MIN(cvg) AS min_cvg, COUNT(cvg) AS nr_less100, SUM(IF(cvg<20,1,0)) AS nr_less20 FROM
                              (SELECT tgt_Regions.chr, tgt_Regions.start, tgt_Regions.end, tgt_Regions.width, hgnc_gene FROM tgt_Regions
                              LEFT JOIN tgt_Genes ON tgt_Regions.design=tgt_Genes.design AND tgt_Regions.chr=tgt_Genes.chr AND tgt_Regions.start >= tgt_Genes.start AND tgt_Regions.end <= tgt_Genes.end
                              WHERE tgt_Regions.design=1 AND hgnc_gene=:region) as r
                            LEFT JOIN Coverage ON r.chr=Coverage.chr AND Coverage.pos BETWEEN r.start AND r.end AND SampleID=:sid
                            GROUP BY chr, start, end';
                    try
                    {
                        $stmt = $this->_db->prepare($sql);
                        $stmt->bindParam(':region', $region, PDO::PARAM_STR);
                        $stmt->bindParam(':sid', $sid, PDO::PARAM_INT);
                        $stmt->execute();
                        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        return array(1, $rows);
                    }
                    catch(PDOException $e)
                    {
                        return array(0, 
                            "Could not fetch relevant variants.");
                    }

                } else {
                    return array(0, 
                        "The requested SampleID does not belong to the logged in user.");
                }
            } else {
                return $check;
            }
        } else {
            return array(0, 
                    "It seems like you are not logged in.");
        }
    }*/

    /**
     * Fetches the regions without mutations table of the sample with SampleID $sid
     *
     * @param int $sid  The SampleID
     *
     * @return array    An array containing a status code and the query results array or an error message
     */
    /*public function getRegionsWithoutMutations($sid)
    {
        if(isset($_SESSION['UserID']) && isset($_SESSION['LoggedIn']) && $_SESSION['LoggedIn']==1) {
            $check = $this->checkSampleID($sid);
            if($check[0] == 1) {
                if($check[1] == true) {

                    $sql = 'SELECT hgnc_gene, chr, start, end,
                            (SELECT count(*) FROM Variants WHERE SampleID=:sid AND Variants.chr=tgt_Genes.chr AND pos BETWEEN start AND end) AS NrMutations
                            FROM tgt_Genes
                            WHERE design=1
                            ORDER BY NrMutations';
                    try
                    {
                        $stmt = $this->_db->prepare($sql);
                        $stmt->bindParam(':sid', $sid, PDO::PARAM_INT);
                        $stmt->execute();
                        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        return array(1, $rows);
                    }
                    catch(PDOException $e)
                    {
                        return array(0, 
                            "Could not fetch relevant variants.");
                    }

                } else {
                    return array(0, 
                        "The requested SampleID does not belong to the logged in user.");
                }
            } else {
                return $check;
            }
        } else {
            return array(0, 
                    "It seems like you are not logged in.");
        }
    }*/

    /**
     * Fetches the structural variants table of the sample with SampleID $sid
     *
     * @param int $sid  The SampleID
     *
     * @return array    An array containing a status code and the query results array or an error message
     */
    /*public function getStructuralVariants($sid)
    {
        if(isset($_SESSION['UserID']) && isset($_SESSION['LoggedIn']) && $_SESSION['LoggedIn']==1) {
            $check = $this->checkSampleID($sid);
            if($check[0] == 1) {
                if($check[1] == true) {

                    $sql = 'SELECT * FROM StructuralVariants WHERE SampleID=:sid';
                    try
                    {
                        $stmt = $this->_db->prepare($sql);
                        $stmt->bindParam(':sid', $sid, PDO::PARAM_INT);
                        $stmt->execute();
                        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        return array(1, $rows);
                    }
                    catch(PDOException $e)
                    {
                        return array(0, 
                            "Could not fetch relevant variants.");
                    }

                } else {
                    return array(0, 
                        "The requested SampleID does not belong to the logged in user.");
                }
            } else {
                return $check;
            }
        } else {
            return array(0, 
                    "It seems like you are not logged in.");
        }
    }*/

    /**
     * Fetches the filtered variants table of the sample with SampleID $sid
     *
     * @param int $sid  The SampleID
     *
     * @return array    An array containing a status code and the query results array or an error message
     */
    public function getAllVariants($sid, $version, $designID) 
    {
        if(isset($_SESSION['UserID']) && isset($_SESSION['LoggedIn']) && $_SESSION['LoggedIn']==1) {
            $check = $this->checkSampleID($sid);
            if($check[0] == 1) {
                if($check[1] == true) {
                    $sql = file_get_contents('/var/amlvaran/www/report/getAllVariants.sql');

                    try
                    {
                        $stmt = $this->_db->prepare($sql);
                        $stmt->bindParam(':sid', $sid, PDO::PARAM_INT);
                        $stmt->bindParam(':version', $version, PDO::PARAM_INT);
                        $stmt->bindParam(':design', $designID, PDO::PARAM_INT);
                        $stmt->bindParam(':userid', $_SESSION['UserID'], PDO::PARAM_INT);
                        $stmt->execute();
                        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        return array(1, $rows);
                    }
                    catch(PDOException $e)
                    {
                        return array(0, 
                            "Could not fetch relevant variants.");
                    }
                } else {
                    return array(0, "The requested SampleID does not belong to the logged in user. [01]");
                }
            } else {
                return $check;
            }
        } else {
            return array(0, 
                    "It seems like you are not logged in.");
        }
    }
    
        /**
     * Fetches the filtered variants table of the sample with SampleID $sid
     *
     * @param int $sid  The SampleID
     *
     * @return array    An array containing a status code and the query results array or an error message
     */
    public function getAllRanges($sid, $design ,$version)
    {
        if(isset($_SESSION['UserID']) && isset($_SESSION['LoggedIn']) && $_SESSION['LoggedIn']==1) {
            $check = $this->checkSampleID($sid);
            if($check[0] == 1) {
                if($check[1] == true) {
                    $sql = file_get_contents('/var/amlvaran/www/report/getAllRanges.sql');

                    try
                    {
                        $stmt = $this->_db->prepare($sql);
                        $stmt->bindParam(':sid', $sid, PDO::PARAM_INT);
                        $stmt->bindParam(':version', $version, PDO::PARAM_INT);
                        $stmt->bindParam(':design', $design, PDO::PARAM_INT);
                        $stmt->execute();
                        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        return array(1, $rows);
                    }
                    catch(PDOException $e)
                    {
                        return array(0, 
                            "Could not fetch relevant variants.");
                    }
                } else {
                    return array(0, "The requested SampleID does not belong to the logged in user. [02]");
                }
            } else {
                return $check;
            }
        } else {
            return array(0, 
                    "It seems like you are not logged in.");
        }
    }

     /**
     * Fetches the diagnosis information of the sample with SampleID $sid
     *
     * @param int $sid  The SampleID
     *
     * @return array    An array containing a status code and the query results array or an error message
     */
    /*public function getDiagnosis($sid)
    {
        if(isset($_SESSION['UserID']) && isset($_SESSION['LoggedIn']) && $_SESSION['LoggedIn']==1) {
            $check = $this->checkSampleID($sid);
            if($check[0] == 1) {
                if($check[1] == true) {

                    $sql = 'SELECT Prognosis, Color, Source, URL, GROUP_CONCAT(CONCAT(name, "=", mutated)) AS Rule, MIN(((LEAST(NrMutations,mutated)>0) OR (GREATEST(NrMutations, mutated, NrBadCovered)=0))) AS Fulfilled FROM
          (SELECT rul_Diagnosis.RuleID, Prognosis, Color, Source, rul_Diagnosis.URL, name, mutated,
          (SELECT COUNT(*) FROM Coverage
            WHERE (SampleID=:sid AND Coverage.chr=chr AND pos BETWEEN start AND end AND cvg < 20)) AS NrBadCovered,

          (SELECT COUNT(*) FROM
            (SELECT chr, pos, ref, alt, mut_type, CLNSIG, SUM(IF(Primary_Site="haematopoietic_and_lymphoid_tissue", 1, 0)) AS NrHaemato FROM
              (SELECT v.chr, v.pos, v.ref, v.alt, mut_type, CLNSIG, ID_Sample, Primary_Site FROM
                (SELECT * FROM Variants WHERE SampleID=:sid) AS v
                LEFT JOIN clinvar ON v.chr=clinvar.chr AND v.pos=clinvar.pos AND v.ref=clinvar.ref AND v.alt=clinvar.alt
                LEFT JOIN CosmicCoding ON v.chr=CosmicCoding.chr AND v.pos=CosmicCoding.pos AND v.ref=CosmicCoding.ref AND v.alt LIKE CosmicCoding.alt
                LEFT JOIN CosmicDetails ON CosmicCoding.Mutation_ID=CosmicDetails.Mutation_ID
              GROUP BY v.chr, v.pos, v.ref, v.alt, ID_Sample) AS sc

            GROUP BY chr, pos, ref, alt) AS vc
            WHERE vc.chr=tgt_KnownMutations.chr AND vc.pos BETWEEN tgt_KnownMutations.start AND tgt_KnownMutations.end
            AND (vc.mut_type=tgt_KnownMutations.mut_type OR tgt_KnownMutations.mut_type IS NULL)
            AND ((mut_type="duplication" OR mut_type="deletion") OR (CLNSIG LIKE "%4%" OR CLNSIG LIKE "%5%") OR (NrHaemato>1))
          ) AS NrMutations



          FROM rul_Diagnosis
          INNER JOIN rul_Mutations ON rul_Diagnosis.RuleID=rul_Mutations.RuleID
          INNER JOIN tgt_KnownMutations ON rul_Mutations.MutationID=tgt_KnownMutations.MutationID) AS v

        GROUP BY RuleID
        HAVING Fulfilled=TRUE';
                    try
                    {
                        $stmt = $this->_db->prepare($sql);
                        $stmt->bindParam(':sid', $sid, PDO::PARAM_INT);
                        $stmt->execute();
                        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        return array(1, $rows);
                    }
                    catch(PDOException $e)
                    {
                        return array(0, 
                            "Could not fetch overview.");
                    }

                } else {
                    return array(0, 
                        "The requested SampleID does not belong to the logged in user.");
                }
            } else {
                return $check;
            }
        } else {
            return array(0, 
                    "It seems like you are not logged in.");
        }
    }*/

    /**
     * Fetches the overview information of the sample with SampleID $sid
     *
     * @param int $sid  The SampleID
     *
     * @return array    An array containing a status code and the query results array or an error message
     */
    /*public function getOverview($sid)
    {
        if(isset($_SESSION['UserID']) && isset($_SESSION['LoggedIn']) && $_SESSION['LoggedIn']==1) {
            $check = $this->checkSampleID($sid);
            if($check[0] == 1) {
                if($check[1] == true) {

                    $sql = 'SELECT tgt_KnownMutations.MutationID, tgt_KnownMutations.name, tgt_KnownMutations.chr, tgt_KnownMutations.start, tgt_KnownMutations.end,
        COUNT(*) as NrRegions,
        (tgt_KnownMutations.end-tgt_KnownMutations.start) as width,
        SUM(LEAST(tgt_Regions.end, tgt_KnownMutations.end)-GREATEST(tgt_Regions.start, tgt_KnownMutations.start)) as Overlap,

        (SELECT COUNT(*) FROM
          (SELECT chr, pos, ref, alt, mut_type, CLNSIG, SUM(IF(Primary_Site="haematopoietic_and_lymphoid_tissue", 1, 0)) AS NrHaemato FROM
            (SELECT v.chr, v.pos, v.ref, v.alt, mut_type, CLNSIG, ID_Sample, Primary_Site FROM
              (SELECT * FROM Variants WHERE SampleID=:sid) AS v
              LEFT JOIN clinvar ON v.chr=clinvar.chr AND v.pos=clinvar.pos AND v.ref=clinvar.ref AND v.alt=clinvar.alt
              LEFT JOIN CosmicCoding ON v.chr=CosmicCoding.chr AND v.pos=CosmicCoding.pos AND v.ref=CosmicCoding.ref AND v.alt LIKE CosmicCoding.alt
              LEFT JOIN CosmicDetails ON CosmicCoding.Mutation_ID=CosmicDetails.Mutation_ID
            GROUP BY v.chr, v.pos, v.ref, v.alt, ID_Sample) AS sc
          GROUP BY chr, pos, ref, alt) AS vc
          WHERE vc.chr=tgt_KnownMutations.chr AND vc.pos BETWEEN tgt_KnownMutations.start AND tgt_KnownMutations.end
          AND (vc.mut_type=tgt_KnownMutations.mut_type OR tgt_KnownMutations.mut_type IS NULL)
          AND ((mut_type="duplication" OR mut_type="deletion") OR (CLNSIG LIKE "%4%" OR CLNSIG LIKE "%5%") OR (NrHaemato>1))
        ) AS NrMutations,

        (SELECT COUNT(*) FROM Coverage
          WHERE (SampleID=:sid AND Coverage.chr=tgt_KnownMutations.chr AND pos BETWEEN tgt_KnownMutations.start AND tgt_KnownMutations.end AND cvg < 20)) AS NrBadCovered

        FROM tgt_KnownMutations LEFT JOIN tgt_Regions ON tgt_Regions.design=1 AND tgt_KnownMutations.chr=tgt_Regions.chr
        AND ((tgt_Regions.start BETWEEN tgt_KnownMutations.start AND tgt_KnownMutations.end)
        OR (tgt_Regions.end BETWEEN tgt_KnownMutations.start AND tgt_KnownMutations.end)
        OR (tgt_Regions.start <= tgt_KnownMutations.start AND tgt_Regions.end >= tgt_KnownMutations.end))
        GROUP BY tgt_KnownMutations.name
        ORDER BY NrMutations DESC, NrBadCovered, MutationID';
                    try
                    {
                        $stmt = $this->_db->prepare($sql);
                        $stmt->bindParam(':sid', $sid, PDO::PARAM_INT);
                        $stmt->execute();
                        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        return array(1, $rows);
                    }
                    catch(PDOException $e)
                    {
                        return array(0, 
                            "Could not fetch overview.");
                    }

                } else {
                    return array(0, 
                        "The requested SampleID does not belong to the logged in user.");
                }
            } else {
                return $check;
            }
        } else {
            return array(0, 
                    "It seems like you are not logged in.");
        }
    }*/

    /**
     * Fetches the Overview variants table of the sample with SampleID $sid
     *
     * @param int $sid  The SampleID
     *
     * @param encoded json $ovfilter  array of the regions
     *
     * @param array $sortlist  array of the sort settings
     *
     * @return array    An array containing a status code and the query results array or an error message
     */
    /*public function getOverviewVariants($sid, $overviewselected, $sortlist) #Used by downloadcsv.php
    {
        if(isset($_SESSION['UserID']) && isset($_SESSION['LoggedIn']) && $_SESSION['LoggedIn']==1) {
            $check = $this->checkSampleID($sid);
            if($check[0] == 1) {
                if($check[1] == true) {

                    if(isset($overviewselected)) {
                        if(isset($sortlist)) {
                            $filterarray =  json_decode($overviewselected);
                            $sortarray =  json_decode($sortlist);

                            $sql = 'SELECT region_name, region_type, chr, pos, ref, alt, strand, mut_type, function, Quality, FORMAT((1-POW(10,(-Quality/10)))*100,4) AS QualScore, GT, AD, DP, (AD/DP) as AF, ID as "dbSNP ID", CLNSIG, CLNDBN, CLNREVSTAT, GROUP_CONCAT(DISTINCT CosmicID) AS CosmicID, GROUP_CONCAT(Site_Counts ORDER BY nr DESC) AS CosmicSites, MAX(NrHaemato2) AS NrHaemato,
    (SELECT COUNT(*) FROM db_civicEvidence WHERE region_name LIKE gene AND function LIKE CONCAT("%:p.", variant, "%")) AS NrCivic,
(SELECT COUNT(DISTINCT SampleID) FROM Variants WHERE vc.chr=Variants.chr AND vc.pos=Variants.pos AND vc.ref=Variants.ref and vc.alt=Variants.alt) AS NrSamples,
MIN(MutationID) AS inHotspot FROM
      (SELECT region_name, region_type, chr, pos, ref, alt, strand, mut_type, function, Quality, GT, AD, DP, ID, CLNSIG, CLNDBN, CLNREVSTAT, GROUP_CONCAT(DISTINCT CosmicID) AS CosmicID, CONCAT(Primary_Site, " (", COUNT(DISTINCT ID_Sample), ")") AS Site_Counts, COUNT(DISTINCT ID_Sample) AS nr, SUM(IF(Primary_Site="haematopoietic_and_lymphoid_tissue", 1, 0)) AS NrHaemato2, MutationID FROM
        (SELECT region_name, region_type, v.chr, v.pos, v.ref, v.alt, CosmicCoding.strand, v.mut_type, function, Quality, GT, AD, DP, v.ID, CLNSIG, CLNDBN, CLNREVSTAT, GROUP_CONCAT(DISTINCT CosmicCoding.Mutation_ID) AS CosmicID, ID_Sample, Primary_Site, MutationID FROM
                  (SELECT Variants.chr, pos, ref, alt, region_name, region_type, Variants.mut_type, function, Quality, GT, AD, DP, Variants.ID
                    FROM Variants INNER JOIN tgt_KnownMutations ON Variants.chr=tgt_KnownMutations.chr AND pos between start AND end
                                            WHERE SampleID=:sid AND (MutationID=' . $filterarray[0];
                            for ($i = 1; $i < sizeof($filterarray); $i++) {
                                $sql .= ' OR MutationID=' . $filterarray[$i];
                            }
                            $sql .= ')) AS v
                                          LEFT JOIN tgt_KnownMutations ON v.chr=tgt_KnownMutations.chr AND v.pos BETWEEN tgt_KnownMutations.start AND tgt_KnownMutations.end AND (v.mut_type=tgt_KnownMutations.mut_type OR tgt_KnownMutations.mut_type IS NULL)
                                          LEFT JOIN clinvar ON v.chr=clinvar.chr AND v.pos=clinvar.pos AND v.ref=clinvar.ref AND v.alt=clinvar.alt
                                          LEFT JOIN CosmicCoding ON v.chr=CosmicCoding.chr AND v.pos=CosmicCoding.pos AND v.ref=CosmicCoding.ref AND v.alt LIKE CosmicCoding.alt
                                          LEFT JOIN CosmicDetails ON CosmicCoding.Mutation_ID=CosmicDetails.Mutation_ID
                                        GROUP BY v.chr, v.pos, v.ref, v.alt, ID_Sample) as sc
                                      GROUP BY chr, pos, ref, alt, Primary_Site) as vc
                                    GROUP BY chr, pos, ref, alt
                                    HAVING (mut_type="duplication" OR mut_type="deletion") OR (CLNSIG LIKE "%4%" OR CLNSIG LIKE "%5%") OR (NrHaemato>1) ';
                            if(sizeof($sortarray) > 0) {
                                $sql .= 'ORDER BY ' . $sortarray[0][0] . ' ' . $sortarray[0][1];
                                for ($i = 1; $i < sizeof($sortarray); $i++) {
                                    $sql .= ', ' . $sortarray[$i][0] . ' ' . $sortarray[$i][1];
                                }
                            } else {
                                $sql .= 'ORDER BY region_name';
                            }

                            try
                            {
                                $stmt = $this->_db->prepare($sql);
                                $stmt->bindParam(':sid', $sid, PDO::PARAM_INT);
                                $stmt->execute();
                                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                return array(1, $rows);
                            }
                            catch(PDOException $e)
                            {
                                return array(0, 
                                    "Could not fetch relevant variants.");
                            }
                        } else {
                            return array(0, 
                                "No sort information.");
                        }
                    } else {
                        return array(0, 
                                "No filters given.");
                    }

                } else {
                    return array(0, 
                        "The requested SampleID does not belong to the logged in user.");
                }
            } else {
                return $check;
            }
        } else {
            return array(0, 
                    "It seems like you are not logged in.");
        }
    }*/

    /**
     * Fetches the overview information of the sample with SampleID $sid
     *
     * @param int $sid  The SampleID
     *
     * @return array    An array containing a status code and the query results array or an error message
     */
    /*public function getRegionsForReport($sid)
    {
        if(isset($_SESSION['UserID']) && isset($_SESSION['LoggedIn']) && $_SESSION['LoggedIn']==1) {
            $check = $this->checkSampleID($sid);
            if($check[0] == 1) {
                if($check[1] == true) {

                    $sql = 'SELECT group_concat(MutationID) as MutationID, group_concat(name) as name, COUNT(*) as NrRegions, SUM(width2) AS width, SUM(Overlap2) AS Overlap, SUM(NrMutations2) AS NrMutations, SUM(NrAllMutations2) AS NrAllMutations, SUM(NrBadCovered2) AS NrBadCovered FROM

  (SELECT tgt_KnownMutations.MutationID, tgt_KnownMutations.name, tgt_KnownMutations.version, tgt_KnownMutations.chr, tgt_KnownMutations.start, tgt_KnownMutations.end,
  (tgt_KnownMutations.end-tgt_KnownMutations.start) AS width2,
  SUM(LEAST(tgt_KnownMutations.end, tgt_Regions.end)-GREATEST(tgt_KnownMutations.start, tgt_Regions.start)) AS Overlap2,

  (SELECT COUNT(*) FROM
    (SELECT chr, pos, ref, alt, region_type, mut_type, CLNSIG, SUM(IF(Primary_Site="haematopoietic_and_lymphoid_tissue", 1, 0)) AS NrHaemato FROM
      (SELECT v.chr, v.pos, v.ref, v.alt, region_type, mut_type, CLNSIG, ID_Sample, Primary_Site FROM
        (SELECT * FROM Variants WHERE SampleID=:sid) AS v
        LEFT JOIN clinvar ON v.chr=clinvar.chr AND v.pos=clinvar.pos AND v.ref=clinvar.ref AND v.alt=clinvar.alt
        LEFT JOIN CosmicCoding ON v.chr=CosmicCoding.chr AND v.pos=CosmicCoding.pos AND v.ref=CosmicCoding.ref AND v.alt LIKE CosmicCoding.alt
        LEFT JOIN CosmicDetails ON CosmicCoding.Mutation_ID=CosmicDetails.Mutation_ID
      GROUP BY v.chr, v.pos, v.ref, v.alt, ID_Sample) AS sc
    GROUP BY chr, pos, ref, alt) AS vc
    WHERE vc.chr=tgt_KnownMutations.chr AND vc.pos BETWEEN tgt_KnownMutations.start AND tgt_KnownMutations.end
    AND (vc.mut_type=tgt_KnownMutations.mut_type OR tgt_KnownMutations.mut_type IS NULL)
    AND ((mut_type="duplication" OR mut_type="deletion") OR (CLNSIG LIKE "%4%" OR CLNSIG LIKE "%5%") OR (NrHaemato>1))
    AND (region_type="exonic" OR region_type="structural") AND (mut_type!="synonymous SNV")
  ) AS NrMutations2,

  (SELECT COUNT(DISTINCT chr, pos, ref, alt) FROM Variants
    WHERE SampleID=:sid
    AND Variants.chr=tgt_KnownMutations.chr AND Variants.pos BETWEEN tgt_KnownMutations.start AND tgt_KnownMutations.end
    AND (Variants.mut_type=tgt_KnownMutations.mut_type OR tgt_KnownMutations.mut_type IS NULL)
    AND (region_type="exonic" OR region_type="structural") AND (mut_type!="synonymous SNV")
  ) AS NrAllMutations2,

  (SELECT COUNT(*) FROM Coverage
    WHERE (SampleID=:sid AND Coverage.chr=tgt_KnownMutations.chr AND pos BETWEEN tgt_KnownMutations.start AND tgt_KnownMutations.end AND cvg < 20)) AS NrBadCovered2

  FROM tgt_KnownMutations LEFT JOIN tgt_Regions ON tgt_KnownMutations.version=0 AND tgt_Regions.design=1 AND tgt_KnownMutations.chr=tgt_Regions.chr
  AND (GREATEST(tgt_KnownMutations.start, tgt_Regions.start) BETWEEN tgt_KnownMutations.start AND tgt_KnownMutations.end)
  AND (LEAST(tgt_KnownMutations.end, tgt_Regions.end) BETWEEN tgt_KnownMutations.start AND tgt_KnownMutations.end)
  GROUP BY MutationID, tgt_KnownMutations.chr, tgt_KnownMutations.start, tgt_KnownMutations.end) as r

WHERE version=0
GROUP BY MutationID
ORDER BY NrBadCovered, NrMutations DESC, MutationID';
                    try
                    {
                        $stmt = $this->_db->prepare($sql);
                        $stmt->bindParam(':sid', $sid, PDO::PARAM_INT);
                        $stmt->execute();
                        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        return array(1, $rows);
                    }
                    catch(PDOException $e)
                    {
                        return array(0, 
                            "Could not fetch regions.");
                    }

                } else {
                    return array(0, 
                        "The requested SampleID does not belong to the logged in user.");
                }
            } else {
                return $check;
            }
        } else {
            return array(0, 
                    "It seems like you are not logged in.");
        }
    }*/

    /**
     * Fetches the gene overview information of the sample with SampleID $sid
     *
     * @param int $sid  The SampleID
     *
     * @return array    An array containing a status code and the query results array or an error message
     */
    public function getGeneOverview($sid, $design)
    {
        if(isset($_SESSION['UserID']) && isset($_SESSION['LoggedIn']) && $_SESSION['LoggedIn']==1) {
            $check = $this->checkSampleID($sid);
            if($check[0] == 1) {
                if($check[1] == true) {

                    $sql = file_get_contents('/var/amlvaran/www/report/getGenePanel.sql');
                    try
                    {
                        $stmt = $this->_db->prepare($sql);
                        $stmt->bindParam(':sid', $sid, PDO::PARAM_INT);
                        $stmt->bindParam(':design', $design, PDO::PARAM_INT);
                        $stmt->execute();
                        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        return array(1, $rows);
                    }
                    catch(PDOException $e)
                    {
                        return array(0, 
                            "Could not fetch overview.");
                    }

                } else {
                    return array(0, "The requested SampleID does not belong to the logged in user. [03]");
                }
            } else {
                return $check;
            }
        } else {
            return array(0, 
                    "It seems like you are not logged in.");
        }
    }

    /**
     * Fetches the Civic information
     *
     * @param String $gene  The hgnc_gene name
     * @param String $protein  The protein field
     *
     * @return array    An array containing a status code and the query results array or an error message
     */
    public function getCivicInfo($variant_id)
    {
        if(isset($_SESSION['UserID']) && isset($_SESSION['LoggedIn']) && $_SESSION['LoggedIn']==1) {
            
            $sql = 'SELECT variant, disease, clinical_significance, drugs, evidence_type, evidence_direction, evidence_level, pubmed_id, citation, evidence_statement, evidence_civic_url FROM db_civicEvidence WHERE variant_id=:variant_id';

            try
            {
                $stmt = $this->_db->prepare($sql);
                $stmt->bindParam(':variant_id', $variant_id, PDO::PARAM_INT);
                $stmt->execute();
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                return array(1, $rows);
            }
            catch(PDOException $e)
            {
                return array(0, 
                    "Could not fetch relevant variants.");
            }

        } else {
            return array(0, 
                    "It seems like you are not logged in.");
        }
    }
    
    /**
     * Fetches the CivicGenes information
     *
     * @param String $gene  The hgnc_gene name
     *
     * @return array    An array containing a status code and the query results array or an error message
     */
    public function getCivicGenesInfo($gene)
    {
        if(isset($_SESSION['UserID']) && isset($_SESSION['LoggedIn']) && $_SESSION['LoggedIn']==1) {

            $sql = 'SELECT * FROM db_civicGenes WHERE name LIKE CONCAT(:gene, "%")';
            try
            {
                $stmt = $this->_db->prepare($sql);
                $stmt->bindParam(':gene', $gene, PDO::PARAM_STR);
                $stmt->execute();
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                return array(1, $rows);
            }
            catch(PDOException $e)
            {
                return array(0, 
                    "Could not fetch gene information.");
            }

        } else {
            return array(0, 
                    "It seems like you are not logged in.");
        }   
    }
    
    
    /**
     * Fetches the CivicVariants information
     *
     * @param Int $position The position of the gene 
     * @param Int $chr   The chromosome of the gene 
     *
     * @return array    An array containing a status code and the query results array or an error message
     */
    public function getCivicVariantsInfo($position, $chr)
    {  
        if(isset($_SESSION['UserID']) && isset($_SESSION['LoggedIn']) && $_SESSION['LoggedIn']==1) {
            
            $sql = 'SELECT * FROM db_civicVariants WHERE (:pos between start AND stop) AND chr = :chr';
            try
            {
                $stmt = $this->_db->prepare($sql);
                $stmt->bindParam(':pos', $position, PDO::PARAM_INT);
                $stmt->bindParam(':chr', $chr, PDO::PARAM_INT);
                $stmt->execute();
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                return array(1, $rows);
            }
            catch(PDOException $e)
            {
                return array(0, 
                    "Could not fetch gene information.");
            }

        } else {
            return array(0, 
                    "It seems like you are not logged in.");
        } 
    }
    
	 public function getPdfVersion($sid, $version){
         if(isset($_SESSION['UserID']) && isset($_SESSION['LoggedIn']) && $_SESSION['LoggedIn']==1) {
            $check = $this->checkSampleID($sid);
            if($check[0] == 1) {
                if($check[1] == true) {
                    
                    
                    $sql = 'SELECT * FROM reports WHERE SampleID=:sid AND version=:version';
                    try
                    {
                        $stmt = $this->_db->prepare($sql);
                        $stmt->bindParam(':sid', $sid, PDO::PARAM_INT);
                        $stmt->bindParam(':version', $version, PDO::PARAM_INT);
                        $stmt->execute();
                        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        return array(1, $rows);
                    }
                    catch(PDOException $e)
                    {
                        return array(0, 
                            "Could not fetch overview.");
                    }
                    
                    
                    
                }else {
                    return array(0, "The requested SampleID does not belong to the logged in user. [04]");
                }
            } else {
                return $check;
            }
         }else {
            return array(0, 
                    "It seems like you are not logged in.");
         }
    }
    
    
    /**
     * Fetches the list of reports for the sample with ID $sid
     *
     * @param int $sid   The Sample ID
     *
     * @return array    An array containing a status code and the query results array or an error message
     */
    public function getPdfReports($sid)
    {
        if(isset($_SESSION['UserID']) && isset($_SESSION['LoggedIn']) && $_SESSION['LoggedIn']==1) {
            $check = $this->checkSampleID($sid);
            if($check[0] == 1) {
                if($check[1] == true) {

                    $sql = 'SELECT * FROM reports WHERE SampleID=:sid ORDER BY Version DESC';
                    try
                    {
                        $stmt = $this->_db->prepare($sql);
                        $stmt->bindParam(':sid', $sid, PDO::PARAM_INT);
                        $stmt->execute();
                        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        return array(1, $rows);
                    }
                    catch(PDOException $e)
                    {
                        return array(0, 
                            "Could not fetch overview.");
                    }

                } else {
                    return array(0, "The requested SampleID does not belong to the logged in user. [05]");
                }
            } else {
                return $check;
            }
        } else {
            return array(0, 
                    "It seems like you are not logged in.");
        }
    }

    /**
     * Inserts a new report into the database
     *
     * @param int $sid   The Sample ID
     * @param int $version  The associated version
     *
     * @return array    An array containing a status code and the query results array or an error message
     */
    public function createPdfReport($sid, $version) #Used by generatepdf.php
    {
        if(isset($_SESSION['UserID']) && isset($_SESSION['LoggedIn']) && $_SESSION['LoggedIn']==1) {
            $check = $this->checkSampleID($sid);
            if($check[0] == 1) {
                if($check[1] == true) {

                    $sql = 'INSERT INTO reports (SampleID, Version, Created)
                        VALUES (:sid, :version, NOW())';
                    try
                    {
                        $stmt = $this->_db->prepare($sql);
                        $stmt->bindParam(':sid', $sid, PDO::PARAM_INT);
                        $stmt->bindParam(':version', $version, PDO::PARAM_INT);

                        if($stmt->execute())
                        {
                            return array(1, $this->_db->lastInsertId());
                        }
                        else
                        {
                            return array(0, 
                                "Something went wrong while saving the report info.");
                        }
                    }
                    catch(PDOException $e)
                    {
                        return array(0, 
                            "Could not fetch overview.");
                    }

                } else {
                    return array(0, "The requested SampleID does not belong to the logged in user. [06]");
                }
            } else {
                return $check;
            }
        } else {
            return array(0, 
                    "It seems like you are not logged in.");
        }
    }

    /**
     * Fetches the current targeting version
     *
     * @return array    An array containing a status code and the query results array or an error message
     */
    public function getCurrentVersion($sid)
    {
        if(isset($_SESSION['UserID']) && isset($_SESSION['LoggedIn']) && $_SESSION['LoggedIn']==1) {
            //$sql = 'select max(version) from tgt_KnownMutations';
            //$sql = 'SELECT max(version) FROM samples WHERE UserID=:userid';
            $sql = 'SELECT version FROM samples WHERE UserID=:userid AND SampleID=:sid';
            try
            {
                $stmt = $this->_db->prepare($sql);
                $stmt->bindParam(':userid', $_SESSION['UserID'], PDO::PARAM_INT);
                $stmt->bindParam(':sid', $sid, PDO::PARAM_INT);
                $stmt->execute();
                $row = $stmt->fetch();
                return array(1, $row[0]);
            }
            catch(PDOException $e)
            {
                return array(0, 
                    "Could not fetch version.");
            }
        } else {
            return array(0, 
                    "It seems like you are not logged in.");
        }
    }

    /**
     * Stores a new sample in the database.
     *
     * @param array dataarray   An array containing Diagnosis, Comments and SampleTakeDate
     *
     * @return array    An array containing a status code and status message
     */
   public function createSample($pid, $dataarray)
    {
        if(isset($_SESSION['UserID']) && isset($_SESSION['LoggedIn']) && $_SESSION['LoggedIn']==1) {
            if(isset($_SESSION['editPermission']) && $_SESSION['editPermission']==1) {
                $sql = 'SELECT PatientID FROM patients WHERE UserID=:uid AND PatientID=:pid';
                try{
                    $stmt = $this->_db->prepare($sql);
                    $stmt->bindParam(':uid', $_SESSION['UserID'], PDO::PARAM_INT);
                    $stmt->bindParam(':pid', $pid, PDO::PARAM_INT);
                    $stmt->execute();

                    if($stmt->rowCount()==1)
                    {
                        if(isset($dataarray)) {
                            $newver = 0;
                            $data = json_decode($dataarray);
                            $sql = 'SELECT max(version) FROM samples WHERE UserID=:uid';
                            try
                            {
                                $stmt = $this->_db->prepare($sql);
                                $stmt->bindParam(':uid', $_SESSION['UserID'], PDO::PARAM_INT);

                                $stmt->execute();
                                $row = $stmt->fetch();
                                $newver = intval($row[0]);
                            }
                            catch(PDOException $e)
                            {
                                return array(0, 
                                    "Something went wrong while saving the sample info. [1]");
                            }

                            $sql = 'INSERT INTO samples (PatientID, UserID, Diagnosis, Comments, SampleTakeDate, Created, design, version)
                                VALUES (:pid, :uid, :diagnosis, :comments, DATE(:std), NOW(), :designId, :versionId)';
                            try
                            {
                                $stmt = $this->_db->prepare($sql);
                                $stmt->bindParam(':pid', $pid, PDO::PARAM_INT);
                                $stmt->bindParam(':uid', $_SESSION['UserID'], PDO::PARAM_INT);
                                $stmt->bindParam(':diagnosis', $data[0], PDO::PARAM_STR);
                                $stmt->bindParam(':comments', $data[1], PDO::PARAM_STR);
                                if (empty($data[2]) || $data[2] == ' ')
                                  $stmt->bindValue(':std', NULL, PDO::PARAM_STR);
                                else
                                  $stmt->bindParam(':std', $data[2], PDO::PARAM_STR);
                                $stmt->bindParam(':designId', $data[3], PDO::PARAM_INT);
                                $stmt->bindParam(':versionId', $newver, PDO::PARAM_INT);

                                if($stmt->execute())
                                {
                                    return array(1, $this->_db->lastInsertId());
                                }
                                else
                                {
                                    return array(0, 
                                        "Something went wrong while saving the sample info. [2]");
                                }
                            }
                            catch(PDOException $e)
                            {
                                return array(0, 
                                    "Something went wrong while saving the sample info. [3]");
                            }
                        } else {
                            return array(0, 
                                    "No data array.");
                        }
                    } else {
                        return array(0, 
                            "The PatientID does not belong to the logged in User.");
                    }
                }
                catch(PDOException $e)
                {
                    return array(0, 
                        "Could not check PatientID.");
                }
            } else {
                return array(0, 
                    "You have no edit-Permissions.");
            }
        } else {
            return array(0, 
                    "It seems like you are not logged in.");
        }
    }
    
    /**
    *Set all appreci8 values = 0 
    *@param String $sid The SampleID
    *
    */
    public function clearAppreci8($sid){
        if(isset($_SESSION['UserID']) && isset($_SESSION['LoggedIn']) && $_SESSION['LoggedIn']==1) {
                $check = $this->checkSampleID($sid);
                if($check[0] == 1) {
                    if($check[1] == true) {

                        
                        $sql = 'UPDATE Variants SET appreci8=0 WHERE SampleID=:sid';
                        try
                        {
                            $stmt = $this->_db->prepare($sql);
                            $stmt->bindParam(':sid', $sid, PDO::PARAM_INT);
                            $stmt->execute();

                            if($stmt->rowCount() > 0)
                            {
                                return array(1, 'Success');
                            }
                            else
                            {
                                return array(1, "Everything was already up to date.");
                            }
                        }
                        catch(PDOException $e)
                        {
                            return array(0, 
                                "Could not fetch overview.");
                        }

                    } else {
                        return array(0, "The requested SampleID does not belong to the logged in user. [07]");
                    }
                } else {
                    return $check;
                }
        } else {
            return array(0, 
                    "It seems like you are not logged in.");
        }
    }
    
    /**
     * Updates appreci8 in the database.
     *
     * @param String $sid  The SampleID
     *
     * @return array    An array containing a status code and status message
     */
    public function setAppreci8($sid, $chr, $pos, $ref, $alt , $score)
    {
        if(isset($_SESSION['UserID']) && isset($_SESSION['LoggedIn']) && $_SESSION['LoggedIn']==1) {
                $check = $this->checkSampleID($sid);
                if($check[0] == 1) {
                    if($check[1] == true) {

                        
                        $sql = 'UPDATE Variants SET appreci8=:score
                            WHERE SampleID=:sid and chr=:chr and pos=:pos and ref=:ref and alt=:alt';
                        try
                        {
                            $stmt = $this->_db->prepare($sql);
                            $stmt->bindParam(':sid', $sid, PDO::PARAM_INT);
                            $stmt->bindParam(':chr', $chr, PDO::PARAM_STR);
                            $stmt->bindParam(':pos', $pos, PDO::PARAM_INT);
                            $stmt->bindParam(':ref', $ref, PDO::PARAM_STR);
                            $stmt->bindParam(':alt', $alt, PDO::PARAM_STR);
                            $stmt->bindParam(':score', $score, PDO::PARAM_INT);
                            $stmt->execute();

                            if($stmt->rowCount() > 0)
                            {
                                return array(1, 'Success');
                            }
                            else
                            {
                                return array(1, "Everything was already up to date.");
                            }
                        }
                        catch(PDOException $e)
                        {
                            return array(0, 
                                "Could not fetch overview.");
                        }

                    } else {
                        return array(0, 
                            "The requested SampleID $sid does not belong to the logged in user. [08]");
                    }
                } else {
                    return $check;
                }
        } else {
            return array(0, 
                    "It seems like you are not logged in.");
        }
    }
    
    
    
    
    
    
    
    
    /**
    * Load some information for the additional info tables
    *
    * @param String $gene   The name of the gene
    *
    * @return An array containing a status code and status message
    */
    public function getAdditionalTableInfo($gene)
    {
        if(isset($_SESSION['UserID']) && isset($_SESSION['LoggedIn']) && $_SESSION['LoggedIn']==1) {
                

            
            $sql = 'SELECT `Gene_full_name`, `Function_description`, `Pathway(ConsensusPathDB)` FROM db_dbNSFPGene WHERE Gene_name=:gene';
                        
            try
            {
                $stmt = $this->_db->prepare($sql);
                $stmt->bindParam(':gene', $gene, PDO::PARAM_STR);
                $stmt->execute();
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                return array(1, $row);
                
            }         
            catch(PDOException $e)
            {
                return array(0, 
                    "Could not fetch additional info.");
            }

                    
        } else {
            return array(0, 
                    "It seems like you are not logged in.");
        }
    }
    
    
    
    
    
    
    

    /**
     * Updates a sample in the database.
     *
     * @param String $sid  The SampleID
     * @param String $diagnosis  The Diagnosis
     * @param String $comments  The Comments
     * @param String $std  The SampleTakeDate
     *
     * @return array    An array containing a status code and status message
     */
    public function updateSample($sid, $diagnosis, $comments, $std)
    {
        if(isset($_SESSION['UserID']) && isset($_SESSION['LoggedIn']) && $_SESSION['LoggedIn']==1) {
            if(isset($_SESSION['editPermission']) && $_SESSION['editPermission']==1) {
                $check = $this->checkSampleID($sid);
                if($check[0] == 1) {
                    if($check[1] == true) {

                        $sql = 'UPDATE samples
                    SET Diagnosis=:diagnosis, Comments=:comments, SampleTakeDate=DATE(:std)
                    WHERE SampleID=:sid';
                        try
                        {
                            $stmt = $this->_db->prepare($sql);
                            $stmt->bindParam(':sid', $sid, PDO::PARAM_INT);
                            $stmt->bindParam(':diagnosis', $diagnosis, PDO::PARAM_STR);
                            $stmt->bindParam(':comments', $comments, PDO::PARAM_STR);
                            if (empty($std || $std == ' '))
                              $stmt->bindValue(':std', NULL, PDO::PARAM_STR);
                            else
                              $stmt->bindParam(':std', $std, PDO::PARAM_STR);
                            $stmt->execute();

                            if($stmt->rowCount() > 0)
                            {
                                return array(1, 'Success');
                            }
                            else
                            {
                                return array(0, 
                                    "Something went wrong while updating the patient info.");
                            }
                        }
                        catch(PDOException $e)
                        {
                            return array(0, 
                                "Could not fetch overview.");
                        }

                    } else {
                        return array(0, "The requested SampleID does not belong to the logged in user. [09]");
                    }
                } else {
                    return $check;
                }
            } else {
                return array(0, 
                    "You have no edit-Permissions.");
            }
        } else {
            return array(0, 
                    "It seems like you are not logged in.");
        }
    }

    /**
     * Updates a sample in the database to set the State to File Uploaded.
     *
     * @param String $sid  The SampleID
     *
     * @return array    An array containing a status code and status message
     */
    public function updateFileUpload($sid, $state)
    {
        if(isset($_SESSION['UserID']) && isset($_SESSION['LoggedIn']) && $_SESSION['LoggedIn']==1) {
            if(isset($_SESSION['editPermission']) && $_SESSION['editPermission']==1) {
                $check = $this->checkSampleID($sid);
                if($check[0] == 1) {
                    if($check[1] == true) {

                        $sql = 'UPDATE samples
                    SET StateCode=:state
                    WHERE SampleID=:sid';
                        try
                        {
                            $stmt = $this->_db->prepare($sql);
                            $stmt->bindParam(':sid', $sid, PDO::PARAM_INT);
                            $stmt->bindParam(':state', $state, PDO::PARAM_INT);
                            $stmt->execute();

                            if($stmt->rowCount() > 0)
                            {
                                return array(1, 'Success');
                            }
                            else
                            {
                                return array(0, 
                                    "Something went wrong while updating the patient info.");
                            }
                        }
                        catch(PDOException $e)
                        {
                            return array(0, 
                                "Could not fetch overview.");
                        }

                    } else {
                        return array(0, "The requested SampleID does not belong to the logged in user. [10]");
                    }
                } else {
                    return $check;
                }
            } else {
                return array(0, 
                    "You have no edit-Permissions.");
            }
        } else {
            return array(0, 
                    "It seems like you are not logged in.");
        }
    }
    
    
    
    /**
     * Updates the Rating in the Samples Database.
     *
     * @param String $sid  The SampleID
     * @param String $report The Rating
     *
     * @return array    An array containing a status code and status message
     */
    public function updateRating($sid, $rating)
    {
        if(isset($_SESSION['UserID']) && isset($_SESSION['LoggedIn']) && $_SESSION['LoggedIn']==1) {
            $check = $this->checkSampleID($sid);
            if($check[0] == 1) {
                if($check[1] == true) {

                    $sql = 'UPDATE samples
                        SET Rating=:rating
                        WHERE SampleID=:sid';
                    try
                    {
                        $stmt = $this->_db->prepare($sql);
                        $stmt->bindParam(':sid', $sid, PDO::PARAM_INT);
                        $stmt->bindParam(':rating', $rating, PDO::PARAM_STR);
                        $stmt->execute();

                        if($stmt->rowCount() > 0)
                        {
                            return array(1, 'Success');
                        }
                        else
                        {
                            return array(1, 'No changes made');
                        }
                    }
                    catch(PDOException $e)
                    {
                        return array(0, 
                            "Could not fetch overview.");
                    }

                } else {
                    return array(0, "The requested SampleID does not belong to the logged in user. [11]");
                }
            } else {
                return $check;
            }
        } else {
            return array(0, 
                    "It seems like you are not logged in.");
        }
    }
    
    
    /**
     * Get the Rating for the given SampleID
     *
     * @param String $sid  The SampleID
     *
     * @return array    An array containing a status code and status message
     */
    public function getRating($sid)
    {
        if(isset($_SESSION['UserID']) && isset($_SESSION['LoggedIn']) && $_SESSION['LoggedIn']==1) {
            $check = $this->checkSampleID($sid);
            if($check[0] == 1) {
                if($check[1] == true) {

                    $sql = 'SELECT Rating FROM samples WHERE SampleID=:sid';
                    try
                    {
                        $stmt = $this->_db->prepare($sql);
                        $stmt->bindParam(':sid', $sid, PDO::PARAM_INT);
                        $stmt->execute();
                        $row = $stmt->fetch(PDO::FETCH_ASSOC);
                        return array(1, $row);
                    }
                    catch(PDOException $e)
                    {
                        return array(0, 
                            "Could not fetch rating.");
                    }

                } else {
                    return array(0, "The requested SampleID does not belong to the logged in user. [12]");
                }
            } else {
                return $check;
            }
        } else {
            return array(0, 
                    "It seems like you are not logged in.");
        }
    }

    
    /**
    *Look for the canonical Transcript of the given gene    *
    */
    public function getCanonicalTranscript($gene){
            if(isset($_SESSION['UserID']) && isset($_SESSION['LoggedIn']) && $_SESSION['LoggedIn']==1) {
                
                    $sql = 'SELECT transcriptEnsemble FROM db_CanonicalTranscriptsUCSC WHERE geneSymbol=:gene';
                    try
                    {
                        $stmt = $this->_db->prepare($sql);
                        $stmt->bindParam(':gene', $gene, PDO::PARAM_STR);
                        $stmt->execute();
                        $row = $stmt->fetch(PDO::FETCH_ASSOC);
                        return array(1, $row);
                    }
                    catch(PDOException $e)
                    {
                        return array(0, 
                            "Could not fetch the canonical Transcript of "+$gene+".");
                    }
            } else {
            return array(0, 
                    "It seems like you are not logged in.");
        }
    }
    
    
    /**
    *   Remove the given sample
    */
    public function removeSample($sid, $pid){
        if(isset($_SESSION['UserID']) && isset($_SESSION['LoggedIn']) && $_SESSION['LoggedIn']==1) {
            //if(isset($_SESSION['editPermission']) && $_SESSION['editPermission']==1) {
                $check = $this->checkSampleID($sid);
                if($check[0] == 1) {
                
                    $sql = 'DELETE FROM samples WHERE SampleID=:sid';
                
                    try
                    {
                        $stmt = $this->_db->prepare($sql);
                        $stmt->bindParam(':sid', $sid, PDO::PARAM_INT);
                        $stmt->execute();
                        if($stmt->rowCount() <= 0){
                            return array(1, 'Failed to delete the sample data.');
                        }
                    }
                    catch(PDOException $e)
                    {
                        return array(0,"Could not delete the Sample data of Sample $sid .[1]");
                    }
                    
                    
                    
                    
                    
                    $sql = 'DELETE FROM Variants WHERE SampleID=:sid';
                
                    try
                    {
                        $stmt = $this->_db->prepare($sql);
                        $stmt->bindParam(':sid', $sid, PDO::PARAM_INT);
                        $stmt->execute();
                    }
                    catch(PDOException $e)
                    {
                        return array(0, 
                            "Could not delete the Sample data of Sample $sid .[1]");
                    }
                
                    
                    try {

                        //DELETE Folder and Files
                        $dir = '../samples/' . $pid . '/' . $sid;
                        exec("rm -r $dir");    

                    } catch(ErrorException $ex) {
                        return array(0, "Error: " . $ex->getMessage());
                    }
                    return array(1, "Success");
                    
                    
                } else {
                    return $check;
                }
            /*} else {
                return array(0,"You have no edit-Permissions.");
            }*/
        } else {
            return array(0, 
                    "It seems like you are not logged in.");
        }
    }
    
    
    
    public function getAllSamples(){
        if(isset($_SESSION['UserID']) && isset($_SESSION['LoggedIn']) && $_SESSION['LoggedIn']==1) {
                    $sql = 'SELECT count(*) FROM samples WHERE UserID=:userid';
                    try
                    {
                        $stmt = $this->_db->prepare($sql);
                        $stmt->bindParam(':userid', $_SESSION['UserID'], PDO::PARAM_INT);
                        $stmt->execute();
                        $row = $stmt->fetch(PDO::FETCH_ASSOC);
                        return array(1, $row);
                    }
                    catch(PDOException $e)
                    {
                        return array(0, 
                            "Could not fetch allSamples.");
                    } 
        } else {
            return array(0, 
                    "It seems like you are not logged in.");
        }
    
    
    
    
    
    
    
    
    }
    

}
