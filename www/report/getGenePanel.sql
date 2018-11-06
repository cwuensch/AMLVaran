SELECT gene_name AS gene_name, chr, count(*) AS NrRegions, min(reg.start) AS start, max(reg.end) AS end, sum(reg.width) AS width, sum(regMutations) AS NrMutations, sum(regBadCovered) AS NrBadCovered
FROM
  (SELECT gene_name, chr, start, end, width, 
  (SELECT COUNT(*) FROM Variants
    WHERE SampleID=:sid AND Variants.chr=tgt_Regions.chr AND Variants.pos BETWEEN tgt_Regions.start AND tgt_Regions.end) AS regMutations,
  (SELECT COUNT(*) FROM Coverage WHERE SampleID=:sid AND Coverage.chr=tgt_Regions.chr AND Coverage.pos BETWEEN tgt_Regions.start AND tgt_Regions.end AND cvg < 20) AS regBadCovered
  FROM tgt_Regions
  WHERE design=:design AND gene_name IS NOT NULL) AS reg
GROUP BY gene_name
ORDER BY gene_name