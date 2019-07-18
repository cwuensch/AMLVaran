SELECT MutationID, MutName as name, MutText, MutRef, COUNT(*) as NrRegions, SUM(width2) AS width, SUM(Overlap2) AS Overlap, SUM(NrMutations2) AS NrMutations, SUM(NrAllMutations2) AS NrAllMutations, SUM(NrGoodCovered2) AS NrGoodCovered, IFNULL(MAX(((Overlap2-NrGoodCovered2)/Overlap2)>cvgPercent), 1) AS isBadCovered FROM

  (SELECT tgt_KnownMutations.MutationID, MutName, tgt_KnownMutations.version, MutText, MutRef, tgt_KnownMutations.chr, tgt_KnownMutations.start, tgt_KnownMutations.end,
  (tgt_KnownMutations.end-tgt_KnownMutations.start+1) AS width2,
  (LEAST(tgt_KnownMutations.end, tgt_Regions.end)-GREATEST(tgt_KnownMutations.start, tgt_Regions.start)+1) AS Overlap2, tgt_Regions.cvgPercent,

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

  (SELECT COUNT(*) FROM Coverage
    WHERE (SampleID=:sid AND Coverage.chr=tgt_KnownMutations.chr AND pos BETWEEN tgt_KnownMutations.start AND tgt_KnownMutations.end AND cvg >= tgt_Regions.cvgThresh)
  ) AS NrGoodCovered2

  FROM tgt_KnownMutations LEFT JOIN tgt_Regions ON tgt_KnownMutations.version=:version AND tgt_Regions.design=:design AND tgt_KnownMutations.chr=tgt_Regions.chr
  AND (GREATEST(tgt_KnownMutations.start, tgt_Regions.start) BETWEEN tgt_KnownMutations.start AND tgt_KnownMutations.end)
  AND (LEAST(tgt_KnownMutations.end, tgt_Regions.end) BETWEEN tgt_KnownMutations.start AND tgt_KnownMutations.end)
  GROUP BY MutationID, tgt_KnownMutations.chr, tgt_KnownMutations.start, tgt_KnownMutations.end) as r

WHERE version=:version
GROUP BY MutationID
ORDER BY NrMutations DESC, isBadCovered, MutationID