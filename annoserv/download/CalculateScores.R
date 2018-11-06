options(stringsAsFactors=FALSE)
library(rlist)
library(jsonlite)
library(readxl)
#VariantList2 <- read_excel("Z:/Promotion/SarahsTolleDaten/VariantList.xls")
#VariantList['CosmicSNP'] <- integer(length = nrow(VariantList));

# Hilfs-Funktionen, die im JSON genutzt werden
x = function(name)
{
  VariantList[,name];
}

isEmpty = function(input)
{
  is.na(input) | input=="" | input==" ";
}

stringContains = function(haystack, needle)
{
  grepl(needle, haystack);
}

stringConcat = paste;
strLength = nchar;
parseFloat = as.numeric;


# Anpassen eines Scores, inkl. Ausgabe des Protokoll-Strings
IncreaseScore = function(ResultVector, IncreaseBy, ProtocollStr, ArtiPoly)
{
  if (!is.null(IncreaseBy) && (IncreaseBy != 0) && (IncreaseBy != ""))
  {
    IncreaseVector = ifelse(ResultVector & !is.na(ResultVector), IncreaseBy, 0);
    ProtVector = ifelse(ResultVector & !is.na(ResultVector), paste(IncreaseBy, ProtocollStr, sep=': '), '');
    if (ArtiPoly == 1)
    {
      ArtiScore <<- ArtiScore + IncreaseVector;
      ArtiProt  <<- paste(ArtiProt, ProtVector, sep="|");
    }
    else if (ArtiPoly == 2)
    {
      PolyScore <<- PolyScore + IncreaseVector;
      PolyProt  <<- paste(PolyProt, ProtVector, sep="|");
    }
    else if (ArtiPoly == 3)
    {
      Result <<- ifelse(ResultVector & !is.na(ResultVector), IncreaseBy, Result);
    }
  }
}

# Erzeugen einer Expression aus einem String
makeFuncFromStr = function(str)
{
  str = paste('function(t) {', str, '}');
#  str = gsub('IIF', 'ifelse', str);
#  str = gsub('THEN', ', ', str);
#  str = gsub('ELSE', ', ', str);
  str = gsub('true', 'TRUE', str);
  str = gsub('false', 'FALSE', str);
  str = gsub(':=', '<<-', str);
  str = gsub('\\&\\&', '&', str);
  str = gsub('\\|\\|', '|', str);
  str = gsub('max\\(', 'pmax(', str);
  str = gsub('min\\(', 'pmin(', str);
  for (i in c(3,2,1,0))
    str = gsub(paste('t\\[', i, '\\]', sep=''), paste('t[', i+1, ']', sep=''), str);
  eval(parse(text=str))
}

# Einlesen der JSON-Datei
json=jsonlite::fromJSON('D:/Promotion_neu/CalculateScores.json', simplifyDataFrame = FALSE, simplifyMatrix = FALSE, simplifyVector = FALSE);
Funktionen = list()
for (Kategorie in json)
{
  print(Kategorie['Caption']);
  for (Eintrag in Kategorie[['Entries']])
  {
    SubFunktionen = NULL

    if ((Eintrag[['Type']] == 'AND-Node') || (Eintrag[['Type']] == 'OR-Node'))
    {
      SubFunktionen = list()
      for (SubEintrag in Eintrag[['Entries']])
      {
        SubFunktionen = list.append(SubFunktionen, list(Func=makeFuncFromStr(SubEintrag[['Condition']]), Type=SubEintrag[['Type']], Default=unlist(SubEintrag[['Default']]), ArtiScore=SubEintrag[['ArtiScore']], PolyScore=SubEintrag[['PolyScore']], Result=SubEintrag[['Result']], Prot=SubEintrag[['Protocoll']]));
      }
    }
    Funktionen = list.append(Funktionen, list(Func=makeFuncFromStr(Eintrag[['Condition']]), Type=Eintrag[['Type']], Default=unlist(Eintrag[['Default']]), ArtiScore=Eintrag[['ArtiScore']], PolyScore=Eintrag[['PolyScore']], Result=Eintrag[['Result']], Prot=Eintrag[['Protocoll']], SubEntries=SubFunktionen));
  }
}

# Ausführen der Varianten-Bewertung
AllSamples = 233
ArtiScore  = integer(length = nrow(VariantList));
PolyScore  = integer(length = nrow(VariantList));
Result     = rep('Probably True', nrow(VariantList));
ArtiProt   = character(length = nrow(VariantList));
PolyProt   = character(length = nrow(VariantList));
NrClinicalDBs = integer(length = nrow(VariantList));
NrNonClinicalDBs = integer(length = nrow(VariantList));
NrAnyDBs   = integer(length = nrow(VariantList));
special    = logical(length = nrow(VariantList));
previous   = logical(length = nrow(VariantList));
current    = logical(length = nrow(VariantList));

for (Eintrag in Funktionen)
{
  previous = current;  # hier ist previous der letzte (Sub-)Entry, bzw. bei OR-Node das Oder aller Sub-Entries
#print(Eintrag[['Func']]);
  
  if (Eintrag[['Type']] == 'OR-Node')
  {
    current = logical(length = nrow(VariantList));
    for (SubEintrag in Eintrag[['SubEntries']])
    {
      previous = current;  # hier ist previous das ODER aller bisherigen Sub-Entries
      current = current | SubEintrag[['Func']](SubEintrag[['Default']]);
    }
  }
  else
    current = Eintrag[['Func']](Eintrag[['Default']]);

#print(current[[1]]);
  IncreaseScore(current, Eintrag[['ArtiScore']], Eintrag[['Prot']], 1);
  IncreaseScore(current, Eintrag[['PolyScore']], Eintrag[['Prot']], 2);
  IncreaseScore(current, Eintrag[['Result']], NA, 3);
  
  if (Eintrag[['Type']] == 'AND-Node')
  {
    curAND = current;
    for (SubEintrag in Eintrag[['SubEntries']])
    {
      previous = current;  # hier ist previous der letzte Sub-Entry
#print(SubEintrag[['Func']]);
      current = curAND & SubEintrag[['Func']](SubEintrag[['Default']]);
#print(current[[1]])
      IncreaseScore(current, SubEintrag[['ArtiScore']], SubEintrag[['Prot']], 1);
      IncreaseScore(current, SubEintrag[['PolyScore']], SubEintrag[['Prot']], 2);
      IncreaseScore(current, SubEintrag[['Result']], NA, 3);
    }
  }
}
