import java.io.File;
import java.io.FileReader;
import java.io.BufferedReader;
import java.io.FileWriter;
import java.io.BufferedWriter;
import java.io.IOException;
import java.io.FileNotFoundException;
import java.nio.file.Path;
import java.nio.file.Files;

import java.lang.ProcessBuilder;
import java.lang.ProcessBuilder.Redirect;
import java.lang.Process;
import java.lang.InterruptedException;

import java.util.Locale;
import java.util.ArrayList;
import java.util.Map;
import java.util.TreeMap;

class Variant
{
  public String SampleID, chr, ref, alt;
  public int pos;
  public int Cvg, Cvg_fwd, Cvg_rev;
  public int NRref, NRref_fwd, NRref_rev, NRalt, NRalt_fwd, NRalt_rev;
  public double BQref, BQalt;
  public String csvLine;
  
  public Variant(String pSampleID, String pChr, int pPos, String pRef, String pAlt)
  {
    SampleID = pSampleID;
    chr = pChr;
    pos = pPos;
    ref = pRef;
    alt = pAlt;
    Cvg = 0; Cvg_fwd = 0; Cvg_rev = 0;
    NRref = 0; NRref_fwd = 0; NRref_rev = 0; NRalt = 0; NRalt_fwd = 0; NRalt_rev = 0;
    BQref = 0.0; BQalt = 0.0;
  }

  public void normalize()
  {
    while (!ref.equals("") && !alt.equals("") && (ref.charAt(0) == alt.charAt(0)))
    {
      ref = ref.substring(1);
      alt = alt.substring(1);
      pos += 1;
      if (ref.equals("")) ref = "-";
      if (alt.equals("")) alt = "-";
    }
  }  
    
  public String toVCF()
  {
    return String.format("%s\t%d\t.\t%s\t%s", chr, pos, ref, alt);
  }
  public String toString()
  {
    return String.format(Locale.US, "%s;%s;%d;%s;%s;%d;%d;%d;%.2f;%.2f;%d;%d;%d;%d;%d;%d", SampleID, chr, pos, ref, alt, Cvg, NRref, NRalt, BQref, BQalt, NRref_fwd, NRref_rev, NRalt_fwd, NRalt_rev, Cvg_fwd, Cvg_rev);
  }
}

