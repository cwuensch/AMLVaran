cd genomes
if [ ! -f Homo_sapiens.GRCh37.67.fasta ]; then
    sh getReference.sh
fi

cd gatk
if [ ! -f 1000G_phase1.indels.b37.vcf ]; then
    sh getRessources.sh
fi

cd ../blast
if [ ! -f nr.05.psq ]; then
    sh getBlastDB.sh
fi

cd ../..
#docker build -f Dockerfile.db -t amlvaran/db .
#docker build -f Dockerfile.www -t amlvaran/www .
#docker build -f Dockerfile -t amlvaran/worker .

docker run --rm -t amlvaran/db
docker run --rm -v /media/watson/projects/christian/amlvaran/samples:/var/samples -v /media/watson/projects/christian/amlvaran/genomes:/var/genomes -t amlvaran/worker
docker run --rm -v /media/watson/projects/christian/amlvaran/samples:/var/samples -t amlvaran/www
