#!/bin/bash
#WORKER_ID="$( /sbin/ifconfig eth0 | grep 'inet Adresse' | cut -d: -f2 | awk '{print $1}' )"
#SCRIPTS="$( dirname "$0" )"
SCRIPT_DIR=$(readlink -f $0)
SCRIPT_DIR=${SCRIPT_DIR%/*}

## Set Variant Tools ressources folder
#vtools --version
#echo "user_stash='~/.variant_tools;/var/genomes'" >> ~/.variant_tools/user_options.py 

## Set MySQL access data
#echo "[client]" > ~/.my.cnf
#echo "host=${MYSQL_HOST}" >> ~/.my.cnf
#echo "user=${MYSQL_USER}" >> ~/.my.cnf
#echo "password=${MYSQL_PASSWORD}" >> ~/.my.cnf
#echo "database=${MYSQL_DATABASE}" >> ~/.my.cnf


# trap ctrl-c and call ctrl_c()
trap ctrl_c INT

function ctrl_c() {
  echo "** Trapped CTRL-C"
  ps all
  lsof
}


while [ 1 ]
do
  mysql -sNe "UPDATE samples SET Worker='$WORKER_ID' WHERE (StateCode BETWEEN 1 AND 99) AND (Worker='$WORKER_ID' OR Worker IS NULL) ORDER BY Created LIMIT 1"
  NEXTSAMPLE=$(mysql -sNe "SELECT PatientID, SampleID, design, StateCode, Worker FROM samples WHERE (StateCode BETWEEN 1 AND 99) AND Worker='$WORKER_ID' ORDER BY Created LIMIT 1")
  if [ -n "$NEXTSAMPLE" ]; then
    echo $NEXTSAMPLE
    IFS=$'\t' read -ra PARAMS <<< "$NEXTSAMPLE"
    PATIENTID=${PARAMS[0]}
    SAMPLEID=${PARAMS[1]}
    DESIGN=${PARAMS[2]}
    STATE=${PARAMS[3]}
    
    cd $SAMPLEDIR/$PATIENTID/$SAMPLEID    
    mysql -sNe "SELECT chr, start-1, end FROM tgt_Regions WHERE design='$DESIGN'" > ./Design.bed
    
    $SCRIPT_DIR/Pipeline.sh -b ./Design.bed -s $STATE $PATIENTID $SAMPLEID
  fi
  sleep 5
done
