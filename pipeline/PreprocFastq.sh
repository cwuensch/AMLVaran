#!/bin/bash

#if [ ! -f "val_R1.fastq" ] || [ ! -f "val_R2.fastq" ]; then
  # First Merge reads
  if [ ! -f "R1.fastq" ] || [ ! -f "R2.fastq" ]; then
    cat *_R1_* > R1.fastq
    cat *_R2_* > R2.fastq
  fi

  # Then Trimming
  ADAPTER=AGATCGGAAGAGCGGTTCAGCAGGAATGCCGAG
  trimgalore --path_to_cutadapt cutadapt --paired -a $ADAPTER R1.fastq R2.fastq 2> log/A1_trim.log

  if [ $? -ne 0 ]; then
    exit 1
  else
    rm "R1.fastq"
    rm "R2.fastq"
    mv "R1_val_1.fq" "val_R1.fastq"
    mv "R2_val_2.fq" "val_R2.fastq"
    # move ~.fastq_trimming_report.txt s  to log dir
    mv R1.fastq_trimming_report.txt log/
    mv R2.fastq_trimming_report.txt log/
  fi
#fi