public class QualityTool
{
  public static void main (String[] args)
  {
    Map<String, ArrayList<Variant>> VariantList = new TreeMap<String, ArrayList<Variant>> ();
    System.out.println ("");
    System.out.println ("Variant Quality Analysis Tool v1.0");
    System.out.println ("(c) 2018 Christian Wuensch");
    System.out.println ("");
    
    String inFile    = "";
    String outFile   = "";
    String bamDir    = "";
    String fastaFile = "";
    
    for (int i = 0; i < args.length; i++)
    {
      if (args[i].equals("-f"))  fastaFile = args[++i];
      if (args[i].equals("-b"))  bamDir    = args[++i];
      if (args[i].equals("-i"))  inFile    = args[++i];
      if (args[i].equals("-o"))  outFile   = args[++i];
    }

    // Check parameters
    if (inFile.equals("") || outFile.equals("") || fastaFile.equals("") || bamDir.equals(""))
    {
      System.err.println ("Reads quality metrics (coverage, base quality, strand counts) for reference and alternative allele from bam files.");
      System.err.println ("Input is a csv containing columns 'SampleID', 'chr', 'pos', 'ref', 'alt'.");
      System.err.println ("In the bam-folder a <SampleID>.bam and <SampleID>.bai file must be present.");
      System.err.println ("");
      System.err.println ("Calling the programme:");
      System.err.println ("  java QualityTool -f <path-to-fasta> -b <bam-folder> -i <input> -o <output>");
      System.err.println ("");
      System.exit(1);
    }
    
    try
    {
      // Create temporary folder
      Path tempDir = Files.createTempDirectory("QualTools");
      tempDir.toFile().deleteOnExit();
      System.out.println("TempDir: " + tempDir);
      
      // Read source file
      try (BufferedReader in = new BufferedReader(new FileReader (inFile)))
      {
        // Parse headers
        String line = in.readLine();
        String[] Headers = line.split(";");
        int pSample=0, pChr=1, pPos=2, pRef=3, pAlt=4;
        for (int i = 0; i < Headers.length; i++)
        {
          if     (Headers[i].equals("SampleID"))  pSample = i;
          else if(Headers[i].equals("chr"))       pChr = i;
          else if(Headers[i].equals("pos"))       pPos = i;
          else if(Headers[i].equals("ref"))       pRef = i;
          else if(Headers[i].equals("alt"))       pAlt = i;
        }
        
        // Read variants into HashMap
        while ((line = in.readLine()) != null)
        {
          String[] lineVals = line.split(";");
          Variant curVar = new Variant(lineVals[pSample], lineVals[pChr], Integer.parseInt(lineVals[pPos]), lineVals[pRef], lineVals[pAlt]);
          curVar.normalize();
          curVar.csvLine = line;
          
          if (VariantList.get(curVar.SampleID) == null)
            VariantList.put(curVar.SampleID, new ArrayList<Variant>());
          VariantList.get(curVar.SampleID).add(curVar);
        }        
      }
      catch (FileNotFoundException e)
      {
        System.err.println ("Error: File '" + inFile + "' not found!");
        System.exit(1);
      }
      catch (IOException e)
      {
        System.err.println(e);
      }

      try (BufferedWriter out = new BufferedWriter(new FileWriter (outFile)))      
      {
        int n = 1;
        out.write("SampleID;chr;pos;ref;alt;Cvg;NRref;NRalt;BQref;BQalt;NRref_fwd;NRref_rev;NRalt_fwd;NRalt_rev;Cvg_fwd;Cvg_rev" + System.lineSeparator()); 
        
        // For each Sample
        for (Map.Entry<String, ArrayList<Variant>> entry : VariantList.entrySet())
        {
          String curSample = entry.getKey();
          ArrayList<Variant> VariantSet = entry.getValue();

          System.out.printf("Sample [%d / %d]: %s\n", n, VariantList.size(), curSample);

          // Write the bed file
          File f = new File(tempDir + "/" + curSample + ".vcf");
          f.deleteOnExit();
          try (BufferedWriter bed = new BufferedWriter(new FileWriter (f, true)))
          {
            for (Variant curVar : VariantSet)
            {
              if (curVar.ref.equals("-"))
              {
                Variant temp = new Variant(null, curVar.chr, curVar.pos-1, curVar.ref, ".");
                bed.write(temp.toVCF() + System.lineSeparator());
              }  
              bed.write(curVar.toVCF() + System.lineSeparator());
            }
          }        
          catch (IOException e)
          {
            System.err.println(e);
          }

          // Run BamReadCount
          ProcessBuilder pb = new ProcessBuilder ("java", "-jar", "./BamEvaluator-all-1.0.jar", "-f", fastaFile, "-b", bamDir + "/" + curSample + ".bam", "-t", tempDir.toString() + "/" + curSample + ".vcf");
  //        System.out.println("java" + " -jar" + " ../QualityTool/BamEvaluator/build/libs/BamEvaluator-all-1.0.jar" + " -f" + " ../QualityTool/Homo_sapiens.GRCh37.67.dna.chromosome.all.fasta" + " -b" + " S:/Analyses/Nijmegen_MDS_sequencing/MDS-Triage/Sweden_1/alignment/" + curSample + ".bam" + " -t " + tempDir.toString() + "/" + curSample + ".vcf");
          pb.redirectErrorStream(true);
          pb.redirectOutput(Redirect.INHERIT);
          Process p = pb.start();
          int ret = p.waitFor();

          // Read results
          try (BufferedReader in = new BufferedReader(new FileReader ("output.txt")))
          {
            String line = in.readLine();
            for (Variant curVar : VariantSet)
            {
              if (line != null)
              {
                String alt = curVar.alt;
                if(curVar.alt.equals("-"))  alt = "-" + curVar.ref;
                else if(curVar.ref.equals("-"))  alt = "+" + alt;

                String[] lineVals = line.split("\t");
                if (!(curVar.chr.equals(lineVals[0])) || (Math.abs(Integer.parseInt(lineVals[1]) - curVar.pos) > 1))
                {
                  System.err.printf("Warning!! Variant[sample=%s, chr=%s, pos=%d] does not match Quality[chr=%s, pos=%s].\n", curSample, curVar.chr, curVar.pos, lineVals[0], lineVals[1]);
                  continue;
                }
                
                for (int k = 1; k <= (curVar.ref.equals("-") ? 2 : 1); k++)
                {
                  String ref = lineVals[2];
                  curVar.Cvg = Integer.parseInt(lineVals[3]);

                  for (int i = 4; i < lineVals.length; i++)
                  {
                    String quals[] = lineVals[i].replace(",", ".").split(":");
// System.out.printf("alt: %s, quals: %s, alt[1]: %s, quals[1]: %s, equal: %b, equal1: %b\n", alt, quals[0], alt.substring(0,1), quals[0].substring(0,1), quals[0].equals(alt), quals[0].substring(0,1).equals(alt.substring(0,1)));
                    if (quals[0].equals(ref))
                    {
                      curVar.NRref = Integer.parseInt(quals[1]);
                      curVar.BQref = Float.parseFloat(quals[2]);
                      curVar.NRref_fwd = Integer.parseInt(quals[4]);
                      curVar.NRref_rev = Integer.parseInt(quals[5]);
                    }
                    
                    else if (k <= 1 && (quals[0].equals(alt) || quals[0].substring(0,1).equals(curVar.alt.substring(0,1))))
                    {
                      curVar.NRalt = Integer.parseInt(quals[1]);
                      if (!curVar.alt.equals("-") && !curVar.ref.equals("-"))
                        curVar.BQalt = Float.parseFloat(quals[2]);
                      curVar.NRalt_fwd = Integer.parseInt(quals[4]);
                      curVar.NRalt_rev = Integer.parseInt(quals[5]);
                    }
                    
                    if (k <= 1 && (quals[0].length() == 1))
                    {
                      curVar.Cvg_fwd += Integer.parseInt(quals[4]);
                      curVar.Cvg_rev += Integer.parseInt(quals[5]);
                    }
                  }
                  if (k == 1 && curVar.ref.equals("-"))
                  {
                    line = in.readLine();
                    lineVals = line.split("\t");
                  }
                }
                line = in.readLine();
              }
            }
          }
          catch (FileNotFoundException e)
          {
            System.err.println ("Error: File '" + args[0] + "' not found!");
            System.exit(1);
          }
          catch (IOException e)
          {
            System.err.println(e);
          }

          // Output final results
          for (Variant curVar : VariantSet)
            out.write(curVar.toString() + System.lineSeparator());
          n++;
        }
      }
      catch (IOException e)
      {
        System.err.println(e);
      }
    }
    catch (InterruptedException e)
    {
      System.err.println(e);
    }
    catch (IOException e)
    {
      System.err.println(e);
    }
  }
}
