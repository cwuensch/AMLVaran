#!/usr/bin/python

# Imports coverage list from csv file to MySQL-DB
#
# Usage: python ImportCoverage.py <SampleID> <INPUT.csv>
#
# (C) 2016 Christian Wuensch

import csv
import MySQLdb
#from ConfigParser import RawConfigParser
import sys
import os

print "ImportCoverage 1.0"
print "(C) 2016 Christian Wuensch"
print ""

# Get command line arguments
if len(sys.argv) > 2:
    InputFile = sys.argv[2]
    SampleID = sys.argv[1]
else:
    print "Usage: python " + sys.argv[0] + " <SampleID> <INPUT.csv>"
    sys.exit(-1)

print "Importing coverage for SampleID " + SampleID + " from " + InputFile + "..."
i = 0
l = 0

# Open CSV file
f = open(InputFile, 'r')
csvReader = csv.reader(f, delimiter='\t', quotechar='"')

# Read MySQL-defaults
#cfgParser = RawConfigParser()
#cfgParser.read("/root/.my.cnf")
#DBhost=cfgParser.get("client", "host")
#DBuser=cfgParser.get("client", "user")
#DBpassword=cfgParser.get("client", "password")
#DBdatabase=cfgParser.get("client", "database")
DBhost=os.environ['MYSQL_HOST']
DBuser=os.environ['MYSQL_USER']
DBpassword=os.environ['MYSQL_PASSWORD']
DBdatabase=os.environ['MYSQL_DATABASE']

Targets = {}

# Write to MySQL-DB
con = MySQLdb.connect(DBhost, DBuser, DBpassword, DBdatabase)
cur = con.cursor()

try:
    print "Getting relevant regions..."
    cur.execute("SELECT chr, start, end FROM tgt_KnownMutations order by chr, start, end")
    rows = cur.fetchall()
    for row in rows:
        chrom = row[0]
        start = int(row[1])
        end   = int(row[2])
        if not chrom in Targets:
            Targets[chrom] = []
        if not [start, end] in Targets[chrom]:
            Targets[chrom].append([start, end])
            
#    for key in Targets:
#        for tgt in Targets[key]:
#           print "chr %s: %d - %d" % (key, tgt[0], tgt[1])            

    print "Deleting old entries..."
    cur.execute("DELETE FROM Coverage WHERE SampleID=%s", [SampleID])

    print "Writing new entries..."
    next(csvReader, None)  # skip the headers
    
    for row in csvReader:
        chrom = row[0]
        pos   = int(row[1])
        cvg   = int(row[2])
        if chrom in Targets:
            for tgt in Targets[chrom]:
                if (pos >= tgt[0] and pos <= tgt[1]):
                    cur.execute("INSERT INTO Coverage(SampleID, chr, pos, cvg) VALUES (%s, %s, %s, %s)", [SampleID, chrom, pos, cvg])
                    i = i+1
                    break
        l = l+1
    con.commit()
    print "%d of %d lines imported." % (i, l)

except MySQLdb.Error, e:
  
    print "MySQL Error %d: %s" % (e.args[0], e.args[1])
    con.rollback()
    sys.exit(2)

finally:
    if con:
        con.close()
        f.close()

print "Finished."