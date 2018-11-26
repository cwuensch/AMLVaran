#!/usr/bin/python

# Annotation and basic filtering of a vcf file
#
# Usage: python Pipeline.py <SampleID> <INPUT.vcf>
#
# (C) 2017 Christian Wuensch

import csv
import MySQLdb
import sys
import subprocess
import os
import collections
from collections import namedtuple
import re
import scipy.stats
csv.field_size_limit(min(2147483648, sys.maxsize))

AminoAcids = {'Arg': 'R', 'His': 'H', 'Lys': 'K', 'Asp': 'D', 'Glu': 'E', 'Ser': 'S', 'Thr': 'T',
              'Asn': 'N', 'Gln': 'Q', 'Cys': 'C', 'Sec': 'U', 'Gly': 'G', 'Pro': 'P', 'Ala': 'A',
              'Val': 'V', 'Ile': 'I', 'Leu': 'L', 'Met': 'M', 'Phe': 'F', 'Trp': 'W', 'Tyr': 'Y'}
Variant    = collections.namedtuple('Variant', ('chr', 'pos', 'ref', 'alt', 'Transcript', 'Gene', 'varType', 'regionType', 'Exon', 'AAchange', 'Codon', 'Protein', 'Impact', 'Genotypes', 'Callers', 'NrCallers'))
Variants   = list()
Quality    = collections.namedtuple('Quality', ('chr', 'pos', 'ref', 'alt', 'Coverage', 'NRref', 'NRalt', 'BQref', 'BQalt', 'NRref_fwd', 'NRalt_fwd', 'NRref_rev', 'NRalt_rev', 'Cvg_fwd', 'Cvg_rev', 'StrandBias'))
Qualities  = list()
Proveans   = list()
SIFTs      = list()
return_code = 0


print "Annotation and basic filtering of a vcf file"
print "(C) 2017 Christian Wuensch"
print ""

# Get command line arguments
if len(sys.argv) > 2:
    InputFile = sys.argv[2]
    SampleID = sys.argv[1]
else:
    print "Usage: python " + sys.argv[0] + " <SampleID> <INPUT.vcf> <REFERENCE.fasta>"
    sys.exit(-1)

print "Annotation of SampleID " + SampleID + " from file " + InputFile + "...\n"
num_lines1 = sum(1 for line in open(InputFile[:-11] + ".vcf"))
num_lines2 = sum(1 for line in open(InputFile))
protocol = "Sample" + SampleID + "\t" + str(num_lines1) + "\t" + str(num_lines2-1)


# Database connection
con = MySQLdb.connect('amlvaran', 'amlvaran', '', 'amlvaran')
cur = con.cursor()
con.autocommit(False)


# ------------------------------------------------------------
#   [1] Functional annotation with SNPeff
# ------------------------------------------------------------
print ("STEP 1: Annotation")

# Launch the SNPeff tool
return_code = subprocess.call("snpeff -Xmx4g -XX:-UseGCOverheadLimit -v GRCh37.75 -hgvs1LetterAa -noStats -no-utr -no-downstream -no-upstream -no-intron -no-intergenic " + InputFile + " > ./Variants_SNPeff.vcf", shell=True)
if (return_code != 0):  sys.exit(11)

