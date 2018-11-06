SELECT va.chr, va.pos, a.dbSNP, va.Transcripts, va.Exons, va.Codons, va.Proteins, va.NRalt, va.Cvg, va.NRalt/va.Cvg as Freq, G1000_AF as 1000G, va.Provean_Scores as Provean, CLNSIG as ClinVar, NrHaemato as Cosmic, CosmicSNP, va.Category FROM
  (SELECT v.SampleID, v.SampleName, v.chr, v.pos, v.ref, v.alt, a.Gene, group_concat(a.Transcript) AS Transcripts, group_concat(a.varType) AS varTypes, group_concat(a.regionType) AS regionTypes, group_concat(a.Exon) AS Exons, group_concat(a.Codon) AS Codons, group_concat(a.Protein) AS Proteins, group_concat(a.Impact) AS Impacts, group_concat(a.Provean_Score) AS Provean_Scores, v.Cvg, v.NRref, v.NRalt, v.BQref, v.BQalt, v.NRref_fwd, v.NRalt_fwd, v.NRref_rev, v.NRalt_rev, v.Cvg_fwd, v.Cvg_rev, v.StrandBias, v.Category, v.appreci8, v.Comment FROM
    (SELECT * FROM Variants WHERE SampleID=:sid) AS v
    LEFT JOIN VariantAnno AS a ON v.chr=a.chr AND v.pos=a.pos AND v.ref=a.ref AND v.alt=a.alt
  GROUP BY v.SampleID, v.chr, v.pos, v.ref, v.alt) AS va
  LEFT JOIN db_dbSNP    AS a ON va.chr=a.chr AND va.pos=a.pos AND va.ref=a.ref AND va.alt=a.alt
  LEFT JOIN db_G1000    AS b ON va.chr=b.chr AND va.pos=b.pos AND va.ref=b.ref AND va.alt=b.alt
  LEFT JOIN db_ESP6500  AS c ON va.chr=c.chr AND va.pos=c.pos AND va.ref=c.ref AND va.alt=c.alt
  LEFT JOIN db_ExAC     AS d ON va.chr=d.chr AND va.pos=d.pos AND va.ref=d.ref AND va.alt=d.alt
  LEFT JOIN db_clinvar  AS e ON va.chr=e.chr AND va.pos=e.pos AND va.ref=e.ref AND va.alt=e.alt
  LEFT JOIN db_Cosmic   AS f ON va.chr=f.chr AND va.pos=f.pos AND va.ref=f.ref AND va.alt=f.alt
  LEFT JOIN tgt_KnownMutations AS t ON t.version=:version AND va.chr=t.chr AND va.pos BETWEEN t.start AND t.end AND (va.varTypes like t.mut_type OR t.mut_type IS NULL)
  WHERE MutationID=:mid AND (regionTypes="structural" OR appreci8=1)
GROUP BY va.chr, va.pos, va.ref, va.alt
