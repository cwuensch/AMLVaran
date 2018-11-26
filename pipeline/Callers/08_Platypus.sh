#!/bin/bash

dir=$1
sample=$2

mkdir -p $dir/Platypus

echo "Processing sample ${sample} with Platypus"
if [ ! -f $dir/Platypus/${sample}.vcf ] ; then
  platypus callVariants --bamFiles=$dir/${sample}.bam --refFile $GENOME --output=$dir/Platypus/${sample}.vcf --filterDuplicates=0 --minFlank=0
  if [ $? -ne 0 ]; then
    echo "Error Platypus"
    rm $dir/Platypus/${sample}.vcf
    exit 1
  fi
fi

if [ -e $dir/log.txt ] ; then
  rm $dir/log.txt
fi

