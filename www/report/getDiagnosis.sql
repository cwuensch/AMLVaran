SELECT Prognosis, Color, Source, URL, GROUP_CONCAT(CONCAT(MutName, "=", mutated)) AS Rule, MIN((LEAST(NrMutations,mutated)>0) OR (GREATEST(NrMutations, mutated)=0 AND isBadCovered=0)) AS Fulfilled FROM

  (SELECT RuleID, Prognosis, Color, Source, URL, MutName, mutated, SUM(Overlap2) AS Overlap,  MAX((NrBadCovered2/Overlap2)>cvgPercent) AS isBadCovered, SUM(NrMutations2) AS NrMutations FROM

    (SELECT tgt_KnownMutations.MutationID, rul_Diagnosis.RuleID, Prognosis, Color, Source, rul_Diagnosis.URL, MutName, mutated,
    (LEAST(tgt_KnownMutations.end, tgt_Regions.end)-GREATEST(tgt_KnownMutations.start, tgt_Regions.start)+1) AS Overlap2, tgt_Regions.cvgPercent,

    (SELECT COUNT(DISTINCT v.chr, v.pos, v.ref, v.alt) FROM
      (SELECT * FROM Variants WHERE SampleID=:sid) AS v
      INNER JOIN VariantAnno AS a ON v.chr=a.chr AND v.pos=a.pos AND v.ref=a.ref AND v.alt=a.alt      
      WHERE v.chr=tgt_KnownMutations.chr AND v.pos BETWEEN tgt_KnownMutations.start AND tgt_KnownMutations.end
      AND (varType=tgt_KnownMutations.mut_type OR tgt_KnownMutations.mut_type IS NULL)
      AND (appreci8=1 OR regionType="structural")
    ) AS NrMutations2,
    
    COUNT(IF(cvg<tgt_Regions.cvgThresh,1,NULL)) AS NrBadCovered2    

    FROM rul_Diagnosis
    LEFT JOIN rul_Mutations ON rul_Diagnosis.RuleID=rul_Mutations.RuleID
    LEFT JOIN tgt_KnownMutations ON rul_Mutations.MutationID=tgt_KnownMutations.MutationID
    LEFT JOIN tgt_Regions ON tgt_KnownMutations.version=:version AND tgt_Regions.design=:design AND tgt_KnownMutations.chr=tgt_Regions.chr
      AND (GREATEST(tgt_KnownMutations.start, tgt_Regions.start) BETWEEN tgt_KnownMutations.start AND tgt_KnownMutations.end)
      AND (LEAST(tgt_KnownMutations.end, tgt_Regions.end) BETWEEN tgt_KnownMutations.start AND tgt_KnownMutations.end)
    LEFT JOIN Coverage ON (Coverage.SampleID=:sid AND Coverage.chr=tgt_KnownMutations.chr AND Coverage.pos BETWEEN tgt_Regions.start AND tgt_Regions.end AND Coverage.cvg < tgt_Regions.cvgThresh)
    WHERE rul_Diagnosis.version=:version
    GROUP BY RuleID, MutationID, tgt_Regions.start, tgt_Regions.end) as t

  GROUP BY RuleID, MutationID) as r

GROUP BY RuleID
HAVING Fulfilled=TRUE
