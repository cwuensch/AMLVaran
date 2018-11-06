#!/bin/bash

scripts="$( dirname "$0" )"
source "$scripts/../Config.sh"

dir=$1
sample=$2

mkdir -p $dir/SNVer

echo "Processing sample ${sample} with SNVer"
if [ ! -f $dir/SNVer/${sample}.filter.vcf ] && [ ! -f $dir/SNVer/${sample}.indel.filter.vcf ] ; then
  $java_path -jar $snver -i $dir/${sample}.bam -r $genome -b 0.01 -o $dir/SNVer/${sample}
  if [ $? -ne 0 ]; then
    echo "Error SNVer"
    rm $dir/SNVer/${sample}.failed.log $dir/SNVer/${sample}.raw.vcf  $dir/SNVer/${sample}.indel.raw.vcf $dir/SNVer/${sample}.filter.vcf $dir/SNVer/${sample}.indel.filter.vcf
    exit 1
  fi
fi

if [ -e $dir/SNVer/${sample}.raw.vcf ] ; then
  rm $dir/SNVer/${sample}.raw.vcf
fi

if [ -e $dir/SNVer/${sample}.indel.raw.vcf ] ; then
  rm $dir/SNVer/${sample}.indel.raw.vcf
fi

if [ -e $dir/SNVer/${sample}.failed.log ] ; then
  rm $dir/SNVer/${sample}.failed.log 
fi