# Open input file (SNPeff annotated raw variants)
f = open("./Variants_SNPeff.vcf", 'r')
csvReader = csv.reader(f, delimiter='\t', quotechar='"')
for row in csvReader:
    if (row[0][0] == '#'): continue
    chrom      = row[0]
    pos        = int(row[1])
    ref        = row[3]
    alt        = row[4]
    Gene       = list()
    Transcript = list()
    varType    = list()
    regionType = list()
    Exon       = list()
    Codon      = list()
    Protein    = list()
    Impact     = list()
    AAchange   = list()
    info       = row[7]

    callers    = None
    nrcallers  = 0
    p = info.find('callers=')
    if (p >= 0):
        callers = info[p+8:].split(';')[0]
        nrcallers = callers.count('|') + 1

    genotypes  = None
    p = info.find('genotypes=')
    if (p >= 0):  genotypes = info[p+10:].split(';')[0]
    
    DP = 0
    p = info.find('DP=')
    if (p >= 0): DP = int(info[p+3:].split(';')[0])
    
    AF = 0.0
    p = info.find('AF=')
    if (p >= 0): AF = float(info[p+3:].split(';')[0].split(',')[0])
    
    AC = None
    if (DP is not None and AF is not None): AC = DP*AF

    # Parse eff field
    if (info.find('ANN=') >= 0):
        # ANN = Allele | Annotation | Annotation_Impact | Gene_Name | Gene_ID | Feature_Type | Feature_ID | Transcript_BioType | Rank | HGVS.c | HGVS.p | cDNA.pos / cDNA.length | CDS.pos / CDS.length | AA.pos / AA.length | Distance | ERRORS / WARNINGS / INFO
        # ANN=A|missense_variant|MODERATE|CCT8L2|ENSG00000198445|transcript|ENST00000359963|protein_coding|1/1|c.1183G>T|p.Gly395Cys|1443/2034|1183/1674|395/557||,
        #     A|downstream_gene_variant|MODIFIER|FABP5P11|ENSG00000240122|transcript|ENST00000430910|processed_pseudogene||n.*397G>T|||||3721|
        p = info.find('ANN=')
        annos = info[p+4:]
        annos = annos.split(','+alt)
        for anno in annos:
            values = anno.split("|")
            if (len(values) >= 11):
                if (("synonymous_variant" not in values[1]) and ("non_coding_transcript_exon_variant" not in values[1]) and (values[1] != "sequence_feature") and (values[1] != "splice_region_variant") and (values[1] != "TF_binding_site_variant")):
                    AnnoSet = False
                    for i in range(0, len(Transcript)):
                        if (Transcript[i] == values[6]):
                            AnnoSet = True
                            break
    
                    prot = values[10]
                    for j,k in AminoAcids.items():  prot = prot.replace(j, k)
    
                    if (AnnoSet):
                        varType[i] = varType[i] + " & " + values[1]
                        if ((values[2] == "HIGH") or (values[2] == "MODERATE" and Impact[i] != "HIGH")):  Impact[i] = values[2]
                        if (Protein[i] == ""):  Protein[i] = prot
                    else:
                        Transcript.append(values[6])
                        Gene.append      (values[3])
                        varType.append   (values[1])
                        Impact.append    (values[2])
                        regionType.append(values[7])
                        Codon.append     (values[9])
                        Protein.append(prot)
                        Exon.append(int(values[8].split('/')[0]))
                        AAchange.append('')

    elif (info.find('EFF=') >= 0):
        # EFF = Effect ( Effect_Impact | Functional_Class | Codon_Change | Amino_Acid_Change| Amino_Acid_Length | Gene_Name | Transcript_BioType | Gene_Coding | Transcript_ID | Exon_Rank  | Genotype_Number [ | ERRORS | WARNINGS ] )
        # EFF=missense_variant+splice_region_variant(MODERATE|MISSENSE|Ggc/Cgc|p.Gly198Arg/c.592G>C|1070|MIB2|protein_coding|CODING|ENST00000505820|3|C),
        p = info.find('EFF=')
        annos = info[p+4:]
        annos = annos.split(',')
        for anno in annos:
            values = anno.split("|")
            if (len(values) >= 10):
                if (("synonymous_variant" not in values[0]) and ("non_coding_transcript_exon_variant" not in values[0]) and (values[0] != "sequence_feature") and (values[0] != "splice_region_variant") and (values[0] != "TF_binding_site_variant")):
                    AnnoSet = False
                    for i in range(0, len(Transcript)):
                        if (Transcript[i] == values[8]):
                            AnnoSet = True
                            break;
    
                    prot = values[3].split('/')[0]
                    for j,k in AminoAcids.items():  prot = prot.replace(j, k)
    
                    if (AnnoSet):
                        varType[i] = varType[i] + " & " + values[0].split('(')[0]
                        impact = values[0].split('(')[1]
                        if ((impact == "HIGH") or (impact == "MODERATE" and Impact[i] != "HIGH")):  Impact[i] = impact
                        if (Protein[i] == ""):  Protein[i] = prot
                    else:
                        Transcript.append(values[8])
                        Gene.append      (values[5])
                        varType.append   (values[0].split('(')[0])
                        Impact.append    (values[1])
                        regionType.append(values[6])
                        if (values[3].find('/') >= 0):
                            Codon.append (values[3].split('/')[1])
                        else:
                            Codon.append ('')
                        Protein.append(prot)
                        Exon.append(int(values[9]))
                        AAchange.append(values[2])
    
    else: continue

    # Adjust variant format
    while (ref[0] == alt[0]):
        ref = ref[1:]
        alt = alt[1:]
        pos = pos + 1
        if (ref == ''):
            ref = '-'
        elif (alt == ''):
            alt = '-'

    if (len(Gene) > 0):
        Variants.append  (Variant(chrom, pos, ref, alt, Transcript, Gene, varType, regionType, Exon, AAchange, Codon, Protein, Impact, genotypes, callers, nrcallers))
        Qualities.append (Quality(chrom, pos, ref, alt, DP, AC, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0))

