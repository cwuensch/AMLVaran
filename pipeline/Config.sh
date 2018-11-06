PATH=$PATH:/var/amlvaran/opt/samtools-1.3

SAMPLEDIR=/var/amlvaran/samples

#########################################################################################################################
# Required tools:													#
#########################################################################################################################
python=/usr/bin/python
java_path=/usr/bin/java
samtoolsdir=/var/amlvaran/opt/samtools-1.3
samtools=$samtoolsdir/samtools
vcftools=/var/amlvaran/opt/vcftools_0.1.13/bin/vcftools
snpeff=/var/amlvaran/opt/snpEff/snpEff.jar
bam_readcount=/var/amlvaran/opt/bam-readcount/bin/bam-readcount
provean=provean.sh
bwa=/var/amlvaran/opt/bwa-0.7.12/bwa
trimgalore=/var/amlvaran/opt/trim_galore-0.4.1/trim_galore
cutadapt=/var/amlvaran/opt/cutadapt-1.9.1/bin/cutadapt


#########################################################################################################################
# Variant Callers:													#
#########################################################################################################################
vardict=/var/amlvaran/opt/VarDict
lofreq=/var/amlvaran/opt/lofreq_star-2.1.2/bin/lofreq
gatk=/var/amlvaran/opt/GenomeAnalysisTK-3.3-0/GenomeAnalysisTK.jar
varscan=/var/amlvaran/opt/VarScan/VarScan.v2.3.9.jar
freebayes=/var/amlvaran/opt/freebayes/bin/freebayes
snver=/var/amlvaran/opt/SNVer/SNVerIndividual.jar
platypus=/var/amlvaran/opt/Platypus_0.8.1/Platypus.py


#########################################################################################################################
# Required resources:													#
#########################################################################################################################

# GENOME data
genome=/var/amlvaran/resources/Genomes/Homo_sapiens.GRCh37.67/bwa-0.7.10/Homo_sapiens.GRCh37.67.dna.chromosome.all.fasta
peptides=/var/amlvaran/resources/Genomes/Homo_sapiens.GRCh37.67/provean/

# DATABASE resources (Calling)
#13. dbsnp1		     -> dbSNP data (vcf)									#
#14. dbsnp2		     -> dbSNP data regarding polymorphisms, i.e. excluding sites after version 129 (vcf)	#
#17. knownindels1	     -> Gold standard set of indels (vcf)							#
#18. knownindels2	     -> Gold standard set of indels (vcf)							#
#dbsnp1=/var/amlvaran/resources/b37/dbsnp_138.b37.vcf
#dbsnp2=/var/amlvaran/resources/b37/dbsnp_138.b37.excluding_sites_after_129.vcf
#knownindels1=/var/amlvaran/resources/b37/Mills_and_1000G_gold_standard.indels.b37.vcf
#knownindels2=/var/amlvaran/resources/b37/1000G_phase1.indels.b37.vcf
