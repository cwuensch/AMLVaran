SCRIPT_DIR=$(readlink -f $0)
SCRIPT_DIR=${SCRIPT_DIR%/*}

echo "Downloading ressources. This may take a few hours..."

cd $SCRIPT_DIR/genomes
if [ ! -f Homo_sapiens.GRCh37.67.fasta ]; then
    echo "Get reference genome..."
    sh getReference.sh
fi

cd $SCRIPT_DIR/genomes/gatk
if [ ! -f 1000G_phase1.indels.b37.vcf ]; then
    echo "Get GATK ressources..."
    sh getRessources.sh
fi

cd $SCRIPT_DIR/genomes/blast
if [ ! -f nr.05.psq ]; then
    echo "Get Blast DB for Provean [optional]..."
    sh getBlastDB.sh
fi

if [ ! -f $SCRIPT_DIR/mysql_data/amlvaran/Variants.ibd ]; then
    echo "Prepopulating MySQL database..."
    mkdir $SCRIPT_DIR/mysql_data
    cd $SCRIPT_DIR/mysql_data
    wget -qO- https://amlvaran.uni-muenster.de/SQLdump/DB.tar.gz | tar -xz -C $SCRIPT_DIR/mysql_data
fi

echo "Starting Docker..."
cd $SCRIPT_DIR
export HOST_USER_ID=$(id -u)
export HOST_GROUP_ID=$(id -g)
docker-compose up
