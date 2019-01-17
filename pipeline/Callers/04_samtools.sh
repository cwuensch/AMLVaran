#!/bin/bash

dir=$1
sample=$2

mkdir -p $dir/samtools

echo "Processing sample ${sample} with SAMtools"

if [ ! -f $dir/samtools/${sample}.vcf ] ; then
  if [ ! -f $dir/samtools/${sample}.bcf ] ; then
    samtools mpileup -q 1 -g -u -o $dir/samtools/${sample}.bcf -f $GENOME $dir/${sample}.bam 
    if [ $? -ne 0 ]; then
      echo "Error SAMtools: mpileup"
      rm $dir/samtools/${sample}\.bcf
      exit 1
    fi
  fi

  bcftools call -vmO v -o $dir/samtools/${sample}.vcf $dir/samtools/${sample}.bcf
  if [ $? -ne 0 ]; then
    echo "Error SAMtools: call"
    rm $dir/samtools/${sample}.vcf
    exit 2
  fi
fi

if [ -e $dir/samtools/${sample}.bcf ] ; then
  rm $dir/samtools/${sample}.bcf
fi