f.close()
print ("  --> %d variants processed with SNPeff.\n" % len(Variants))
protocol = protocol + "\t" + str(len(Variants))


if os.path.isfile("./Sample.bam"):
    # ------------------------------------------------------------
    #   [2] Calculate read quality values with BamReadCount
    # ------------------------------------------------------------
    print ("STEP 2: Quality analysis")
    Qualitites.clear()

    # Output chr, pos in BED format
    tempFile = open('Variant_pos.csv', 'w')
    for var in Variants:
        hpos = var.pos
        if (var.ref == "-"): hpos = hpos-1
        tempFile.write ("%s\t%s\t%s\n" % (var.chr, hpos, var.pos))
    tempFile.close()
    
    # Launch BamReadCount tool
    return_code = subprocess.call("bam_readcount --reference-fasta " + os.environ['GENOME'] + " --site-list ./Variant_pos.csv ./Sample.bam > ./Variant_quals.csv 2>/dev/null", shell=True)
    if (return_code != 0):  sys.exit(12)
    
    # Process the output of BamReadCount
    with open('Variant_quals.csv', 'r') as f:
        csvReader = csv.reader(f, delimiter='\t', quotechar='"')
        
        i = 0
        NrVariants = len(Variants)
        SecondRun = False
    
        for row in csvReader:
            chrom      = row[0]
            pos        = int(row[1])
            ref        = row[2]
            Coverage   = int(row[3])
            NRref      = 0
            BQref      = 0.0
            NRref_fwd  = 0
            NRref_rev  = 0
            Cvg_fwd    = 0
            Cvg_rev    = 0
            StrandBias = 0.0
            if (not SecondRun):
                NRalt      = 0
                BQalt      = None
