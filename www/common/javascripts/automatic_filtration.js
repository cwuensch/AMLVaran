var nr_alt = 20;
var nr_alt_reset = nr_alt;
var dp = 50;
var dp_reset = dp;
var vaf = 0.01;
var var_reset = vaf;
var low_bq = 15;
var low_bq_reset = low_bq;
var bq_diff = 7;
var bq_diff_reset = bq_diff;
var nrsamples = 3;
var limit_provean = -4;
var limit_provean2 = -1.5;
var primerpositions = false; //true or false

var minvaf = 0.35;
var midvaf = 0.65;
var maxvaf = 0.85;

var esp6500Threshold = 0.03;
var G1000Threshold = 0.001;
var cosmicNrHaematoThreshold = 20;
var exacThreshold = 0.0005;

//filter for frequency
function filter_frequency (NRalt, DP, VAF, artifact_because) {
    if( NRalt !== " " && DP !== " " && VAF !== " ") { //TODO: != null statt " " ueberall
		if(  NRalt < nr_alt || DP < dp || VAF < vaf ) {
		    artifact_because.exclude = true;
		    artifact_because.lowNRaltDPVAF = true;
		}
	}
}

//filter for low base quality
function filter_lowbasequality (NRref, BQ_ref, BQ_alt, artifact_because) {
    if( BQ_ref !== " " && BQ_alt !== " ") { 
        if(BQ_alt <= low_bq) { //ComplexIndel-query not available for our database
            if(BQ_ref > low_bq) {
                artifact_because.lowbq = true;
            } else {
                if(NRref > 0) {
                    artifact_because.lowbq = true;
                    artifact_because.badalignment = true;
                }
            }
        } else {
            if((BQ_ref-BQ_alt)>= bq_diff) {
                artifact_because.lowbq = true;
            }
        }        
    }
    if(BQ_ref !== " " && NRref > (dp-nr_alt) && BQ_ref <= low_bq) {
        artifact_because.lowbq = true;
    }
}

//filter for databases:
function filter_db_esp(ESP6500, artifact_because){ 
    if(ESP6500 !== " " && ESP6500 !== "NO") {
        artifact_because.NRmutationindatabases += 1;
        if(ESP6500 > esp6500Threshold) {
            artifact_because.NRdatabasethreshold += 1;
        }
    }
}

function filter_db_1000G(G1000, artifact_because){
    if(G1000 !== " " && G1000 !== "NO" && G1000 != 0) {
        artifact_because.NRmutationindatabases += 1;
        if(G1000 > G1000Threshold) {
            artifact_because.NRdatabasethreshold += 1;
        }
    }
//TODO: Kann ESP5600 mehr als einen Eintrag haben, dann noch zusätzliche abfrage nach maximum (Siehe R)    
}

function filter_db_Cosmic(Cosmic, NrHaemato, artifact_because){
    if(Cosmic !== " " && Cosmic !== "NO") {
        //zähle durch komma getrennte einträge ist das gleich NrHaemato??:
        artifact_because.NRmutationindatabases += 1;
        if(NrHaemato > cosmicNrHaematoThreshold) {
            artifact_because.NRdatabasethreshold2 +=1;
        } else {
            artifact_because.NRdatabasethreshold += 1;
        }
    }
}

function filter_db_dbSNP(dbSNP_SNPs, dbSNP_SNVs, PM_flag, artifact_because){
    if(dbSNP_SNPs !== " " && dbSNP_SNPs !== "NO") {
        artifact_because.NRmutationindatabases +=1;
        artifact_because.NRdatabasethreshold +=1;
    }
    if(dbSNP_SNVs !== " " && dbSNP_SNVs !== "NO") {  // kann nicht passieren
        artifact_because.NRmutationindatabases +=1;
        if(PM_flag == 1 || (PM_flag == 0 && (dbSNP_SNPs === " " || dbSNP_SNPs === "NO"))) {
            artifact_because.NRdatabasethreshold2 += 1;
        }
    }
}

function filter_db_ExAC(ExAC, artifact_because){
    if(ExAC !== " " && ExAC !== "NO") {
        artifact_because.NRmutationindatabases += 1;
        if(ExAC > exacThreshold) {
            artifact_because.NRdatabasethreshold += 1;
        }
    }
}

