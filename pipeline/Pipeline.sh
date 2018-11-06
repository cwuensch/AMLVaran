#!/bin/bash
################################################################################
# NEW Pipeline script
# (C) 2018 Christian Wuensch
################################################################################

echo "NEW Pipeline script"
echo "(C) 2018 Christian Wuensch"
echo ""
date
echo ""
starttime=$(date +%s)

# Parameter prüfen
SCRIPTS="$( dirname "$0" )"
source "$SCRIPTS/Config.sh"
STATE="0"
BEDFILE=""
if [ "$1" = "-b" ] ; then
  shift
  BEDFILE=$1
  shift
fi
if [ "$1" = "-s" ] ; then
  shift
  STATE=$1
  shift
fi

if [ -z $2 ] ; then
  echo "Calling: $0 [-b <BED file>] [-s <State>] <PatientID> <SampleID>"
  echo ""
  exit 1
fi
PATIENTID=$1
SAMPLEID=$2

echo "Processing sample $SAMPLEID..."

cd $SAMPLEDIR/$PATIENTID/$SAMPLEID
if [ ! -d "log" ] ; then
  mkdir log
fi


# ------------------------------------------------------------
#   [1] Preprocessing (fastq)
# ------------------------------------------------------------

if [ $STATE -lt 10 ] ; then
  # Preprocessing: Merge and trim reads
  echo "[Preprocessing: Merge and trim reads]"
  $SCRIPTS/PreprocFastq.sh
  if [ $? -ne 0 ]; then
    STATE="101"
  else
    STATE="10"
  fi
  mysql -e "UPDATE samples SET StateCode='$STATE' WHERE PatientID='$PATIENTID' AND SampleID='$SAMPLEID'"
fi

# ------------------------------------------------------------
#   [2] Alignment
# ------------------------------------------------------------

if [ $STATE -lt 20 ] ; then
  echo "[Alignment]"
#  vtools execute $SCRIPTS/bwa_gatk33_hg19 align --input val_R1.fastq val_R2.fastq --output Sample.bam --name Sample --production true 2> log/Align.log
  $SCRIPTS/Align.sh Sample.bam val_R1.fastq val_R2.fastq
  if [ $? -ne 0 ]; then
    STATE="201"
  else
    rm "val_R1.fastq" "val_R2.fastq"
    STATE="20"
  fi
  mysql -e "UPDATE samples SET StateCode='$STATE' WHERE PatientID='$PATIENTID' AND SampleID='$SAMPLEID'"
fi

if [ $STATE -lt 25 ] ; then
  # Create index
  echo "[Create index]"
  $samtools index -b Sample.bam Sample.bai
  STATE="25"
  mysql -e "UPDATE samples SET StateCode='$STATE' WHERE PatientID='$PATIENTID' AND SampleID='$SAMPLEID'"
fi


# ------------------------------------------------------------
#   [3] Calculate coverage
# ------------------------------------------------------------

if [ $STATE -lt 30 ] ; then
  echo "[Calculate coverage]"
  if [ ! "$BEDFILE" = "" ] ; then
    $samtools depth -b $BEDFILE Sample.bam > ./Coverage.txt
  else
    $samtools depth Sample.bam > ./Coverage.txt
  fi

  STATE="30"
  mysql -e "UPDATE samples SET StateCode='$STATE' WHERE PatientID='$PATIENTID' AND SampleID='$SAMPLEID'"
fi


# ------------------------------------------------------------
#   [4] Run callers in parallel (bam)
# ------------------------------------------------------------