#                BQalt_max  = 0.0
                NRalt_fwd  = 0
                NRalt_rev  = 0
    
            while (i < len(Variants) and (chrom != Variants[i].chr or not (pos == Variants[i].pos or pos+1 == Variants[i].pos))):
                print("Assertion error! i=%d: chrom=%s, pos=%s, Variants[i].chr=%s, Variants[i].pos=%s" %(i, chrom, pos, Variants[i].chr, Variants[i].pos)) 
                del(Variants[i])
                SecondRun = False
            if (i >= len(Variants)):
                print("Error! i=%d: Length of variants array (%d) exceeded." %(i, len(Variants))) 
                sys.exit(21)
    
            alt = Variants[i].alt
            if (alt == "-"):
                alt = "-" + Variants[i].ref
            elif (Variants[i].ref == "-"):
                alt = "+" + alt
    
            # Parse BamReadCount
            # chr  position  reference_base  depth  base:count:avg_mapping_quality:avg_basequality:avg_se_mapping_quality:num_plus_strand:num_minus_strand:avg_pos_as_fraction:avg_num_mismatches_as_fraction:avg_sum_mismatch_qualities:num_q2_containing_reads:avg_distance_to_q2_start_in_q2_reads:avg_clipped_length:avg_distance_to_effective_3p_end
    
            for j in range(4, len(row)):
                quals = row[j].split(":", 7)            
                if (quals[0] == ref):
                    NRref      = int(quals[1])
                    BQref      = float(quals[3])
                    NRref_fwd  = int(quals[5])
                    NRref_rev  = int(quals[6])
        
                elif (not SecondRun and ((quals[0] == alt) or (quals[0] == Variants[i].alt[0]))):
                    NRalt      = int(quals[1])
                    if ((Variants[i].alt != "-") and (Variants[i].ref != "-")):
                        BQalt  = float(quals[3])
                    NRalt_fwd  = int(quals[5])
                    NRalt_rev  = int(quals[6])
    
#                elif (Variants[i].alt == "-"):
#                    BQalt = max(BQalt, float(quals[3]))  # das ist ein Problem, weil evtl. auch die Referenz folgen kann
#                    BQalt = -1.0
    
                if (len(quals[0]) == 1):
                    Cvg_fwd = Cvg_fwd + int(quals[5])
                    Cvg_rev = Cvg_rev + int(quals[6])
    
            if (SecondRun):
                SecondRun = False
            elif (Variants[i].ref == "-"):
                SecondRun = True
                continue
    
            oddsratio, StrandBias = scipy.stats.fisher_exact([[NRref_fwd, NRalt_fwd], [NRref_rev, NRalt_rev]])
            Qualities.append (Quality(Variants[i].chr, Variants[i].pos, Variants[i].ref, Variants[i].alt, Coverage, NRref, NRalt, BQref, BQalt, NRref_fwd, NRalt_fwd, NRref_rev, NRalt_rev, Cvg_fwd, Cvg_rev, StrandBias))
            i = i + 1
    
        while (i < len(Variants)):
            print("Assertion error! i=%d: chrom=%s, pos=%s, Variants[i].chr=%s, Variants[i].pos=%s" %(i, chrom, pos, Variants[i].chr, Variants[i].pos)) 
            del(Variants[i])
            
    print ("  --> %d of %d variants annotated with quality scores.\n" % (len(Variants), NrVariants))


    # ------------------------------------------------------------
    #   [3] Apply basic hard filters
    # ------------------------------------------------------------
    print ("STEP 3: Basic filtering")
    i = 0
    NrVariants = len(Variants)
    while (i < len(Qualities)):
        qual = Qualities[i]
        var  = Variants[i]
        if ((qual.NRalt < 20) or (qual.Coverage < 50) or (float(qual.NRalt)/qual.Coverage < 0.01) or ((qual.BQalt >= 0) and ((qual.BQalt < 15) or (qual.BQref-qual.BQalt > 7)))):
#            print ("Filtered: Qualities[i]: %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s" % (Qualities[i].chr, Qualities[i].pos, Qualities[i].ref, Qualities[i].alt, Qualities[i].Coverage, Qualities[i].NRref, Qualities[i].NRalt, Qualities[i].BQref, Qualities[i].BQalt, Qualities[i].NRref_fwd, Qualities[i].NRalt_fwd, Qualities[i].NRref_rev, Qualities[i].NRalt_rev))
            del(Variants[i])
            del(Qualities[i])
    
        else:
            i = i+1
    
    print ("  --> %d of %d variants remained after filtering.\n" % (len(Qualities), NrVariants))
    protocol = protocol + "\t" + str(len(Variants))