function filter_db_Clinvarclinical(ClinVar_clinical, artifact_because){
    if(ClinVar_clinical && ClinVar_clinical !== " " && ClinVar_clinical !== "NO") { //Changed " " to NO
        artifact_because.NRmutationindatabases += 1;
        artifact_because.NRdatabasethreshold2 += 1;
    }
}

function filter_db_Clinvarcommon(ClinVar_noimpact, artifact_because){
    if(ClinVar_noimpact && ClinVar_noimpact !== " " && ClinVar_noimpact !== "NO") { //Changed " " to NO
        artifact_because.NRmutationindatabases += 1;
        artifact_because.NRdatabasethreshold += 1;
    }
}

//filter for allele frequecy:
//check for frequency when toleraded
function filter_allele_toleradedfreq(VAF, artifact_because) {
    if((VAF >= minvaf && VAF <= midvaf) || VAF >= maxvaf) {
        artifact_because.allelefreq = 1;
    }
}

//large number of samples and high VAF
function filter_allele_lagenumbersamples_highVAF(NRsamples, NRSamplesHighVAF, artifact_because) {
    //NRSamplesHighVAF sind alle VAF-einträge > 0.85
    if(NRsamples > nrsamples && NRSamplesHighVAF > (0.9*NRsamples)) {
        artifact_because.allelefreq += 2;
    }
}
//test for strand bias
function filter_allele_strandbias(Nr_Ref_fwd, Nr_Alt_fwd, Nr_Ref_rev, Nr_Alt_rev, Chr, Ref, artifact_because) {
    if(Nr_Ref_fwd !== " " && Nr_Alt_fwd !== " " && Nr_Ref_rev !== " " && Nr_Alt_rev !== " ") {
        artifact_because.strandbias = fishertest(Nr_Ref_fwd, Nr_Alt_fwd, Nr_Ref_rev, Nr_Alt_rev);
    }    
    /*if(primerpositions == true) {
        if(Ref.length == 1) {
            //TODO: Wie sehen primerpositions.bed aus? Aktuell haben wir keine also auskommentieren
            //Hier werden irgendie die chr und pos mit primer verglichen und wenn 3 true sind dann 
            //artifact_because.strandbias = 2;
        }
    }*/
}

//check for Hotspots:
function filter_hotspots(inHotspot, artifact_because) {//Gene, Mutation, VAF, HotspotGene, HotspotMutation, HotspotMin_VAF, artifact_because) {
    //Mutation entspricht Protein
    //Vergleiche obs einen eintrag gibt mit GENE   
        //splitte bei p. (proteine zählen??)
        //Hier kann es dann sein dass am ende ein * steht. *=alles möglich
        //ref ist nur die erste mutation?? 
        //ref1 sind die ersten 3 zeichen nach p.
        //ref2 sind die letzen 2 zeichen der mut
        //wie wird hier gene mit hotspot verglichen? Tabelle hotspot und wie sind gene aus
        
        //wenn (hotspots[line,3] != null und VAF >=hotspots[line,3]) oder wenn (hotspots[line,3] == null):
        //artifact_because.hotspot = 2;
        // return da man sich dann das flag spart
    
        //nächster if vergleich artifact_because.hotspot = 1;
        //Vereinfacht:
        if(inHotspot !== " " && inHotspot > 0) {
            artifact_because.hotspot = true;
        }
}

