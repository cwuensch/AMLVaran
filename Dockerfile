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
RUN conda config --add channels defaults && \
  conda config --add channels bioconda && \
  conda config --add channels conda-forge && \
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
  varscan=2.4.0 \
  freebayes=1.0.* \
  snver=0.5.3 \
  platypus-variant=0.8.1 \
  openjdk=8 \
  openssl=1.0

# Install GATK 3.3 (not in bioconda)
#COPY GenomeAnalysisTK.jar /opt/GATK/
#RUN gatk-register /opt/GATK/GenomeAnalysisTK.jar
COPY GenomeAnalysisTK.jar /opt/miniconda/opt/GATK-3.3/
ADD https://raw.githubusercontent.com/bioconda/bioconda-recipes/master/recipes/gatk/3.5/gatk.sh /opt/miniconda/opt/GATK-3.3/
RUN ln -s /opt/miniconda/opt/GATK-3.3/gatk.sh /opt/miniconda/bin/gatk
RUN chmod 755 /opt/miniconda/bin/gatk

# Install PROVEAN (optional)
RUN conda install cd-hit blast openssl=1.0
ADD https://static.uni-muenster.de/amlvaran/Reference/Provean_compiled.tar.gz /opt/Provean/
WORKDIR /opt/Provean
RUN tar -xzf Provean_compiled.tar.gz
RUN rm Provean_compiled.tar.gz
RUN ln -s /opt/Provean/provean /usr/local/bin
RUN ln -s /opt/Provean/provean.sh /usr/local/bin
RUN chmod 755 /usr/local/bin/provean
RUN chmod 755 /usr/local/bin/provean.sh

# Change data dir for SNPeff
RUN sed -ri -e 's!./data/!/var/genomes/snpEff/data/!g' /opt/miniconda/share/snpeff-4.2-0/snpEff.config

# Create home dir for VariantTools
#RUN mkdir /root/.variant_tools
#RUN chown $HOST_USER_ID:$HOST_USER_GROUP /.variant_tools
RUN vtools --version
RUN echo "user_stash='~/.variant_tools;/var/genomes'" >> /root/.variant_tools/user_options.py 

# Get pipeline code from Git
COPY pipeline /var/pipeline/
WORKDIR /var/pipeline
RUN chmod 755 *.sh *.py Callers/*.sh

# Set MySQL access data
RUN echo "[client]" > /root/.my.cnf && \
    echo "host=${MYSQL_HOST}" >> /root/.my.cnf && \
    echo "user=${MYSQL_USER}" >> /root/.my.cnf && \
    echo "password=${MYSQL_PASSWORD}" >> /root/.my.cnf && \
    echo "database=${MYSQL_DATABASE}" >> /root/.my.cnf

# Environment variables (default values)
ENV MYSQL_HOST=${MYSQL_HOST}
ENV MYSQL_USER=${MYSQL_USER}
ENV MYSQL_PASSWORD=${MYSQL_PASSWORD}
ENV MYSQL_DATABASE=${MYSQL_DATABASE}

# Set environment variables for mounted volumes
ENV SAMPLEDIR=/var/samples
ENV GENOME=/var/genomes/Homo_sapiens.GRCh37.67.fasta
ENV GATK_RES=/var/genomes/gatk
ENV BLAST_DB=/var/genomes/blast


# Get reference genome [required, but should be mounted instead]
#ADD https://static.uni-muenster.de/amlvaran/Reference/Homo_sapiens.GRCh37.67.tar.gz /var/genomes/
#ADD https://bioinformatics.mdanderson.org/Software/VariantTools/repository/reference/hg19.crr /var/genomes/
#WORKDIR /var/genomes
#RUN tar -xzf Homo_sapiens.GRCh37.67.tar.gz
#RUN rm Homo_sapiens.GRCh37.67.tar.gz

# Get GATK ressources [optional, but should be mounted instead]
#ADD https://static.uni-muenster.de/amlvaran/Reference/GATK_ressources.tar.gz /var/genomes/gatk/
#WORKDIR /var/genomes/gatk
#RUN tar -xzf GATK_ressources.tar.gz
#RUN rm GATK_ressources.tar.gz

# Get BlastDB ressources [optional, but should be mounted instead]

WORKDIR /var/samples
ENTRYPOINT /var/pipeline/PipelineDB.sh
