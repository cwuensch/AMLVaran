<?php
// Database credentials
define('DB_HOST', '192.168.1.2');
define('DB_USER', 'annoserv');
define('DB_PASS', 'DrubGovdug2');
define('DB_NAME', 'amlvaran');

$dsn = "mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8";
$db = new PDO($dsn, DB_USER, DB_PASS);

$sqlGetVariant1 =
'SELECT va.chr, va.pos, va.ref, va.alt, 
  rs_dbSNP150, genename AS Gene, cds_strand, refcodon, codonpos, codon_degeneracy, aaref, aaalt, aapos,
  Ensembl_geneid, Ensembl_transcriptid, Ensembl_proteinid, SIFT_score, SIFT_converted_rankscore, SIFT_pred, Uniprot_acc_Polyphen2, Uniprot_id_Polyphen2, Uniprot_aapos_Polyphen2, Polyphen2_HDIV_score, Polyphen2_HDIV_rankscore, Polyphen2_HDIV_pred, Polyphen2_HVAR_score, Polyphen2_HVAR_rankscore, Polyphen2_HVAR_pred, LRT_score, LRT_converted_rankscore, LRT_pred, LRT_Omega, MutationTaster_score, MutationTaster_converted_rankscore, MutationTaster_pred, MutationTaster_model, MutationTaster_AAE, MutationAssessor_UniprotID, MutationAssessor_variant, MutationAssessor_score, MutationAssessor_score_rankscore, MutationAssessor_pred, FATHMM_score, FATHMM_converted_rankscore, FATHMM_pred, PROVEAN_score, PROVEAN_converted_rankscore, PROVEAN_pred,
  Transcript_id_VEST3, Transcript_var_VEST3, VEST3_score, VEST3_rankscore, MetaSVM_score, MetaSVM_rankscore, MetaSVM_pred, MetaLR_score, MetaLR_rankscore, MetaLR_pred, Reliability_index, `M-CAP_score`, `M-CAP_rankscore`, `M-CAP_pred`, REVEL_score, REVEL_rankscore, MutPred_score, MutPred_rankscore, MutPred_protID, MutPred_AAchange, MutPred_Top5features, CADD_raw, CADD_raw_rankscore, CADD_phred, DANN_score, DANN_rankscore, `fathmm-MKL_coding_score`, `fathmm-MKL_coding_rankscore`, `fathmm-MKL_coding_pred`, `fathmm-MKL_coding_group`, Eigen_coding_or_noncoding, `Eigen-raw`, `Eigen-phred`, `Eigen-PC-raw`, `Eigen-PC-phred`, `Eigen-PC-raw_rankscore`, GenoCanyon_score, GenoCanyon_score_rankscore, integrated_fitCons_score, integrated_fitCons_score_rankscore, integrated_confidence_value, GM12878_fitCons_score, GM12878_fitCons_score_rankscore, GM12878_confidence_value, `H1-hESC_fitCons_score`, `H1-hESC_fitCons_score_rankscore`, `H1-hESC_confidence_value`, HUVEC_fitCons_score, HUVEC_fitCons_score_rankscore, HUVEC_confidence_value, `GERP++_NR`, `GERP++_RS`, `GERP++_RS_rankscore`, phyloP100way_vertebrate, phyloP100way_vertebrate_rankscore, phyloP20way_mammalian, phyloP20way_mammalian_rankscore, phastCons100way_vertebrate, phastCons100way_vertebrate_rankscore, phastCons20way_mammalian, phastCons20way_mammalian_rankscore, SiPhy_29way_pi, SiPhy_29way_logOdds, SiPhy_29way_logOdds_rankscore,
  1000Gp3_AF, 1000Gp3_EUR_AF, TWINSUK_AF, ALSPAC_AF, ESP6500_EA_AF, ExAC_AF, ExAC_Adj_AF, ExAC_NFE_AF, gnomAD_exomes_AF, gnomAD_exomes_NFE_AF, gnomAD_genomes_AF, gnomAD_genomes_NFE_AF, Interpro_domain, GTEx_V6p_gene, GTEx_V6p_tissue,
  CLNSIG AS ClinVar_Significance, CLNDN AS ClinVar_Disease, CLNREVSTAT AS Clinvar_Status, CosmicID, CosmicSites, NrHaemato AS Cosmic_NrHaemato, NrSamples AS Cosmic_NrSamples, NrSites AS Cosmic_NrSites,
