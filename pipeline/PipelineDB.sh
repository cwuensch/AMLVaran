#!/bin/bash
MYIP="$( /sbin/ifconfig eth0 | grep 'inet Adresse' | cut -d: -f2 | awk '{print $1}' )"
SCRIPTS="$( dirname "$0" )"

while [ 1 ]
do
  mysql -sNe "UPDATE samples SET Worker='$MYIP' WHERE (StateCode BETWEEN 1 AND 99) AND (Worker='$MYIP' OR Worker IS NULL) ORDER BY Created LIMIT 1"
  NEXTSAMPLE=$(mysql -sNe "SELECT PatientID, SampleID, design, StateCode, Worker FROM samples WHERE (StateCode BETWEEN 1 AND 99) AND Worker='$MYIP' ORDER BY Created LIMIT 1")
  if [ -n "$NEXTSAMPLE" ]; then
    echo $NEXTSAMPLE
    IFS=$'\t' read -ra PARAMS <<< "$NEXTSAMPLE"
    PATIENTID=${PARAMS[0]}
    SAMPLEID=${PARAMS[1]}
    DESIGN=${PARAMS[2]}
    STATE=${PARAMS[3]}
    
    cd $SAMPLEDIR/$PATIENTID/$SAMPLEID    
    mysql -sNe "SELECT chr, start, end FROM tgt_Regions WHERE design='$DESIGN'" > ./Design.bed
    
    $SCRIPTS/Pipeline.sh -b ./Design.bed -s $STATE $PATIENTID $SAMPLEID
  fi
  sleep 5
done
