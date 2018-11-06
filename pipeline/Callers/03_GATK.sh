#!/bin/bash

scripts="$( dirname "$0" )"
source "$scripts/../Config.sh"

dbsnp1=/var/amlvaran/resources/b37/dbsnp_138.b37.vcf
dbsnp2=/var/amlvaran/resources/b37/dbsnp_138.b37.excluding_sites_after_129.vcf
knownindels1=/var/amlvaran/resources/b37/Mills_and_1000G_gold_standard.indels.b37.vcf
knownindels2=/var/amlvaran/resources/b37/1000G_phase1.indels.b37.vcf

dir=$1
sample=$2
bedfile=$3

tempdir=$dir/GATK/bam
mkdir -p $tempdir

echo "Processing sample ${sample} with GATK"
if [ ! -f $dir/GATK/${sample}.rawMutations.vcf ] ; then

  if [ ! -f ${tempdir}/${sample}_RecalData.csv ] ; then
    $java_path -jar $gatk -T BaseRecalibrator -I $dir/${sample}.bam -R $genome --maximum_cycle_value 1500 --covariate ContextCovariate \
      --covariate CycleCovariate --covariate QualityScoreCovariate --covariate ReadGroupCovariate -knownSites $dbsnp1 -knownSites $knownindels1 \
      -knownSites $knownindels2 -nct 1 -o ${tempdir}/${sample}_RecalData.csv 
    if [ $? -ne 0 ]; then
      echo "Error GATK: BaseRecalibrator"
      rm ${tempdir}/${sample}_RecalData.csv
      exit 1
    fi
  fi
  
  if [ ! -f ${tempdir}/${sample}.bam ] ; then
    $java_path -jar $gatk -T PrintReads -I $dir/${sample}.bam -R $genome -BQSR ${tempdir}/${sample}_RecalData.csv -o ${tempdir}/${sample}.bam 
    if [ $? -ne 0 ]; then
      echo "Error GATK: PrintReads"
      rm ${tempdir}/${sample}.bam
      exit 2
    fi
  fi
  
  if [ ! -f ${tempdir}/${sample}.bai ] ; then
    $java_path -jar $pic/BuildBamIndex.jar VALIDATION_STRINGENCY="LENIENT" INPUT=${tempdir}/${sample}.bam OUTPUT=${tempdir}/${sample}.bai 
    if [ $? -ne 0 ]; then
      echo "Error GATK: BuildBamIndex"
      rm ${tempdir}/${sample}.bai
      exit 3
    fi
  fi

  downsample="1500"
  $java_path -jar $gatk -T HaplotypeCaller -R $genome -stand_call_conf 30.0 -stand_emit_conf 10.0 --dbsnp $dbsnp2 -L $bedfile --max_alternate_alleles 9 \
    --downsample_to_coverage $downsample -nct 1 -I ${tempdir}/${sample}.bam -o $dir/GATK/${sample}.rawMutations.vcf 
  if [ $? -ne 0 ]; then
    echo "Error GATK: HaplotypeCaller"
    rm $OUTFILE
    exit 4
  fi
fi
rm -R ${tempdir}

