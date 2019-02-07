#!/bin/bash

dir=$1
sample=$2

mkdir -p $dir/lofreq

echo "Processing sample ${sample} with LoFreq"
if [ ! -f $dir/lofreq/${sample}.vcf ] ; then
  lofreq call --call-indels -f $GENOME -o $dir/lofreq/${sample}.vcf $dir/${sample}.bam
  if [ $? -ne 0 ]; then
    echo "Error LoFreq"
    rm $dir/lofreq/${sample}.vcf
    exit 1
  fi
fi



