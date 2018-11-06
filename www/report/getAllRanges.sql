SELECT MutationID, RangeID, MutName, RngName, MutText, tgt_KnownMutations.version, tgt_KnownMutations.chr, tgt_KnownMutations.start AS rngStart, tgt_KnownMutations.end AS rngEnd,
  (tgt_KnownMutations.end-tgt_KnownMutations.start+1) AS rngWidth,
  GREATEST(tgt_KnownMutations.start, tgt_Regions.start) AS tgtStart, LEAST(tgt_KnownMutations.end, tgt_Regions.end) AS tgtEnd,
  LEAST(tgt_KnownMutations.end, tgt_Regions.end)-GREATEST(tgt_KnownMutations.start, tgt_Regions.start)+1 AS tgtWidth, tgt_Regions.cvgPercent,

  (SELECT COUNT(DISTINCT v.chr, v.pos, v.ref, v.alt) FROM
    (SELECT * FROM Variants WHERE SampleID=:sid) AS v
    INNER JOIN VariantAnno AS a ON v.chr=a.chr AND v.pos=a.pos AND v.ref=a.ref AND v.alt=a.alt    
    WHERE v.chr=tgt_KnownMutations.chr AND v.pos BETWEEN tgt_KnownMutations.start AND tgt_KnownMutations.end
    AND (varType=tgt_KnownMutations.mut_type OR tgt_KnownMutations.mut_type IS NULL)
    AND (appreci8=1 OR regionType="structural")
  ) AS NrMutations2,
    
  (SELECT COUNT(DISTINCT v.chr, v.pos, v.ref, v.alt) FROM
    (SELECT * FROM Variants WHERE SampleID=:sid) AS v
    INNER JOIN VariantAnno AS a ON v.chr=a.chr AND v.pos=a.pos AND v.ref=a.ref AND v.alt=a.alt
    WHERE v.chr=tgt_KnownMutations.chr AND v.pos BETWEEN tgt_KnownMutations.start AND tgt_KnownMutations.end
    AND (varType=tgt_KnownMutations.mut_type OR tgt_KnownMutations.mut_type IS NULL)
    AND (regionType="exonic" OR regionType="structural" OR regionType="protein_coding") AND (varType!="synonymous SNV")
  ) AS NrAllMutations2,

  (SELECT tgtWidth - COUNT(*) FROM Coverage
    WHERE (SampleID=:sid AND Coverage.chr=tgt_KnownMutations.chr AND pos BETWEEN tgtStart AND tgtEnd AND cvg >= tgt_Regions.cvgThresh)
  ) AS NrBadCovered

FROM tgt_KnownMutations LEFT JOIN tgt_Regions
ON tgt_Regions.design=:design AND tgt_KnownMutations.chr=tgt_Regions.chr
  AND (GREATEST(tgt_KnownMutations.start, tgt_Regions.start) BETWEEN tgt_KnownMutations.start AND tgt_KnownMutations.end)
  AND (LEAST(tgt_KnownMutations.end, tgt_Regions.end) BETWEEN tgt_KnownMutations.start AND tgt_KnownMutations.end)
WHERE tgt_KnownMutations.version=:version
ORDER BY MutationID, rngStart, tgtStart
