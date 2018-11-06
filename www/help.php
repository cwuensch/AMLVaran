<?php
    include_once "common/base.php";
    $pageTitle = "Home";
    include_once "common/header.php";

if(isset($_SESSION['LoggedIn']) && isset($_SESSION['Username']) && $_SESSION['LoggedIn']==1) { ?>

<div class="container-fluid">
    <div id="start" class="row row1">
        <div class="col-md-12 column1">
            <h1>Getting started with AML VARAN</h1>
            <p>AML VARAN lets you upload, manage and analyze your patients AML sample data.</p>

            <h3>Anmeldung mit dem Test-Account</h3>
            <p>Klicken Sie oben rechts auf „Sign In“, und melden sich mit folgenden Zugangsdaten an:<br>
            User name (Email): „evaluation“, Password: „AMLtest2016“.</p>

            <h3>Die Patientenübersicht</h3>
            <p>Auf der linken Seite finden Sie eine Übersicht aller bisher angelegter Patienten. Durch Anklicken kann ein Patient ausgewählt werden.</p>
            <p>Daraufhin erscheint auf der rechten Seite eine Übersicht über die Stammdaten des gewählten Patienten und in der darunter befindlichen Liste werden nur noch die für diesen Patienten bereits analysierten Proben aufgelistet. Durch Anklicken können die Untersuchungsergebnisse für eine Probe aufgerufen werden.</p>

            <h3>Clinical Variant Report (Anzeige der Untersuchungsergebnisse)</h3>
            <p>Im oberen Teil der Seite sehen Sie erneut die Stammdaten des gerade betrachteten Patienten.</p>
            <h4>Hotspots</h4>
            <p>Im unteren Teil finden Sie zunächst eine Übersicht der wichtigsten Hotspot-Bereiche, von denen bekannt ist, dass sich darin häufig für AML relevante Mutationen zeigen.</p>
            <p>Zur schnellen Übersicht ist für jeden Bereich durch einen Farbcode angezeigt, ob sich darin relevante Mutationen befinden [blau], oder nicht [weiß], oder ob für diesen Bereich aufgrund schlechter Coverage keine Aussage getroffen werden kann [grau].</p>
            <p>Durch Selektieren eines oder mehrerer Bereiche mit der Maus werden Details zu den darin enthaltenen Mutationen in Form einer Tabelle angezeigt.</p>
            <p>Unterhalb der Übersicht finden Sie eine schnelle Interpretationshilfe, die eine erste grobe Einordnung der therapeutischen Relevanz einiger gefundener Mutationen, basierend auf den modifizierten ELN-Guidelines und den Empfehlungen der WHO liefert.<br>
            Bitte beachten Sie, dass diese Einschätzung lediglich auf der Basis eines automatisierten Algorithmus erfolgt, der niemals die Expertise eines erfahrenen Arztes oder Biologen ersetzen kann! Die Einschätzung erfolgt somit ohne jegliche Gewähr.</p>
            <p>Außer der Hotspots-Übersicht stehen noch 3 weitere Reiter zur Verfügung, die jeweils unterschiedliche Detailansichten bereitstellen:</p>
            <h4>Filtered Variants</h4>
            <p>Hier werden alle von uns als relevant eingestuften Mutationen aufgelistet, die bei der Analyse gefunden wurden. Diese können ggf. auch außerhalb der in der Übersicht gezeigten Hotspot-Bereiche liegen.</p>
            <p>Wichtige Informationen liefern vor allem die Spalten clinvar Significance (die klinische Einschätzung dieser Mutation nach der clinvar-Datenbank) und NrHaemato (wie oft diese Mutation in der COSMIC mit „haematopoietic_and_lymphoid_tissue“ assoziiert wurde).</p>
            <p>Die Tabellendarstellung bietet einige spezielle Funktionen, die die Interpretation der Ergebnisse vereinfachen:</p>
            <ul>
                <li>Ein- und Ausblenden von Spalten:<br>
Verwenden Sie den Knopf „Select Columns“ rechts oberhalb der Tabelle, um eine Liste aller anzeigbarer Spalten einzublenden. Markieren Sie hier dir für Sie relevanten Spalten mit einem Häkchen und klicken Sie zum Übernehmen erneut auf den „Select Columns“ Knopf.</li>
                <li>Sortieren der Tabelle:<br>
Klicken Sie dazu die Überschrift einer Spalte an, nach der sortiert werden soll.
Es ist auch möglich, nach mehreren Spalten zu sortieren, z.B. zuerst nach Chromosom, und dann nach Position. Klicken Sie dazu bitte zuerst die Spalte „chr“ an, halten die Shift-Taste (Großschr.) gedrückt, und klicken dann auf die Spalte „pos“.</li>
                <li>Filtern:<br>
Unsere Vorgaben, welche Varianten als relevant klassifiziert werden, lassen sich (zumindest in gewissem Rahmen) anpassen. Klicken Sie dazu rechts oberhalb der Tabelle auf den Button „Filter“. Hier können verschiedene Optionen angewählt werden, welche Varianten in der Tabelle aufgeführt werden sollen. Zum Übernehmen der Filterungseinstellung klicken Sie bitte auf „Save“.</li>
                <li>Export der Tabelle:<br>
Für jede Tabelle steht (ebenfalls oben rechts) ein Knopf mit der Aufschrift „Download CSV“ zur Verfügung. Klicken Sie diesen an, um die aktuell angezeigte Tabelle, mitsamt den vorgenommenen Filter- und Sortiereinstellungen im .csv-Format (z.B. Microsoft Excel) herunterzuladen.</li>
            </ul>
            <h4>Coverage Analysis</h4>
            <p>Hier sind für jedes untersuchte Gen gewisse Kenngrößen aufgeführt, die eine Aussage über die Coverage in der betrachteten Region treffen.</p>
            <p>Durch Anklicken einer Zeile lassen sich detailliertere Informationen über jede einzelne enthaltene Zielregion anzeigen.</p>
            <h4>Complete Panel</h4>
            <p>Unter diesem Reiter können Sie sich alle gefundenen Varianten (auch Polymorphismen und Synonymous SNPs) in allen untersuchten Regionen anzeigen lassen.</p>
            <p>Wie beim Overview ist auch hier eine Filterung der Liste nach Genen möglich (durch Selektieren eines oder mehrerer Gene mit der Maus). Mit „Select all“ können auch alle Gene auf einmal markiert werden. Zudem bietet die Tabellendarstellung auch hier wieder sämtliche unter „Filtered Variants“ erläuterten Features an.</p>
        </div>
    </div>
</div>
</script>

<?php } else { ?>

<div class="container-fluid">
    <div id="start" class="row row1">
      <div class="col-md-12 column1">
            <h1>Please log in!</h1>
      </div>
    </div>
</div>

<?php } ?>

<?php include_once "common/footer.php"; ?>