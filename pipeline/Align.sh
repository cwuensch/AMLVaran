#!/bin/bash

scripts="$( dirname "$0" )"
source "$scripts/Config.sh"

OUTPUT=$1
READS1=$2
READS2=$3

#if [ ! -f $OUTPUT ] ; then
  # 1. Align with bwa mem
  $bwa mem -M -t 4 $genome $READS1 $READS2 > Sample.sam
  if [ $? -ne 0 ]; then
    exit 1
  fi

  # 2. Convert to bam
  $samtools view -S -b Sample.sam > Sample_unsort.bam
  if [ $? -ne 0 ]; then
    exit 2
  fi

  # 3. Sort bam file
  $samtools sort Sample_unsort.bam -o $OUTPUT
  if [ $? -ne 0 ]; then
    exit 3
  fi

  rm Sample.sam Sample_unsort.bam
#fi
