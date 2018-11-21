cd genomes
sh getReference.sh
cd ..

cd gatk
sh getRessources.sh
cd ..

cd blast
sh getBlastDB.sh
cd ..

docker build -t cw/amlvaran .
docker run --rm -it cw/amlvaran -v ./genomes:/var/genomes -v ./samples /var/samples