(SELECT COUNT(*) FROM db_civicVariants WHERE chr=va.chr AND va.pos BETWEEN start and stop AND (alt IS NULL OR alt LIKE va.alt)) AS NrCivic
FROM
  (SELECT :chr AS chr, :pos AS pos, :ref AS ref, :alt AS alt) AS va
  LEFT JOIN db_dbNSFP   AS a ON va.chr=a.chr AND va.pos=a.pos AND va.ref=a.ref AND va.alt=a.alt
  LEFT JOIN clinvar     AS e ON va.chr=e.chr AND va.pos=e.pos AND va.ref=e.ref AND va.alt=e.alt
  LEFT JOIN db_Cosmic   AS f ON va.chr=f.chr AND va.pos=f.pos AND va.ref=f.ref AND va.alt=f.alt';

$sqlGetVariant2 =
'SELECT va.chr, va.pos, va.ref, va.alt, va.Gene, va.Transcripts, va.Exons, a.dbSNP, a.Version AS dbSNP_Version, a.Precious AS dbSNP_PM, G1000_AF, G1000_eur, ESP6500, ExAC_all, ExAC_eur, CLNSIG AS ClinVar_Significance, CLNDN AS ClinVar_Disease, CLNREVSTAT AS Clinvar_Status, CosmicID, CosmicSites, NrHaemato AS Cosmic_NrHaemato, NrSamples AS Cosmic_NrSamples, NrSites AS Cosmic_NrSites,
(SELECT COUNT(*) FROM db_civicVariants WHERE chr=va.chr AND va.pos BETWEEN start and stop AND (alt IS NULL OR alt LIKE va.alt)) AS NrCivic
FROM
  (SELECT v.chr, v.pos, v.ref, v.alt, e.gene_name AS Gene, group_concat(e.transcript) AS Transcripts, group_concat(e.exon_nr) AS Exons FROM
    (SELECT :chr AS chr, :pos AS pos, :ref AS ref, :alt AS alt) AS v
    LEFT JOIN db_Exons AS e ON v.chr=e.chr AND v.pos BETWEEN e.start AND e.end AND (:trans is null OR :trans = "" OR :trans LIKE concat("%", e.transcript, "%"))
  GROUP BY v.chr, v.pos, v.ref, v.alt) AS va
  LEFT JOIN db_dbSNP    AS a ON va.chr=a.chr AND va.pos=a.pos AND va.ref=a.ref AND va.alt=a.alt
  LEFT JOIN db_G1000    AS b ON va.chr=b.chr AND va.pos=b.pos AND va.ref=b.ref AND va.alt=b.alt
  LEFT JOIN db_ESP6500  AS c ON va.chr=c.chr AND va.pos=c.pos AND va.ref=c.ref AND va.alt=c.alt
  LEFT JOIN db_ExAC     AS d ON va.chr=d.chr AND va.pos=d.pos AND va.ref=d.ref AND va.alt=d.alt
  LEFT JOIN clinvar     AS e ON va.chr=e.chr AND va.pos=e.pos AND va.ref=e.ref AND va.alt=e.alt
  LEFT JOIN db_Cosmic   AS f ON va.chr=f.chr AND va.pos=f.pos AND va.ref=f.ref AND va.alt=f.alt';

$sqlGetGene =
'SELECT * FROM db_dbNSFPGene WHERE Gene_name = :gene';

$sqlGetProvean = 
'SELECT * FROM db_Provean WHERE transcript_id=:trans AND position=:pos';

$sqlGetSIFT = 
'SELECT * FROM db_SIFT WHERE transcript_id=:trans AND position=:pos';


$chr = (isset($_GET['chr'])) ? $_GET['chr'] : '2';
$pos = (isset($_GET['pos'])) ? intval($_GET['pos']) : 25457243;
$ref = (isset($_GET['ref'])) ? $_GET['ref'] : 'G';
$alt = (isset($_GET['alt'])) ? $_GET['alt'] : 'T';
$trans_str = (isset($_GET['trans'])) ? $_GET['trans'] : '';
$prots_str = (isset($_GET['prots'])) ? $_GET['prots'] : '';
$csv = (isset($_GET['output']) && ($_GET['output'] == 'csv'));
$sqlstr = 1;
$gene = '';

