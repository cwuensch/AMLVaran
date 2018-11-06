#!/usr/bin/python

# Converts indels from varscan to basic format
#
# Usage: python Convert.py <INPUT.txt> <OUTPUT.txt>
#
# (C) 2017 Christian Wuensch

import csv
import sys

print "Convert varscan to basic"
print "(C) 2017 Christian Wuensch"
print ""

# Get command line arguments
if len(sys.argv) > 2:
    InputFile = sys.argv[1]
    OutputFile = sys.argv[2]
else:
    print "Usage: python " + sys.argv[0] + " <INPUT.txt> <OUTPUT.txt>"
    sys.exit(-1)

print "Converting " + InputFile + " to " + OutputFile + "..."
i = 0

# Open CSV file
f = open(InputFile, 'r')
csvReader = csv.reader(f, delimiter='\t', quotechar='"')

# Open output file
o = open(OutputFile, 'w')
csvWriter = csv.writer(o, delimiter='\t', quotechar='"')

try:
    for row in csvReader:
        if (row[3][0] == '+'):
            row[3] = row[2] + row[3][1:]
        elif (row[3][0] == '-'):
            temp = row[3][1:]
            row[3] = row[2]
            row[2] = row[2] + temp
    
        csvWriter.writerow(row)
        i = i+1
    print "%d lines converted." % i

except e:
    print "Error %d occured: %s" % (e.args[0], e.args[1])
    sys.exit(2)

finally:
    o.close()
    f.close()

print "Finished."