if [ $STATE -lt 40 ] ; then
  # Run Callers
  echo "[Run Variant Callers]"
  parallel -j4 --verbose ::: $SCRIPTS/Callers/*.sh ::: . ::: "Sample" ::: $BEDFILE

  STATE="40"
  mysql -e "UPDATE samples SET StateCode='$STATE' WHERE PatientID='$PATIENTID' AND SampleID='$SAMPLEID'"
fi


# ------------------------------------------------------------
#   [5] Integrate variants from different callers
# ------------------------------------------------------------

if [ $STATE -lt 50 ] ; then
  echo "[Integrate variants from callers]"
  vtools init project --build hg19

  for D in *; do
    if [ -d "${D}" ] && [ ! "${D}" = "log" ] ; then
      vtools import --sample_name "${D}" --format $SCRIPTS/Formats/${D}.fmt ${D}/*.vcf
    fi
  done

#  vtools import --sample_name "GATK"      --format vcf                                ./gatk/Sample.rawMutations.vcf
#  vtools import --sample_name "freebayes" --format $SCRIPTS/Formats/myvcf_nogeno.fmt  ./freebayes/Sample.vcf
#  vtools import --sample_name "lofreq"    --format vcf                                ./lofreq/Sample.indels.vcf ./lofreq/Sample.SNPs.target.vcf
#  vtools import --sample_name "Platypus"  --format vcf                                ./Platypus/Sample.vcf
#  vtools import --sample_name "samtools"  --format vcf                                ./samtools/Sample.vcf
#  vtools import --sample_name "SNVer"     --format vcf                                ./SNVer/Sample.vcf.filter.vcf ./SNVer/Sample.vcf.indel.filter.vcf 
#  vtools import --sample_name "varscan"   --format basic                              ./varscan/snvs/Sample.txt ./varscan/indels/Sample.vcf
#  vtools import --sample_name "vardict"   --format $SCRIPTS/Formats/vardict.fmt       ./vardict/Sample.vcf
  
  #vtools update variant --from_stat "NrCallers=#(GT)"
  #vtools output variant chr pos ref alt eff "genotype('GATK')" "genotype('samtools')" "genotype('lofreq')"
  
  #vtools execute snpEff eff --snpeff_path /var/amlvaran/opt/snpEff/
  
  #vtools update variant --set "caller_str=samples()"
  vtools update variant --set "callers=samples()" "genotypes=genotype(,'missing=.')"
  vtools export variant --format $SCRIPTS/Formats/myvcf.fmt --header $'##fileformat=VCFv4.1\n#CHROM\tPOS\tID\tREF\tALT\tQUAL\tFILTER\tINFO' --var_info callers genotypes --output ./Variants_raw.vcf
  
  #vtools export variant --format vcf --samples 1 --header CHROM POS ID REF ALT QUAL FILTER INFO FORMAT GATK freebayes lofreq lofreq Platypus samtools SNVer SNVer varscan varscan vardict --format_string GT --output ./Variants_raw2.vcf

  STATE="50"
  mysql -e "UPDATE samples SET StateCode='$STATE' WHERE PatientID='$PATIENTID' AND SampleID='$SAMPLEID'"
fi


# ------------------------------------------------------------
#   [6] Basic annotation and hard filtering (vcf)
# ------------------------------------------------------------

if [ $STATE -lt 60 ] ; then
  # Filter for target bed
  echo "[Filter for target bed]"
  if [ ! "$BEDFILE" = "" ] ; then
    $vcftools --vcf ./Variants_raw.vcf --bed $BEDFILE --recode --recode-INFO-all --out ./Variants_raw
  fi

#  echo "##fileformat=VCFv4.1" > Variants.vcf
#  cat Variants_SNPeff.vcf >> Variants.vcf
  ln Variants_SNPeff.vcf Variants.vcf

  STATE="60"
  mysql -e "UPDATE samples SET StateCode='$STATE' WHERE PatientID='$PATIENTID' AND SampleID='$SAMPLEID'"
fi

# ------------------------------------------------------------
#   [7] Process data and import into database
# ------------------------------------------------------------

if [ $STATE -lt 80 ] ; then
  # Process output with Python
  echo "[Run Annotation and Import pipeline]"
  export snpeff bam_readcount genome peptides
  python $SCRIPTS/Pipeline.py $SAMPLEID ./Variants_raw.recode.vcf

  STATE="80"
  mysql -e "UPDATE samples SET StateCode='$STATE' WHERE PatientID='$PATIENTID' AND SampleID='$SAMPLEID'"
fi

if [ $STATE -lt 90 ] ; then
  # Import coverage
  echo "[Import coverage]"
  python $SCRIPTS/ImportCoverage.py $SAMPLEID ./Coverage.txt

  STATE="100"
  mysql -e "UPDATE samples SET StateCode='$STATE' WHERE PatientID='$PATIENTID' AND SampleID='$SAMPLEID'"
fi

echo "Finished."
endtime=$(date +%s)
echo "Elapsed time: " $(($endtime - $starttime)) " seconds"
echo "Sample " $SAMPLEID ": " $(($endtime - $starttime)) " seconds" >> /var/amlvaran/samples/statistik.log
