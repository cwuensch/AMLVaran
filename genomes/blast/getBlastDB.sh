for i in `seq 0 5`;
do
    wget -O- ftp://ftp.jcvi.org/pub/data/provean/nr_Aug_2011/nr.0$i.tar.gz | tar -xz
done
 