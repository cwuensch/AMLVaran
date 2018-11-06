#!/usr/bin/python

"""
HTTP server for SNPeff.

Usage::
    ./SNPeffServ.py

Send a GET request::
    curl http://localhost:8080

Send a HEAD request::
    curl -I http://localhost:8080

Send a POST request::
    curl -d $'1\t1559079\t.\tG\tA' http://localhost:8080

"""

from BaseHTTPServer import BaseHTTPRequestHandler, HTTPServer
from threading import Lock
import shlex, subprocess, time
import json

AminoAcids = {'Arg': 'R', 'His': 'H', 'Lys': 'K', 'Asp': 'D', 'Glu': 'E', 'Ser': 'S', 'Thr': 'T',
              'Asn': 'N', 'Gln': 'Q', 'Cys': 'C', 'Sec': 'U', 'Gly': 'G', 'Pro': 'P', 'Ala': 'A',
              'Val': 'V', 'Ile': 'I', 'Leu': 'L', 'Met': 'M', 'Phe': 'F', 'Trp': 'W', 'Tyr': 'Y'}

def parseEff(effects):
    # EFF = Effect ( Effect_Impact | Functional_Class | Codon_Change | Amino_Acid_Change| Amino_Acid_Length | Gene_Name | Transcript_BioType | Gene_Coding | Transcript_ID | Exon_Rank  | Genotype_Number [ | ERRORS | WARNINGS ] )
    # EFF=missense_variant+splice_region_variant(MODERATE|MISSENSE|Ggc/Cgc|p.Gly198Arg/c.592G>C|1070|MIB2|protein_coding|CODING|ENST00000505820|3|C),
    p = effects.find('EFF=')
    if (p>=0):  effects = effects[p+4:]

    anno = list()

    effects = effects.split(',')
    for effect in effects:      
        values = effect.split("|")
        if (len(values) >= 10):                
            if (("synonymous_variant" not in values[0]) and ("non_coding_transcript_exon_variant" not in values[0]) and (values[0] != "sequence_feature") and (values[0] != "splice_region_variant") and (values[0] != "TF_binding_site_variant")):
                ann = dict()
                AnnoSet = False
                for old in anno:
                    if (old['Transcript'] == values[8]):
                        AnnoSet = True
                        ann = old
                        break;

                typ  = values[0].split('(')[0]
                imp  = values[0].split('(')[1]
                prot = values[3].split('/')[0]
                for j,k in AminoAcids.items():  prot = prot.replace(j, k)
                cod  = ''
                if (values[3].find('/') >= 0):  cod = values[3].split('/')[1]

                if (AnnoSet):
                    ann['varType'] = ann['varType'] + " & " + typ
                    if ((imp == "HIGH") or (imp == "MODERATE" and ann['Impact'] != "HIGH")):  ann['Impact'] = imp
                    if (ann['Protein'] == ''):  ann['Protein'] = prot
                    if (ann['Codon'] == ''):    ann['Codon'] = cod
                else:
                    ann['Transcript'] = values[8]
                    ann['Gene']       = values[5]
                    ann['varType']    = typ
                    ann['Impact']     = imp
                    ann['regionType'] = values[6]
                    ann['Exon']       = int(values[9])
                    ann['AAchange']   = values[2]
                    ann['Protein']    = prot
                    ann['Codon']      = cod
                    anno.append(ann)
    return anno;


class S(BaseHTTPRequestHandler):
    def _set_headers(self):
        self.send_response(200)
        self.send_header('Content-type', 'application/json')
        self.end_headers()

    def do_GET(self):
        self._set_headers()
        self.wfile.write("<html><body><h1>SNPeff server v1.0</h1>Use with POST request (JSON: chr, pos, ref, alt).</body></html>")

    def do_HEAD(self):
        self._set_headers()
        
    def do_POST(self):
        mutex.acquire()
        try:
            content_length = int(self.headers['Content-Length'])  # <--- Gets the size of data
            post_data = self.rfile.read(content_length)           # <--- Gets the data itself
            
            Variants = json.loads(post_data)
            print Variants
            in_str = ''
            for var in Variants:
                if ('chr' in var and 'pos' in var and 'ref' in var and 'alt' in var and var['chr']!='' and int(var['pos'])!=0 and var['ref']!='' and var['alt']!=''):
                    # Inverse Normalization:
                    if ((var['ref'] == '-') or (var['alt'] == '-')):
                        if (var['ref'] == '-'):  var['ref'] = ''
                        if (var['alt'] == '-'):  var['alt'] = ''
                        var['pos'] = int(var['pos']) - 1
                        var['ref'] = 'A' + var['ref']  # A as a Dummy
                        var['alt'] = 'A' + var['alt']  # A as a Dummy
                    in_str = in_str + str(var['chr']) + "\t" + str(var['pos']) + "\t.\t" + var['ref'] + "\t" + var['alt'] + "\n" 
            in_str = in_str + "Z\t0\t.\tA\tA\n"
            print (in_str)
            proc.stdin.write(in_str)

            Variants = list()
            while True:
                out_str = proc.stdout.readline()
                if (out_str[0] == 'Z'):  break
                if (out_str[0] == '#'):  continue
                print(out_str)

                row = out_str.split("\t")
                info_str = row[7]

                if (info_str.find('EFF=') >= 0):
                    anno = parseEff(info_str)
                else:
                    anno = None
                
                # Re-Normalization:
                while (row[3] != '' and row[4] != '' and row[3][0] == row[4][0]):
                    row[3] = row[3][1:]
                    row[4] = row[4][1:]
                    row[1] = int(row[1]) + 1
                    if (row[3] == ''):  row[3] = '-'
                    if (row[4] == ''):  row[4] = '-'
                Variants.append({'chr':row[0], 'pos':int(row[1]), 'ref':row[3], 'alt':row[4], 'anno':anno})

            self._set_headers()
            self.wfile.write(json.dumps(Variants, sort_keys=False))
        finally:
            mutex.release()

def run(server_class=HTTPServer, handler_class=S, port=80):
    server_address = ('', port)
    httpd = server_class(server_address, handler_class)
    print 'Starting httpd...'
    httpd.serve_forever()


if (__name__ == "__main__"):
    mutex = Lock()

    command_line = "java -jar -Xms1g -Xmx4g /opt/snpEff/snpEff.jar ann -formatEff -noInteraction -noMotif -noNextProt -noStats -no-utr -no-downstream -no-upstream -no-intron -no-intergenic -v GRCh37.75 -"
    args = shlex.split(command_line)
    proc = subprocess.Popen(args, stdin=subprocess.PIPE, stdout=subprocess.PIPE, bufsize=1)

    time.sleep(60)

    run(port=8080)
    
    proc.stdin.close()
    proc.stdout.close()