# ------------------------------------------------------------
#   [4] Calculate Provean scores
# ------------------------------------------------------------
print ("STEP 4: Calculate Provean scores")
parser = re.compile('^(?P<ref>\D+)(?P<pos>\d+)(?P<alt>\D*)')

tempFile = open('Variant_prot.csv', 'w')
for var in Variants:
    Proveans.append(list())
    SIFTs.append(list())
    if (var.Protein != ''):
        for i in range(0, len(var.Protein)):
            Provean = None
            SIFT = None
            prot = var.Protein[i]
            print ("Variant: %s: %s" % (var.Transcript[i], prot))

#            print("SELECT Provean_Score, SIFT_Score FROM VariantAnno WHERE chr=%s AND pos=%s AND ref=%s AND alt=%s AND Transcript=%s" % (var.chr, var.pos, var.ref, var.alt, var.Transcript[i]))
            cur.execute("SELECT Provean_Score, SIFT_Score FROM VariantAnno WHERE chr=%s AND pos=%s AND ref=%s AND alt=%s AND Transcript=%s", (var.chr, var.pos, var.ref, var.alt, var.Transcript[i]))
            if (cur.rowcount > 0):
                row = cur.fetchone()
                Provean = row[0]
                SIFT = row[0]

            elif (prot != ''):
                if (prot[0] == 'p'): prot = prot[2:]
                match = parser.match(prot)
                if (match):
                    (ref, pos, alt) = (match.group("ref"), match.group("pos"), match.group("alt"))
                    if (alt == "fs" or alt == "*"):
                        Provean = None
                        SIFT = None
                    elif (alt == ref):
                        Provean = 0.0
                        SIFT = 0.0
                    elif ((len(alt) == 1 and alt[0] in "ACDEFGHIKLMNPQRSTVWY") or alt == "del"):
#                        print("SELECT `" + alt + "` FROM db_Provean WHERE transcript_id='" + var.Transcript[i] + "' AND position=" + pos)
                        cur.execute("SELECT `" + alt + "` FROM db_Provean WHERE transcript_id='" + var.Transcript[i] + "' AND position=" + pos)
                        if (cur.rowcount > 0):
                            row = cur.fetchone()
                            if (row[0] != ""):  Provean = float(row[0])
#                            print ("Result (DB): %f" % Provean)
                        if (alt != "del"):
#                            print("SELECT " + alt + " FROM db_SIFT WHERE transcript_id='" + var.Transcript[i] + "' AND position=" + pos)
                            cur.execute("SELECT `" + alt + "` FROM db_SIFT WHERE transcript_id='" + var.Transcript[i] + "' AND position=" + pos)
                            if (cur.rowcount > 0):
                                row = cur.fetchone()
                                if (row[0] != ""):  SIFT = float(row[0])
                    else:
#                        return_code = subprocess.call("mysql --host=genome-mysql.cse.ucsc.edu --user=genome --password= --database=hg19 --no-auto-rehash --skip-column-names -e 'SELECT seq from ensPep WHERE name=\"" + var.Transcript[i] + "\"' > /tmp/CurSeq.pep", shell=True)
                        tempFile = open('/tmp/CurVar.var', 'w')
                        tempFile.write ("%s\n" % prot)
                        tempFile.close()
#                        return_code = subprocess.call("provean.sh -q /tmp/CurSeq.pep -v /tmp/CurVar.var > ./Provean_out.txt", shell=True)
#                        with open('./Provean_out.txt', 'r') as tempFile:
#                            for line in tempFile:
#                                if (line[:len(prot)] == prot):
#                                    print(line[len(prot)+1:])
#                                    Provean = float(line[len(prot)+1:])
#                                    break
            print("Result: Provean=%s, SIFT=%s" % (Provean, SIFT))
            Proveans[len(Proveans)-1].append(Provean)
            SIFTs[len(SIFTs)-1].append(SIFT)


