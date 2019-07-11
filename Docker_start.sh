echo ""
echo "WARNING!"
echo "========"
echo ""
echo "This software is intended for RESEARCH USE only!"
echo ""
echo "The software components need to be adapted to local reqiurements. Especially the variant calling parameters are to be adapted for the type of data to be used."
echo "Importantly, the system needs to be assembled and validated locally before use!"
echo ""
echo "This code is provided 'AS IS' and any express or implied warranties, including, but not limited to, the implied warranties of merchantability and fitness for a particular purpose are disclaimed."
echo ""
read -r -p "Type 'YES' to confirm that you agreee to this notice: " response
case "$response" in
    [yY][eE][sS])
        ;;
    *)
        echo "Installation cannot be continued."
        exit 1
        ;;
esac

SCRIPT_DIR=$(readlink -f $0)
SCRIPT_DIR=${SCRIPT_DIR%/*}

export HOST_USER_ID=$(id -u)
export HOST_GROUP_ID=$(id -g)

echo ""
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

if [ ! -d $SCRIPT_DIR/mysql_data/amlvaran ]; then
    echo "Prepopulating MySQL database..."
    mkdir $SCRIPT_DIR/mysql_data
    cd $SCRIPT_DIR/mysql_data
    wget -O- https://static.uni-muenster.de/amlvaran/SQLdump/DB.tar.gz | tar -xz -C $SCRIPT_DIR/mysql_data
fi

echo "Starting Docker..."
cd $SCRIPT_DIR
docker-compose up