$csv || print('<!DOCTYPE HTML>
<html>
<head>
  <meta charset="utf-8">
  <title>VariantAnnotator - GET API</title>
</head>
<body>
<h1>VariantAnnotator - GET API</h1>
  <p><b>Please ensure that you upload only data for which you are entitled to do so!</b></p>
  <p>Enter one variant into the following fields (or send it via HTTP GET request):</p>');
$csv && print("<pre>\n");

$csv || print("<form action='' method='get'>
  <table>
    <tr><td align='right'><label for='chr'>Chromosome:</label></td> <td><input type='text' id='chr' name='chr' value='$chr' size='20'> <small>( e.g. 2 )</small></td></tr>
    <tr><td align='right'><label for='pos'>Position:</label></td> <td><input type='text' id='pos' name='pos' value='$pos' size='20'> <small>( e.g. 25457243 )</small></td></tr>
    <tr><td align='right'><label for='ref'>Reference Allele:</label></td> <td><input type='text' id='ref' name='ref' value='$ref' size='20'> <small>( e.g. G )</small></td></tr>
    <tr><td align='right'><label for='alt'>Alternative Allele:</label></td> <td><input type='text' id='alt' name='alt' value='$alt' size='20'> <small>( e.g. T )</small></td></tr>
    <tr><td align='right'><label for='trans'><i>Transcripts* (comma separated):</i></label></td> <td><input type='text' id='trans' name='trans' value='$trans_str' size='50'></td></tr>
    <tr><td align='right'><label for='prots'><i>Protein changes (comma separated, one for each transcript):</i></label></td> <td><input type='text' id='prots' name='prots' value='$prots_str' size='50'></td></tr>
    <tr><td></td> <td><input type='checkbox' id='output' name='output' value='csv'> <label for='output'>Output as csv</label></td></tr>
    <tr><td></td> <td><input type='submit' value='Annotate'></td></tr>
  </table>
</form>\n");

try
{
    $getVar1 = $db->prepare($sqlGetVariant1);
    $getVar2 = $db->prepare($sqlGetVariant2);
    $getGene = $db->prepare($sqlGetGene);
    $getProv = $db->prepare($sqlGetProvean);
    $getSIFT = $db->prepare($sqlGetSIFT);
}
catch(PDOException $e)
{
    echo 'Problem with database query';
}


$trans = explode(',', $trans_str);
$prots = explode(',', $prots_str);
while ($ref != "" && $alt != "" && $ref[0] == $alt[0])
{
  $ref = substr($ref, 1);
  $alt = substr($alt, 1);
  $pos = $pos + 1;
  if ($ref == "") $ref = "-";
  if ($alt == "") $alt = "-";
}

$starttime = time();
$csv || print("\n<h3>Variant info:</h3>\n");
$csv || print("<table border>\n");

if (isset($_GET['chr']))
for ($i = 0; $i < 1; $i++)
{
    try
    {
        $getVar1->bindParam(':chr', $chr, PDO::PARAM_STR, 2);
        $getVar1->bindParam(':pos', $pos, PDO::PARAM_INT);
        $getVar1->bindParam(':ref', $ref, PDO::PARAM_STR);
        $getVar1->bindParam(':alt', $alt, PDO::PARAM_STR);
        $getVar1->execute();
        if($getVar1->rowCount() >= 1)
            $rows = $getVar1->fetchAll(PDO::FETCH_ASSOC);
    }
    catch(PDOException $e)
    {
        echo 'Problem with database query1';
        $rows = None;
    }

    if (!isset($rows) || $rows[0]['Gene'] === null)
    {
        $sqlstr = 2;
        try
        {
            $getVar2->bindParam(':chr', $chr, PDO::PARAM_STR, 2);
            $getVar2->bindParam(':pos', $pos, PDO::PARAM_INT);
            $getVar2->bindParam(':ref', $ref, PDO::PARAM_STR);
            $getVar2->bindParam(':alt', $alt, PDO::PARAM_STR);
            $getVar2->bindParam(':trans', $trans_str, PDO::PARAM_STR);
            $getVar2->execute();
            if($getVar2->rowCount() >= 1)
                $rows = $getVar2->fetchAll(PDO::FETCH_ASSOC);
        }
        catch(PDOException $e)
        {
            echo 'Problem with database query2';
            $rows = None;
        }
    }
    
    if(isset($rows))
    {
        if ($i == 0)
        {
            // Get row data
//            while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $csv || print("<thead>\n");
            $csv || print('  <tr>');
            foreach (array_keys($rows[0]) as $name)
                print($csv ? "$name\t" : "<th>$name</th>");
            if ($sqlstr == 2)
                print($csv ? "Provean\tSIFT\n" : '<th>Provean</th><th>SIFT</th>');
            $csv || print("</tr>\n");
            $csv || print("</thead>\n<tbody>");
            print("\n");
        }
        
        foreach ($rows as $row)  // eigentlich nur eine Row
        {
            $gene = $row['Gene'];
            $csv || print('  <tr>');
            foreach ($row as $val)
            {
                $csv || $val2 = str_replace(',', ', <br>', $val);
                $csv || $val2 = str_replace(';', '; <br>', $val2);
                $csv || $val2 = str_replace('|', '| ', $val2);
                print($csv ? "$val\t" : "<td valign='top'>$val2</td>");
            }
            
            if ($sqlstr == 2)
            {
                $proveans = "";
                $sifts = "";
                
                for ($j = 0; $j < count($prots); $j++)
                {
                    if (!isset($trans) || empty($trans) || $trans[0] == "" || i >= count($trans))
                        break;
                    $curProt = $prots[$j];
                    $curTrans = $trans[$j];
                    $curRef = '';
                    $curAlt = '';
                    $curPos = 0;
                    $curProv = '?';
                    $curSIFT = '?';
        
                    if ($curProt[0] == 'p')
                        $curProt = substr($curProt, 2);
        
                    if (preg_match('/^(?<ref>\D+)(?<pos>\d+)(?<alt>\D*)/', $curProt, $matches) == true)
                    {
                        $curRef = $matches['ref'];
                        $curAlt = $matches['alt'];
                        $curPos = $matches['pos'];
                        if ($curAlt == "fs" || $curAlt == "*")
                        {
                            $curProv = '-';
                            $curSIFT = '-';
                        }
                        elseif ($curAlt == $curRef)
                        {
                            $curProv = 0.0;
                            $curSIFT = 0.0;
                        }
                        else if ((strlen($curAlt) == 1 && strspn($curAlt, 'ACDEFGHIKLMNPQRSTVWY') == 1) || $curAlt == 'del')
                        {
                            try
                            {
                                $getProv->bindParam(':trans', $curTrans, PDO::PARAM_STR, 16);
                                $getProv->bindParam(':pos', $curPos, PDO::PARAM_INT);
                                $getProv->execute();
                                if($getProv->rowCount() >= 1)
                                    $curProv = $getProv->fetch()[$curAlt];
            
                                if (curAlt != "del")
                                {
                                    $getSIFT->bindParam(':trans', $curTrans, PDO::PARAM_STR, 16);
                                    $getSIFT->bindParam(':pos', $curPos, PDO::PARAM_INT);
                                    $getSIFT->execute();
                                    if($getSIFT->rowCount() >= 1)
                                        $curSIFT = $getSIFT->fetch()[$curAlt];
                                }
                            }
                            catch(PDOException $e)
                            {
                                echo 'Problem with database query';
                            }
                        }
                    }
                    $proveans = $proveans . $curProv . ',';
                    $sifts = $sifts . $curSIFT . ',';
                }
                $csv || $proveans = str_replace(',', ', <br>', $proveans);
                $csv || $sifts = str_replace(',', ', <br>', $sifts);
                print($csv ? "$proveans\t$sifts\n" : "<td valign='top'>$proveans</td><td valign='top'>$sifts</td>");
            }
            $csv || print("</tr>\n");
        }
    }
    else
        $csv || print('<tr><td>No data available</td></tr>');
}        
$csv || print("</tbody>\n</table>\n<br>\n");


if (!$csv)
{
    print("\n<h3>Gene info:</h3>\n");
    print("<table border>\n");
    
    for ($i = 0; $i < 1; $i++)
    {
        $rows = null;
        try
        {
            $getGene->bindParam(':gene', $gene, PDO::PARAM_STR);
            $getGene->execute();
            if($getGene->rowCount() >= 1)
                $rows = $getGene->fetchAll(PDO::FETCH_ASSOC);
        }
        catch(PDOException $e)
        {
            echo 'Problem with database gene query';
            $rows = None;
        }
    
        if(isset($rows))
        {
/*            if ($i == 0)
            {
                // Get row data
//                while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                print("<thead>\n");
                print('  <tr>');
                foreach (array_keys($rows[0]) as $name)
                    print("<th>$name</th>");
                print("</tr>\n");
                print("</thead>\n<tbody>\n");
            }  */

            foreach (array_keys($rows[0]) as $col)
            {
                print("<tr>\n");
                print("  <td>$col</td>\n");
                foreach ($rows as $row)
                    print("  <td>" . str_replace(';', '; <br>', $row[$col]) . "</td>\n");
                print("</tr>\n");   
            }
        }
        else
            print('<tr><td>No gene data available</td></tr>');
    }        
    print("</tbody>\n</table>\n");
}


$runtime = time() - $starttime;
$csv || print("<p>Laufzeit: $runtime sek.</p>\n");
$csv || print('</body></html>');
$csv && print("\n</pre>\n");

?>
