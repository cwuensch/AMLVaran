FROM debian

LABEL maintainer=christian.wuensch@ukmuenster.de
LABEL version=1.0

# Build arguments (default values)
ARG MYSQL_HOST=127.0.0.1
ARG MYSQL_USER=amlvaran
ARG MYSQL_PASSWORD=123456 
ARG MYSQL_DATABASE=amlvaran 

# Update operating system
RUN apt-get -q update && apt-get -q upgrade -yqq

# Install libs
RUN apt-get -q update && apt-get install -y \
  bzip2 parallel net-tools mysql-client
#  python2.7 python-mysqldb
#  openjdk-8-jre-headless 

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
  mysql-python \
  scipy \
  samtools=1.3 \
  variant_tools=2.7.0 \
  vcftools=0.1.* \
  bcftools \
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
  snver=0.5.3 \
  platypus-variant=0.8.1 

# Special action for GATK
COPY GenomeAnalysisTK.jar /opt/GATK/
RUN gatk-register /opt/GATK/GenomeAnalysisTK.jar

# Install SNVer (not in bioconda)
#ADD https://downloads.sourceforge.net/project/snver/SNVer-0.5.3.tar.gz /opt/miniconda/opt/SNVer-0.5.3/
#COPY SNVerIndividual.sh /opt/miniconda/opt/SNVer-0.5.3/
#WORKDIR /opt/miniconda/opt/SNVer-0.5.3
#RUN tar -xzf SNVer-0.5.3.tar.gz
#RUN rm SNVer-0.5.3.tar.gz
#RUN ln -s /opt/miniconda/opt/SNVer-0.5.3/SNVerIndividual.sh /opt/miniconda/bin/SNVerIndividual
#RUN chmod 755 /opt/miniconda/bin/SNVerIndividual

# Install PROVEAN (optional)
RUN conda install cd-hit blast
ADD https://amlvaran.uni-muenster.de/Reference/Provean_compiled.tar.gz /opt/Provean/
WORKDIR /opt/Provean
RUN tar -xzf Provean_compiled.tar.gz
RUN rm Provean_compiled.tar.gz
RUN ln -s /opt/Provean/provean /usr/local/bin
RUN ln -s /opt/Provean/provean.sh /usr/local/bin
RUN chmod 755 /usr/local/bin/provean
RUN chmod 755 /usr/local/bin/provean.sh

# Get pipeline code from Git
COPY pipeline /var/pipeline/
WORKDIR /var/pipeline
RUN chmod 755 *.sh *.py Callers/*.sh

# Set environment variables for mounted volumes
ENV SAMPLEDIR=/var/samples
ENV GENOME=/var/genomes/Homo_sapiens.GRCh37.67.fasta
ENV GATK_RES=/var/genomes/gatk
ENV BLAST_DB=/var/genomes/blast
RUN vtools --version
RUN echo "user_stash='~/.variant_tools;/var/genomes'" >> /root/.variant_tools/user_options.py 

# Set MySQL access data
RUN echo "[client]" > /root/.my.cnf
RUN echo "host=${MYSQL_HOST}" >> /root/.my.cnf
RUN echo "user=${MYSQL_USER}" >> /root/.my.cnf
RUN echo "password=${MYSQL_USER}" >> /root/.my.cnf
RUN echo "database=${MYSQL_DATABASE}" >> /root/.my.cnf


# Get reference genome [required, but should be mounted instead]
#ADD https://amlvaran.uni-muenster.de/Reference/Homo_sapiens.GRCh37.67.tar.gz /var/genomes/
#ADD https://bioinformatics.mdanderson.org/Software/VariantTools/repository/reference/hg19.crr /var/genomes/
#WORKDIR /var/genomes
#RUN tar -xzf Homo_sapiens.GRCh37.67.tar.gz
#RUN rm Homo_sapiens.GRCh37.67.tar.gz

# Get GATK ressources [optional, but should be mounted instead]
#ADD https://amlvaran.uni-muenster.de/Reference/GATK_ressources.tar.gz /var/genomes/gatk/
#WORKDIR /var/genomes/gatk
#RUN tar -xzf GATK_ressources.tar.gz
#RUN rm GATK_ressources.tar.gz

# Get BlastDB ressources [optional, but should be mounted instead]

WORKDIR /var/samples
ENTRYPOINT /var/pipeline/PipelineDB.sh
