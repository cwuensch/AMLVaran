FROM debian

LABEL maintainer=christian.wuensch@ukmuenster.de
LABEL version=1.0

# Update operating system
RUN apt-get -q update && apt-get -q upgrade -yqq

# Install libs
RUN apt-get -q update && apt-get install -y \
  bzip2
#  python2.7 openjdk-8-jre-headless 

# Install Conda
WORKDIR ~
ADD https://repo.continuum.io/miniconda/Miniconda3-latest-Linux-x86_64.sh miniconda.sh
RUN bash miniconda.sh -b -p /opt/miniconda
ENV PATH="/opt/miniconda/bin:$PATH"

# Install Tools via Conda
WORKDIR /opt
RUN conda config --add channels conda-forge && \
  conda config --add channels bioconda && \
  conda config --add channels https://conda.binstar.org/bpeng
RUN conda install \
  samtools=1.3 \
  variant_tools=2.7.0 \
  vcftools=0.1.* \
  snpeff=4.2 \
  bam-readcount \
  bwa=0.7.12 \
  trim-galore \
  cutadapt \
  vardict-java=1.5.5 \
  lofreq=2.1.2 \ 
  gatk=3.5 \
  varscan=2.4.0 \
  freebayes=1.0.* \
  platypus-variant=0.8.1 

# Special action for GATK
COPY GenomeAnalysisTK.jar /opt/GATK/
RUN gatk-register /opt/GATK/GenomeAnalysisTK.jar

# Install SNVer (not in bioconda)
ADD https://downloads.sourceforge.net/project/snver/SNVer-0.5.3.tar.gz /opt/miniconda/opt/SNVer-0.5.3/
COPY downloads/variant_caller/SNVer/SNVerIndividual.sh /opt/miniconda/opt/SNVer-0.5.3/
WORKDIR /opt/miniconda/opt/SNVer-0.5.3
RUN tar -xf SNVer-0.5.3.tar.gz
RUN rm SNVer-0.5.3.tar.gz
RUN ln -s /opt/miniconda/opt/SNVer-0.5.3/SNVerIndividual.sh /opt/miniconda/bin/SNVerIndividual
RUN chmod 755 /opt/miniconda/bin/SNVerIndividual

# Install PROVEAN (optional)
RUN conda install cd-hit blast
ADD https://amlvaran.uni-muenster.de/Reference/Provean_compiled.tar.gz /opt/Provean/
RUN ln -s /opt/Provean/provean /usr/local/bin
RUN ln -s /opt/Provean/provean.sh /usr/local/bin
RUN chmod 755 /usr/local/bin/provean

# Get pipeline code from Git
WORKDIR /var
COPY pipeline /var/pipeline/

# Get reference genome
#ADD https://amlvaran.uni-muenster.de/Reference/Homo_sapiens.GRCh37.67.tar.gz /var/genomes/Homo_sapiens.GRCh37.67/
#WORKDIR /var/genomes/Homo_sapiens.GRCh37.67
#RUN tar -xf Homo_sapiens.GRCh37.67.tar.gz
#RUN rm Homo_sapiens.GRCh37.67.tar.gz

# Get GATK ressources
#ADD https://amlvaran.uni-muenster.de/Reference/GATK_ressources.tar.gz /var/genomes/Homo_sapiens.GRCh37.67/
#WORKDIR /var/genomes/Homo_sapiens.GRCh37.67
#RUN tar -xf GATK_ressources.tar.gz
#RUN rm GATK_ressources.tar.gz

