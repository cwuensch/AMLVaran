#!/bin/bash

dir=$1
sample=$2
bedfile=$3

mkdir -p $dir/vardict

echo "Processing sample ${sample} with VarDict"
if [ ! -f $dir/vardict/${sample}.vcf ] ; then
  af_thr="0.01"
  vardict-java -C -G $GENOME -f $af_thr -N "VarDict" -b $dir/${sample}.bam -h -c 1 -S 2 -E 3 -g 4 $bedfile > $dir/vardict/${sample}.vcf
  if [ $? -ne 0 ]; then
    echo "Error VarDict"
    rm $dir/vardict/${sample}.vcf
    exit 1
  fi
  cd $dir
fi

