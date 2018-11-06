SELECT tgt_KnownMutations.MutationID, tgt_KnownMutations.RangeID, MutName, RngName, MutText, tgt_KnownMutations.version, tgt_KnownMutations.chr, tgt_KnownMutations.start AS rngStart, tgt_KnownMutations.end AS rngEnd,
  (tgt_KnownMutations.end-tgt_KnownMutations.start+1) AS rngWidth,
  tgtStart, tgtEnd, tgtEnd-tgtStart+1 AS tgtWidth, tgt_Regions.cvgPercent,

  (SELECT COUNT(*) FROM
    (SELECT chr, pos, ref, alt, region_type, mut_type, CLNSIG, CLNSIG_txt, SUM(IF(Primary_Site="haematopoietic_and_lymphoid_tissue", 1, 0)) AS NrHaemato FROM
      (SELECT v.chr, v.pos, v.ref, v.alt, region_type, mut_type, CLNSIG, CLNSIG_txt, ID_Sample, Primary_Site FROM
        (SELECT * FROM Variants WHERE SampleID=:sid) AS v
        LEFT JOIN clinvar ON v.chr=clinvar.chr AND v.pos=clinvar.pos AND v.ref=clinvar.ref AND v.alt=clinvar.alt
        LEFT JOIN CosmicCoding ON v.chr=CosmicCoding.chr AND v.pos=CosmicCoding.pos AND v.ref=CosmicCoding.ref AND v.alt LIKE CosmicCoding.alt
        LEFT JOIN CosmicDetails ON CosmicCoding.Mutation_ID=CosmicDetails.Mutation_ID
      GROUP BY v.chr, v.pos, v.ref, v.alt, CosmicDetails.ID_Sample) AS sc
    GROUP BY chr, pos, ref, alt) AS vc
    WHERE vc.chr=tgt_KnownMutations.chr AND vc.pos BETWEEN tgtStart AND tgtEnd
    AND (vc.mut_type=tgt_KnownMutations.mut_type OR tgt_KnownMutations.mut_type IS NULL)
    AND ((mut_type="duplication" OR mut_type="deletion") OR (CLNSIG LIKE "%4%" OR CLNSIG LIKE "%5%") OR (NrHaemato>1 AND (CLNSIG_txt IS NULL OR CLNSIG_txt NOT LIKE "%benign")))
    AND (region_type="exonic" OR region_type="structural" OR region_type="protein_coding") AND (mut_type!="synonymous SNV")
  ) AS NrMutations,

  (SELECT COUNT(DISTINCT chr, pos, ref, alt) FROM Variants
    WHERE SampleID=:sid
    AND Variants.chr=tgt_KnownMutations.chr AND Variants.pos BETWEEN tgtStart AND tgtEnd
    AND (Variants.mut_type=tgt_KnownMutations.mut_type OR tgt_KnownMutations.mut_type IS NULL)
    AND (region_type="exonic" OR region_type="structural" OR region_type="protein_coding") AND (mut_type!="synonymous SNV")
  ) AS NrAllMutations,

  (SELECT tgtWidth-NrGoodCovered2) As NrBadCovered

FROM tgt_KnownMutations LEFT JOIN tgt_Regions
ON tgt_Regions.design=:design AND tgt_KnownMutations.chr=tgt_Regions.chr
  AND (GREATEST(tgt_KnownMutations.start, tgt_Regions.start) BETWEEN tgt_KnownMutations.start AND tgt_KnownMutations.end)
  AND (LEAST(tgt_KnownMutations.end, tgt_Regions.end) BETWEEN tgt_KnownMutations.start AND tgt_KnownMutations.end)
LEFT JOIN Coverage2 ON Coverage2.SampleID=2 AND Coverage2.MutationID=tgt_KnownMutations.MutationID AND Coverage2.RangeID=tgt_KnownMutations.RangeID
  AND Coverage2.version=tgt_KnownMutations.version AND Coverage2.design=tgt_Regions.design AND Coverage2.rgStart=tgt_Regions.start AND Coverage2.rgEnd=tgt_Regions.end
WHERE tgt_KnownMutations.version=:version AND tgt_KnownMutations.MutationID=:mid
ORDER BY MutationID, rngStart, tgtStart
