#!/usr/bin/python

# Imports coverage list from csv file to MySQL-DB
#
# Usage: python ImportCoverage.py <SampleID> <INPUT.csv>
#
# (C) 2016 Christian Wuensch

import csv
import MySQLdb
from ConfigParser import RawConfigParser
import sys

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

# Open CSV file
f = open(InputFile, 'r')
csvReader = csv.reader(f, delimiter='\t', quotechar='"')

# Read MySQL-defaults
cfgParser = ConfigParser.RawConfigParser()
cfgParser.read("~/.my.cfg")
DBhost=cfgParser.get("client", "host")
DBuser=cfgParser.get("client", "user")
DBpassword=cfgParser.get("client", "password")
DBdatabase=cfgParser.get("client", "database")

# Write to MySQL-DB
con = MySQLdb.connect(DBhost, DBuser, DBpassword, DBdatabase)
cur = con.cursor()

try:
    print "Deleting old entries..."
    cur.execute("DELETE FROM Coverage WHERE SampleID=%s", SampleID)

    print "Writing new entries..."
    next(csvReader, None)  # skip the headers
    for row in csvReader:
        cur.execute("INSERT INTO Coverage(SampleID, chr, pos, cvg) VALUES (%s, %s, %s, %s)", [SampleID, row[0], row[1], row[2]])
        i = i+1
    con.commit()
    print "%d lines imported." % i

except MySQLdb.Error, e:
  
    print "MySQL Error %d: %s" % (e.args[0], e.args[1])
    con.rollback()
    sys.exit(2)

finally:
    if con:
        con.close()
        f.close()

print "Finished."