//Complex Filtration
//NRsamplesSamePos: vergleicht nur, ob chromosome und Position gleich sind. bei NRsamples ist chr, pos, ref und alt gleich.
function complex_filtration(NRsamples, NRsamplesSamePos, nrsamples_high, Ref, Alt, VAF, Nr_Alt_fwd, Nr_Ref_fwd, Nr_Alt_rev, Nr_Ref_rev, Called, PM_flag, LoFreq, FreeBayes, VarDict, mut_type, Provean_Score, NrHaemato, artifact_because){
    var category = "no category found"; //the category that will be returned by this function.
    var test1 = "";
    var test2 = "";
    
    //calculate artifact_score
    if(NRsamples > nrsamples) {
        artifact_because.artifact_score += 2;
        artifact_because.poly_score += 1;
        test1 = test1 + "1, ";
        test2 = test2 + "1, ";
    }
    if(NRsamples > nrsamples_high && artifact_because.hotspot == false) {
            artifact_because.artifact_score += 2;
            test2 = test2 + "2, ";
    }
    if((Ref == "-" || Ref.length > 1 ) || (Alt == "-" || Alt.length > 1)) {
        if(NRsamplesSamePos > NRsamples) {
            artifact_because.artifact_score += 1;
            test2 = test2 + "3, ";
        }
        if(VAF != " " && VAF < 0.05) {
            artifact_because.artifact_score += 1;
            test2 = test2 + "4, ";
        }
    }
    if(artifact_because.allelefreq >= 2) {
        artifact_because.artifact_score += 2;
        test2 = test2 + "5, ";
    }

 //   if(primerpositions == false || artifact_because.strandbias != 2) { 
 	if(!isNaN(artifact_because.strandbias)) {
        if(artifact_because.strandbias < 0.001) {
            artifact_because.artifact_score += 1;
            test2 = test2 + "6, ";
     //TODO: (Nr_Alt_fwd !== " " && Nr_Alt_rev !== " ") als vorherige abfrage       
            if((Nr_Alt_fwd !== " " && Nr_Alt_rev !== " ") && (Nr_Alt_fwd >= (nr_alt/2) && Nr_Alt_rev >= (nr_alt/2))) {
                artifact_because.artifact_score -= 1;
                test2 = test2 + "7, ";
            }
            if((Nr_Alt_fwd !== " " && Nr_Ref_fwd !== " ") && (Nr_Alt_fwd <= 2 && Nr_Ref_fwd < ((dp-nr_alt)/2))) {
                artifact_because.artifact_score -= 1;
                test2 = test2 + "8, ";
            }
            if((Nr_Alt_rev !== " " && Nr_Ref_rev !== " ") && (Nr_Alt_rev <= 2 && Nr_Ref_rev < ((dp-nr_alt)/2))) {
                artifact_because.artifact_score -= 1;
                test2 = test2 + "9, ";
            }
            
        }
        if(artifact_because.strandbias >= 0.001) {
            if((Nr_Alt_fwd !== " " && Nr_Ref_fwd !== " ") && (Nr_Alt_fwd <= 2 && Nr_Ref_fwd >= ((dp-nr_alt)/2))) {
                artifact_because.artifact_score += 1;
                test2 = test2 + "10, ";
            }
            if((Nr_Alt_rev !== " " && Nr_Ref_rev !== " ") && (Nr_Alt_rev <= 2 && Nr_Ref_rev >= ((dp-nr_alt)/2))) {
                artifact_because.artifact_score += 1;
                test2 = test2 + "11, ";
            }
        }
	}
//    } else { //if(primerpositions == true && artifact_because.strandbias == 2)
//        artifact_because.artifact_score -= 1;
//          test2 = test2 + "12, ";
//    }
    if(VAF !== " " && VAF < 0.02) {
        artifact_because.artifact_score += 2;
        test2 = test2 + "13, ";
    }
    if(artifact_because.NRmutationindatabases == 0) {
        if(VAF !== " " && VAF < 0.1) {
            artifact_because.artifact_score += 1;
            test2 = test2 + "14, ";
        }
        if(NRsamples > nrsamples_high) {
            artifact_because.artifact_score += 1;
            test2 = test2 + "15, ";
        }
    }

    if(Provean_Score !== " ") {
        if(Provean_Score < limit_provean) {
            artifact_because.artifact_score -= 1;
            test2 = test2 + "16, ";
        }
        if(Provean_Score > limit_provean2 && artifact_because.allelefreq == 0) {
            artifact_because.artifact_score += 1;
            test2 = test2 + "17, ";
        }
    }

    if(Called <= 1) {
        artifact_because.artifact_score += 1;
        test2 = test2 + "18, ";
    } else if(Called >= 4) {
        artifact_because.artifact_score -= 1;
        test2 = test2 + "19, ";
        if(Called >= 5) {
            artifact_because.artifact_score -= 1;
            test2 = test2 + "20, ";
            if(Called >= 6) {
                artifact_because.artifact_score -= 1;
                artifact_because.poly_score += 1;
                test1 = test1 + "2, ";
                test2 = test2 + "21, ";
            }
        }
    }
    if(artifact_because.lowbq == true) {
        artifact_because.artifact_score += 4;
        test2 = test2 + "22, ";        
    }
    if(PM_flag == 1 && artifact_because.hotspot == false) { //PM_flag "YES" ?
        artifact_because.artifact_score -= 1;
        test2 = test2 + "23, ";
    }
    if(artifact_because.hotspot == true) {
        artifact_because.artifact_score -= 3;
        test2 = test2 + "24, ";
    }
    if(LoFreq == true && FreeBayes == true && VarDict == true) {
        artifact_because.artifact_score -= 3;
        test2 = test2 + "25, ";
    }
    //evaluation of artifact_score
    if(artifact_because.artifact_score >= 0) {
        category = 'Artifact (' + artifact_because.artifact_score + ')';
    } else {
        if(artifact_because.hotspot == false) {
            category = 'Probably True (' + artifact_because.artifact_score + ')';
        } else {
            category = 'Hotspot (' + artifact_because.artifact_score + ')';
        }
    }
    //console.log(category);
    //calculate poly_score
    //teilweise in ifabfragen von artifact_score integriert.    
    var cosmic_flag = false;
    
    if(NRsamples == 1) {
        artifact_because.poly_score -= 1;
        test1 = test1 + "3, ";
    }
    if(artifact_because.NRdatabasethreshold >= 2) {
        artifact_because.poly_score += 1;
        test1 = test1 + "4, ";
        if(artifact_because.NRdatabasethreshold >= 4) {
            artifact_because.poly_score += 1;
            test1 = test1 + "5, ";
        }
    } else if(artifact_because.NRdatabasethreshold == 0) {
        artifact_because.poly_score -= 1;
        test1 = test1 + "6, ";
    }
    if(artifact_because.NRdatabasethreshold2 >= 2) {
        artifact_because.poly_score -= 1;
        test1 = test1 + "7, ";
    }
    if(mut_type !== " " && mut_type.indexOf('inframe') >= 0 && mut_type.indexOf('stop_gained') == -1) { //indexOf gibt position des erstens passenden strings im String an. Wenn kein match, dann -1.
        artifact_because.poly_score += 1;
        test1 = test1 + "8, ";
    }
    if(artifact_because.allelefreq == 1 || artifact_because.allelefreq == 3) {
        artifact_because.poly_score += 1;
        test1 = test1 + "9, ";
    }
    if(Provean_Score !== " " && Provean_Score >= -2.5 && Provean_Score >= limit_provean2) {
        artifact_because.poly_score += 1;
        test1 = test1 + "10, ";
    }
    if((Provean_Score < -2.5 && Provean_Score <= limit_provean) || (mut_type !== " " && mut_type.indexOf('stop_gained') >= 0)) {
        artifact_because.poly_score -= 1;   
        test1 = test1 + "11, ";
    }
    if(PM_flag == 1) { //YES?
        artifact_because.poly_score -= 2;
        test1 = test1 + "12, ";
    }
    if(NrHaemato > 100) { //NrHaemato entspicht [i,9]
        cosmic_flag = true;
    }

    //evaluation of poly_score:
    if(artifact_because.hotspot == false) {
        if(artifact_because.poly_score >= 2 && cosmic_flag == true) {
            category = 'Likely Polymorphism';
        }
        if((artifact_because.poly_score >= 2 && cosmic_flag == false) || artifact_because.poly_score >= 3) {
            category = 'Polymorphism';
        }
        //console.log(category);
        /*if((artifact_because.poly_score >= 2 && cosmic_flag == true) //ist das nicht das gleich, wie if(poly_score >= 2) {}?????
           || ((artifact_because.poly_score >= 2 && cosmic_flag == false) || artifact_because.poly_score >= 3)) {*/
        if(artifact_because.poly_score >= 2) {
            var flag = false;
            if(VAF !== " " && VAF <= 0.1) {
                flag = true;
                artifact_because.artifact_score += 5;
                test2 = test2 + "30, ";
            }
          	if(VAF !== " " &&VAF <= 0.2) {
                flag = true;
     			      artifact_because.artifact_score += 2;
                test2 = test2 + "31, ";
       	   	}
            if(mut_type !== " " && mut_type.indexOf('frameshift') >= 0) { 
                flag = true;
                artifact_because.artifact_score += 2;
                test2 = test2 + "32, ";
            }

            if(flag) {
                if(artifact_because.artifact_score > -1) {
                    category = '2Artifact (' + artifact_because.artifact_score + ')';
                } else {
                    if(artifact_because.hotspot == false) {
                        category = '2Probably True (' + artifact_because.artifact_score + ')';
                    } else {
                        category = '2Hotspot (' + artifact_because.artifact_score + ')';
                    }
                }
            }
        }
    }
    return category + " (" + test2 + ")";
}
