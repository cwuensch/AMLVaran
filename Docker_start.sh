SCRIPT_DIR=$(readlink -f $0)
SCRIPT_DIR=${SCRIPT_DIR%/*}

cd $SCRIPT_DIR/genomes
if [ ! -f Homo_sapiens.GRCh37.67.fasta ]; then
    sh getReference.sh
fi

cd $SCRIPT_DIR/genomes/gatk
if [ ! -f 1000G_phase1.indels.b37.vcf ]; then
    sh getRessources.sh
fi

cd $SCRIPT_DIR/genomes/blast
if [ ! -f nr.05.psq ]; then
    sh getBlastDB.sh
fi

mkdir $SCRIPT_DIR/mysql_data
wget -qO- https://amlvaran.uni-muenster.de/SQLdump/DB.tar.gz | tar -xz -C $SCRIPT_DIR/mysql_data

cd $SCRIPT_DIR
#docker build -f Dockerfile_db -t amlvaran/db .
#docker build -f Dockerfile_web -t amlvaran/web .
#docker build -f Dockerfile -t amlvaran/worker .

#docker run --rm -t amlvaran/db
#docker run --rm -v /media/watson/projects/christian/amlvaran/samples:/var/samples -v /media/watson/projects/christian/amlvaran/genomes:/var/genomes -t amlvaran/worker
#docker run --rm -v /media/watson/projects/christian/amlvaran/samples:/var/samples -t amlvaran/web

export HOST_USER_ID=$(id -u)
export HOST_GROUP_ID=$(id -g)
docker-compose up
