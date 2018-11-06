<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: *');

// Database credentials
define('DB_HOST', '192.168.1.2');
define('DB_USER', 'annoserv');
define('DB_PASS', 'DrubGovdug2');
define('DB_NAME', 'amlvaran');

$dsn = "mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8";
$db = new PDO($dsn, DB_USER, DB_PASS);

// Amino Acid Replacements
$AminoAcids = array('Arg' => 'R', 'His' => 'H', 'Lys' => 'K', 'Asp' => 'D', 'Glu' => 'E', 'Ser' => 'S', 'Thr' => 'T',
                    'Asn' => 'N', 'Gln' => 'Q', 'Cys' => 'C', 'Sec' => 'U', 'Gly' => 'G', 'Pro' => 'P', 'Ala' => 'A',
                    'Val' => 'V', 'Ile' => 'I', 'Leu' => 'L', 'Met' => 'M', 'Phe' => 'F', 'Trp' => 'W', 'Tyr' => 'Y');

// Read POST data
if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
  $json_in = file_get_contents('php://input');
  if(substr($json_in, 0, 3) == 'in=')
    $json_in = urldecode(substr($json_in, 3));
  
  $use_cosmic = (strncmp($_SERVER['REMOTE_ADDR'], '128.176.', 8) === 0 || strncmp($_SERVER['REMOTE_ADDR'], '10.', 3) === 0);

  // Pass JSON input to SNPeff
  $context = stream_context_create(array('http' => array('method' => 'POST', 'header' => "Content-Type: application/json\r\n", 'content' => $json_in)));
  $response = file_get_contents('http://localhost:8080', FALSE, $context);
  if (!($response === FALSE))  $json_in = $response;
  
  // Parse input variants
  $variants = json_decode($json_in);
  $sql = "";
  $transset = false;
  for ($i=0; $i < count($variants); $i++)
  {
    if (isset($variants[$i]->chr) && isset($variants[$i]->pos) && isset($variants[$i]->ref) && isset($variants[$i]->alt))
    {
      // Get Parameters
      $chr = str_replace('"','',$variants[$i]->chr);
      $ref = str_replace('"','',$variants[$i]->ref); 
      $alt = str_replace('"','',$variants[$i]->alt); 
      $pos = intval($variants[$i]->pos);
      $nranno = 0;
  
      // Normalization
      while ($ref != "" && $alt != "" && $ref[0] == $alt[0])
      {
        $ref = substr($ref, 1);
        $alt = substr($alt, 1);
        $pos = $pos + 1;
        if ($ref == "") $ref = "-";
        if ($alt == "") $alt = "-";
      }
  
      // Split Protein / Transcript
      if (isset($variants[$i]->anno))
      {
        $anno = $variants[$i]->anno;
        for ($j = 0; $j < count($anno); $j++)
        {
          if (!isset($anno[$j]->Transcript) || empty($anno[$j]->Transcript) || $anno[$j]->Transcript == "")
            break;
          $curTrans = $anno[$j]->Transcript;
          $curProt = $anno[$j]->Protein;
          $curRef = '';
          $curAlt = '';
          $curPos = 0;
      
          if ($curProt[0] == 'p')
            $curProt = substr($curProt, 2);
      
          if (preg_match('/^(?<ref>\D+)(?<pos>\d+)(?<alt>\D*)/', $curProt, $matches) == true)
          {
            $curRef = $matches['ref'];
            $curAlt = $matches['alt'];
            $curPos = $matches['pos'];          
            
            if (array_key_exists($curAlt, $AminoAcids))
              $curAlt = $AminoAcids[$curAlt];
  
            if ((strlen($curAlt) == 1) && (strspn($curAlt, 'ACDEFGHIKLMNPQRSTVWY') == 1))
              $anno[$j]->ProtAlt = $curAlt;
            else if (substr($curAlt, 0, 3) == 'del')
              $anno[$j]->ProtAlt = 'del';
            else if ($curAlt == '*' || $curAlt == 'fs')
              $anno[$j]->ProtAlt = '';
            else
              $anno[$j]->ProtAlt = '';
          }
          $anno[$j]->ProtPos = intval($curPos);
          $nranno++;
        }
      }
    
      // Prepare input for SQL query
      for ($j = 0; $j < max(1, $nranno); $j++)
      {
        if ($i+$j > 0) $sql = $sql . "\n    UNION ALL \n";
        $sql = $sql . '    SELECT "' . $chr . '" AS chr, ' . $pos . ' AS pos, "' . $ref . '" AS ref, "' . $alt . '" AS alt';
        if ($nranno > 0)
          $sql = $sql . ', "' . $anno[$j]->Gene . '" AS Gene, "' . $anno[$j]->Transcript . '" AS transcript, ' . $anno[$j]->ProtPos . ' AS protpos, "' . $anno[$j]->ProtAlt . '" AS protalt, "' . $anno[$j]->varType . '" AS varType, "' . $anno[$j]->Impact . '" AS Impact, "' . $anno[$j]->regionType . '" AS regionType, ' . $anno[$j]->Exon . ' AS Exon, "' . $anno[$j]->AAchange . '" AS AAchange, "' . $anno[$j]->Codon . '" AS Codon, "' . $anno[$j]->Protein . '" AS Protein';
        else
          $sql = $sql . ", null AS Gene, null AS transcript, null AS protpos, null AS protalt, null AS varType, null AS Impact, null AS regionType, null AS Exon, null AS AAchange, null AS Codon, null AS Protein";
      }
  
      if ($nranno > 0)
        $transset = true;
    }
  }
  
  // SQL query
  $sql = "SELECT va.chr, va.pos, va.ref, va.alt, " .
   ($transset ? "va.Gene, va.AAchange, va.Transcripts, va.varTypes, va.Impacts, va.regionTypes, va.Exons, va.Codons, va.Proteins, va.Provean_scores, va.Provean_max, " : "") . 
   (!$transset ? "x.genename AS Gene, replace(x.Ensembl_transcriptid, ';', ',') AS Transcripts, " : "") . "
    a.dbSNP, a.Version AS dbSNP_Version, a.Precious AS dbSNP_PM, 
    G1000_AF, G1000_eur, d.ESP6500, ExAC_all, ExAC_eur, CLNSIG AS ClinVar_Significance, CLNDN AS ClinVar_Disease, CLNREVSTAT AS Clinvar_Status, " .
    ($use_cosmic ? "CosmicID, CosmicSites, CosmicSNP, NrHaemato AS Cosmic_NrHaemato, NrSamples AS Cosmic_NrSamples, NrSites AS Cosmic_NrSites, " : "'[forbidden]' AS CosmicID, '[forbidden]' AS CosmicSites, '[forbidden]' AS CosmicSNP, null AS Cosmic_NrHaemato, null AS Cosmic_NrSamples, null AS Cosmic_NrSites, ") .
   (true ? "ucsc.transcriptEnsemble AS CanonicalTranscript, replace(x.PROVEAN_score, ';', ',') AS Provean_scores, replace(x.PROVEAN_converted_rankscore, ';', ',') AS Provean_rank, replace(x.PROVEAN_pred, ';', ',') AS Provean_pred, replace(x.SIFT_score, ';', ',') AS SIFT_scores, replace(x.SIFT_converted_rankscore, ';', ',') AS SIFT_rank, replace(x.SIFT_pred, ';', ',') AS SIFT_pred, replace(x.LRT_score, ';', ',') AS LRT_scores, replace(x.LRT_converted_rankscore, ';', ',') AS LRT_rank, replace(x.LRT_pred, ';', ',') AS LRT_pred, replace(x.FATHMM_score, ';', ',') AS FATHMM_scores, replace(x.FATHMM_converted_rankscore, ';', ',') AS FATHMM_rank, replace(x.FATHMM_pred, ';', ',') AS FATHMM_pred, " : "") .
   (true ? "replace(x.MutationTaster_score, ';', ',') AS MutationTaster_scores, replace(x.MutationTaster_converted_rankscore, ';', ',') AS MutationTaster_rank, replace(x.MutationTaster_pred, ';', ',') AS MutationTaster_pred, replace(x.Polyphen2_HDIV_score, ';', ',') AS Polyphen2_HDIV_scores, replace(x.Polyphen2_HDIV_rankscore, ';', ',') AS Polyphen2_HDIV_rank, replace(x.Polyphen2_HDIV_pred, ';', ',') AS Polyphen2_HDIV_pred, replace(x.Polyphen2_HVAR_score, ';', ',') AS Polyphen2_HVAR_scores, replace(x.Polyphen2_HVAR_rankscore, ';', ',') AS Polyphen2_HVAR_rank, replace(x.Polyphen2_HVAR_pred, ';', ',') AS Polyphen2_HVAR_pred, " : "") .
   (true ? "replace(x.MutationAssessor_score, ';', ',') AS MutationAssessor_scores, replace(x.MutationAssessor_score_rankscore, ';', ',') AS MutationAssessor_rank, replace(x.MutationAssessor_pred, ';', ',') AS MutationAssessor_pred, replace(x.MetaSVM_score, ';', ',') AS MetaSVM_scores, replace(x.MetaSVM_rankscore, ';', ',') AS MetaSVM_rank, replace(x.MetaSVM_pred, ';', ',') AS MetaSVM_pred, replace(x.MetaLR_score, ';', ',') AS MetaLR_scores, replace(x.MetaLR_rankscore, ';', ',') AS MetaLR_rank, replace(x.MetaLR_pred, ';', ',') AS MetaLR_pred, " : "") . "
    (SELECT min(MutationID) FROM tgt_KnownMutations AS t WHERE t.version=10 AND t.chr=va.chr AND va.pos BETWEEN t.start AND t.end) AS inHotspot
    FROM
    (SELECT v.chr, v.pos, v.ref, v.alt, v.Gene, v.AAchange, group_concat(v.transcript) as Transcripts, group_concat(IFNULL(v.varType, '-')) AS varTypes, group_concat(IFNULL(v.Impact, '-')) AS Impacts, group_concat(IFNULL(v.regionType, '-')) AS regionTypes, group_concat(IFNULL(v.Exon, '-')) AS Exons, group_concat(IFNULL(v.Codon, '-')) AS Codons, group_concat(IFNULL(v.Protein, '-')) AS Proteins,
      group_concat(IFNULL(round(CASE protalt
        WHEN 'A' THEN p.A  WHEN 'C' THEN p.C  WHEN 'D' THEN p.D  WHEN 'E' THEN p.E  WHEN 'F' THEN p.F  WHEN 'G' THEN p.G  WHEN 'H' THEN p.H
        WHEN 'I' THEN p.I  WHEN 'K' THEN p.K  WHEN 'L' THEN p.L  WHEN 'M' THEN p.M  WHEN 'N' THEN p.N  WHEN 'P' THEN p.P  WHEN 'Q' THEN p.Q
        WHEN 'R' THEN p.R  WHEN 'S' THEN p.S  WHEN 'T' THEN p.T  WHEN 'V' THEN p.V  WHEN 'W' THEN p.W  WHEN 'Y' THEN p.Y  WHEN 'del' THEN p.del
      END, 2), '-')) AS Provean_scores,
      min(round(CASE protalt
        WHEN 'A' THEN p.A  WHEN 'C' THEN p.C  WHEN 'D' THEN p.D  WHEN 'E' THEN p.E  WHEN 'F' THEN p.F  WHEN 'G' THEN p.G  WHEN 'H' THEN p.H
        WHEN 'I' THEN p.I  WHEN 'K' THEN p.K  WHEN 'L' THEN p.L  WHEN 'M' THEN p.M  WHEN 'N' THEN p.N  WHEN 'P' THEN p.P  WHEN 'Q' THEN p.Q
        WHEN 'R' THEN p.R  WHEN 'S' THEN p.S  WHEN 'T' THEN p.T  WHEN 'V' THEN p.V  WHEN 'W' THEN p.W  WHEN 'Y' THEN p.Y  WHEN 'del' THEN p.del
      END, 2)) AS Provean_max
      FROM (
  $sql) AS v
      LEFT JOIN db_Provean AS p ON v.transcript=p.transcript_id AND v.protpos=p.position
    GROUP BY chr, pos, ref, alt) AS va
    LEFT JOIN db_dbSNP    AS a ON va.chr=a.chr AND va.pos=a.pos AND va.ref=a.ref AND va.alt=a.alt
    LEFT JOIN db_G1000    AS c ON va.chr=c.chr AND va.pos=c.pos AND va.ref=c.ref AND va.alt=c.alt
    LEFT JOIN db_ESP6500  AS d ON va.chr=d.chr AND va.pos=d.pos AND va.ref=d.ref AND va.alt=d.alt
    LEFT JOIN db_ExAC     AS e ON va.chr=e.chr AND va.pos=e.pos AND va.ref=e.ref AND va.alt=e.alt
    LEFT JOIN db_clinvar  AS f ON va.chr=f.chr AND va.pos=f.pos AND va.ref=f.ref AND va.alt=f.alt
    LEFT JOIN db_Cosmic   AS g ON va.chr=g.chr AND va.pos=g.pos AND va.ref=g.ref AND va.alt=g.alt \n" .
  (true ? "  LEFT JOIN db_dbNSFP    AS x ON va.chr=x.chr AND va.pos=x.pos AND va.ref=x.ref AND va.alt=x.alt  
    LEFT JOIN db_CanonicalTranscriptsUCSC AS ucsc ON x.genename=ucsc.GeneSymbol \n" : "") .
   "GROUP BY va.chr, va.pos, va.ref, va.alt";
  
  // DEBUG
  $f = fopen("../PostSQL.log", "a");
  fwrite($f, $sql . "\n\n");
  fclose($f);
  
  // Prepare SQL query
  try
  {
    $getVariants = $db->prepare($sql);
  }
  catch(PDOException $e)
  {
    echo 'Problem with database query';
  }
  
  // Execute SQL query
  try
  {
    $getVariants->execute();
    if($getVariants->rowCount() >= 1)
      $rows = $getVariants->fetchAll(PDO::FETCH_ASSOC);
  }
  catch(PDOException $e)
  {
    echo 'Problem with getting variants';
    $rows = None;
  }

  // Output as JSON
  if(isset($rows))
  {
    header('Content-Type: application/json');
    print(json_encode($rows));
  }
}
else
{
  header('Content-Type: text/html');
  $test = '[{"chr":"2","pos":25457243,"ref":"G","alt":"T"},{"chr":"2","pos":25523096,"ref":"T","alt":"G"}]';  
  $test2 = "2\t25457243\tG\tT\n2\t25523096\tT\tG";  
//  print ("<script type='text/javascript'>test='" + $test + "';</script>"); 
//  print('<script type="text/javascript">function do_post(content) {xhttp.open("POST", "annopost.php", true); xhttp.setRequestHandler("Content-type", "application/json"); xhttp.send(content);}</script>');
//  print('This page must be used with POST data.<input type="button" onClick="do_post(test)" value="Test">');
  
  print ('<!DOCTYPE HTML>
  <html>
  <head>
    <meta charset="utf-8">
    <title>VariantAnnotator - GET API</title>
    <script type="text/javascript">
      function convertToJSON()
      {
        csv = document.getElementById("csvin").value;
        csv = csv.split("\n");

        var output = new Array(csv.length);
        for (var i = 0; i < csv.length; i++)
        {
          line = csv[i].split("\t");
          output[i] = {"chr":line[0], "pos":parseInt(line[1]), "ref":line[2], "alt":line[3]};
        }
        document.getElementById("in").value = JSON.stringify(output);
      }
    </script>
  </head>
  <body>
    <h1>VariantAnnotator - POST API</h1>
    <p><b>Please ensure that you upload only data for which you are entitled to do so!</b></p>
    <p>Enter your variants in JSON format containing {chr, pos, ref, alt} into the text field (or send it via HTTP POST Request):</p>
    <form method="post" action="">
      <textarea id="in" name="in" rows="16", cols="80">' . $test . '</textarea><br>
      <input type="submit" value="Send">
    </form>
    <p>Or enter it in tab separated format (chr, pos, ref, alt) below and click on Convert, then Send.</p>
    <form action="">
      <textarea id="csvin" name="csvin" rows="8", cols="80">' . $test2 . '</textarea><br>
      <input type="button" value="Convert" onClick="convertToJSON()">
    </form>    
  </body></html>');
}

?>