# ------------------------------------------------------------
#   [5] Import variants into database
# ------------------------------------------------------------
print ("STEP 5: Database import")

try:
    if (len(Variants) != len(Qualities)):
       print("Assertion error! Variants (%d) and Qualities (%d) not equal!" %(len(Variants), len(Qualities)))
       sys.exit(4) 

    for i in range(0, len(Qualities)):
        quality = Qualities[i]
        print      ("INSERT INTO Variants (SampleID, chr, pos, ref, alt, Cvg, NRref, NRalt, BQref, BQalt, NRref_fwd, NRalt_fwd, NRref_rev, NRalt_rev, Cvg_fwd, Cvg_rev, StrandBias, Genotypes, Callers, NrCallers) VALUES (%s, %s, %d, %s, %s, %d, %d, %d, %f, %s, %d, %d, %d, %d, %d, %d, %f, %s, %s, %d)" % (SampleID, quality.chr, quality.pos, quality.ref, quality.alt, quality.Coverage, quality.NRref, quality.NRalt, quality.BQref, quality.BQalt, quality.NRref_fwd, quality.NRalt_fwd, quality.NRref_rev, quality.NRalt_rev, quality.Cvg_fwd, quality.Cvg_rev, quality.StrandBias, Variants[i].Genotypes, Variants[i].Callers, Variants[i].NrCallers))
        cur.execute("INSERT INTO Variants (SampleID, chr, pos, ref, alt, Cvg, NRref, NRalt, BQref, BQalt, NRref_fwd, NRalt_fwd, NRref_rev, NRalt_rev, Cvg_fwd, Cvg_rev, StrandBias, Genotypes, Callers, NrCallers) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)",  (SampleID, quality.chr, quality.pos, quality.ref, quality.alt, quality.Coverage, quality.NRref, quality.NRalt, quality.BQref, quality.BQalt, quality.NRref_fwd, quality.NRalt_fwd, quality.NRref_rev, quality.NRalt_rev, quality.Cvg_fwd, quality.Cvg_rev, quality.StrandBias, Variants[i].Genotypes, Variants[i].Callers, Variants[i].NrCallers))

    for i in range(0, len(Variants)):
        variant = Variants[i]
        for j in range(0, len(variant.Transcript)):
            print      ("INSERT IGNORE INTO VariantAnno (chr, pos, ref, alt, Transcript, Gene, varType, regionType, Exon, AAchange, Codon, Protein, Impact, Provean_Score, SIFT_Score) VALUES (%s, %d, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)" % (variant.chr, variant.pos, variant.ref, variant.alt, variant.Transcript[j], variant.Gene[j], variant.varType[j], variant.regionType[j], variant.Exon[j], variant.AAchange[j], variant.Codon[j], variant.Protein[j], variant.Impact[j], Proveans[i][j], SIFTs[i][j]))
            cur.execute("INSERT IGNORE INTO VariantAnno (chr, pos, ref, alt, Transcript, Gene, varType, regionType, Exon, AAchange, Codon, Protein, Impact, Provean_Score, SIFT_Score) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)",  (variant.chr, variant.pos, variant.ref, variant.alt, variant.Transcript[j], variant.Gene[j], variant.varType[j], variant.regionType[j], variant.Exon[j], variant.AAchange[j], variant.Codon[j], variant.Protein[j], variant.Impact[j], Proveans[i][j], SIFTs[i][j]))

    print("Now committing")
    con.commit()

    print ("done")

except MySQLdb.Error, e:
    print "MySQL Error %d: %s" % (e.args[0], e.args[1])
    if con:
        con.rollback()
    sys.exit(3)

finally:
    if con: con.close()

with open('protocol.log', 'a') as f:
    f.write(protocol + "\n")

print protocol
print "Finished."
