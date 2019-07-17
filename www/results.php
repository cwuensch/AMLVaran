<?php
    include_once "common/base.php";
    $pageTitle = "Patient overview";
    include_once "common/header.php";

    if(isset($_GET['pid']) || isset($_GET['sid'])){

        if(isset($_SESSION['LoggedIn']) && isset($_SESSION['Username']) && $_SESSION['LoggedIn']==1){ ?>

    <link href="common/stylesheets/reportstylesheet.css" rel="stylesheet" />
    <link href="common/stylesheets/jquery-ui.min.css" rel="stylesheet" />
    <link href="common/stylesheets/pileup.css" rel="stylesheet" />
    <link href="common/stylesheets/bootstrap-datepicker3.min.css" rel="stylesheet" />
    <link href="common/stylesheets/appreci8Filter.css" rel="stylesheet" />

    <div class="container-fluid">
        <div class="row row1">
            <div class="col-md-12 columnwide">
                <div>
                    <a href="patients.php">Patients</a>
                    <span style="display:inline-block; width: 15px;"></span>•<span style="display:inline-block; width: 15px;"></span>
                    <div id="patientslist" class="dropdown" style="display: inline;"></div>
                    <span style="display:inline-block; width: 15px;"></span>•<span style="display:inline-block; width: 15px;"></span>
                    <div id="sampleslist" class="dropdown" style="display: inline;"></div>
                </div>
                <div id="errorpanel" class="alert alert-danger" role="alert" style="display:none;">
                    <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
                    <b>Error:</b><br>
                </div>

                <h3 style="text-align:center; margin-top:0px;">Clinical Variant Report</h3>
                <p class="warning">This is not a medical product. Use only for research purposes!</p>
            </div>
        </div>
        <div class="row row1">
            <div class="col-md-12 columnwide">

                <div id="panels">
                    <ul class="nav nav-tabs">
                        <li><a tab="info" style="cursor:pointer;" data-toggle="popover">Sample Info</a></li>
                        <li><a tab="hotspots" style="cursor:pointer;" data-toggle="popover">Hotspots</a></li>
                        <li><a tab="completepanel" style="cursor:pointer;" data-toggle="popover">Variant Inspector</a></li>
                        <li><a tab="genomebrowser" style="cursor:pointer;" data-toggle="popover">Genome Browser</a></li>
                        <div id="columnchooser"></div>

                        <div id='report' tab='report' data-toggle='dropdown'>Assessment by pathologist <span class="glyphicon glyphicon-chevron-up"></span></div>
                    </ul>

                    <div id='personalReport' style='display:none;'>
                        <p><b>Assessment by pathologist:</b></p>
                        <form>
                            <textarea id='reportTextarea' class='form-control' style='width: 100%; max-width: 100%;' placeholder='Write down additional informations about this sample.'></textarea>
                        </form>
                    </div>
                    <div id="mainloader" style="display:none; height:400px;"><img src="common/ajax-loader.gif" style="display:block; margin:auto;" /></div>
                    <div id="filterPanel" class="mainpanelResults"></div>
                    <div id="hotspotsPanel" class="mainpanelResults" style="display: none;"></div>
                    <div id="showInfoPanel" class="mainpanelResults" style="display: none;"></div>
                    <div id="genomeBrowserPanel" class="mainpanelResults" style="display: none;"></div>
                    <div id="additionalpanels"></div>
                </div>
            </div>

            <?php           } else { ?>
            <div class="container-fluid">
                <div class="row row1">
                    <div class="col-md-12 column1">
                        <h1>Please log in!</h1>
                        <?php } ?>
                    </div>
                </div>
            </div>
            <?php } else {
        echo '<meta http-equiv="refresh" content="0;index.php">';
    }

    include_once 'common/footer.php';

    if(isset($_SESSION['LoggedIn']) && isset($_SESSION['Username']) && $_SESSION['LoggedIn']==1){ ?>

            <script src="common/javascripts/bootstrap-datepicker.js"></script>
            <script type="text/javascript" src="common/javascripts/tablesorter/jquery.tablesorter.js"></script>
            <script type="text/javascript" src="common/javascripts/tablesorter/jquery.tablesorter.widgets.js"></script>
            <script type="text/javascript" src="common/javascripts/tablesorter/widgets/widget-columnSelector.js"></script>
            <script type="text/javascript" src="common/javascripts/pileup.min.js"></script>
            <script type="text/javascript" src="common/javascripts/jquery-ui.min.js"></script>
            <!--script type="text/javascript" src="common/javascripts/dalliance-compiled.js"></script-->
            <script type="text/javascript" src="common/javascripts/dalliance/dalliance-all.js"></script>
            <script type="text/javascript" src="common/javascripts/fishertest.js"></script>
            <!--script type="text/javascript" src="common/javascripts/automatic_filtration.js"></script-->

            <script type="text/javascript" src="common/javascripts/jquery.tabletojson.min.js"></script>


            <!-- Appreci8 calculation and GUI -->
            <script type="text/javascript" src="common/Interpreter.js"></script>


            <!-- jQuery UI CSS -->
            <link rel="stylesheet" type="text/css" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.2/themes/smoothness/jquery-ui.css" />

            <!-- Font Awesome CSS -->
            <link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" />

            <!-- IGV CSS -->
            <link rel="stylesheet" type="text/css" href="common/stylesheets/igv-1.0.9.css">


            <!-- jQuery JS -->
            <!--script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script-->

            <!-- Bootstrap JS - for demo only, NOT REQUIRED FOR IGV ->
            <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
            
            <!-- IGV JS -->
            <script type="text/javascript" src="common/javascripts/igv-1.0.9.js"></script>
            <!--script type="text/javascript" src="common/javascripts/igv-1.0.9.min.js"></script-->
            <!--script type="text/javascript" src="common/javascripts/igv-beta.min.js"></script-->



            <script>
                //NOTE DEFAULT VALUES
                var test;
                var additionalPanelCallId = 0;
                var regionFilterDefault = 1;
                var typeFilterDefault = 2;
                var exclusionFiltersDefault = '["artifacts","polymorphisms"]';
                var selectedColumnsDefault = [0, 3, 4, 5, 6, 7, 8, 10, 11, 12, 13, 14, 15, 16, 18, 20, 21, 22, 23, 24, 25, 32, 35, 36, 37, 144, 145, 146, 147, 148, 149, 150, 151, 152, 153, 154, 158, 159, 162, 163, 166, 168, 169, 170, 171, 172];
                var selectedColumnsMinimum = [2, 3, 4, 5, 6];
                var sortListDefault = "[[3,0],[4,0]]";
                var columnWidthDefault = "['29px','86px','116px','48px','77px','78px','56px','71px','133px','156px','187px','132px','64px','91px','84px','103px','122px','116px','97px','121px','51px','61px','61px','62px','66px','63px','90px','90px','88px','90px','85px','139px','81px','83px','86px','91px','121px','98px','158px','107px','117px','201px','144px','230px','236px','180px','192px','193px','202px','205px','179px','205px','213px','175px','188px','196px','103px','160px','257px','267px','163px','179px','205px','209px','183px','249px','255px','182px','138px','223px','156px','165px','178px','136px','142px','154px','163px','119px','143px','147px','129px','131px','140px','140px','112px','140px','149px','148px','152px','148px','164px','170px','86px','164px','107px','132px','200px','229px','241px','207px','210px','213px','105px','118px','131px','184px','182px','220px','221px','251px','262px','209px','249px','253px','207px','250px','253px','202px','235px','250px','191px','104px','105px','189px','191px','260px','257px','265px','280px','282px','280px','284px','169px','239px','634px','114px','162px','193px','201px','168px','203px','129px','137px','148px','90px','93px','82px','91px','90px','223px','215px','290px','107px','258px','106px','151px','152px','128px','71px','96px','150px','156px','85px','114px','113px','159px','67px','317px','72px','116px','107px','326px','291px','116px','123px','123px']";
                var clickedRowBorderColour = "rgb(72, 86, 226)"; //only use RGB values!!!! Otherwise equality test will fail!

                var chosentab;
                var pid;
                var sid;
                var version;
                var sampledata = new Array();
                var patientdata = new Array();
                var pdfreports;
                var selectedpdfversion;
                var tabledata = {};
                var lastActivatedTableRow;
                var nosamples = false;
                var reportTextareaChanges = false;
                var designID;


                var lastUsedLine = -1; //needed for appreci8 calculation and function x(...)
                var oldScrollbarPositionLeft = 0; //Workaround: scroll left->Fix the columnn width ->scroll right

                var additionalPanelWidth;
                var hiddenPopover = true; //remember that the user already closes this popover, so we need to show it again if the user presses this button a third time



                var columnindices = new Object(); //for completepanelsubtable
                var columnindices_other = new Object(); //for all other tables

                var igvBpWidth = 250;
                var pileuprange = {
                    chr: 'chr5',
                    start: 170837294, //170837524,
                    stop: 170837794 //170837564
                };

                var pageloadingflag = true;
                //complete panel variables
                var scrollposition = 0;
                var selectedrow = 0;
                var genefilterselected = new Array();
                var exclusionfilterselected = new Array();
                var saveSettingsFlag = false;
                var loadedSettings = null;


                var firstLoad = true;
                var versionFromDB = false;
                var genomeBrowserChange = true;
                var genomeBrowserNew = true;
                var newVersion; //true: new Version ; false: existing version=current version
                var igvbrowser;

                var editsample = false;
                var editpatient = false;
                var bamSource;
                var vcfSource;

                var progressbarstatus = 0;
                var progressbarWaitTime = 400; //We need to wait some time, so the browser has time to repaint the website. Otherwise the user won't see the progress.

                var calculateScoresFunctions = {};
                var VAFmin1 = 0;
                var VAFmin2 = 65;
                var VAFmax1 = 35;
                var VAFmax2 = 85;

                var AllSamples;
                var actCompletePanelTableRow = null;
                var NrNonClinicalDBs;
                var NrClinicalDBs;
                var NrAnyDBs;
                var ArtiScore;
                var PolyScore;
                var previous;
                var Result;

                var debug = true; //set to false to hide all console.log lines

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
                

                //--- INITIAL FUNCTION ---//
                $(document).ready(function() { //NOTE document ready start



                    $('#navPatients').addClass('active');

                    <?php if(isset($_GET['pid'])) echo 'pid = ' . $_GET['pid'] . ';'; ?>;
                    <?php if(isset($_GET['sid'])) echo 'sid = ' . $_GET['sid'] . ';'; ?>
                    <?php if(isset($_GET['version'])) echo 'version = ' . $_GET['version'] . ';'; ?>
                    $.ajax({
                        type: "POST",
                        url: "sampleinteraction.php",
                        data: "action=getRating&sid=" + sid,
                        dataType: 'json',
                        success: function(ret) {
                            if (debug) console.log("success case ajax 1");
                            if (ret[0] == 1) {
                                $('#reportTextarea').val(ret[1]['Rating']);
                                $('#personalReport').show();
                            } else {
                                displayError(ret[1]);
                            }
                        },
                        error: function(xhr, status, errorThrown) {
                            displayError(errorThrown + "[2]");
                        }
                    });

                    $.ajax({
                        type: "POST",
                        url: "sampleinteraction.php",
                        data: "action=getAllSamples",
                        dataType: 'json',
                        success: function(ret) {
                            if (debug) console.log("success case ajax 2");
                            if (ret[0] == 1) {
                                AllSamples = ret[1]['count(*)'];
                            } else {
                                displayError(ret[1] + "[3]");
                            }
                        },
                        error: function(xhr, status, errorThrown) {
                            displayError(errorThrown + "[4]");
                        }
                    });



                    if (isNaN(version)) {
                        $.ajax({
                            type: "POST",
                            url: "sampleinteraction.php",
                            data: "action=getCurrentVersion&sid=" + sid,
                            dataType: 'json',
                            success: function(ret) {
                                if (ret[0] == 1) {
                                    version = parseInt(ret[1]);
                                    versionFromDB = true;

                                    if (typeof sid != 'undefined') {
                                        $.ajax({
                                            type: "POST",
                                            url: "patientinteraction.php",
                                            data: "action=getPatientBySid&sid=" + sid,
                                            dataType: 'json',
                                            success: function(ret) {
                                                if (debug) console.log("success case ajax 3");
                                                if (ret[0] == 1) {
                                                    pid = ret[1]['PatientID'];
                                                    patientdata = ret[1];
                                                    preparePatientslist();
                                                    prepareSampleslist();
                                                } else {
                                                    displayError(ret[1] + " [5]");
                                                    if (debug) console.log("[5]: " + ret[1]);
                                                    if (debug) console.log(ret[1] + "<- ret(1)");
                                                    if (ret[1] == "The requested patient does not exist or you are not allowed to access this patient.") { //only show the error message
                                                        $('h3').empty();
                                                        $('#panels').empty();
                                                    }
                                                }
                                            },
                                            error: function(xhr, status, errorThrown) {
                                                $('h3').empty();
                                                $('#panels').empty();
                                                displayError(errorThrown + "[6]");
                                            }
                                        });
                                    } else {
                                        $.ajax({
                                            type: "POST",
                                            url: "patientinteraction.php",
                                            data: "action=getPatient&pid=" + pid,
                                            dataType: 'json',
                                            success: function(ret) {
                                                if (debug) console.log("success case ajax 4");
                                                if (ret[0] == 1) {
                                                    patientdata = ret[1];
                                                    preparePatientslist();
                                                    prepareSampleslist();
                                                } else {
                                                    displayError(ret[1] + "[7]");
                                                }
                                            },
                                            error: function(xhr, status, errorThrown) {
                                                displayError(errorThrown + "[8]");
                                            }
                                        });
                                    }

                                } else {
                                    displayError(ret[1] + "[9]");
                                }
                            },
                            error: function(xhr, status, errorThrown) {
                                displayError(errorThrown + "[10]");
                            }
                        });
                    } else {
                        if (typeof sid != 'undefined') {
                            $.ajax({
                                type: "POST",
                                url: "patientinteraction.php",
                                data: "action=getPatientBySid&sid=" + sid,
                                dataType: 'json',
                                success: function(ret) {
                                    if (debug) console.log("success case ajax 5");
                                    if (ret[0] == 1) {
                                        pid = ret[1]['PatientID'];
                                        patientdata = ret[1];
                                        preparePatientslist();
                                        prepareSampleslist();
                                    } else {
                                        displayError(ret[1] + "[11]");
                                        if (ret[1] == "The requested patient does not exist or you are not allowed to access this patient.") { //only show the error message
                                            $('h3').empty();
                                            $('#panels').empty();
                                        }
                                    }
                                },
                                error: function(xhr, status, errorThrown) {
                                    displayError(errorThrown + "[12]");
                                    $('h3').empty();
                                    $('#panels').empty();
                                }
                            });
                        } else {
                            $.ajax({
                                type: "POST",
                                url: "patientinteraction.php",
                                data: "action=getPatient&pid=" + pid,
                                dataType: 'json',
                                success: function(ret) {
                                    if (debug) console.log("success case ajax 6");
                                    if (ret[0] == 1) {
                                        patientdata = ret[1];
                                        preparePatientslist();
                                        prepareSampleslist();
                                    } else {
                                        displayError(ret[1] + "[13]");
                                    }
                                },
                                error: function(xhr, status, errorThrown) {
                                    displayError(errorThrown + "[14]");
                                }
                            });
                        }

                    }

                    $('[tab="info"]').popover({
                        delay: {
                            "show": 1000,
                            "hide": 100
                        },
                        trigger: 'hover',
                        container: 'body',
                        placement: 'top',
                        html: 'true',
                        title: 'Info Tab',
                        content: 'Information on the current sample, such as patient data, modality, reference values....'
                    });
                    $('[tab="hotspots"]').popover({
                        delay: {
                            "show": 1000,
                            "hide": 100
                        },
                        trigger: 'hover',
                        container: 'body',
                        placement: 'top',
                        html: 'true',
                        title: 'Hotspots Tab',
                        content: 'Overview of known, disease-associated hotspot regions and their mutation status in the examined sample. A detailed description of the selected regions and their implicance for therapy is included.'
                    });
                    $('[tab="completepanel"]').popover({
                        delay: {
                            "show": 1000,
                            "hide": 100
                        },
                        trigger: 'hover',
                        container: 'body',
                        placement: 'top',
                        html: 'true',
                        title: 'Variant Inspector Tab',
                        content: 'Outputs all variants that have been found in the sample and offers various possibilities for dynamic filtering. A detailed view provides a broad overview of annotation and literature information for each variant.'
                    });
                    $('[tab="genomebrowser"]').popover({
                        delay: {
                            "show": 1000,
                            "hide": 100
                        },
                        trigger: 'hover',
                        container: 'body',
                        placement: 'top',
                        html: 'true',
                        title: 'Genome Browser Tab',
                        content: 'Allows live inspection of raw data in a read alignment.'
                    });

                    //indexOf Method for IE8
                    if (!Array.prototype.indexOf) {
                        Array.prototype.indexOf = function(elt /*, from*/ ) {
                            var len = this.length >>> 0;

                            var from = Number(arguments[1]) || 0;
                            from = (from < 0) ?
                                Math.ceil(from) :
                                Math.floor(from);
                            if (from < 0)
                                from += len;

                            for (; from < len; from++) {
                                if (from in this &&
                                    this[from] === elt)
                                    return from;
                            }
                            return -1;
                        };
                    }


                    //NOTE document ready end
                });




                //FUNCTION preparePatientslist
                function preparePatientslist() {
                    $.ajax({
                        type: "POST",
                        url: "patientinteraction.php",
                        data: "action=getPatients",
                        dataType: 'json',
                        success: function(ret) {
                            if (debug) console.log("success case ajax 14");
                            if (ret[0] == 1) {
                                var patients = ret[1];
                                var patientsHTML = '';
                                if (patients.length > 0) {
                                    patientsHTML = '<a href="#" class="dropdown-toggle" id="patientslisttext" data-toggle="dropdown">' + patientdata['Patientname'] + ' <span class="caret"></span></a><ul class="dropdown-menu scrollable-menu">';

                                    $.each(patients, function(i, item) {
                                        patientsHTML += '<li PatientID="' + item['PatientID'] + '" Patientname="' + item['Patientname'] + '"';
                                        if (item['PatientID'] == pid) patientsHTML += ' class="chosenli" ';
                                        patientsHTML += '><a href="#">' + item['Patientname'] + '</a></li>';
                                    });

                                    patientsHTML += '</ul>';

                                } else {
                                    displayError('<p>Sorry, it seems like there are no patients yet.</p>' + "[26]");
                                }

                                $("#patientslist").empty().append(patientsHTML);
                            } else {
                                displayError(ret[1] + "[27]");
                            }
                        },
                        error: function(xhr, status, errorThrown) {
                            displayError(errorThrown + "[28]");
                        }
                    });
                }


                //FUNCTION prepareSampleslist
                function prepareSampleslist() {
                    $.ajax({
                        type: "POST",
                        url: "sampleinteraction.php",
                        data: "action=getSamplesByPid&pid=" + pid,
                        dataType: 'json',
                        success: function(ret) {
                            if (debug) console.log("success case ajax 15");
                            if (ret[0] == 1) {
                                var samples = ret[1];
                                var samplesHTML = '';
                                if (samples.length > 0) {
                                    nosamples = true;
                                    $('.nav-tabs a').show();
                                    samplesHTML = '<a href="#" class="dropdown-toggle" id="sampleslisttext" data-toggle="dropdown">Sample <span class="caret"></span></a><ul class="dropdown-menu scrollable-menu">';

                                    $.each(samples, function(i, item) {
                                        if (item['StateCode'] == 100) {
                                            samplesHTML += '<li SampleID="' + item['SampleID'] + '" SampleTakeDate="' + item['SampleTakeDate'] + '"><a href="#">' + item['SampleTakeDate'] + '</a></li>';
                                            nosamples = false;
                                        }
                                    });

                                    samplesHTML += '</ul>';
                                    $("#sampleslist").empty().append(samplesHTML);

                                    if (!nosamples) {
                                        if (sid != undefined) {
                                            $("#sampleslisttext").empty().append($('#sampleslist li[SampleID="' + sid + '"]').attr("SampleTakeDate") + ' <span class="caret"></span>');

                                            $('#sampleslist >> .chosenli').removeClass('chosenli');
                                            $('#sampleslist li[SampleID="' + sid + '"]').addClass('chosenli');
                                        } else {
                                            sid = $("#sampleslist li").first().attr("SampleID");
                                            $("#sampleslisttext").empty().append($("#sampleslist li").first().attr("SampleTakeDate") + ' <span class="caret"></span>');

                                            $('#sampleslist >> .chosenli').removeClass('chosenli');
                                            $("#sampleslist li").first().addClass('chosenli');
                                        }

                                        if (typeof chosentab !== 'undefined') {
                                            $('.nav-tabs a[tab="' + chosentab + '"]').tab('show');
                                            tabselected();
                                        } else {
                                            //load everything in completepanel and infotab. Hotspots will be loaded after completePanel is loaded - using applySettings for this
                                            chosentab = 'info';
                                            showInfoTab();
                                            designID = sampledata['design'];
                                            chosentab = 'completepanel';
                                            showCompletePanel();





                                        }
                                    }
                                } else {
                                    nosamples = true;
                                }

                                if (nosamples) {
                                    samplesHTML = 'No Samples yet';
                                    $("#sampleslist").empty().append(samplesHTML);

                                    chosentab = 'info';
                                    $('.nav-tabs a').hide();
                                    $('.nav-tabs a[tab="info"]').show();
                                    $('.nav-tabs a[tab="info"]').tab('show');
                                    tabselected();
                                }
                            } else {
                                displayError(ret[1] + "[29]");
                            }
                        },
                        error: function(xhr, status, errorThrown) {
                            displayError(errorThrown + "[30]");
                        }
                    });
                }


                /*  FUNCTION tabselected
                 *   Change the selected Tab
                 *   Hide the other tabs, but don't remove them
                 */
                function tabselected() {
                    $('#grey_background').hide();
                    $('#errorpanel').empty().hide();
                    $('#additionalpanels').hide(); //otherwise the resizeable tablehaeader doesn't work
                    $("#hotspotsPanel").hide();
                    $('#showInfoPanel').hide();
                    $('#genomeBrowserPanel').hide();
                    if (!firstLoad) { //cause the progressbar is inside the filterpanel -.-
                        $('#filterPanel').hide();
                    }

                    $("#mainloader").show();

                    switch (chosentab) {
                        case 'info':
                            showInfoTab();
                            break;

                        case 'hotspots':
                            showHotspots();
                            break;

                        case 'completepanel':
                            showCompletePanel();
                            break;

                        case 'genomebrowser':
                            showGenomebrowser();
                            break;
                    }
                }


                //FUNCTION readDownsampler
                function readDownsampler(featureSets) {
                    const reads = featureSets[0];
                    const sampledReads = [];
                    var step = Math.max(Math.floor(reads.length / 500), 1);
                    for (var i = 0; i < reads.length; i += step) {
                        sampledReads.push(reads[i]);
                    }
                    return sampledReads;
                }




                /* Hier wird der genomeBroser gebaut. Hier sind verschiedene Getestet worden, darum ist hier das eine oder mal das andere aukommentiert. */
                /*  FUNCTION showGenomebrowser
                 *   Show the Genome Browser Tab
                 *   Create a new browser or show a previously created browser.
                 *   Parameter genomeBrowserNew: is this the first time the users opens the genome browser tab?
                 *   Parameter genomeBrowserChange: do we need to recreate the genome browser for a new variant?
                 */
                function showGenomebrowser() {
                    /*
                    bamSource = pileup.formats.bam({
                        url: 'getSampleSource.php?pid=' + pid + '&sid=' + sid + '&type=bam',
                        indexUrl: 'getSampleSource.php?pid=' + pid + '&sid=' + sid + '&type=bai'
                    });

                    vcfSource = pileup.formats.vcf({
                        url: 'getSampleSource.php?pid=' + pid + '&sid=' + sid + '&type=vcf'
                    });
                    */


                    if (!genomeBrowserNew && !genomeBrowserChange) {
                        $("#mainloader").hide();
                        $('#genomeBrowserPanel').show();
                        pageloadingflag = false;
                    } else if (genomeBrowserNew) {

                        $('#mainloader').hide();
                        $('#genomeBrowserPanel').show();
                        //$('#genomeBrowserPanel').empty().append('<div id="pileup" style="height:500px;"></div><div id="svgHolder"></div>');
                        $('#genomeBrowserPanel').empty().append('<div id="igvbrowser"></div><div id="svgHolder"></div>');


                        /******IGV TEST***********/
                        var loc = pileuprange['chr'] + ":" + pileuprange['start'] + "-" + pileuprange['stop'];

                        var igvoptions = {
                            doubleClickDelay: 200,
                            showNavigation: true,
                            showRuler: true,
                            genome: "hg19",
                            locus: loc, //"chr5:170,837,524-170,837,564",
                            tracks: [{
                                    name: "Genes",
                                    type: "annotation",
                                    format: "bed",
                                    sourceType: "file",
                                    url: "https://s3.amazonaws.com/igv.broadinstitute.org/annotations/hg19/genes/refGene.hg19.bed.gz",
                                    indexURL: "https://s3.amazonaws.com/igv.broadinstitute.org/annotations/hg19/genes/refGene.hg19.bed.gz.tbi",
                                    order: Number.MAX_VALUE,
                                    visibilityWindow: 300000000,
                                    displayMode: "SQUISHED"
                                }, {
                                    name: 'BAM Track',
                                    colorBy: "strand",
                                    type: 'alignment',
                                    url: 'getSampleSource.php?pid=' + pid + '&sid=' + sid + '&type=bam',
                                    indexURL: 'getSampleSource.php?pid=' + pid + '&sid=' + sid + '&type=bai',
                                    displayMode: "SQUISHED",
                                    viewAsPairs: true
                                }, {
                                    name: "Variants",
                                    format: "vcf",
                                    //url: "https://s3.amazonaws.com/1000genomes/release/20130502/ALL.wgs.phase3_shapeit2_mvncall_integrated_v5b.20130502.sites.vcf.gz",
                                    //indexURL:  "https://s3.amazonaws.com/1000genomes/release/20130502/ALL.wgs.phase3_shapeit2_mvncall_integrated_v5b.20130502.sites.vcf.gz.tbi",
                                    //url: 'https://amlvaran-dev.uni-muenster.de/Sample.vcf',
                                    url: 'getSampleSource.php?pid=' + pid + '&sid=' + sid + '&type=vcf',
                                    type: "variant",
                                    displayMode: "EXPANDED"
                                }
                                //https://amlvaran-dev.uni-muenster.de/getSampleSource.php?pid=129&sid=129&type=vcf
                            ]
                        };
                        if (debug) console.log("igvoptions: " + igvoptions.locus);
                        igvbrowser = igv.createBrowser($('#igvbrowser')[0], igvoptions);
                        if (debug) console.log("igvbrowser" + igvbrowser);


                        /*
                        var p = pileup.create($('#pileup')[0], {
                          range: {contig: pileuprange['chr'], start: pileuprange['start'], stop: pileuprange['stop']},
                          tracks: [
                            {
                              viz: pileup.viz.genome(),
                              isReference: true,
                              data: pileup.formats.twoBit({
                                url: 'https://www.biodalliance.org/datasets/hg19.2bit'
                              }),
                              name: 'Reference'
                            },
                            {
                                viz: pileup.viz.scale(),
                                name: 'Scale'
                            },
                            {
                                viz: pileup.viz.location(),
                                name: 'Location'
                            },
                            {
                                viz: pileup.viz.genes(),
                                data: pileup.formats.bigBed({
                                    
                                url: 'https://www.biodalliance.org/datasets/ensGene.bb'
                                }),
                                name: 'Genes'
                            },
                            {
                                viz: pileup.viz.variants(),
                                data: vcfSource,
                                name: 'Variants'
                            },
                            {
                                viz: pileup.viz.coverage(),
                                data: bamSource,
                                cssClass: 'normal',
                                name: 'Coverage'
                            },
                            {
                              viz: pileup.viz.pileup(),
                              data: bamSource,
                              cssClass: 'normal',
                              name: 'Alignments'
                            }
                          ]
                        });
                        */


                        //*********dalliance GenomeBrowser********/
                        /*
                    var geneBrowser = new Browser({
                        reverseScrolling: 'true',
                        chr:          parseInt(pileuprange['chr'].substring(3, pileuprange['chr'].length)),
                        viewStart:    parseInt(pileuprange['start']),
                        viewEnd:      parseInt(pileuprange['stop']),

                        coordSystem: {
                          speciesName: 'Human',
                          taxon: 9606,
                          auth: 'GRCh',
                          version: '37',
                          ucscName: 'hg19'
                        },
                        
                        maxHeight: 500,
                        //cookieKey: 'noPersist',
                        //noPersist: 'true',
                        
                        sources:     [{name:                'Genome',
                                       twoBitURI:           '//www.biodalliance.org/datasets/hg19.2bit',
                                       tier_type:           'sequence'},
                                      {name:                'Genes',
                                       desc:                'Gene structures from GENCODE 19',
                                       bwgURI:              '//www.biodalliance.org/datasets/gencode.bb',
                                       stylesheet_uri:      '//www.biodalliance.org/stylesheets/gencode.xml',
                                       collapseSuperGroups: true,
                                       trixURI:             '//www.biodalliance.org/datasets/geneIndex.ix'},
                                      {name:                 'Variants',
                                       uri:                  'getSampleSource.php?pid=' + pid + '&sid=' + sid + '&type=vcf',
                                       tier_type:            'memstore',
                                       payload:              'vcf'},
                                      {name:                 'BAMTrack',
                                       overlay: [{ bamURI:   'getSampleSource.php?pid=' + pid + '&sid=' + sid + '&type=bam',
                                                   baiURI:   'getSampleSource.php?pid=' + pid + '&sid=' + sid + '&type=bai'
                                                 }],
                                       subtierMax: 500,
                                       merge: readDownsampler,
                                       style: [
                                              {
                                                type: "density",
                                                zoom: "low",
                                                style: {
                                                  glyph: "HISTOGRAM",
                                                  COLOR1: "black",
                                                  COLOR2: "red",
                                                  HEIGHT: 30,
                                                  __SEQCOLOR: "mismatch"
                                                }
                                              },
                                              {
                                                type: "density",
                                                zoom: "medium",
                                                style: {
                                                  glyph: "HISTOGRAM",
                                                  COLOR1: "black",
                                                  COLOR2: "red",
                                                  HEIGHT: 30,
                                                  __SEQCOLOR: "mismatch"
                                                }
                                              },
                                              {
                                                type: "bam",
                                                zoom: "high",
                                                style: {
                                                  glyph: "__SEQUENCE",
                                                  FGCOLOR: "black",
                                                  BGCOLOR: "blue",
                                                  HEIGHT: 8,
                                                  BUMP: true,
                                                  LABEL: false,
                                                  ZINDEX: 20,
                                                  __SEQCOLOR: "mismatch"
                                                }
                                              }
                                            ],                        
                                      }],

                    });
        
                    geneBrowser.setLocation(pileuprange['chr'].substring(3, pileuprange['chr'].length), parseInt(pileuprange['start']), parseInt(pileuprange['stop']))
                    */
                        //********ENDE dalliance****/
                        pageloadingflag = false;
                        genomeBrowserNew = false;
                        genomeBrowserChange = false;
                    } else { //GenomeBrowser wurde schon geladen und es soll nur die Position geaendert werden
                        $("#mainloader").hide();
                        $('#genomeBrowserPanel').show();
                        pageloadingflag = false;
                        if (debug) console.log("pileuprange chr: " + pileuprange['chr'] + " start " + pileuprange['start'] + " stop " + pileuprange['stop']);
                        igvbrowser.search(pileuprange['chr'] + ":" + pileuprange['start'] + "-" + pileuprange['stop']); //'chr10:1000-2000');//goto(pileuprange['chr'],40);// pileuprange['start'] ,end);// pileuprange['stop']);
                        genomeBrowserChange = false;
                    }

                }


                /*  FUNCTION showInfoTab
                 *   Show the 'Sample Info' Tab
                 *   Create the content if it wasn't created before.
                 */
                function showInfoTab() {


                    if (!firstLoad) {
                        $("#mainloader").hide();
                        $('#showInfoPanel').show();
                        pageloadingflag = false;
                    } else {
                        $("#showInfoPanel").empty().append('<div class="panel-body" style="display:none"><div id="samplepanel" class="samplepanel"></div><div id="patientpanel" class="patientpanelResults"></div>');


                        $('.panel-body').show();
                        $('#showInfoPanel').hide();

                        var patientHTML = '<table style="font-size:14px; float:left;">' +
                            '<tr><td style="width:170px">Name:</td><td id="Patientname">' + patientdata['Patientname'] + '</td></tr>' +
                            '<tr><td>Birth date:</td><td id="Birthdate">' + patientdata['Birthdate'] + '</td></tr>' +
                            '<tr><td>Patient number:</td><td id="Patientnumber">' + patientdata['Patientnumber'] + '</td></tr>' +
                            '<tr><td>Sex:</td><td id="Sex">' + patientdata['Sex'] + '</td></tr></table>';

                        <?php if($_SESSION['editPermission'] == 1) { ?>
                        patientHTML += '<div><span class="glyphicon glyphicon-pencil glyphsmallbutton" aria-hidden="true" data-toggle="tooltip" data-placement="top" title="Edit patient info" style="margin-left:30px;"></span></div>' +
                            '<div><span class="glyphicon glyphicon-ok glyphsmallbutton" aria-hidden="true" data-toggle="tooltip" data-placement="top" title="Submit" style="margin-left:30px; margin-top:20px; display:none; color:green;" id="patienteditok"></span><span class="glyphicon glyphicon-remove glyphsmallbutton" aria-hidden="true" data-toggle="tooltip" data-placement="top" title="Cancel editing" style="margin-left:30px; margin-top:20px; display:none; color:red;" id="patienteditcancel"></span></div>';
                        <?php } else { ?>
                        patientHTML += '<span class="glyphicon glyphicon-pencil glyphsmallbutton glyphbuttondisabled" aria-hidden="true" data-toggle="tooltip" data-placement="top" title="This user can not edit patient info" style="margin-left:30px;"></span>';
                        <?php } ?>

                        $("#patientpanel").append(patientHTML);
                        var sampleinfoHtml = '';
                        if (!nosamples) {
                            $.ajax({
                                type: "POST",
                                url: "sampleinteraction.php",
                                data: "action=getSampleinfo&sid=" + sid,
                                dataType: 'json',
                                async: false, //Need to be false, because otherwise sampledata['design'] could be used before this function sets the values
                                success: function(ret) {
                                    if (debug) console.log("success case ajax 16");
                                    if (ret[0] == 1) {
                                        sampledata = ret[1];
                                        sampleinfoHtml = '<table style="font-size:14px; float:left;">' +
                                            '<tr><td style="width:100px">Taken:</td><td id="SampleTakeDate">' + sampledata['SampleTakeDate'] + '</td></tr>' +
                                            '<tr><td style="width:100px">Diagnosis:</td><td id="Diagnosis">' + sampledata['Diagnosis'] + '</td></tr>' +
                                            '<tr><td>Created:</td><td id="Created">' + sampledata['Created'] + '</td></tr>' +
                                            '<tr><td>Comments:</td><td id="Comments">' + sampledata['Comments'] + '</td></tr></table>';

                                        <?php if($_SESSION['editPermission'] == 1) { ?>
                                        sampleinfoHtml += '<div><span class="glyphicon glyphicon-pencil glyphsmallbutton" aria-hidden="true" data-toggle="tooltip" data-placement="top" title="Edit sample info" style="margin-left:30px;"></span></div>' +
                                            '<div><span class="glyphicon glyphicon-ok glyphsmallbutton" aria-hidden="true" data-toggle="tooltip" data-placement="top" title="Submit" style="margin-left:30px; margin-top:20px; display:none; color:green;" id="sampleeditok"></span><span class="glyphicon glyphicon-remove glyphsmallbutton" aria-hidden="true" data-toggle="tooltip" data-placement="top" title="Cancel editing" style="margin-left:30px; margin-top:20px; display:none; color:red;" id="sampleeditcancel"></span></div>';
                                        <?php } else { ?>
                                        sampleinfoHtml += '<span class="glyphicon glyphicon-pencil glyphsmallbutton glyphbuttondisabled" aria-hidden="true" data-toggle="tooltip" data-placement="top" title="This user can not edit sample info" style="margin-left:30px;"></span>';
                                        <?php } ?>

                                        $("#samplepanel").append(sampleinfoHtml);
                                    } else {
                                        displayError(ret[1] + "[31]");
                                    }
                                    pageloadingflag = false;
                                },
                                error: function(xhr, status, errorThrown) {
                                    displayError(errorThrown + "[32]");
                                    pageloadingflag = false;
                                }
                            });
                        } else {
                            sampleinfoHtml = 'There are no processed samples for this patient yet.';
                            $("#samplepanel").append(sampleinfoHtml);
                        }

                        $("#mainloader").hide();
                    }
                }


                /*  FUNCTION showHotspots
                 *   Show the Hotspot Tab
                 *   Create the content if the user cchanged the version
                 */
                function showHotspots() {
                    $("#columnchooser").empty();
                    $("#mainloader").hide();

                    if (!firstLoad && !newVersion) {
                        $("#hotspotsPanel").show();
                        pageloadingflag = false;
                    } else { //wenn das erste mal geladen wird, oder es eine neue version gibt:

                        //$("#columnchooser").empty();
                        $("#hotspotsPanel").empty().append('<div id="hotspotpanel"><div id="pdfpanel" style="float:right;"><div id="pdfloader"><img src="common/ajax-loader.gif" style="display:none; margin:auto;" /></div></div><div id="reportpanel"></div></div>');

                        $.ajax({
                            type: "POST",
                            url: "sampleinteraction.php",
                            data: "action=getPdfVersion&sid=" + sid + "&version=" + version,
                            dataType: 'json',
                            async: false,
                            success: function(ret) {
                                if (debug) console.log("success case ajax 18");
                                if (ret[0] == 1) {
                                    if (debug) console.log("erfolg");
                                    var report = ret[1] + " ";
                                    if (debug) console.log("return report.match('object') : " + report.match('object'));
                                    if (report.match('object') != null) { //wenn ein objekt zurückkommt existiert eine entsprechende version in der DB
                                        newVersion = false;
                                    } else {
                                        newVersion = true;
                                    }
                                } else {
                                    displayError(ret[1] + "[35]");
                                }


                                var appreci8Success = true;

                                if (newVersion && versionFromDB) {
                                    if (debug) console.log("schreibe appreci8 werte");
                                    //first: clear the appreci8 values
                                    $.ajax({
                                        type: "POST",
                                        url: "sampleinteraction.php",
                                        data: "action=clearAppreci8&sid=" + sid,
                                        dataType: 'json',
                                        async: false,
                                        success: function(ret) {
                                            if (debug) console.log("success case ajax 19");
                                            if (ret[0] == 1) {
                                                //NOTHING TO DO..
                                            } else {
                                                displayError(ret[1] + "[36]");
                                            }

                                            $.each($('#completepanelsubtable > tbody > tr'), function(key, row) {
                                                var appreci = $(this)[0].cells[columnindices['appreci8']].innerHTML;
                                                var score = 0;
                                                if (appreci.indexOf('Probably True') >= 0) {
                                                    score = 1;
                                                } else if (appreci.indexOf('Polymorphism') >= 0) {
                                                    score = 2;
                                                }

                                                if (debug) console.log(appreci);
                                                if (score == 1) {
                                                    var sampleID = $(this)[0].cells[columnindices['SampleID']].innerHTML;
                                                    var chr = $(this)[0].cells[columnindices['chr']].innerHTML;
                                                    var pos = $(this)[0].cells[columnindices['pos']].innerHTML;
                                                    var ref = $(this)[0].cells[columnindices['ref']].innerHTML;
                                                    var alt = $(this)[0].cells[columnindices['alt']].innerHTML;
                                                    if (debug) console.log(chr + " ; " + pos + " ; " + ref + " ; " + alt + " ; ")



                                                    //now set the calculated appreci8 values
                                                    $.ajax({
                                                        type: "POST",
                                                        url: "sampleinteraction.php",
                                                        data: "action=setAppreci8&sid=" + sampleID + "&chr=" + chr + "&pos=" + pos + "&ref=" + ref + "&alt=" + alt + "&score=" + score,
                                                        dataType: 'json',
                                                        async: false,
                                                        success: function(ret) {
                                                            if (debug) console.log("success case ajax 20");
                                                            if (ret[0] == 1) {
                                                                //NOTHING TO DO..
                                                            } else {
                                                                displayError(ret[1] + "[37]");
                                                            }

                                                            //$("#mainloader").hide();
                                                            //pageloadingflag = false;
                                                        },
                                                        error: function(xhr, status, errorThrown) {
                                                            displayError(errorThrown + "[38]");
                                                            pageloadingflag = false;
                                                            appreci8Success = false;
                                                        }
                                                    });

                                                }
                                            });
                                        },
                                        error: function(xhr, status, errorThrown) {
                                            displayError(errorThrown + "[39]");
                                            pageloadingflag = false;
                                        }

                                    });
                                    newVersion = false;
                                }

                                if (appreci8Success) {
                                    $.ajax({
                                        type: "POST",
                                        url: "getreport.php",
                                        data: "sid=" + sid + "&version=" + version,
                                        dataType: 'text',
                                        async: false,
                                        success: function(ret) {
                                            if (debug) console.log("success case ajax 21");
                                            $('#reportpanel').append(ret);

                                            $('.reporttable').each(function() {
                                                $(this).hide();
                                            });

                                            $('.hotspotsbox').first().trigger("hotspotsboxclicked");

                                            $('[data-toggle="tooltip"]').tooltip();

                                            $("#mainloader").hide();
                                            pageloadingflag = false;
                                            checkPdfReports();
                                        },
                                        error: function(xhr, status, errorThrown) {
                                            displayError(errorThrown + "[40]");
                                            pageloadingflag = false;
                                        }
                                    });
                                } else {
                                    var errorMessage = "Calculation of the appreci8-values failed.";
                                    $('#hotspotsPanel').append("<p><b>" + errorMessage + "<b>");
                                    displayError(errorMessage + " [58]"); //NOTE highest displayError value 58
                                }
                            },
                            error: function(xhr, status, errorThrown) {
                                displayError(errorThrown + "[41]");
                            }
                        });

                        $("#hotspotsPanel").show();
                    }



                    //$("#columnchooser").empty();


                    //var table = $('#completepanelsubtable').tableToJSON();
                    //if(debug) console.log(table);



                }


                //FUNCTION checkPdfReports
                function checkPdfReports() {
                    $.ajax({
                        type: "POST",
                        url: "sampleinteraction.php",
                        data: "action=getPdfReports&sid=" + sid,
                        dataType: 'json',
                        async: false, //IE otherrwise the internet explorer won't work -.-    altenatively: dont use any async:false! Maybe do that if we have some time
                        //We could use ajax... .done() for this
                        success: function(ret) {
                            if (debug) console.log("success case ajax 22");
                            var versionfound = false;
                            if (ret[0] == 1) {
                                $.each(ret[1], function(i, item) {
                                    if (item['Version'] == version) versionfound = true;
                                });

                                if (versionfound) {
                                    pdfreports = ret[1];
                                    showPdfReports();
                                } else {
                                    $.ajax({
                                        type: "POST",
                                        url: "getreport.php",
                                        data: "sid=" + sid + "&version=" + version + "&pdf=true",
                                        dataType: 'text',
                                        success: function(ret) {
                                            if (debug) console.log("success case ajax 23");
                                            ret=encodeURIComponent(ret);
                                            $.ajax({
                                                type: "POST",
                                                url: "generatepdf.php",
                                                data: "html=" + ret + "&sid=" + sid + "&pid=" + pid + "&version=" + version,
                                                dataType: 'json',
                                                success: function(ret) {
                                                    if (debug) console.log("success case ajax 24");
                                                    if (ret[0] == 1) {
                                                        $.ajax({
                                                            type: "POST",
                                                            url: "sampleinteraction.php",
                                                            data: "action=getPdfReports&sid=" + sid,
                                                            dataType: 'json',
                                                            success: function(ret) {
                                                                if (debug) console.log("success case ajax 25");
                                                                if (ret[0] == 1) {
                                                                    pdfreports = ret[1];
                                                                    showPdfReports();
                                                                } else {
                                                                    displayError(ret[1] + "[42]");
                                                                }
                                                            },
                                                            error: function(xhr, status, errorThrown) {
                                                                displayError(errorThrown + "[43]");
                                                            }
                                                        });
                                                    } else {
                                                        displayError(ret[1] + "[44]");
                                                    }
                                                },
                                                error: function(xhr, status, errorThrown) {
                                                    displayError(errorThrown + "[45]");
                                                }
                                            });
                                        },
                                        error: function(xhr, status, errorThrown) {
                                            displayError(errorThrown + "[46]");
                                        }
                                    });
                                }
                            } else {
                                displayError(ret[1] + "[47]");
                            }
                        },
                        error: function(xhr, status, errorThrown) {
                            displayError(errorThrown + "[48]");
                        }
                    });
                }


                //FUNCTION showPdfReports
                function showPdfReports() {
                    var pdfReportsHtml = 'Pdf download:<br><div class="btn-group"><button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" id="pdfreportsbutton">Choose Report <span class="caret"></span></button>' +
                        '<ul class="dropdown-menu scrollable-menu" role="menu" style="left:-71px;">';

                    $.each(pdfreports, function(i, item) {
                        pdfReportsHtml += '<li style="cursor:pointer;" id="pdfreport' + item['Version'] + '" class="pdfreportli" version="' + item['Version'] + '" displayname="' + item['Created'] + '"><a>' + item['Created'] +
                            ', Version ' + item['Version'] + '</a></li>';
                    });

                    pdfReportsHtml += '</ul></div><div><button type="button" class="btn btn-default" id="pdfdownloadbutton">Download Pdf</button></div>';

                    $('#pdfloader').hide();
                    $('#pdfpanel').append(pdfReportsHtml);
                    $("#pdfpanel li").first().trigger("pdfreportclicked");
                }


                /*  FUNCTION showCompletePanel
                 *   Show the Variant Inspector (previously known as complete Panel)
                 */
                function showCompletePanel() {
                    var setupCompletePanel = false;
                    $("#columnchooser").empty();
                    if (!firstLoad) {
                        $("#mainloader").hide();
                        $('#additionalpanels').show();
                        $('#filterPanel').show();
                        $(".filterpanel").show();
                        $(".tablesorter-resizable-handle").show();
                        resizeTable(); //IMPORTANT because of tablesorter resizable widget
                        pageloadingflag = false;

                        //remember whether we changed the tab (genome browser button) while the additional info panel was in foreground
                        if ($('#grey_background').hasClass("backgroundActivated")) {
                            $('#grey_background').show();
                        }
                    } else {
                        var windowHeight = window.innerHeight - 70;
                        $('#filterPanel').empty().append(
                            '<div class="filterpanel_loading"><p style="font-size: 20px;">Loading Sample Data. Please Wait.</p>' +
                            '<div style="height:50px;"><img src="common/ajax-loader.gif" style="display:block; margin:auto;" /></div>' +
                            '<div><span class="progress_info">Loading Variants...</span>(<span id="percentage_progress_info">0%</span>)</div>' +
                            '<div class="progress"><div class="progress-bar" id="progress-bar-filter" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;"></div></div>' +
                            '</div>' +
                            '<div id="completepanel">' +
                            '<div class="filterpanel dropdown" style="display:none">' +

                            '<div id="showcomplexfilter" class="filtertoggle" data-toggle="dropdown" style="width:180px;">appreci8-Score <span class="glyphicon glyphicon-chevron-down"></span></div>' +
                            '<div id="GUIappreci8" class="dropdown-menu" style="position: fixed; width:838px; left: 200px; top: 50px; height: ' + windowHeight + 'px; padding-top: 0px;">' +
                            '<div id="GUI-top"><h3 class="popover-title" style="padding-top: 1px; padding-bottom: 0px; margin-bottom: 1px;"><div class="h4"><b>appreci8-Score</b><div class="right-element"><button type="button" class="btn btn-default" id="CloseAppreci8Score" style="margin-top: -9px; background-color: #E56567;"><span class="glyphicon glyphicon-remove"></span></button>&nbsp;&nbsp;</div></div></h3></div>' +
                            '<div id="GUI" style="width:838px; height: 100%; padding-left: 10px;"></div></div></div>' +
                            '<div id="complexfilterpanel" style="display:none"></div>' +

                            '</div>' +
                            '<br>' +
                            '<div class="filterpanel" style="display:none"><!--p style="font-size: 20px;">Filter</p>-->' +
                            '<div style="display: inline-block; width:20%; vertical-align: top;">' +
                            '<div class="dropdown"><div id="showgenes" class="filtertoggle" data-toggle="dropdown" onclick="countVariants();">Filter by Genes <span class="glyphicon glyphicon-chevron-down"></span></div>' +
                            '<div class="dropdown-menu" style="width:400%;" onClick="stopEventPropagation(event)"><div id="genespanel"></div></div></div>' +
                            '<div id="selectedgenes" style="margin-top:10px;"><p id="nogenesselected">No gene filter active</p></div>' +
                            '</div>' +
                            '<div style="display: inline-block; width:20%; vertical-align: top;">' +
                            '<div class="dropdown"><div id="showregions" class="filtertoggle" data-toggle="dropdown">Filter by Region <span class="glyphicon glyphicon-chevron-down"></span></div>' +
                            '<div id="regionspanel" class="dropdown-menu" style="width:100%;" onClick="stopEventPropagation(event)"></div></div>' +
                            '<div id="selectedregion" style="margin-top:10px;"><p>All regions selected</p></div>' +
                            '</div>' +
                            '<div style="display: inline-block; width:20%; vertical-align: top;">' +
                            '<div class="dropdown"><div id="showtypes" class="filtertoggle" data-toggle="dropdown">Filter by Type <span class="glyphicon glyphicon-chevron-down"></span></div>' +
                            '<div id="typespanel" class="dropdown-menu" style="width:100%;" onClick="stopEventPropagation(event)"></div></div>' +
                            '<div id="selectedtype" style="margin-top:10px;"><p>All variant types selected</p></div>' +
                            '</div>' +
                            '<div style="display: inline-block; width:20%; vertical-align: top;">' +
                            '<div class="dropdown"><div id="showquality" class="filtertoggle" data-toggle="dropdown">Filter by Quality <span class="glyphicon glyphicon-chevron-down"></span></div>' +
                            '<div id="qualitypanel" class="dropdown-menu" style="width:200%;" onClick="stopEventPropagation(event)"></div></div>' +
                            '<div id="selectedquality" style="margin-top:10px;"><p>Default values selected</p></div>' +
                            '</div>' +
                            '<div style="display: inline-block; width:20%; vertical-align: top;">' +
                            '<div class="dropdown"><div id="showexclusion" class="filtertoggle" data-toggle="dropdown">Exclusion Filters <span class="glyphicon glyphicon-chevron-down"></span></div>' +
                            '<div id="exclusionpanel" class="dropdown-menu" style="width:100%;" onClick="stopEventPropagation(event)"></div></div>' +
                            '<div id="selectedexclusion" style="margin-top:10px;"><p id="noexclusionselected">No Exclusion filter active</p></div>' +
                            '</div>' +
                            '</div>' +
                            '</div>');

                        var exclusionHTML = '<div style="margin: 10px 30px 10px;">' +
                            '<label style="font-weight: normal;"><input id="excludebenign" type="checkbox"> Exclude variants with "benign" rating (Clinvar)</label></br>' +
                            '<label style="font-weight: normal;"><input id="excludecommon" type="checkbox"> Exclude common variants (1000 Genomes)</label></br>' +
                            '<label style="font-weight: normal;"><input id="excludeartifacts" type="checkbox"> Exclude variants rated as artifacts</label></br>' +
                            '<label style="font-weight: normal;"><input id="excludepolymorphisms" type="checkbox"> Exclude variants rated as polymorphisms</label></br>' +
                            '</div>';
                        $("#exclusionpanel").append(exclusionHTML);



                        var qualityFiltrationHTML = '<div style="margin: 10px 30px 10px;">' +
                            '<table width="100%">' +
                            '<tr>' +
                            '<td>min. Nr. reads with variant</td>' +
                            '<td width="45%"><div id="nraltslider"></div></td>' +
                            '<td width="65px" align="right"><input type="text" name="nralttext" id="nralttext" value="' + nr_alt + '" maxlength="3" size="3"></td>' +
                            '</tr><tr>' +
                            '<td>min. Coverage</td>' +
                            '<td width="45%"><div id="dpslider"></div></td>' +
                            '<td width="65px" align="right"><input type="text" name="dptext" id="dptext" value="' + dp + '" maxlength="3" size="3"></td>' +
                            '</tr><tr>' +
                            '<td>min. Allelic Frequency (VAF)</td>' +
                            '<td width="45%"><div id="vafslider"></div></td>' +
                            '<td width="65px" align="right"><input type="text" name="vaftext" id="vaftext" value="' + vaf + '" maxlength="5" size="3"></td>' +
                            '</tr><tr>' +
                            '<td>min. Base Quality</td>' +
                            '<td width="45%"><div id="lowbqslider"></div></td>' +
                            '<td width="65px" align="right"><input type="text" name="lowbqtext" id="lowbqtext" value="' + low_bq + '" maxlength="2" size="3"></td>' +
                            '</tr><tr>' +
                            '<td>max. Base Quality distance</td>' +
                            '<td width="45%"><div id="bqdiffslider"></div></td>' +
                            '<td width="65px" align="right"><input type="text" name="bqdifftext" id="bqdifftext" value="' + bq_diff + '" maxlength="2" size="3"></td>' +
                            '</tr>' +
                            '</table>' +
                            '<!--br /><label style="font-weight: normal;"><input id="qualityfilter" type="checkbox"> hide filtered items from list.</label-->' +
                            '<div style="margin: 10px 0px 10px; text-align: right; float: right;">' +
                            '<button type="button" class="btn btn-default resetqualitybutton">reset</button>' +
                            '</div>';
                        $("#qualitypanel").append(qualityFiltrationHTML);


                        if (nr_alt == nr_alt_reset && dp == dp_reset && vaf == var_reset && low_bq == low_bq_reset && bq_diff == bq_diff_reset) {
                            $('#selectedquality').empty().append('Default values selected');
                        } else {
                            $('#selectedquality').empty().append('Custom values selected');
                        }



                        //filterByQuality Slider
                        $('#nraltslider').slider({
                            min: 1,
                            max: 100,
                            value: nr_alt,
                            change: function(event, ui) {
                                if (debug) console.log("Trigger 50");
                                nraltChange();
                            },
                            slide: function(event, ui) {
                                $('#nralttext').val(ui.value);
                            }
                        });
                        $('#dpslider').slider({
                            min: 10,
                            max: 200,
                            value: dp,
                            change: function(event, ui) {
                                if (debug) console.log("Trigger 51");
                                dpChange();
                            },
                            slide: function(event, ui) {
                                $('#dptext').val(ui.value);
                            }
                        });
                        $('#vafslider').slider({
                            min: 1,
                            max: 25,
                            value: vaf * 1000,
                            change: function(event, ui) {
                                if (debug) console.log("Trigger 52");
                                vafChange();
                            },
                            slide: function(event, ui) {
                                if (ui.value <= 10) {
                                    $('#vaftext').val(ui.value / 1000);
                                } else {
                                    $('#vaftext').val(Math.round(((0.02 * ui.value) - 0.2) * 100) / 100)
                                }
                            }
                        });
                        $('#lowbqslider').slider({
                            min: 1,
                            max: 50,
                            value: low_bq,
                            change: function(event, ui) {
                                if (debug) console.log("Trigger 53");
                                lowbqChange();
                            },
                            slide: function(event, ui) {
                                $('#lowbqtext').val(ui.value);
                            }
                        });
                        $('#bqdiffslider').slider({
                            min: 1,
                            max: 50,
                            value: bq_diff,
                            change: function(event, ui) {
                                if (debug) console.log("Trigger 54");
                                bqdiffChange();
                            },
                            slide: function(event, ui) {
                                $('#bqdifftext').val(ui.value);
                            }
                        });





                        //Autogenerate rest of Filtersettings:
                        createAppreci8GUI();

                        var typesHTML = '<div style="height:280px; margin: 10px 30px 10px;"><div id="typesslider" style="height: 250px;"></div>' +
                            '<div style="position: absolute; top: 10px; left: 60px;">All variant types</div>' +
                            '<div style="position: absolute; top: 90px; left: 60px;">Protein coding change</div>' +
                            '<div style="position: absolute; top: 170px; left: 60px;">Protein coding change with severe effect (SNPeff)</div>' +
                            '<div style="position: absolute; top: 250px; left: 60px;">Pathogenous rating by Clinvar and COSMIC (but not synonymous SNV)</div>' +
                            '</div>';
                        $("#typespanel").append(typesHTML);
                        $('#typesslider').slider({
                            orientation: "vertical",
                            range: "min",
                            min: 0,
                            max: 3,
                            value: 3,
                            change: function(event, ui) {
                                if (debug) console.log("Trigger 55");
                                switch (ui.value) {
                                    case 0:
                                        $('#selectedtype').empty().append('<p>Pathogenous rating by Clinvar and COSMIC (but not synonymous SNV)</p>');
                                        $('#completepanelsubtable > tbody > tr').removeClass('typefiltered');
                                        $.each($('#completepanelsubtable > tbody > tr'), function(key, row) {
                                            if ($(this)[0].cells[columnindices['varTypes']].innerHTML === 'synonymous SNV'){
                                                $(this).addClass('typefiltered');
                                            } else {
                                                if (($(this)[0].cells[columnindices['ClinVar_Significance']].innerHTML.indexOf('athogenic') < 0) &&
                                                    (((parseInt($(this)[0].cells[columnindices['Cosmic_NrHaemato']].innerHTML) || 0) <= 1) || ($(this)[0].cells[columnindices['Cosmic_SNP']].innerHTML == '1')) &&
                                                    ($(this)[0].cells[columnindices['varTypes']].innerHTML !== 'duplication') &&
                                                    ($(this)[0].cells[columnindices['varTypes']].innerHTML !== 'deletion'))
                                                      $(this).addClass('typefiltered');
                                            }
                                        });
                                        break;
                                    case 1:
                                        $('#selectedtype').empty().append('<p>Protein coding change with severe effect (SNPeff)</p>');
                                        $('#completepanelsubtable > tbody > tr').removeClass('typefiltered');
                                        $.each($('#completepanelsubtable > tbody > tr'), function(key, row) {
                                            if (($(this)[0].cells[columnindices['varTypes']].innerHTML === 'synonymous SNV') || 
                                                ($(this)[0].cells[columnindices['Impacts']].innerHTML.indexOf('MODERATE') == -1 &&
                                                    $(this)[0].cells[columnindices['Impacts']].innerHTML.indexOf('HIGH') == -1)) $(this).addClass('typefiltered');
                                        });
                                        break;
                                    case 2:
                                        $('#selectedtype').empty().append('<p>Protein coding change</p>');
                                        $('#completepanelsubtable > tbody > tr').removeClass('typefiltered');
                                        $.each($('#completepanelsubtable > tbody > tr'), function(key, row) {
                                            if ($(this)[0].cells[columnindices['varTypes']].innerHTML === 'synonymous SNV') $(this).addClass('typefiltered');
                                        });
                                        break;
                                    case 3:
                                        $('#selectedtype').empty().append('<p>All variant types</p>');
                                        $('#completepanelsubtable > tbody > tr').removeClass('typefiltered');
                                        break;
                                }
                                resizeTable();
                                saveSettings();
                            }
                        });


                        var regionsHTML = '<div style="height:170px; margin: 10px 30px 10px;"><div id="regionsslider" style="height: 170px;"></div>' +
                            '<div style="position: absolute; top: 10px; left: 60px;">All regions</div>' +
                            '<div style="position: absolute; top: 90px; left: 60px;">Exons and structural variants only</div>' +
                            '<div style="position: absolute; top: 170px; left: 60px;">Hotspots only</div>' +
                            '</div>';
                        $("#regionspanel").append(regionsHTML);
                        $('#regionsslider').slider({
                            orientation: "vertical",
                            range: "min",
                            min: 0,
                            max: 2,
                            value: 2,
                            change: function(event, ui) {
                                if (debug) console.log("Trigger 56");
                                switch (ui.value) {
                                    case 0:
                                        $('#selectedregion').empty().append('<p>Hotspots only</p>');
                                        $('#completepanelsubtable > tbody > tr').removeClass('regionfiltered');
                                        $.each($('#completepanelsubtable > tbody > tr'), function(key, row) {
                                            if ((parseInt($(this)[0].cells[columnindices['inHotspot']].innerHTML) || 0) === 0) $(this).addClass('regionfiltered');
                                        });
                                        break;
                                    case 1:
                                        $('#selectedregion').empty().append('<p>Exons and structural variants only</p>');
                                        $('#completepanelsubtable > tbody > tr').removeClass('regionfiltered');
                                        $.each($('#completepanelsubtable > tbody > tr'), function(key, row) {
                                            if (($(this)[0].cells[columnindices['regionTypes']].innerHTML.indexOf('exonic') == -1) &&
                                                ($(this)[0].cells[columnindices['regionTypes']].innerHTML.indexOf('protein_coding') == -1) &&
                                                $(this)[0].cells[columnindices['regionTypes']].innerHTML !== 'structural') $(this).addClass('regionfiltered');
                                        });
                                        break;
                                    case 2:
                                        $('#selectedregion').empty().append('<p>All regions</p>');
                                        $('#completepanelsubtable > tbody > tr').removeClass('regionfiltered');
                                        break;
                                }
                                resizeTable();
                                saveSettings();
                            }
                        });



                        if ($("#additionalpanels").html() === "") {
                            setupCompletePanel = true;
                            $("#additionalpanels").empty().append('<div id="subcolumnchooser" style="padding-bottom:40px;"></div>' +
                                '<div id="tablesubpanel" class="additionalpanel"></div>' +
                                '<div id="additionalinfopanel" class="additionalpanel" style="display:none;"></div>');
                        }

                        $("#mainloader").hide();

                        progressbar_change('Loading Variants...', 10, '[1]');

                        if (setupCompletePanel == true) {
                            tabledata.length = 0;
                            $('#tablesubpanel').empty().hide();
                            $('#additionalinfopanel').empty().hide();
                            $('#tablesubloader').show();

                            $.ajax({
                                type: "POST",
                                url: "sampleinteraction.php",
                                data: 'action=getAll&sid=' + sid + '&version=' + version + '&designID=' + designID,
                                dataType: 'json',
                                success: function(ret) {
                                    if (debug) console.log("success case ajax 27");
                                    progressbar_change('Loading Usersettings... ', 10, '[2]');



                                    if (ret[0] == 1) {
                                        tabledata = ret[1];



                                        //NOTE progressbar 
                                        if (tabledata.length > 0) {
                                            setTimeout(function() {
                                                loadSettingsAtStart();

                                                progressbar_change('Creating Table... ', 10, '[3]');

                                                setTimeout(function() {
                                                    createMainTable();
                                                    editTableInformations();

                                                    progressbar_change('Applying Gene-Filters ', 5, '[4]');

                                                    $('#additionalpanels').hide();

                                                    setTimeout(function() {
                                                        createGeneOverview();
                                                        applyGeneFilters();

                                                        progressbar_change('Choose selected columns ', 5, '[5]');

                                                        setTimeout(function() {
                                                            //choose selected columns takes a lot of time -> extra function for the progress bar
                                                            applySettingsSelectColumns(); //progressbarstatus += 30

                                                            progressbar_change('Calculate Appreciate Score ', 20, '[6]');

                                                            setTimeout(function() {
                                                                addClickHandlers(); //do this before applySettings()
                                                                calculateAppreci8();

                                                                progressbar_change('Apply Settings And Create Gene Overview', 20, '[6]');

                                                                setTimeout(function() {
                                                                    applySettings(); //TODO this takes a lot of time. Can we reduce the calculation time?
                                                                    createGeneHotspotsTab();

                                                                    progressbar_change('Success ', 20, '[7]');

                                                                }, progressbarWaitTime);
                                                            }, progressbarWaitTime);
                                                        }, progressbarWaitTime);
                                                    }, progressbarWaitTime);
                                                }, progressbarWaitTime);
                                            }, progressbarWaitTime);


                                        } else {
                                            //
                                            // enter this case, if the sample is empty. We can not create all parts of the tabs in this case
                                            //

                                            setTimeout(function() {
                                                loadSettingsAtStart(); //progressbarstatus += 10
                                                progressbar_change('Creating Table... ', 10, '[3]');

                                                setTimeout(function() {
                                                    $('#tablesubpanel').append("<b>There are no records to show.</b>").show();
                                                    $('#tablesubloader').empty();
                                                    //progressbarstatus += 20

                                                    setTimeout(function() {
                                                        //createGeneOverview();
                                                        progressbar_change('Choose selected columns ', 10, '[5]');

                                                        setTimeout(function() {
                                                            //choose selected columns takes a lot of time -> extra function for the progress bar
                                                            applySettingsSelectColumns(); //progressbarstatus += 30    
                                                            progressbar_change('Create Gene Overview', 20, '[6]');

                                                            setTimeout(function() {
                                                                addClickHandlers();
                                                                //calculateAppreci8();
                                                                //applySettings(); 
                                                                createGeneHotspotsTab(); //progressbarstatus += 10 (=100)
                                                                $('#filterPanel').empty();
                                                                progressbar_change('Success (sample-data was empty) ', 20, '[7]');

                                                            }, progressbarWaitTime);
                                                        }, progressbarWaitTime);
                                                    }, progressbarWaitTime);
                                                }, progressbarWaitTime);
                                            }, progressbarWaitTime);

                                        }


                                    } else {
                                        displayError(ret[1] + "[51]");
                                    }
                                    if (debug) console.log("ende success");
                                },
                                error: function(xhr, status, errorThrown) {
                                    displayError(errorThrown + "[52]");
                                    pageloadingflag = false;
                                }
                            });

                        } else {
                            applySettings2();
                        }
                    }
                }



                //FUNCTION applyGeneFilters
                function applyGeneFilters() {
                    if (genefilterselected.length > 0) {
                        $('#completepanelsubtable > tbody > tr').addClass('genefiltered');
                        $.each($('#completepanelsubtable > tbody > tr'), function(key, row) {
                            for (i = 0; i < genefilterselected.length; i++) {
                                if ($(this)[0].cells[columnindices['chr']].innerHTML == genefilterselected[i][1] &&
                                    $(this)[0].cells[columnindices['pos']].innerHTML >= genefilterselected[i][2] &&
                                    $(this)[0].cells[columnindices['pos']].innerHTML <= genefilterselected[i][3]) {

                                    $(this).removeClass('genefiltered');
                                }
                            }
                        });
                    } else {
                        $('#completepanelsubtable > tbody > tr').removeClass('genefiltered');
                    }
                }


                /*  FUNCTION nraltChange
                 *   function to apply the quality filter after changes
                 */
                function nraltChange() {
                    nr_alt = parseInt($('#nralttext').val());
                    applyQualityFilters();
                    if (debug) console.log("alt " + nr_alt);
                }
                /*  FUNCTION dpChange
                 *   function to apply the quality filter after changes
                 */
                function dpChange() {
                    dp = parseInt($('#dptext').val());
                    applyQualityFilters();
                    if (debug) console.log("dp " + dp);
                }
                /*  FUNCTION vafChange
                 *   function to apply the quality filter after changes
                 */
                function vafChange() {
                    vaf = parseFloat($('#vaftext').val());
                    applyQualityFilters();
                    if (debug) console.log("vaf " + vaf);
                }
                /*  FUNCTION lowbqChange
                 *   function to apply the quality filter after changes
                 */
                function lowbqChange() {
                    low_bq = parseInt($('#lowbqtext').val());
                    applyQualityFilters();
                    if (debug) console.log("lowbq " + low_bq);
                }
                /*  FUNCTION bqdiffChange
                 *   function to apply the quality filter after changes
                 */
                function bqdiffChange() {
                    bq_diff = parseInt($('#bqdifftext').val());
                    applyQualityFilters();
                    if (debug) console.log("bqdiff" + bq_diff);
                }

                /*  FUNCTION applyQualityFilters
                 *   applies the quality filters
                 */
                function applyQualityFilters() {
                    if (nr_alt == nr_alt_reset && dp == dp_reset && vaf == var_reset && low_bq == low_bq_reset && bq_diff == bq_diff_reset) {
                        $('#selectedquality').empty().append('Default values selected');
                        if (debug) console.log("default");
                    } else {
                        $('#selectedquality').empty().append('Custom values selected');
                        if (debug) console.log("custom");
                    }
                    $.each($('#completepanelsubtable > tbody > tr'), function(key, row) {
                        //$('#progress-bar-filter').animate({ width: "+=" + (i*100/total) + '%'}, 000);
                        //$(this).addClass('automatedfiltered');
                        var artifact_because = {
                            lowNRaltDPVAF: false, //[i,1]
                            lowbq: false, //[i,2]
                            badalignment: false, //[i,3]
                            NRmutationindatabases: 0, //[i,6]
                            NRdatabasethreshold: 0, //[i,7]
                            NRdatabasethreshold2: 0, //[i,8]
                            allelefreq: 0, //[i,10]
                            exlude: false, //exclude var
                            strandbias: 0, //strandbias
                            hotspot: false,
                            artifact_score: 0,
                            poly_score: 0
                        };
                        var BQ_ref = $(this)[0].cells[columnindices['BQref']].innerHTML;
                        var BQ_alt = $(this)[0].cells[columnindices['BQalt']].innerHTML;
                        var NRalt = $(this)[0].cells[columnindices['NRalt']].innerHTML;
                        var NRref = $(this)[0].cells[columnindices['NRref']].innerHTML;
                        var Coverage = $(this)[0].cells[columnindices['Cvg']].innerHTML;
                        var VAF = parseFloat(NRalt) / parseFloat(Coverage);

                        filter_frequency(NRalt, Coverage, VAF, artifact_because);
                        filter_lowbasequality(NRref, BQ_ref, BQ_alt, artifact_because);

                        var activeFilters = "";
                        if (artifact_because.exclude) activeFilters += "Excluded<br>";
                        if (artifact_because.lowNRaltDPVAF) activeFilters += "Low Nralt/DP/VAF<br>";
                        if (artifact_because.lowbq) activeFilters += "Low BQ<br>";
                        if (artifact_because.badalignment) activeFilters += "Bad Alignment";
                        if (activeFilters === "") activeFilters = "NONE";

                        $(this).removeClass('automatedfiltered');
                        if (artifact_because.exclude || artifact_because.lowNRaltDPVAF || artifact_because.lowbq || artifact_because.badalignment) {
                            $(this).addClass('automatedfiltered');
                        }
                    });
                }

                //FUNCTION countVariants
                function countVariants() {
                    $.each($('.genebutton.btn-mutations'), function() {
                        var count = 0;
                        var tempchr = $(this).attr('chr');
                        var tempstart = $(this).attr('start');
                        var tempend = $(this).attr('end');

                        $.each($('#completepanelsubtable > tbody > tr'), function(key, row) {
                            if (!($(this).hasClass('regionfiltered') || $(this).hasClass('typefiltered') || $(this).hasClass('exclusionfiltered') || $(this).hasClass('automatedfiltered') || $(this).hasClass('filtered'))) {
                                if ($(this)[0].cells[columnindices['chr']].innerHTML === tempchr &&
                                    $(this)[0].cells[columnindices['pos']].innerHTML >= tempstart &&
                                    $(this)[0].cells[columnindices['pos']].innerHTML <= tempend) count++;
                            }
                        });

                        $('#' + $(this).attr('genename') + 'badge').empty().append(count);

                        if (count === 0) {
                            $(this).addClass('btn-filtered');
                        } else {
                            $(this).removeClass('btn-filtered');
                        }
                    });
                }

                /*  FUNCTION getTableHtml(tableid)
                 *   Creates the html code for a new table
                 *   @Param tableid: the id of the table
                 *   @Param civicTable: this functions should create a slightly different table for the literature civicTable.
                 */
                function getTableHtml(tableid, civicTable) {
                    var tablehtml = '';
                    if (tabledata.length > 0) {
                        if (civicTable == true) {
                            tablehtml += '<div style="overflow-y: auto; ';
                        } else {
                            tablehtml += '<div style="overflow: auto; max-height: 600px; ';
                        }
                        tablehtml += 'position: relative;" id="' + tableid + 'scrollpanel"><table id="' + tableid + '" class="tablesorter"><thead><tr>';


                        columnindices['Details'] = 0;
                        if (tableid == "completepanelsubtable") {
                            tablehtml += '<th data-priority="critical">&nbsp</th>'; //empty field for the lens symbol/button
                        }
                        var i = 1;
                        $.each(tabledata[0], function(key, value) {
                            if (tableid == "completepanelsubtable")
                                columnindices[key] = i;
                            else
                                columnindices_other[key] = i
                            i++;

                            switch (key) {
                                case 'region_name':
                                    tablehtml += '<th>Gene/Region</th>';
                                    break;
                                case 'mut_type':
                                    tablehtml += '<th>Mutation type</th>';
                                    break;
                                case 'function':
                                    tablehtml += '<th>Function</th>';
                                    break;
                                case 'AF':
                                    tablehtml += '<th title="Frequency">Freq</th>';
                                    break;
                                case 'AD':
                                    tablehtml += '<th title="Number Reads with mutation">NrRd</th>';
                                    break;
                                case 'DP':
                                    tablehtml += '<th title="Coverage">Cvg</th>';
                                    break;
                                case 'GT':
                                    tablehtml += '<th title="GT">GT</th>';
                                    break;
                                case 'CLNDBN':
                                    tablehtml += '<th>Clinvar Disease</th>';
                                    break;
                                case 'CLNREVSTAT':
                                    tablehtml += '<th>Review Status</th>';
                                    break;
//                                case 'Cosmic_NrHaemato':
//                                    tablehtml += '<th>Cosmic NrHaemato</th>';
//                                    break;
                                case 'dbSNP':
                                    tablehtml += '<th title="The ID in the dbSNP-Database">dbSNP</th>';
                                    break;
                                case 'thousandG_AF':
                                    tablehtml += '<th >1000G_AF</th>';
                                    break;
                                case 'thousandG_EurAF':
                                    tablehtml += '<th >1000G_EurAF</th>';
                                    break;
                                case 'chr':
                                    tablehtml += '<th title="Chromosome">' + key + '</th>';
                                    break;
                                case 'pos':
                                    tablehtml += '<th title="Position">' + key + '</th>';
                                    break;
                                case 'ref':
                                    tablehtml += '<th title="Reference">' + key + '</th>';
                                    break;
                                case 'alt':
                                    tablehtml += '<th title="Alternative">' + key + '</th>';
                                    break;
                                case 'disease':
                                    tablehtml += '<th>Disease</th>';
                                    break;
                                case 'evidence_type':
                                    tablehtml += '<th>Type</th>';
                                    break;
                                case 'evidence_direction':
                                    tablehtml += '<th title="Supports">Sup</th>';
                                    break;
                                case 'evidence_level':
                                    tablehtml += '<th>Level</th>';
                                    break;
                                case 'clinical_significance':
                                    tablehtml += '<th>Clinical Significance</th>';
                                    break;
                                case 'pubmed_id':
                                    tablehtml += '<th>Pubmed</th>';
                                    break;
                                case 'citation':
                                    tablehtml += '<th>Citation</th>';
                                    break;
                                case 'rating':
                                    tablehtml += '<th>Rating</th>';
                                    break;
                                case 'variant_summary':
                                    tablehtml += '<th>Statement</th>';
                                    break;
                                case 'variant':
                                    tablehtml += '<th>Variant</th>';
                                    break;
                                case 'drugs':
                                    tablehtml += '<th>Drugs</th>';
                                    break;
                                case 'evidence_statement':
                                    tablehtml += '<th>Statement</th>';
                                    break;
                                default:
                                    tablehtml += '<th>' + key + '</th>';
                                    break;
                            }
                        });
                        if (tableid == "completepanelsubtable") {
                            columnindices['arti_score'] = i;
                            columnindices['poly_score'] = i + 1;
                            columnindices['appreci8'] = i + 2;
                            columnindices['appreci8_arti_protocoll'] = i + 3;
                            columnindices['appreci8_poly_protocoll'] = i + 4;



                            tablehtml += '<th>Artifact Score</th>' +
                                '<th>Polymorphism Score</th>' +
                                '<th>appreci8</th>' +
                                '<th>appreci8 arti protocoll</th>' +
                                '<th>appreci8 poly protocoll</th>';

                        }
                        tablehtml += '</tr></thead><tbody>';
                        var countButtons = 1;
                        $.each(tabledata, function(i, item) {

                            if ((item['regionTypes'] != undefined && (item['regionTypes'] === 'structural' || item['regionTypes'].indexOf('exonic') >= 0 || item['regionTypes'].indexOf('protein_coding') >= 0)) &&
                                !(item['varTypes'] != undefined && (item['varTypes'] === 'synonymous SNV'))) {
                                // Hotspot Variant --> bright red
                                if (item['inHotspot'] != undefined && item['inHotspot'] != '') {
                                    tablehtml += '<tr class="coloredvariant1">';
                                }

                                // CLINSIG pathogenic OR COSMIC > 1!! --> bright orange
                                else if ((item['ClinVar_Significance'] != undefined && (item['ClinVar_Significance'].indexOf('athogenic') > -1)) || ((item['Cosmic_NrHaemato'] != undefined && item['Cosmic_NrHaemato'] > 1) && item['Cosmic_SNP'] == 0)) {
                                    tablehtml += '<tr class="coloredvariant2">';
                                }

                                // bright yellow
                                else {
                                    tablehtml += '<tr class="coloredvariant3">';
                                }
                            } else {
                                // no colour
                                tablehtml += '<tr>';
                            }
                            if (tableid == "completepanelsubtable") {
                                tablehtml += '<td><button id="lupe" type="button" class="btn btn-default btn-xs lens lupe' + countButtons + '" aria-label="Left Align"><span class="glyphicon glyphicon-zoom-in" aria-hidden="true"></span></button>  <button id="viewingenomebrowserTABLEbutton" title="View in genome browser" type="button" class="btn btn-default btn-xs" aria-label="Left Align"><span class="glyphicon glyphicon-eye-open" aria-hidden="true"></span></button>  </td>'; //at the start of every row we create a lens- and eye-symbol as buttons for the additional info table and the genome browser
                                countButtons++;
                            }
                            var pubmed_id; //to append the pubmed_id below the citation

                            $.each(item, function(key, value) {
                                if (value != null) {
                                    if (key == 'evidence_direction') {
                                        value = value.replace(/Supports/g, 'yes');
                                        value = value.replace(/Does Not Support/g, 'no');
                                    } else if (key == 'Transcripts' || key == 'varTypes' || key == 'regionTypes' || key == 'Exons' || key == 'Codons' || key == 'Proteins' || key == 'Impacts' || key == 'Provean_Scores') {
                                        value = value.replace(/,/g, '<br>');
                                    } else if (key == 'CosmicSites' || key == 'CosmicID' || key == 'function') {
                                        value = value.replace(/,/g, ', ');
                                        if (key == 'CosmicID') {
                                            var values = value.split(', ');
                                            var uniquevalues = [];
                                            $.each(values, function(i, el) {
                                                if ($.inArray(el, uniquevalues) === -1) uniquevalues.push(el);
                                            });
                                            value = uniquevalues.join(', ');
                                        }
                                    } else if (key == 'ClinVar_Disease') {
                                        value = value.replace(/\|/g, '|<br>');
                                    } else if (key == 'Clinvar_Status') {
                                        value = value.replace(/,/g, ',<br>');
                                    } else if (key == 'Gene') {
                                        value = value.replace(/;/g, '<br>');
                                        value = value.replace(/\|/g, '| ');
                                        value = '<a href="http://www.genecards.org/cgi-bin/carddisp.pl?gene=' + value + '" target="_blank">' + value + '</a>'; //genename als link
                                    } else {
                                        value = value.replace(/;/g, '<br>');
//                                        value = value.replace(/\|/g, '| ');
                                    }
                                    tablehtml += '<td>' + value + '</td>';
                                } else {
                                    tablehtml += '<td> </td>';
                                }
                            });
                            if (chosentab == 'coverage') {
                                var percent = (10000 - Math.round(item['nr_less20'] * 10000 / item['width'])) / 100;
                                tablehtml += '<td><div class="progress" style="text-align:center;position:relative;margin-bottom:0px;"><span class="pb-centered">' + percent + '%</span>' +
                                    '<div class="progress-bar" role="progressbar" style="width: ' + percent + '%;"></div></div></td>';
                            }

                            if (tableid == chosentab + 'subtable') {

                                tablehtml += '<td></td>' //Artifact-Score
                                    +
                                    '<td></td>' //Poly-Score
                                    +
                                    '<td></td>' //appreci8-Score
                                    +
                                    '<td></td>' //protocoll-arti-Score
                                    +
                                    '<td></td>'; //protocoll-poly-Score

                            }
                            tablehtml += '</tr>';
                        });

                        tablehtml += '</tbody></table></div>';
                    }
                    return tablehtml;
                }

                /*  FUNCTION createGeneOverview
                 *   Create the Gene Buttons which we need for the 'Filter by Genes' window
                 */
                function createGeneOverview() {
                    $.ajax({
                        type: "POST",
                        url: "sampleinteraction.php",
                        data: "action=getGeneOverview&sid=" + sid + "&design=" + designID,
                        dataType: 'json',
                        success: function(ret) {
                            if (debug) console.log("success case ajax 26");
                            if (ret[0] == 1) {
                                var genesHTML = '';
                                if (ret[1].length > 0) {
                                    genesHTML = '<p style="margin: 10px 30px 10px;">';
                                    $.each(ret[1], function(i, item) {
                                        if (item['NrMutations'] > 0) {
                                            genesHTML += '<button class="btn btn-mutations genebutton" genename="' + item['gene_name'] + '" chr="' + item['chr'] + '" start="' + item['start'] + '" end="' + item['end'] + '">' +
                                                item['gene_name'] + ' <span class="badge" id="' + item['gene_name'] + 'badge">' + item['NrMutations'] + '</span>' +
                                                '<span class="filter_checkmark" id="' + item['gene_name'] + 'filter_checkmark">&#10003;</span></button>';
                                        } else {
                                            if (item['NrBadCovered'] > 0) {
                                                genesHTML += '<button class="btn btn-badcovered genebutton">' + item['gene_name'] + '</button>';
                                            } else {
                                                genesHTML += '<button class="btn btn-default genebutton">' + item['gene_name'] + '</button>';
                                            }
                                        }
                                    });
                                    genesHTML += '</p><div style="margin: 10px 30px 10px; float: left;"><button class="btn btn-mutations"></button><label>&nbsp;Mutation detected</label>' +
                                        '&nbsp; &nbsp; &nbsp; &nbsp;<button class="btn btn-default btn-filtered"></button><label>&nbsp;Filtered Mutations</label>' +
                                        '&nbsp; &nbsp; &nbsp; &nbsp;<button class="btn btn-default"></button><label>&nbsp;Mutation unlikely</label>' +
                                        '&nbsp; &nbsp; &nbsp; &nbsp;<button class="btn btn-badcovered"></button><label>&nbsp;Bad coverage</label></div>' +
                                        '<div style="margin: 10px 30px 10px; text-align: right; float: right;"><button type="button" class="btn btn-default genefilterCloseButton">Close</button></div>';
                                    $("#genespanel").append(genesHTML);

                                    $('.genebutton').css('width', '140px');

                                    $.each(genefilterselected, function(i, item) {
                                        $('#' + item[0] + 'filter_checkmark').show();
                                        $('.genebutton[genename=' + item[0] + ']').addClass('active_filter'); //checks the filtered gene in 'filter by gene'


                                        $('#selectedgenes').append('<button class="btn btn-select generemovebutton" genename="' + item[0] + '">' +
                                            item[0] + ' <span class="glyphicon glyphicon-remove"></span></button>');
                                        $('#nogenesselected').hide();
                                    });
                                } else {
                                    genesHTML = "There are no records to show";
                                    $("#genespanel").append(genesHTML);
                                    //$('.filterpanel_loading').hide();
                                }
                            } else {
                                displayError(ret[1] + "[49]");
                            }
                        },
                        error: function(xhr, status, errorThrown) {
                            displayError(errorThrown + "[50]");
                            pageloadingflag = false;
                        }
                    });
                }

                /*  FUNCTION createMainTable
                 *   Create the Variant Inspector Table
                 */
                function createMainTable() {
                    $('#tablesubloader').hide();
                    $('#tablesubpanel').append(getTableHtml(chosentab + 'subtable', false)).show();
                    var legendHTML = '<p style="color:#aaa; margin: 10px 0px;">Color-coding:</p>' +
                        '<svg width="930px" height="25px" xmlns="http://www.w3.org/2000/svg">' +
                        '<rect x="0%" y="1" height="16" rx="3" ry="3" width="2%" class="coloredvariant1"/>' +
                        '<text x="2.5%" y="16" height="16" width="10%" font-size="10">coding-variants in hotspot region</text>' +
                        '<rect x="19%" y="1" height="16" rx="3" ry="3" width="2%" class="coloredvariant2"/>' +
                        '<text x="21.5%" y="16" height="16" width="10%" font-size="10">coding-variants with pathogenous rating by ClinVar or COSMIC</text>' +
                        '<rect x="52.5%" y="1" height="16" rx="3" ry="3" width="2%" class="coloredvariant3"/>' +
                        '<text x="55%" y="16" height="16" width="10%" font-size="10">protein-coding variants</text>' +
                        '</svg>';
                    $('#tablesubpanel').append(legendHTML);
                    setupTablesorterMainTable(chosentab + 'subtable', 'subcolumnchooser');
                    $('#tablesubpanel').show(); //added for faster loading here instead of above

                    $('#subcolumnchooser').show();
                    $('#' + chosentab + 'subtablescrollpanel').scrollTop(scrollposition);

                    if (selectedrow > 0) {
                        $('#' + chosentab + 'subtable tr:eq(' + selectedrow + ')').trigger("tablerowclicked");
                    }
                    $("#subcolumnchooser").append('<button id="columnfiltersbutton" type="button" class="btn btn-default pull-right">Column Filters</button>');
                    $("#subcolumnchooser").append('<button id="downloadcsvbutton" type="button" class="btn btn-default pull-right">Download CSV</button>');
                }


                /*  FUNCTION setupTablesorterMainTable(tableid, columnselectorcontainer)
                 *   Creates the bootstrap tablesorter for a given table
                 *   The function "getTableHtml" creates the table html code
                 *
                 */
                function setupTablesorterMainTable(tableid, columnselectorcontainer) {
                    $('#' + columnselectorcontainer).empty().append('<button id="' + tableid + 'popover" type="button" class="btn btn-default pull-right columnchooserbutton">Select Columns</button><div class="hidden"><div id="' + tableid + 'popover-target" class="popover-target"></div></div>');


                    $('#' + tableid).tablesorter({
                        theme: 'bootstrap',
                        headerTemplate: '{content} {icon}',
                        initWidgets: true,
                        widgets: ['zebra', 'uitheme', 'columnSelector', 'resizable', 'saveSort', 'stickyHeaders', 'filter', 'toggle-ts'],

                        widgetOptions: {
                            zebra: ['even', 'odd'],
                            columnSelector_mediaquery: true,
                            columnSelector_saveColumns: false,
                            resizable: false,
                            stickyHeaders_attachTo: '#' + tableid + 'scrollpanel',
                            stickyHeaders_offset: 0,
                            filter_columnFilters: true,
                            columnSelector_mediaqueryName: 'Show all columns'
                        }
                    });






                    $('.tablesorter-filter-row').hide();

                    // call this function to copy the column selection code into the popover
                    //create column-selection click boxes etc
                    $.tablesorter.columnSelector.attachTo($('#' + tableid), '#' + tableid + 'popover-target');

                    //replacing the class will delete the automated selection after each click event
                    //therefore we have to create our own event (see close button)


                    //setup input checkboxes
                    var checkboxes = "";
                    var count = 0;

                    $('#completepanelsubtable').children().first().find('th').each(function() {
                        checkboxes += '<div><label><input type="checkbox" class="" id="ColumnClickBox' + count + '" >' + $(this).text() + '</label></div>';
                        count++;
                    });

                    $('#' + tableid + 'popover-target').empty().append('Search: <input id="columnChooserSearch" class="form-control" type="text" style="width: 200px;"></input>' +
                        '<div class="right-element"><button type="button" class="btn btn-default" id="CloseColumnChooser" style="margin-top: -9px; background-color: #E56567;"><span class="glyphicon glyphicon-remove"></span></button>&nbsp;&nbsp;</div>' +
                        '<hr style="width: 100%;margin: 3px;"> ' +
                        '<div id="columnchooserClickBoxes" class="pre-scrollable">' + checkboxes + "</div><center><div id='columnchooserButtons' margin-bottom='9'></div></center>");

                    var selected = loadedSettings["selectedColumns"].replace("[", "").replace("]", "").split(",");
                    setupClickboxes(selected);

                    $('#columnchooserClickBoxes').css("max-height", "610px");
                    $('#' + tableid + 'popover')
                        .popover({
                            placement: 'left',
                            html: true, // required if content has HTML
                            content: $('#' + tableid + 'popover-target'),
                            trigger: 'manual'
                        })
                        .on("show.bs.popover", function() {
                            if (debug) console.log("Trigger 58.5");
                            $(this).data("bs.popover").tip().css("max-width", "615px")
                        });
                    $('#columnchooserButtons').append('<button type="button" class="btn btn-default columnchooserselectallbutton">select all</button>&nbsp;');
                    $('#columnchooserButtons').append('<button type="button" class="btn btn-default columnchooserDefaultButton">default</button>&nbsp;');
                    $('#columnchooserButtons').append('<button type="button" class="btn btn-default columnchoosersavebutton">save</button>');
                }



                //  FUNCTION setupTablesorterCivicTable(tableid, columnselectorcontainer)
                //  See FUNCTION setupTablesorterMainTable(...) above
                function setupTablesorterCivicTable(tableid, columnselectorcontainer) {
                    $('#' + tableid).tablesorter({
                        theme: 'bootstrap',
                        headerTemplate: '{content} {icon}',
                        initWidgets: true,
                        widgets: ['zebra', 'uitheme', 'columnSelector', 'resizable', 'saveSort', 'stickyHeaders', 'filter'],

                        widgetOptions: {
                            zebra: ['even', 'odd'],
                            columnSelector_mediaquery: false,
                            columnSelector_saveColumns: false,
                            resizable: true,
                            stickyHeaders_attachTo: '#' + tableid + 'scrollpanel',
                            stickyHeaders_offset: 0,
                            filter_columnFilters: true
                        }
                    });
                    $('.tablesorter-filter-row').hide();
                    var literatureWidth = "100%";
                    $('#' + tableid).css({
                        "width": literatureWidth,
                        "min-width": literatureWidth,
                        "max-width": literatureWidth,
                    });
                }


                /*  FUNCTION saveSettings
                 *   Save the user Settings to the database
                 */
                function saveSettings() {
                    if (saveSettingsFlag) {
                        var selectedcolumns = new Array();
                        for (i = 0; i < $('#' + chosentab + 'subtable').data('tablesorter').columns; i++) {
                            if ($('#' + chosentab + 'subtable th:eq(' + i + ')').is(':visible')) {
                                selectedcolumns.push(i);
                            }
                        }

                        var widthArray = getColumnWidths();

                        var datastring = 'action=saveSettings&regionFilter=' + $('#regionsslider').slider('value') +
                            '&typeFilter=' + $('#typesslider').slider('value') +
                            '&exclusionFilters=' + JSON.stringify(exclusionfilterselected) +
                            '&selectedColumns=' + JSON.stringify(selectedcolumns) +
                            '&sortList=' + JSON.stringify($('#completepanelsubtable').data('tablesorter').sortList) +
                            '&columnWidth=' + widthArray;
                        if (debug) console.log(datastring);
                        if (debug) console.log("exlusionfilterselected: " + exclusionfilterselected);





                        $.ajax({
                            type: 'POST',
                            url: 'userinteraction.php',
                            data: datastring,
                            dataType: 'json',
                            success: function(ret) {
                                if (debug) console.log("success case ajax 28");
                                if (ret[0] == 1) {
                                    //if(debug) console.log(ret[1]);
                                } else {
                                    displayError(ret[1] + "[53]");
                                }
                            },
                            error: function(xhr, status, errorThrown) {
                                displayError(errorThrown + "[54]");
                            }
                        });




                        //Store Settings locally
                        loadedSettings.regionFilter = JSON.stringify($('#regionsslider').slider('value'));
                        loadedSettings.typeFilter = JSON.stringify($('#typesslider').slider('value'));
                        loadedSettings.exclusionFilters = JSON.stringify(exclusionfilterselected);
                        loadedSettings.selectedColumns = JSON.stringify(selectedcolumns);
                        loadedSettings.sortList = JSON.stringify($('#completepanelsubtable').data('tablesorter').sortList);
                    }
                }





                /*  FUNCTION loadSettingsAtStart
                 *   Load the user Settings so we can use these Settings later.
                 */
                function loadSettingsAtStart() {
                    //if already loaded: no ajax-call else apply settings directly.
                    saveSettingsFlag = false; //lock savesettings until local or db settings applied.
                    if (loadedSettings == null) {
                        $.ajax({
                            type: 'POST',
                            url: 'userinteraction.php',
                            data: 'action=loadSettings',
                            dataType: 'json',
                            async: false,
                            success: function(ret) {
                                if (debug) console.log("success case ajax 29");
                                if (ret[0] == 1) {
                                    if (ret[1] == false) {
                                        //create usersettings row if missing
                                        $.ajax({
                                            type: 'POST',
                                            url: 'userinteraction.php',
                                            data: 'action=createMissingSettings&regionFilterDefault=' + regionFilterDefault + "&typeFilterDefault=" + typeFilterDefault + "&exclusionFiltersDefault=" + exclusionFiltersDefault + "&selectedColumnsDefault=" + "[" + selectedColumnsDefault + "]" + "&sortListDefault=" + sortListDefault + "&columnWidthDefault=" +
                                                columnWidthDefault,
                                            dataType: 'json',
                                            success: function(ret) {
                                                if (debug) console.log("success case ajax 30");
                                                loadSettingsAtStart();
                                            },
                                            error: function(xhr, status, errorThrown) {
                                                displayError(errorThrown + "[55]");
                                                pageloadingflag = false;
                                            }
                                        });

                                    } else {
                                        loadedSettings = ret[1];
                                        //Set Default Values if null
                                        if (loadedSettings['regionFilter'] == null) {
                                            loadedSettings['regionFilter'] = regionFilterDefault;
                                        }
                                        if (loadedSettings['typeFilter'] == null) {
                                            loadedSettings['typeFilter'] = typeFilterDefault;
                                        }
                                        if (loadedSettings['exclusionFilters'] == null) {
                                            loadedSettings['exclusionFilters'] = exclusionFiltersDefault;
                                        }
                                        if (loadedSettings['selectedColumns'] == null) {
                                            loadedSettings['selectedColumns'] = '[' + selectedColumnsDefault + ']';
                                        } else if (loadedSettings['selectedColumns'] == "[]") {
                                            loadedSettings['selectedColumns'] = '[' + selectedColumnsMinimum + ']';
                                        }
                                        if (loadedSettings['columnWidth'] == null) {
                                            loadedSettings['columnWidth'] = columnWidthDefault;
                                        }


                                    }
                                } else {
                                    displayError(ret[1] + "[56]");
                                }
                                saveSettingsFlag = true;
                            },
                            error: function(xhr, status, errorThrown) {
                                displayError(errorThrown + "[57]");
                                pageloadingflag = false;
                            }
                        });
                    }
                }

                /*  FUNCTION applySetting
                 *   Apply all user Settings (this may take some time for huge samples)
                 */
                function applySettings() {

                    //INPROGRESS test time
                    var start, time;
                    start = performance.now();

                    time = performance.now();
                    if (debug) console.log('Dauer apply Settings: calculate appreci8: ' + (time - start) + ' ms.');
                    start = performance.now();

                    // The filters are only activated for elements which have these classes.


                    if (loadedSettings['exclusionFilters'] != "" && loadedSettings['exclusionFilters'] != null) {
                        $.each(JSON.parse(loadedSettings['exclusionFilters']), function(key, e) {
                            switch (e) {
                                case 'artifacts':
                                    if (debug) console.log("excludeartifacts");
                                    $('#excludeartifacts').trigger('click');
                                    break;
                                case 'polymorphisms':
                                    if (debug) console.log("excludepolymorphisms");
                                    $('#excludepolymorphisms').trigger('click');
                                    break;
                                case 'benign':
                                    if (debug) console.log("excludebenign");
                                    $('#excludebenign').trigger('click');
                                    break;
                                case 'common':
                                    if (debug) console.log("excludecommon");
                                    $('#excludecommon').trigger('click');
                                    break;
                            }
                        });
                    }

                    time = performance.now();
                    if (debug) console.log('Dauer apply Settings: exclusionfilter: ' + (time - start) + ' ms.');
                    start = performance.now();


                    $('#regionsslider').slider('value', loadedSettings['regionFilter']);
                    $('#typesslider').slider('value', loadedSettings['typeFilter']);


                    time = performance.now();
                    if (debug) console.log('Dauer apply Settings: region und typeslider ' + (time - start) + ' ms.');
                    start = performance.now();



                    if (loadedSettings['sortList'] == "" || loadedSettings['sortList'] == null) {
                        loadedSettings['sortList'] = sortListDefault;
                    } else {
                        $('#completepanelsubtable').data('tablesorter').sortList = JSON.parse(loadedSettings['sortList']);
                    }
                    $('#completepanelsubtable').trigger('update');


                    time = performance.now();
                    if (debug) console.log('Dauer apply Settings: sortList: ' + (time - start) + ' ms.');
                    start = performance.now();

                }

                /*  FUNCTION createGeneHotspotsTab
                 *   Creates the content of the 'Hotspots' tab
                 */
                function createGeneHotspotsTab() {
                    chosentab = 'hotspots';
                    $('.nav-tabs a[tab="hotspots"]').tab('show');
                    tabselected();

                    //set columnWidth for each th
                    //part of our solution to fix the buggy tablesorter resize plugin -.-
                    //see function resizeTable, too
                    setColumnWidth();

                    firstLoad = false;
                    saveSettingsFlag = true;
                    pageloadingflag = false;
                }

                /*  FUNCTION applySettingsSelectColumns
                 *   Selects the chosen columns (used while loading the website).
                 */
                function applySettingsSelectColumns() {
                    saveSettingsFlag = false; //lock savesettings until local or db settings applied.

                    var test;
                    var start = performance.now();
                    $('#completepanelsubtable').trigger('refreshColumnSelector', ['selectors', JSON.parse(loadedSettings['selectedColumns'])]);
                    var time = performance.now();
                    if (debug) console.log('Dauer: ' + (time - start) + ' ms.');
                }

                /*  FUNCTION setColumnWidth
                 *   This function is part of our solution to fix the buggy tablesorter resize plugin -.-
                 *   The resizer doesn't have the same size as the table solumns at all time
                 *   This Bug even occurs at the demo page https://mottie.github.io/tablesorter/docs/example-widget-resizable.html
                 *   We further implemented the functions resizeTable() and setResizableColumnWidth() to fix this behaviour.
                 */
                function setColumnWidth() {
                    if (loadedSettings['columnWidth'] != null && loadedSettings['columnWidth'] != "[]") {
                        //Set column-Width:
                        var splitColumnWidth = loadedSettings['columnWidth'].replace("[", "");
                        var sumWidth = 0; //set resizable width 
                        splitColumnWidth = splitColumnWidth.replace("]", "");
                        splitColumnWidth = splitColumnWidth.replace(/'/g, '');
                        splitColumnWidth = splitColumnWidth.split(",");

                        for (i = 0; i < splitColumnWidth.length; i++) {
                            $($('#completepanelsubtable').children().first().find('th')[i]).css({
                                "width": splitColumnWidth[i],
                                "min-width": splitColumnWidth[i],
                                "max-width": splitColumnWidth[i],
                            });
                            sumWidth += splitColumnWidth[i];
                            $($('.tablesorter-resizable-handle')[i]).css('left', sumWidth);
                        }
                        setResizableColumnWidth();
                    }
                }

                /*FUNCTION setResizableColumnWidth()
                 *   This function is part of our solution to fix the buggy tablesorter resize plugin -.-
                 *   The resizer doesn't have the same size as the table solumns at all time
                 *   This Bug even occurs at the demo page https://mottie.github.io/tablesorter/docs/example-widget-resizable.html
                 *   We further implemented the functions resizeTable() and setColumnWidth() to fix this behaviour.
                 */
                function setResizableColumnWidth() {
                    var sumWidth = -3; //set resizable width 
                    var splitColumnWidth = getColumnWidths().replace("[", "");
                    splitColumnWidth = splitColumnWidth.replace("]", "");
                    splitColumnWidth = splitColumnWidth.replace(/'/g, '');
                    splitColumnWidth = splitColumnWidth.replace(/px/g, '');
                    splitColumnWidth = splitColumnWidth.split(",");

                    for (i = 0; i < splitColumnWidth.length; i++) {
                        if ($($('#completepanelsubtable').children().first().find('th')[i]).is(':visible')) {
                            sumWidth += parseInt(splitColumnWidth[i]);
                            $($('.tablesorter-resizable-handle')[i]).css('left', sumWidth + "px");
                        }
                    }
                }


                /*  FUNCTION scrollTableLeft
                 *   Move the table scroll bar to the left side. Otherwise the function "resizeTable()" can't fix the table width.
                 */
                function scrollTableLeft() {
                    oldScrollbarPositionLeft = $('#completepanelsubtablescrollpanel').scrollLeft();
                    if (debug) console.log("position: " + oldScrollbarPositionLeft);
                    $('#completepanelsubtablescrollpanel').scrollLeft(0);
                    //$('#completepanelsubtablescrollpanel').scrollTop(0);
                }

                /*  FUNCTION scrollTableRight
                 *   Return to the old position, before scrollTableLeft() was executed (this doesn't allways work, if the resize widget was too much out of place). But it will work if the bug didn't occur
                 *   So if the bug didn't occur: nothing happend.
                 *   If the bug occured: we could fix the bug and maybe changed the table-scrollbar position a bit.
                 */
                function scrollTableRight() {
                    $('#completepanelsubtablescrollpanel').scrollLeft(oldScrollbarPositionLeft);
                }


                /*  FUNCTION fillEmptyCells(arr)
                 *   This function fills all empty cells of the given additional info tables with the place holder symbol "-"
                 *   @Param arr: a array with tableRowIDs
                 */
                function fillEmptyCells(arr) {
                    //Fill all table-cells with place holder symbol if the cell is empty 
                    for (i = 0; i < arr.length; i++) {
                        var selector = "#" + arr[i];
                        var $row = $(selector);
                        $row.find("td").each(function() {
                            var contentString = this.innerHTML; //innerText doesnt recognize a blank (" ") as the same symbol as a " <br>" -> use innerHTML
                            if (contentString == " " || contentString == "" || contentString == " <br>" || contentString == "<br>") {
                                this.innerText = "-";
                            }
                        });
                    }
                }


                /*  FUNCTION addPercentSymbol
                 *   Adds a symbol to visualize the different rankscores.
                 */
                function addPercentSymbol() {
                    var oldContent;
                    var percent;
                    var rankscoreID;
                    var title;
                    var rightShiftImage; //shift image right by ... pixels
                    var textWidth = 24; //necessary pixels for the percent text

                    $('.addPercentSymbol').each(function() {
                        rankscoreID = undefined;
                        if ($(this).hasClass('addPercentSymbolProvean')) {
                            rankscoreID = "PROVEAN_rankscore";
                        } else if ($(this).hasClass('addPercentSymbolSIFT')) {
                            rankscoreID = "SIFT_rankscore";
                        } else if ($(this).hasClass('addPercentSymbolPolyphen2_HDIV')) {
                            rankscoreID = "Polyphen2_HDIV_rankscore";
                        } else if ($(this).hasClass('addPercentSymbolPolyphen2_HVAR')) {
                            rankscoreID = "Polyphen2_HVAR_rankscore";
                        } else if ($(this).hasClass('addPercentSymbolFATHMM')) {
                            rankscoreID = "FATHMM_converted_rankscore";
                        } else if ($(this).hasClass('addPercentSymbolMutationTaster')) {
                            rankscoreID = "MutationTaster_converted_rankscore";
                        } else if ($(this).hasClass('addPercentSymbolMutationAssessor')) {
                            rankscoreID = "MutationAssessor_score_rankscore";
                        }


                        if (debug) console.log("rankscore: " + rankscoreID);

                        if (rankscoreID == undefined) {
                            return;
                        }
                        percent = parseInt(parseFloat($(lastActivatedTableRow)[0].cells[columnindices[rankscoreID]].innerHTML) * 100);
                        if (isNaN(percent)) {
                            return;
                        }
                        if (percent >= 50) {
                            title = "Prediction score is worse than " + percent + "% of all possible SNV in coding regions.";
                        } else {
                            title = "Prediction score is better than " + (100 - percent) + "% of all possible SNV in coding regions."
                        }


                        oldContent = $(this).html();
                        rightShiftImage = parseInt(50 * percent / 100 - (textWidth / 2)) + "px";
                        if (debug) console.log("right: " + rightShiftImage + " - 50 * percent /100: " + 50 * percent / 100 + " - percent: " + percent);


                        var imageScore = '<img src="common/score-img3.jpg" class="right-element" width="100%" style="padding-top: 10px; margin-bottom:-4px">';

                        var arrowTable = '<table style="left:' + rightShiftImage + ';position:relative;"><tr style="line-height: 0;height: 5px;">' +
                            '<td style="width: ' + textWidth + 'px;text-align: center;">' +
                            '<span class="glyphicon glyphicon-chevron-up"></span>' +
                            '</td></tr>' +
                            //'<tr><td style="font-size:smaller; text-align:center;">' + percent / 100 + '</td></tr>' + //show procent as text below the arrow
                            '</table>';

                        newContent = '<table width="100%"><tr><td width="*">' + oldContent + '</td><td width="70px" style="padding-left:10px; padding-right:10px;" title="' + title + '">' + imageScore + arrowTable + '</td></tr></table>';
                        $(this).html(newContent);

                    });
                }

                /*  FUNCTION editTableInformations
                 *   this functions changes the content of some td elements of the Variant Inspector table
                 */
                function editTableInformations() {
                    var editTable = 1;
                    if (debug) console.log("edit table" + editTable);


                    //delete unnecessary <br> in the given columns, because they cause problems with the additional info table later (cause we use .split("<br>") there)
                    //If we know where this unnecessary <br> came from we can delete this part
                    var splitString = "SIFT_pred,SIFT_Scores,PROVEAN_pred,Polyphen2_HVAR_pred,Polyphen2_HDIV_pred,MutationTaster_pred,MutationAssessor_pred,FATHMM_pred"; //gfs auch noch nötig: 
                    var split = splitString.split(",");
                    $.each($('#completepanelsubtable > tbody > tr'), function(key, row) {
                        for (s = 0; s < split.length; s++) {
                            var oldContent = $(this)[0].cells[columnindices[split[s]]].innerHTML;
                            var newContent = "";
                            var subStringLast = oldContent.substr(oldContent.length - 4, oldContent.length);
                            if (subStringLast == "<br>") {
                                newContent = oldContent.substr(0, oldContent.length - 4);
                            } else {
                                newContent = oldContent;
                            }
                            $(this)[0].cells[columnindices[split[s]]].innerHTML = newContent;
                        }
                    });


                    editTable++;
                    if (debug) console.log("edit table" + editTable);


                    //split by ",":    SIFT Scores,CosmicSites,CosmicID,dbSNP
                    splitString = "SIFT_Scores,CosmicSites,CosmicID,dbSNP";
                    split = splitString.split(",");
                    $.each($('#completepanelsubtable > tbody > tr'), function(key, row) {
                        for (s = 0; s < split.length; s++) {
                            var oldContent = $(this)[0].cells[columnindices[split[s]]].innerHTML.split(',');
                            var newContent = "";
                            //Split
                            for (i = 0; i < oldContent.length; i++) {
                                newContent += oldContent[i];
                                if (i < oldContent.length - 1) {
                                    newContent += '<br>';
                                }
                            }
                            $(this)[0].cells[columnindices[split[s]]].innerHTML = newContent;
                        }
                    });


                    editTable++;
                    if (debug) console.log("edit table" + editTable);


                    //use <b> to label the canonical transcript
                    $.each($('#completepanelsubtable > tbody > tr'), function(key, row) {
                        var canonical = undefined;
                        canonical = $(this)[0].cells[columnindices['CanonicalTranscript']].innerHTML;

                        if (canonical != undefined && canonical != "" && canonical != " ") {
                            var re = new RegExp(canonical, 'g'); //Use the regExpr to replace all occurences of the String "canonical"
                            //replace all occurences of the canonical transcript in Transcripts, Transcripts_dbNSFP, Transcript_id_VEST3
                            if ($(this)[0].cells[columnindices['Transcripts']].innerHTML.split("br").length > 1) {
                                $(this)[0].cells[columnindices['Transcripts']].innerHTML = $(this)[0].cells[columnindices['Transcripts']].innerHTML.replace(re, "<b>" + canonical + "</b>");
                            }
                            if ($(this)[0].cells[columnindices['Transcripts_dbNSFP']].innerHTML.split("br").length > 1) {
                                $(this)[0].cells[columnindices['Transcripts_dbNSFP']].innerHTML = $(this)[0].cells[columnindices['Transcripts_dbNSFP']].innerHTML.replace(re, "<b>" + canonical + "</b>");
                            }
                            if ($(this)[0].cells[columnindices['Transcript_id_VEST3']].innerHTML.split("br").length > 1) {
                                $(this)[0].cells[columnindices['Transcript_id_VEST3']].innerHTML = $(this)[0].cells[columnindices['Transcript_id_VEST3']].innerHTML.replace(re, "<b>" + canonical + "</b>");
                            }
                        }
                    });

                    editTable++;
                    if (debug) console.log("edit table" + editTable);


                    //use <b> to label values which are related to the canonical transcript
                    var count = -1;
                    $.each($('#completepanelsubtable > tbody > tr'), function(key, row) {
                        count++;
                        var canonical = undefined;
                        canonical = $(this)[0].cells[columnindices['CanonicalTranscript']].innerHTML;

                        if (canonical != undefined && canonical != "" && canonical != " ") {
                            var canonicalNumbers = undefined;
                            var splitString = $(this)[0].cells[columnindices['Transcripts']].innerHTML.split('<br>');
                            for (i = 0; i < splitString.length; i++) {
                                if (splitString[i].indexOf("<b>") >= 0) {
                                    if (canonicalNumbers == undefined) {
                                        canonicalNumbers = [i];
                                    } else {
                                        canonicalNumbers.push(i);
                                    }
                                }
                            }

                            if (canonicalNumbers != undefined) {
                                var arr = ["varTypes", "regionTypes", "Exons", "Codons", "Proteins", "Impacts", "Provean_Scores", "SIFT_Scores"];
                                for (j = 0; j < arr.length; j++) {
                                    var content = $(this)[0].cells[columnindices[arr[j]]].innerHTML.split('<br>');
                                    var newContent = "";
                                    var labeled = false;
                                    for (i = 0; i < content.length; i++) {
                                        if (i != 0) {
                                            newContent += "<br>";
                                        }
                                        for (k = 0; k < canonicalNumbers.length; k++) {

                                            if (i == canonicalNumbers[k]) {
                                                newContent += "<b>" + content[i] + "</b>";
                                                labeled = true;

                                            }
                                        }
                                        if (labeled == false) {
                                            newContent += content[i];
                                        } else {
                                            labeled = false;
                                        }

                                    }
                                    $(this)[0].cells[columnindices[arr[j]]].innerHTML = newContent;

                                }
                            }

                        }
                    });


                    editTable++;
                    if (debug) console.log("edit table" + editTable);


                    //show coloured prediction values 
                    var predColumns = "MutationAssessor_pred";
                    var predCol = predColumns.split(",");

                    $.each($('#completepanelsubtable > tbody > tr'), function(key, row) {
                        var predNew = "";
                        for (i = 0; i < predCol.length; i++) {
                            predNew = "";
                            var prediction = $(this)[0].cells[columnindices[predCol[i]]].innerHTML.split("<br>"); //welche einträge müssen verändert werden?...

                            for (j = 0; j < prediction.length; j++) {
                                switch (prediction[j]) {
                                    case "H":
                                        predNew += '<span title="High" style="color:red;"><b>High</b></span>';
                                        break;
                                    case "M":
                                        predNew += '<span title="Medium" style="color:#FF7878;"><b>Medium</b></span>';
                                        break;
                                    case "L":
                                        predNew += '<span title="Low" style="color:orange;"><b>Low</b></span>';
                                        break;
                                    case "N":
                                        predNew += '<span title="Neutral" style="color:green;"><b>Neutral</b></span>';
                                        break;
                                    default:
                                        predNew += prediction[j];
                                        break;
                                }
                                if (j < prediction.length) {
                                    predNew += "<br>";
                                }
                                $(this)[0].cells[columnindices[predCol[i]]].innerHTML = predNew;
                            }
                        }
                    });

                    //show more coloured prediction values 
                    predColumns = "PROVEAN_pred,SIFT_pred,SIFT_Scores,FATHMM_pred,MutationTaster_pred";
                    predCol = predColumns.split(",");
                    $.each($('#completepanelsubtable > tbody > tr'), function(key, row) {
                        var predNew = "";
                        for (i = 0; i < predCol.length; i++) {
                            predNew = "";
                            var prediction = $(this)[0].cells[columnindices[predCol[i]]].innerHTML.split("<br>"); //welche einträge müssen verändert werden?...

                            for (j = 0; j < prediction.length; j++) {
                                switch (prediction[j]) {
                                    case "D":
                                        predNew += '<span title="Deleterious" style="color:red;"><b>Deleterious</b></span>';
                                        break;
                                    case "N":
                                        predNew += '<span title="Neutral" style="color:green;"><b>Neutral</b></span>';
                                        break;
                                    case "T":
                                        predNew += '<span title="Tolerated" style="color:green;"><b>Tolerated</b></span>';
                                        break;
                                    default:
                                        predNew += prediction[j];
                                        break;
                                }
                                if (j < prediction.length) {
                                    predNew += "<br>";
                                }
                                $(this)[0].cells[columnindices[predCol[i]]].innerHTML = predNew;
                            }
                        }
                    });
                    //show more coloured prediction values 
                    predColumns = "Polyphen2_HDIV_pred,Polyphen2_HVAR_pred";
                    predCol = predColumns.split(",");
                    $.each($('#completepanelsubtable > tbody > tr'), function(key, row) {
                        var predNew = "";
                        for (i = 0; i < predCol.length; i++) {
                            predNew = "";
                            var prediction = $(this)[0].cells[columnindices[predCol[i]]].innerHTML.split("<br>"); //welche einträge müssen verändert werden?...

                            for (j = 0; j < prediction.length; j++) {
                                switch (prediction[j]) {
                                    case "D":
                                        predNew += '<span title="Probably Damaging" style="color:red;"><b>Prob. Damaging</b></span>';
                                        break;
                                    case "P":
                                        predNew += '<span title="Possibly Damaging" style="color:orange;"><b>Poss. Damaging</b></span>';
                                        break;
                                    case "B":
                                        predNew += '<span title="Benign" style="color:green;"><b>Benign</b></span>';
                                        break;
                                    default:
                                        predNew += prediction[j];
                                        break;
                                }
                                if (j < prediction.length) {
                                    predNew += "<br>";
                                }
                                $(this)[0].cells[columnindices[predCol[i]]].innerHTML = predNew;
                            }
                        }
                    });




                    editTable++;
                    if (debug) console.log("edit table" + editTable);




                    //link COSMIC ID and dbSNP ID
                    var splitString = "CosmicID,dbSNP";
                    var split = splitString.split(",");
                    for (s = 0; s < split.length; s++) {
                        $.each($('#completepanelsubtable > tbody > tr'), function(key, row) {

                            var oldContent = $(this)[0].cells[columnindices[split[s]]].innerHTML.split('<br>');
                            var newContent = "";
                            //Split
                            for (i = 0; i < oldContent.length; i++) {
                                switch (split[s]) {
                                    case "CosmicID":
                                        if (oldContent[i] == "-") {
                                            newContent += "-";
                                        } else
                                        if (oldContent[i].indexOf("COSM") != -1) {
                                            var id = oldContent[i].replace("COSM", "");
                                            newContent += '<a href="http://cancer.sanger.ac.uk/cosmic/mutation/overview?id=' + id + '" target="_blank">' + oldContent[i] + '</a>';
                                        } else {
                                            newContent += oldContent[i];
                                        }
                                        break;
                                    case "dbSNP":
                                        if (oldContent[i] == "-") {
                                            newContent += "-";
                                        } else
                                        if (oldContent[i].indexOf("rs") != -1) {
                                            newContent += '<a href="https://www.ncbi.nlm.nih.gov/SNP/snp_ref.cgi?searchType=adhoc_search&type=rs&rs=' + oldContent[i] + '" target="_blank">' + oldContent[i] + '</a>';

                                        } else {
                                            newContent += oldContent[i];
                                        }
                                        break;
                                    default:
                                        if (debug) console.log("Fehler! Es soll eine Verlinkung erstellt werden für die es keine Regel gibt!")
                                        break;
                                }
                                if (i < oldContent.length - 1) {
                                    newContent += "<br>";
                                }
                            }
                            $(this)[0].cells[columnindices[split[s]]].innerHTML = newContent;

                        });
                    }
                }



                /*  FUNCTION editAdditionalInfoTables
                 *   This function changes the content of the Additional Info Tables
                 */
                function editAdditionalInfoTables() {

                    //multiply values in the additional info tables if there is only 1 value for this column, but all others contain more values
                    multiplySingleValues();

                    //mark high values
                    var columns = [5] + ''; //FATHMM_score
                    markHighestValueRow("FunctionalPrediction2", columns);
                    columns = [3, 5] + ''; //Polyphen2_HDIV_score,Polyphen2_HVAR_score
                    markHighestValueRow("FunctionalPrediction3", columns);
                    columns = [2] + ''; //VEST3_score
                    markHighestValueRow("FunctionalPrediction6", columns);

                    //mark low values
                    columns = [1, 2] + ''; //Provean_Scores,SIFT_Scores
                    markLowestValueRow("FunctionalPrediction1", columns);
                    columns = [1, 3] + ''; //PROVEAN_score2,SIFT_score2
                    markLowestValueRow("FunctionalPrediction2", columns);


                    markCanonicalTranscriptRow("ProteinBasedLocationTable");
                    markCanonicalTranscriptRow("FunctionalPrediction1");
                    markCanonicalTranscriptRow("FunctionalPrediction2");
                    markCanonicalTranscriptRow("FunctionalPrediction6");



                    //fill empty cells with "-" for the following tables (currently all tables :D)
                    var arr = ["GenomicLocationTable", "ProteinBasedLocationTable", "DatabasesTable1", "DatabasesTable2", "Prevalence2Table1", "FunctionalPrediction1", "FunctionalPrediction2", "FunctionalPrediction3", "FunctionalPrediction4", "FunctionalPrediction5", "FunctionalPrediction6", "SequencingQuality1"];
                    fillEmptyCells(arr);


                    //add percent symbol for all elements with class .addPercentSymbol
                    addPercentSymbol();
                }



                /*  FUNCTION markLowestValueRow(tableRowID, columnIDs)
                 *   this function is looking for the lowest value in each of the given columns of the given tableRow
                 *   mark this value with <b>...</b>
                 *   @Param tableRowID: the id of the table
                 *   @Param columnID: the id of the column
                 */
                function markLowestValueRow(tableRowID, columnIDs) {
                    var selector = "#" + tableRowID;
                    var $row = $(selector);
                    var lowValue;
                    var lowValueCount = 0;
                    var columns = columnIDs.split(",");

                    if ($row.length == 0) { //wenn das Element mit der ID nicht existiert:
                        if (debug) console.log("ERROR: tried to modify $('" + selector + "'), but that element doesn't exist (function markLowestValueRow)");
                        return;
                    }
                    //mark every chosen column
                    for (i = 0; i < columns.length; i++) {
                        lowValue = 1000;
                        //look for the lowest value
                        var content = $row[0].cells[parseInt(columns[i])].innerHTML.split("<br>");
                        var cont;

                        if (content.length > 1) { //dont use this function if there is only 1 value

                            for (j = 0; j < content.length; j++) {
                                cont = parseFloat(content[j]);
                                if (!isNaN(cont) && cont < lowValue) {
                                    lowValue = cont;
                                    lowValueCount = 1;
                                } else if (isNaN(cont) || cont == lowValue) {
                                    lowValueCount++;
                                }

                            }

                            //dont label the value if all values are identical
                            var identical = false;
                            if (content.length == lowValueCount) {
                                identical = true;
                            }

                            if (lowValue != 1000 && !identical) {
                                var newContent = "";
                                for (j = 0; j < content.length; j++) {

                                    cont = parseFloat(content[j]);
                                    if (!cont.isNaN) {
                                        if (cont == lowValue) {
                                            newContent += "<b>" + content[j] + "</b>";
                                            if (j != content.length - 1) {
                                                newContent += "<br>";
                                            }
                                        } else {
                                            newContent += content[j];
                                            if (j != content.length - 1) {
                                                newContent += "<br>";
                                            }
                                        }
                                    } else {
                                        if (j != content.length - 1) {
                                            newContent += "<br>";
                                            if (debug) console.log("Warnung: möglicherweise wurde hier eine Zahl durch" + '"' + '"' + " ersetzt. Dieser Fall sollte jedenfalls nciht eintreten");
                                        }
                                    }
                                }
                                $row[0].cells[parseInt(columns[i])].innerHTML = newContent;
                            }
                        }
                    }
                }


                //as long as the number of rows in the first column equal to the maximum of the row this function works. Maybe change this in the future.
                /*  FUNCTION multiplySingleValues
                 *   show the same amount of rows for every column
                 *   -> use a value several times if there exists only 1 value for that column
                 */
                function multiplySingleValues() {
                    var countRows;
                    var oldContent;
                    var newContent;


                    //use this code if we want to multiply single values for some other td's in other tables, too
                    //the content of every td with this class will be multiplied
                    //
                    /*$('.muSiVa').each(function() { //muSiVa = multiply single values
                        countRows = $(this).parent().children().first().html().split("<br>").length; //number of rows of column 1
                        oldContent = $(this).html();
                        newContent = "";
                        var endWithBr = 0;

                        if (oldContent.substr(oldContent.length - 4, oldContent.length) == "<br>") {
                            if(debug) console.log("olde : br am ende");
                            endWithBr = 1;
                        }

                        if(debug) console.log("olde content: " + oldContent);
                        if (oldContent.split("<br>").length > 1 + endWithBr) {
                            return;
                        }

                        for (i = 0; i < countRows; i++) {
                            newContent += oldContent;
                        }
                        $(this).html(newContent);
                    });*/


                    //multiply the content of all functional prediction td's
                    var row = "#FunctionalPrediction";
                    var multRows = [row + "2", row + "3", row + "4", row + "5", row + "6"];

                    for (i = 0; i < multRows.length; i++) {
                        var columns = $(multRows[i] + " td");
                        if (debug) console.log("columns string: " + multRows[i] + " td")
                        countRows = Math.max(columns[0].innerHTML.split("<br>").length, columns[1].innerHTML.split("<br>").length); //use the maximum of column 1 and 2 as reference

                        for (j = 1; j < columns.length; j++) {
                            oldContent = columns[j].innerHTML;
                            newContent = "";
                            var endWithBr = 0;

                            if (oldContent.substr(oldContent.length - 4, oldContent.length) == "<br>") {
                                if (debug) console.log("olde : br am ende");
                                endWithBr = 1;
                            }

                            if (debug) console.log("olde content: " + oldContent);
                            if (oldContent.split("<br>").length > 1 + endWithBr) {
                                continue;
                            }

                            for (k = 0; k < countRows; k++) {
                                if (k > 0) {
                                    newContent += "<br>";
                                }
                                newContent += oldContent.replace("<br>", "");
                            }
                            columns[j].innerHTML = newContent;
                        }
                    }


                }




                //to do: maybe look for the index of the corresponding headername instead of using the index directly.
                /*  FUNCTION markHighestValueRow(tableRowID, columnIDs)
                 *   this function is looking for the highest value in each of the given columns of the given tableRow
                 *   mark this value with <b>...</b>
                 *   @Param tableRowID: the id of the table
                 *   @Param columnID: the id of the column
                 */
                function markHighestValueRow(tableRowID, columnIDs) {
                    var selector = "#" + tableRowID;
                    var $row = $(selector);
                    var highValue;
                    var highValueCount = 0;
                    var columns = columnIDs.split(",");

                    if ($row.length == 0) { //wenn das Element mit der ID nicht existiert:
                        if (debug) console.log("ERROR: tried to modify $('" + selector + "'), but that element doesn't exist (function: markHighestValueRow)");
                        return;
                    }

                    //mark every chosen column
                    for (i = 0; i < columns.length; i++) {
                        highValue = -1000;
                        //look for the highest value
                        var content = $row[0].cells[parseInt(columns[i])].innerHTML.split("<br>");
                        var cont;

                        if (content.length > 1) { //dont use this function if there is only 1 value

                            for (j = 0; j < content.length; j++) {
                                cont = parseFloat(content[j]);
                                if (!isNaN(cont) && cont > highValue) {
                                    highValue = cont;
                                    highValueCount = 1;
                                } else if (isNaN(cont) || cont == highValue) {
                                    highValueCount++;
                                }

                            }

                            //dont label the value if all values are identical
                            var identical = false;
                            if (content.length == highValueCount) {
                                identical = true;
                            }


                            if (highValue != -1000 && !identical) {
                                var newContent = "";
                                for (j = 0; j < content.length; j++) {
                                    cont = parseFloat(content[j]);
                                    if (!cont.isNaN) {
                                        if (cont == highValue) {
                                            newContent += "<b>" + content[j] + "</b>";
                                            if (j != content.length - 1) {
                                                newContent += "<br>";
                                            }
                                        } else {
                                            newContent += content[j];
                                            if (j != content.length - 1) {
                                                newContent += "<br>";
                                            }
                                        }
                                    } else {
                                        if (j != content.length - 1) {
                                            newContent += "<br>";
                                        }
                                    }
                                }
                                $row[0].cells[parseInt(columns[i])].innerHTML = newContent;
                            }
                        }
                    }
                }




                //this function will mark the canonical transcript line(s) for a given tableRowID
                //the tr element may(and likely will) consist of more than 1 line
                //FUNCTION markCanonicalTranscriptRow(tableRowID)
                function markCanonicalTranscriptRow(tableRowID) {

                    var selector = "#" + tableRowID;
                    var $row = $(selector);
                    if ($row.length == 0) { //wenn das Element mit der ID nicht existiert:
                        if (debug) console.log("ERROR: tried to modify $('" + selector + "'), but that element doesn't exist (function markCanonicalTranscriptRow)");
                        return;
                    }

                    var trans = $row[0].cells[0].innerHTML.split("<br>");
                    var canonicalNumbers = "";

                    if (trans.length > 1) { //dont use this function if there is only 1 value

                        //look for canonical transcripts and save the line as ", <lineNumber> , "
                        for (i = 0; i < trans.length; i++) {
                            if (trans[i].includes("<b>")) {
                                canonicalNumbers += "," + i + ",";

                            }
                        }

                        if (canonicalNumbers != "") {
                            selector = "#" + tableRowID;

                            var countRowsMax = 0;
                            //count maximum number of rows in the first td
                            var firstTd = $(selector)[0].cells[0].innerHTML.split('<br>');
                            countRowsMax = firstTd.length;


                            $(selector).find('td').each(function() {

                                //if td content ends with <br> delete this <br>
                                var oldContent = $(this).html();
                                var newContent = "";
                                var subStringLast = oldContent.substr(oldContent.length - 4, oldContent.length);
                                if (subStringLast == "<br>") {
                                    newContent = oldContent.substr(0, oldContent.length - 4);
                                } else {
                                    newContent = oldContent;
                                }


                                var tdContent = newContent.split("<br>");
                                var tdNew = "";

                                if (tdContent.length == countRowsMax || tdContent.length == 1) {
                                    for (i = 0; i < tdContent.length; i++) {
                                        if (canonicalNumbers.includes("," + i + ",") || tdContent.length == 1) {
                                            tdNew += '<div style="background-color:#e2dfde; width: 100%; padding-left: 6px; padding-right: 6px">';
                                            tdNew += tdContent[i];
                                            tdNew += '</div>';
                                        } else {
                                            tdNew += '<div style="padding-left: 6px; padding-right: 6px">';
                                            tdNew += tdContent[i];
                                            tdNew += '</div>';
                                        }
                                    }
                                    $(this).html(tdNew);

                                    //we set padding left and padding-right to 0, so the gray background fills the line
                                    //thats why we added a div with paddings in every line, to make it look like the padding would still exist
                                    $(this).css("padding-left", "0px");
                                    $(this).css("padding-right", "0px");
                                }
                            });
                        }
                    }
                }


                /* The Following Functions are used for generate Automated-Filtration */

                function isEmpty(input) {

                    return (input == null || input == " " || input == "" || input === ".");
                }

                function strLength(input) {

                    return input.length;
                }

                function stringContains(haystack, needle) {

                    return (haystack.indexOf(needle) >= 0);
                }

                function stringConcat(first, second) {

                    return (first + second);
                }




                //FUNCTION undef(value)
                function undef(value) {
                    if ("undefined" === typeof value) {
                        return "";
                    }
                    return value;
                }

                /*  FUNCTION x(tableheader)
                 *   We need to specify this function for Interpreter.js
                 */
                function x(tableheader) {
                    if (lastUsedLine != curLine) {
                        lastUsedLine = curLine;
                        actCompletePanelTableRow = $('#completepanelsubtable tr')[curLine + 2];
                    }


                    var result = actCompletePanelTableRow.cells[columnindices[tableheader]].innerText;
                    if (isEmpty(result)) {
                        result = null;
                    } else {
                        //          var resultFloat = parseFloat(result);
                        //          if(resultFloat != null && !isNaN(resultFloat))
                        //            result = resultFloat;
                    }
                    return result;
                }

                //FUNCTION createCalculateScoreFunction(key, value)
                function createCalculateScoreFunction(key, value) {
                    if (typeof value !== "undefined") {
                        value = value.replace(/\:\=/g, '=');
                        value = value.replace(/min\(/g, 'Math.min(');
                        value = value.replace(/max\(/g, 'Math.max(');
                        calculateScoresFunctions[key] = new Function('t', 'return ' + value + ';');
                    }
                }

                /*  FUNCTION displayError(errorhtml)
                 *   If we encounter a error while executing a ajax call we use this function to display the corresponding error message.
                 *   If more than 1 error occured we display all messages and don't delete the older messages!
                 */
                function displayError(errorhtml) {

                    try {
                        if (errorhtml.indexOf('log in') >= 0) {
                            $('#errorpanel').empty();
                            window.setTimeout("location.reload()", 0);
                        }
                    } catch (err) {
                        if (debug) console.log('displayError(): ', err);
                    }
                    $(window).scrollTop(0);


                    $('#errorpanel').show().append('<li style="margin-left: 10px;">' + errorhtml + '</li>');
                    if (debug) console.log('errorhtml: ', errorhtml);
                }

                /*  FUNCTION clearAll (UNUSED - maybe delete if we dont need it in the future)
                 *   clears the Website after a big error occurs
                 *   If we want to use this in the future we should think about using .empty() instead of .hide();
                 */
                function clearAll() {
                    $('#panels').hide();
                    $("h3").hide();
                }

                //this function helps filtering a array
                //FUNCTION findWidth
                function findWidth(string) {
                    return string.includes(" min-width:");
                }

                //FUNCTION isIE
                function isIE() {
                    var myNav = navigator.userAgent.toLowerCase();
                    return (myNav.indexOf('msie') != -1) ? parseInt(myNav.split('msie')[1]) : false;
                }


                /*  FUNCTION getColumnWidths 
                 *   width from css width value
                 *   Create a String (looking like a array - maybe use a real array in the future!...) which contains
                 *   the current width of every column.
                 */
                function getColumnWidths() {
                    var widthArray = "[";
                    //create Width-String for every table column


                    var split = $('#completepanelsubtable').children().first().children().first().html().split(";");
                    split = split.filter(findWidth);
                    var maxWidth;
                    for (i = 0; i < split.length; i++) {

                        maxWidth = parseInt(split[i].substring(12, split.length).replace("px", ""));
                        if (maxWidth < $('#completepanelsubtable th')[i].offsetWidth) {
                            maxWidth = $('#completepanelsubtable th')[i].offsetWidth;
                        }

                        if (i == 0) {
                            widthArray += "'" + maxWidth + "px'";
                        } else {
                            widthArray += ",'" + maxWidth + "px'";
                        }
                    }
                    widthArray += "]";
                    return widthArray;
                }



                /*  FUNCTION resizeTable()
                 *   We need to scroll left first, because otherwise the resizable widget calculates some strange percent values and the 
                 *   resizer positions are all at a false position.
                 *   After that force a resize event, so the resizable widget fixes itself and scroll back to the old position.
                 *   Scrolling back doesn't work sometimes, if the old position was behind the actual table.
                 *   This can happen, because the resizer position might be miles behind the actual table...!
                 *
                 *   This function is part of our solution to fix the buggy tablesorter resize plugin -.-
                 *   The resizer doesn't have the same size as the table solumns at all time
                 *   This Bug even occurs at the demo page https://mottie.github.io/tablesorter/docs/example-widget-resizable.html
                 *   We further implemented the functions setResizableColumnWidth() and setColumnWidth() to fix this behaviour.
                 */
                function resizeTable() {
                    scrollTableLeft();
                    $('#completepanelsubtable').css('width', '1%').css('min-width', '1%').css('max-width', '100%');

                    $(window).trigger('resize');

                    setResizableColumnWidth();
                    scrollTableRight();
                }


                /*  FUNCTION setupClickboxes(selected)
                 *   Use this function to set the checkbox value of all input elements which belong to the column chooser popover.
                 *   @param selected: a array with the id of all activated input fields
                 */
                function setupClickboxes(selected) {

                    //first: set all values to false
                    var lengthMax = $('#completepanelsubtable').children().first().find('th').length;
                    for (i = 0; i < lengthMax; i++) {
                        var id = "ColumnClickBox" + i;
                        document.getElementById(id).checked = false;
                    }

                    //now activate all selected input fields
                    for (i = 0; i < selected.length; i++) {
                        var id = "ColumnClickBox" + selected[i];
                        document.getElementById(id).checked = true;
                    }

                    $('#ColumnClickBox0').parent().hide();
                }


                /*  FUNCTION createAppreci8GUI
                 *   Use the FilterScheme.json file to create the appreci8 Gui.
                 *   We need a Element with id 'GUI' for this to work.
                 */
                function createAppreci8GUI() {

                    $.ajax({
                        type: 'GET',
                        async: false,
                        url: '/common/FilterScheme.json',
                        dataType: 'json',
                        success: function(ret) {
                            loadJSON(ret);
                            createGUI();
                        }
                    });
                }


                /*  FUNCTION calculateAppreci8
                 *   Calculate the Appreci8 values for all variants.
                 */
                function calculateAppreci8() {
                    //create Array in which "Interpreter.js" can write the calculated values and get the number of rows
                    createVariantList();

                    readFromGUI();
                    applyFilters();

                    var start = performance.now();

                    outputFunc();

                    var time = performance.now();
                    if (debug) console.log('Dauer appreci8 output: ' + (time - start) + ' ms.');
                }

                /*  FUNCTION applyFilters2
                 *   needed for Interpreter.js file
                 */
                function applyFilters2() {
                    calculateAppreci8();
                }


                /*  FUNCTION outputFunc
                 *   Writes the calculated aprreci8 values into the Variant Inspector table
                 */
                function outputFunc() {
                    var artiScore;
                    var polyScore;
                    var appreci8Result;
                    var artiProto;
                    var polyProto;

                    //save this 2 values to greatly reduce the calculation time
                    var table = $('#completepanelsubtable tr');
                    var currentRow = null;

                    for (i = 0; i < $('#completepanelsubtable tr').length - 2; i++) {
                        currentRow = table[i + 2];
                        $(currentRow).removeClass('filterArtifact');
                        $(currentRow).removeClass('filterPolymorphism');

                        //write calculated values into the table
                        artiScore = VariantList[i].newArtiScore;
                        currentRow.cells[columnindices["arti_score"]].innerHTML = artiScore;

                        polyScore = VariantList[i].newPolyScore;
                        currentRow.cells[columnindices["poly_score"]].innerHTML = polyScore;

                        appreci8Result = VariantList[i].newResult;
                        currentRow.cells[columnindices["appreci8"]].innerHTML = appreci8Result;

                        artiProto = VariantList[i].newArtiProt;
                        currentRow.cells[columnindices["appreci8_arti_protocoll"]].innerHTML = artiProto.replace(/,/g, "<br>");;

                        polyProto = VariantList[i].newPolyProt;
                        currentRow.cells[columnindices["appreci8_poly_protocoll"]].innerHTML = polyProto.replace(/,/g, "<br>");;


                        if (appreci8Result.indexOf('Artifact') >= 0) {
                            $(currentRow).addClass('filterArtifact');
                        } else if (appreci8Result.indexOf('Polymorphism') >= 0) {
                            $(currentRow).addClass('filterPolymorphism');
                        }
                    }
                }




                /*  FUNCTION showStatus(statTxt)
                 *   Used for messages of the Interpreter.js
                 */
                function showStatus(statTxt) {
                    if (debug) console.log("Appreci8 Status: " + statTxt);
                }


                /*  FUNCTION createVariantList
                 *   We need to setup a array called 'VariantList' which contains the Elements newResult, newArtiScore, newPolyScore, newArtiProt, newPolyProt for every first level Element
                 *   Interpreter.js will use this variable to store the calculated appreci8 values.
                 */
                function createVariantList() {
                    var length = $('#completepanelsubtable tr').length;
                    if (debug) console.log("länge:" + length);
                    VariantList = [];

                    for (i = 0; i < length - 2; i++) {
                        VariantList.push({
                            'newResult': '',
                            'newArtiScore': '',
                            'newPolyScore': '',
                            'newArtiProt': '',
                            'newPolyProt': ''
                        });
                    }
                }


                /*  FUNCTION getActivatedCheckboxes
                 *   returns all checked input checkboxes of the column-chooser-popover
                 */
                function getActivatedCheckboxes() {
                    var lengthMax = $('#completepanelsubtable').children().first().find('th').length;
                    var activatedBoxes = undefined;
                    var id;
                    for (i = 0; i < lengthMax; i++) {
                        id = "ColumnClickBox" + i;
                        if (document.getElementById(id).checked == true) {
                            if (activatedBoxes == undefined) {
                                activatedBoxes = [i];
                            } else {
                                activatedBoxes.push(i);
                            }
                        }
                    }

                    return activatedBoxes;
                }


                /*  FUNCTION updatePopover 
                 *   Creates a NEW popover for the last activated lens button
                 */
                function updatePopover() {
                    if (debug) console.log("updatepopover!");

                    //create new additional info navbar
                    var navBar = '<ul class="nav nav-tabs" id="additionalPanelNavbar">' +
                        '<li class=""><a class="active" tab="info" style="cursor:pointer;" >Gene Information</a></li>' +
                        '<li class=""><a tab="detectedVariant" style="cursor:pointer;" >Detected Variant</a></li>' +
                        '<li class=""><a tab="databasesPrevalence" style="cursor:pointer;" >Databases Information</a></li>' +
                        '<li class=""><a tab="prediction" style="cursor:pointer;" >Functional Prediction</a></li>' +
                        '<li class=""><a tab="literature" style="cursor:pointer;" >Literature References</a></li>' +
                        //'<li class=""><a tab="all" style="cursor:pointer;" >Show All</a></li>' +
                        '</ul>';


                    var maxHeight = window.innerHeight - 120; // - 78 - 100; //magic -78 cause of the margins and popover header :) // and minus some more for a bether "window" feeling

                    var genomeButton = $('#genomeBrowserButtonDiv').html();
                    $('#genomeBrowserButtonDiv').remove();

                    lastActivatedTableRow.find("#lupe").popover({
                        animation: false,
                        trigger: 'manual',
                        container: '#tablesubpanel',
                        placement: 'right',
                        html: 'true',
                        title: '<div class="h4"><b>Additional Information</b>' + genomeButton + '<div class="right-element"><button type="button" class="btn btn-default" id="CloseAdditionalInfo" style="margin-top: -9px; background-color: #E56567;"><span class="glyphicon glyphicon-remove"></span></button>&nbsp;&nbsp;</div></div>',
                        content: navBar + '<div style="overflow: auto; max-height: ' + maxHeight + 'px; min-Height: ' + maxHeight + 'px;">' + $('#additionalinfopanel').html() + '</div>'
                    }).on("show.bs.popover", function() {
                        $(this).data("bs.popover").tip().css({
                            "position": "fixed",
                            "width": additionalPanelWidth + 50 + "px",
                            "min-width": additionalPanelWidth + 50 + "px",
                            "max-width": additionalPanelWidth + 50 + "px"
                        });
                    }).popover("show");
                    $('#additionalinfopanel').empty();

                    $('#additionalPanelNavbar li a').each(function() {
                        if ($(this).attr("tab") == "detectedVariant") {
                            $(this).tab("show");
                            changeAdditionalInfoContent($(this).attr("tab"));
                        }
                    })
                    addAdditionalPanelClickHandlers();
                    $('#grey_background').show();
                    $('#grey_background').addClass("CloseAdditionalInfo backgroundActivated");



                    //"column chooser" popover should be in the foreground, but not if the user looks at the "additional info" popover
                    $('#columnchooserClickBoxes').parent().parent().parent().css({
                        "z-index": "5"
                    });
                }

                /*  FUNCTION stopEventPropagation(event)
                 *   We use this function to stop the propagation of a event to the next parent element.
                 *   @param event: the event we like to stop
                 */
                function stopEventPropagation(event) {
                    var event = event || arguments[0] || window.event;
                    if (debug) console.log(event);
                    event.stopPropagation();
                }

                /*  FUNCTION columnSearchShowAll()
                 *   Display all columns in the column chooser popover.
                 */
                function columnSearchShowAll() {
                    $('#columnChooserSearch').val("");
                    $('#columnChooserSearch').trigger("keyup");
                }


                /*  FUNCTION hideAdditionalVariantTables
                 *   This function will hide all tables of the Additional Information window.
                 *   After that we will use some other functions to display at least one of these tables.
                 */
                function hideAdditionalVariantTables() {
                    $('#variantProtLocation').hide();
                    $('#variantGenLocation').hide();
                    $('#variantDatabases').hide();
                    $('#variantPrevalence').hide();
                    $('#variantPrediction').hide();
                    $('#variantQuality').hide();
                }

                /*  FUNCTION changeAdditionalInfoContent
                 *   Switch between the tables of the additional information window
                 */
                function changeAdditionalInfoContent(tab) {
                    $('#tablesubpanel .arrow').hide();
                    $('.additionalInfoPanel').each(function() {
                        $(this).hide();
                    });
                    hideAdditionalVariantTables();
                    switch (tab) {
                        case "info":
                            $('#geneInformation').show();
                            $('#geneInformation2').show();
                            $('#geneInformation3').show();
                            $('#geneInformation4').show();
                            break;
                        case "detectedVariant":
                            $('#variantInformation').show();
                            $('#variantProtLocation').show();
                            $('#variantGenLocation').show();
                            $('#variantQuality').show();
                            break;
                        case "databasesPrevalence":
                            $('#variantInformation').show();
                            $('#variantDatabases').show();
                            $('#variantPrevalence').show();
                            break;
                        case "prediction":
                            $('#variantInformation').show();
                            $('#variantPrediction').show();
                            break;
                        case "literature":
                            $('#mutationInformation').show();
                            break;
                        case "all":
                            $('#mutationInformation').show();
                            $('#geneInformation').show();
                            $('#geneInformation2').show();
                            $('#geneInformation3').show();
                            $('#geneInformation4').show();
                            $('#variantInformation').show();
                            $('#variantProtLocation').show();
                            $('#variantGenLocation').show();
                            $('#variantDatabases').show();
                            $('#variantPrevalence').show();
                            $('#variantPrediction').show();
                            $('#variantQuality').show();
                            $('#tablesubpanel .arrow').show();
                        default:
                            if (debug) console.log("ERROR: tab doesn't exist");
                            break;
                    }
                }


                /*  FUNCTION progressbar_change(msg.progress)
                 *   Show the progressbar change
                 *   @param msg: the message to be shown
                 *   @param progress: the % amount the progressbar changes
                 *   @debug: a number (or message) which will be shown in debug mode
                 */
                function progressbar_change(msg, progress, debug) {
                    progressbarstatus += progress;
                    $('#percentage_progress_info').empty().append(progressbarstatus + '%');
                    if (debug)
                        console.log("percentage_progress_info: " + $('#percentage_progress_info').html() + " " + debug);
                    $('#progress-bar-filter').width(progressbarstatus + "%");
                    $('.progress_info').empty().append(msg);

                    if (progressbarstatus == 100) {
                        $('.filterpanel_loading').fadeOut(1000, function() {
                            if (chosentab != "completepanel")
                                $('#filterPanel').hide();
                        });
                    }
                }


                /*  FUNCTION addAdditionalPanelClickHandlers
                 *   Add Click Handler for the new created additional information popover
                 */
                function addAdditionalPanelClickHandlers() {
                    $('#additionalPanelNavbar a').on('click', function() {
                        $(this).tab('show');
                        changeAdditionalInfoContent($(this).attr("tab"));
                    });
                }



                /*  FUNCTION addApreci8ClickHandler
                 *   Add Click Handler for the Appreci8 GUI.
                 *   We need to set these handlers again, because the Interpreter.js resetGUI() function will destroy the old GUI and create a new one.
                 */
                function addApreci8ClickHandler() {
                    $('#btnReset').on('click', function() {
                        if (debug) console.log("Trigger apreci8 - 1");
                        resetGUI();
                        var filterHeight = $('#GUIappreci8').height() - $('#GUI-top').height() - $('.filter-buttons').height() - 3;
                        $('.filters').height("" + filterHeight + "px");
                        addApreci8ClickHandler(); //add the click handler again, because resetGui deletes the old element
                    });

                }



                //copy from uploadSample
                function highlight_div(selector) {
                    $(selector).css('backgroundColor', '#FF9999'); //[0].scrollIntoView();
                    $(selector).animate({
                        'opacity': '0.6'
                    }, 1000, function() {
                        $(this).css({
                            'backgroundColor': '',
                            'opacity': '1'
                        });
                    });
                }

                //copy from uploadSample
                function checkDate(date) {
                    var isoDateRegex = new RegExp(/\b\d{4}-\d{1,2}-\d{1,2}\b/);
                    var germanDateRegex = new RegExp(/\b\d{1,2}\.\d{1,2}\.\d{4}\b/);
                    var usDateRegex = new RegExp(/\b\d{1,2}\/\d{1,2}\/\d{4}\b/);
                    var day = 0;
                    var month = 0;
                    var year = 0;

                    if (isoDateRegex.test(date)) {
                        var date = date.split('-');
                        year = parseInt(date[0]);
                        month = parseInt(date[1]);
                        day = parseInt(date[2]);
                    } else if (germanDateRegex.test(date)) {
                        var date = date.split('.');
                        year = parseInt(date[2]);
                        month = parseInt(date[1]);
                        day = parseInt(date[0]);
                    } else if (usDateRegex.test(date)) {
                        var date = date.split('/');
                        year = parseInt(date[2]);
                        month = parseInt(date[0]);
                        day = parseInt(date[1]);
                    } else {
                        return [0, "Invalid Date-Format<br>(Use yyyy-mm-dd)"];
                    }
                    if (day >= 1 && day <= 31 &&
                        month >= 1 && month <= 12 &&
                        year >= 1900) {
                        if (month < 10) month = '0' + month;
                        if (day < 10) day = '0' + day;
                        var isoDate = year + '-' + month + '-' + day;
                        return [1, isoDate]
                    } else {
                        return [0, "Invalid Date"];
                    }
                }

                function createDateinputEvent() {
                    $('.dateinput').on('focusout', function() {
                        var datecheck = checkDate($(this).val());
                        if (datecheck[0] == 1) {
                            $(this).val(datecheck[1]);
                            $('#' + $(this).prop('id') + 'Error').hide();
                            formsvalid = true;
                        } else {
                            $('#' + $(this).prop('id') + 'Error').html(" " + datecheck[1])
                            $('#' + $(this).prop('id') + 'Error').css({"margin-bottom": "0px"}).show();
                            formsvalid = false;
                        }
                        if ($(this).val().length === 0) {
                            $('#' + $(this).prop('id') + 'Error').hide();
                        }
                    });
                }

                /*  FUNCTION addClickHandlers
                 *   Here we add (nearly) all click Handlers.
                 */
                function addClickHandlers() {

                    addApreci8ClickHandler();

                    //close open popover/dropdown menu if background is clicked
                    $('#grey_background').on('click', function() {
                        if (debug) console.log("Trigger 1.1");
                        if ($(this).hasClass("CloseAdditionalInfo")) {
                            $('#CloseAdditionalInfo').click();
                        } else if ($(this).hasClass("CloseAppreci8Score")) {
                            $('#CloseAppreci8Score').click();
                        }
                    });


                    //--- PATIENTS AND SAMPLES LIST HANDLERS ---//
                    $("#patientslist").on("click", "li", function() {
                        if (debug) console.log("Trigger 2");
                        pid = $(this).attr("PatientID");
                        $.ajax({
                            type: "POST",
                            url: "sampleinteraction.php",
                            data: "action=getSamplesByPid&pid=" + pid,
                            dataType: 'json',
                            success: function(ret) {
                                if (debug) console.log("success case ajax 8");
                                if (ret[0] == 1) {
                                    var samples = ret[1];
                                    if (samples.length > 0) {
                                        nosamples = true;
                                        $.each(samples, function(i, item) {
                                            if (item['StateCode'] == 100) {
                                                nosamples = false;
                                            }
                                        });

                                        if (nosamples) {
                                            window.location.replace('results.php?pid=' + pid);
                                        } else if (versionFromDB == false) {
                                            window.location.replace('results.php?sid=' + samples[0]['SampleID'] + '&version=' + version);
                                        } else {
                                            window.location.replace('results.php?sid=' + samples[0]['SampleID']);
                                        }

                                    } else {
                                        window.location.replace('results.php?pid=' + pid);
                                    }
                                } else {
                                    displayError(ret[1] + "[17]");
                                }
                            },
                            error: function(xhr, status, errorThrown) {
                                displayError(errorThrown + "[18]");
                            }
                        });

                    });

                    $("#sampleslist").on("click sampleclicked", "li", function() {
                        if (debug) console.log("Trigger 3");
                        if (versionFromDB == false) {
                            window.location.replace('results.php?sid=' + $(this).attr("SampleID") + '&version=' + version);
                        } else {
                            window.location.replace('results.php?sid=' + $(this).attr("SampleID"));
                        }
                    });

                    //--- SAMPLE AND PATIENT EDIT HANDLERS ---//
                    <?php if($_SESSION['editPermission'] == 1) { ?>
                    $("#showInfoPanel").on("click", "#patientpanel >> .glyphicon", function() {
                        if (debug) console.log("Trigger 4");
                        if ($(this).hasClass('glyphicon-pencil')) {
                            if (!editpatient) {
                                $('#Patientname').empty().append('<input type="text" id="EditPatientname" value="' + patientdata['Patientname'] + '" maxlength="50"/>');

                                $('#Birthdate').empty().append('<input class="dateinput" id="EditBirthdate" placeholder="yyyy-mm-dd" value="' + patientdata['Birthdate'] + '"/>' +
                                    '<div class="alert alert-danger" role="alert" id="EditBirthdateError" style="display:none;">' +
                                    '<span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>' +
                                    '<span class="sr-only">Error:</span> Invalid Date-Format<br>(Use yyyy-mm-dd)' +
                                    '</div>');



                                $('#Patientnumber').empty().append('<input type="text" id="EditPatientnumber" value="' + patientdata['Patientnumber'] + '" maxlength="50"/>');

                                $('#Sex').empty().append('<div class="form-group"><select id="EditSex"><option>Unknown</option><option>Male</option><option>Female</option></select></div>');

                                if (patientdata['Sex'] == 'M') {
                                    $('#EditSex').val('Male');
                                } else if (patientdata['Sex'] == 'F') {
                                    $('#EditSex').val('Female');
                                } else {
                                    $('#EditSex').val('Unknown');
                                }



                                editpatient = true;
                                $('#patienteditok').show();
                                $('#patienteditcancel').show();
                            }
                        }
                        if ($(this).hasClass('glyphicon-remove')) {
                            $('#Patientname').empty().append(patientdata['Patientname']);
                            $('#Birthdate').empty().append(patientdata['Birthdate']);
                            $('#Patientnumber').empty().append(patientdata['Patientnumber']);
                            $('#Sex').empty().append(patientdata['Sex']);
                            editpatient = false;
                            $('#patienteditok').hide();
                            $('#patienteditcancel').hide();
                        }
                        if ($(this).hasClass('glyphicon-ok')) {
                            var temppatient = new Array();
                            temppatient['Patientname'] = $('#EditPatientname').val();
                            temppatient['Birthdate'] = $('#EditBirthdate').val();
                            temppatient['Patientnumber'] = $('#EditPatientnumber').val();
                            temppatient['Sex'] = ' ';

                            switch ($('#EditSex').val()) {
                                case 'Male':
                                    temppatient['Sex'] = 'M';
                                    break;
                                case 'Female':
                                    temppatient['Sex'] = 'F';
                                    break;
                            }

                            var datecheck = checkDate(temppatient['Birthdate']);
                            if (temppatient['Birthdate'].length == 0) {
                                temppatient['Birthdate'] = null;
                            } else if (datecheck[0] == 0) {
                                highlight_div($('#EditBirthdate').parent().parent());
                                highlight_div($('#EditBirthdate'));
                                return;
                            }

                            if (patientdata['Patientname'] != temppatient['Patientname'] || patientdata['Patientnumber'] != temppatient['Patientnumber'] ||
                                patientdata['Birthdate'] != temppatient['Birthdate'] || patientdata['Sex'] != temppatient['Sex']) {
                                $.ajax({
                                    type: 'POST',
                                    url: 'patientinteraction.php',
                                    data: 'action=updatePatient&pid=' + pid + '&pname=' + temppatient['Patientname'] + '&pnumber=' + temppatient['Patientnumber'] + '&bd=' + temppatient['Birthdate'] + '&sex=' + temppatient['Sex'],
                                    dataType: 'json',
                                    success: function(ret) {
                                        if (debug) console.log("success case ajax 9");
                                        if (ret[0] == 1) {
                                            patientdata['Patientname'] = temppatient['Patientname'];
                                            patientdata['Patientnumber'] = temppatient['Patientnumber'];
                                            patientdata['Birthdate'] = temppatient['Birthdate'];
                                            patientdata['Sex'] = temppatient['Sex'];

                                            $('#Patientname').empty().append(patientdata['Patientname']);
                                            $('#Patientnumber').empty().append(patientdata['Patientnumber']);
                                            $('#Birthdate').empty().append(patientdata['Birthdate']);
                                            $('#Sex').empty().append(patientdata['Sex']);

                                            $("#patientslisttext").empty().append(patientdata['Patientname'] + ' <span class="caret"></span>');
                                            $('#patientslist >> .chosenli').empty().append('<a href="#">' + patientdata['Patientname'] + '</a>');
                                            $('#patientslist >> .chosenli').attr("Patientname", patientdata['Patientname']);

                                            editpatient = false;
                                            $('#patienteditok').hide();
                                            $('#patienteditcancel').hide();
                                        } else {
                                            displayError(ret[1] + "[19]");
                                        }
                                    },
                                    error: function(xhr, status, errorThrown) {
                                        displayError(errorThrown + "[20]");
                                    }
                                });
                            } else {
                                $('#Patientname').empty().append(patientdata['Patientname']);
                                $('#Birthdate').empty().append(patientdata['Birthdate']);
                                $('#Patientnumber').empty().append(patientdata['Patientnumber']);
                                $('#Sex').empty().append(patientdata['Sex']);

                                editpatient = false;
                                $('#patienteditok').hide();
                                $('#patienteditcancel').hide();
                            }
                        }
                        createDateinputEvent();
                    });

                    $("#showInfoPanel").on("click", "#samplepanel >> .glyphicon", function() {
                        if (debug) console.log("Trigger 5");
                        if ($(this).hasClass('glyphicon-pencil')) {
                            if (!editsample) {
                                $('#SampleTakeDate').empty().append('<input class="dateinput" id="EditSampleTakeDate" placeholder="yyyy-mm-dd" value="' + sampledata['SampleTakeDate'] + '"/>' +
                                    '<div class="alert alert-danger" role="alert" id="EditSampleTakeDateError" style="display:none;">' +
                                    '<span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>' +
                                    '<span class="sr-only">Error:</span> Invalid Date-Format<br>(Use yyyy-mm-dd)' +
                                    '</div>');

                                $('#Diagnosis').empty().append('<input id="EditDiagnosis" type="text" value="' + sampledata['Diagnosis'] + '" maxlength="50"/>');

                                $('#Comments').empty().append('<input type="text" id="EditComments" value="' + sampledata['Comments'] + '" maxlength="150"/>');



                                editsample = true;
                                $('#sampleeditok').show();
                                $('#sampleeditcancel').show();
                            }
                        }
                        if ($(this).hasClass('glyphicon-remove')) {
                            $('#SampleTakeDate').empty().append(sampledata['SampleTakeDate']);
                            $('#Diagnosis').empty().append(sampledata['Diagnosis']);
                            $('#Comments').empty().append(sampledata['Comments']);
                            editsample = false;
                            $('#sampleeditok').hide();
                            $('#sampleeditcancel').hide();
                        }
                        if ($(this).hasClass('glyphicon-ok')) {
                            var tempsample = new Array();
                            tempsample['Diagnosis'] = $('#EditDiagnosis').val();
                            tempsample['Comments'] = $('#EditComments').val();
                            tempsample['SampleTakeDate'] = $('#EditSampleTakeDate').val();

                            var datecheck = checkDate(tempsample['SampleTakeDate']);
                            if (tempsample['SampleTakeDate'].length == 0) {
                                tempsample['SampleTakeDate'] = null;
                            } else if (datecheck[0] == 0) {
                                highlight_div($('#EditSampleTakeDate').parent().parent());
                                highlight_div($('#EditSampleTakeDate'));
                                return;
                            }
                            
                            if (sampledata['Diagnosis'] != tempsample['Diagnosis'] || sampledata['Comments'] != tempsample['Comments'] ||
                                sampledata['SampleTakeDate'] != tempsample['SampleTakeDate']) {
                                $.ajax({
                                    type: 'POST',
                                    url: 'sampleinteraction.php',
                                    data: 'action=updateSample&sid=' + sid + '&diagnosis=' + tempsample['Diagnosis'] + '&comments=' + tempsample['Comments'] + '&std=' + tempsample['SampleTakeDate'],
                                    dataType: 'json',
                                    success: function(ret) {
                                        if (debug) console.log("success case ajax 10");
                                        if (ret[0] == 1) {
                                            sampledata['Diagnosis'] = tempsample['Diagnosis'];
                                            sampledata['Comments'] = tempsample['Comments'];
                                            sampledata['SampleTakeDate'] = tempsample['SampleTakeDate'];

                                            $('#SampleTakeDate').empty().append(sampledata['SampleTakeDate']);
                                            $('#Diagnosis').empty().append(sampledata['Diagnosis']);
                                            $('#Comments').empty().append(sampledata['Comments']);

                                            $("#sampleslisttext").empty().append(sampledata['SampleTakeDate'] + ' <span class="caret"></span>');
                                            $('#sampleslist >> .chosenli').empty().append('<a href="#">' + sampledata['SampleTakeDate'] + '</a>');
                                            $('#sampleslist >> .chosenli').attr("SampleTakeDate", sampledata['SampleTakeDate']);

                                            editsample = false;
                                            $('#sampleeditok').hide();
                                            $('#sampleeditcancel').hide();
                                        } else {
                                            displayError(ret[1] + "[21]");
                                        }
                                    },
                                    error: function(xhr, status, errorThrown) {
                                        displayError(errorThrown + "[22]");
                                    }
                                });
                            } else {
                                $('#SampleTakeDate').empty().append(sampledata['SampleTakeDate']);
                                $('#Diagnosis').empty().append(sampledata['Diagnosis']);
                                $('#Comments').empty().append(sampledata['Comments']);

                                editsample = false;
                                $('#sampleeditok').hide();
                                $('#sampleeditcancel').hide();
                            }
                        }
                        createDateinputEvent();
                    });
                    <?php } ?>

                    //--- COMPLETE PANEL PAGE HANDLERS ---//
                    $("#genespanel").on("click genebuttonclicked", ".genebutton", function() {
                        if (debug) console.log("Trigger 6");
                        if ($(this).hasClass('btn-mutations')) {
                            if ($(this).hasClass('active_filter')) { //Remove filter               
                                var removeItem = $(this).attr('genename');
                                genefilterselected = jQuery.grep(genefilterselected, function(value) {
                                    return value[0] != removeItem;
                                });
                                $('#' + $(this).attr('genename') + 'filter_checkmark').hide(); //hide checkmark, which says, that the filter is active

                                if (genefilterselected.length == 0) {
                                    $('#nogenesselected').show();
                                }
                                $('.generemovebutton[genename=' + removeItem + ']').remove();

                                $(this).removeClass('active_filter');
                                $('#' + $(this).attr('genename') + 'filter_checkmark').hide();
                            } else { //add filter
                                genefilterselected.push([$(this).attr('genename'), $(this).attr('chr'), $(this).attr('start'), $(this).attr('end')]);
                                $('#selectedgenes').append('<button class="btn btn-select generemovebutton" genename="' + $(this).attr('genename') + '">' +
                                    $(this).attr('genename') + ' <span class="glyphicon glyphicon-remove"></span></button>');
                                $(this).addClass('active_filter');
                                $('#' + $(this).attr('genename') + 'filter_checkmark').show();
                                $('#nogenesselected').hide();
                            }
                            applyGeneFilters();
                        }
                        resizeTable();
                    });

                    $("#filterPanel").on("click", ".generemovebutton", function() {
                        if (debug) console.log("Trigger 7");
                        var removeItem = $(this).attr('genename');
                        genefilterselected = jQuery.grep(genefilterselected, function(value) {
                            return value[0] != removeItem;
                        });
                        $('.genebutton[genename=' + $(this).attr("genename") + ']').show();
                        $('#' + $(this).attr('genename') + 'filter_checkmark').hide(); //hide checkmark, which says, that the filter is active
                        $('.genebutton[genename=' + $(this).attr("genename") + ']').removeClass('active_filter');

                        if (genefilterselected.length == 0) {
                            $('#nogenesselected').show();
                        }
                        $(this).remove();
                        applyGeneFilters();
                    });

                    $('#filterPanel').on('change', '#exclusionpanel > > > input', function() {
                        if (debug) console.log("Trigger 8");
                        switch ($(this).attr('id')) {
                            /*case 'columnfilters':
                                if(this.checked) {
                                    $('.tablesorter-filter-row').show();
                                    $('#selectedexclusion').append('<p id="columnfiltersactive">Column filters enabled</p>');
                                } else {
                                    $('.tablesorter').trigger('filterReset');
                                    $('.tablesorter-filter-row').hide();
                                    $('#columnfiltersactive').remove();
                                }
                                break;*/
                            case 'excludebenign':

                                $('#excludebenignactive').remove();
                                exclusionfilterselected = jQuery.grep(exclusionfilterselected, function(value) {
                                    return value != 'benign';
                                });
                                if (this.checked) {
                                    $('#selectedexclusion').append('<div id="excludebenignactive">Variants with \"benign\" rating excluded</div>');
                                    exclusionfilterselected.push('benign');
                                } else {
                                    $('#excludebenignactive').remove();
                                    exclusionfilterselected = jQuery.grep(exclusionfilterselected, function(value) {
                                        return value != 'benign';
                                    });
                                }
                                break;
                            case 'excludecommon':
                                $('#excludecommonactive').remove();
                                exclusionfilterselected = jQuery.grep(exclusionfilterselected, function(value) {
                                    return value != 'common';
                                });
                                if (this.checked) {
                                    $('#selectedexclusion').append('<div id="excludecommonactive">Common variants (1000 Genomes) excluded</div>');
                                    exclusionfilterselected.push('common');
                                } else {
                                    $('#excludecommonactive').remove();
                                    exclusionfilterselected = jQuery.grep(exclusionfilterselected, function(value) {
                                        return value != 'common';
                                    });
                                }
                                break;
                            case 'excludeartifacts':
                                $('#excludeartifactsactive').remove();
                                exclusionfilterselected = jQuery.grep(exclusionfilterselected, function(value) {
                                    return value != 'artifacts';
                                });
                                if (this.checked) {
                                    $('#selectedexclusion').append('<div id="excludeartifactsactive">Variants rated as artifacts excluded</div>');
                                    exclusionfilterselected.push('artifacts');
                                } else {
                                    $('#excludeartifactsactive').remove();
                                    exclusionfilterselected = jQuery.grep(exclusionfilterselected, function(value) {
                                        return value != 'artifacts';
                                    });
                                }
                                break;
                            case 'excludepolymorphisms':
                                $('#excludepolymorphismsactive').remove();
                                exclusionfilterselected = jQuery.grep(exclusionfilterselected, function(value) {
                                    return value != 'polymorphisms';
                                });
                                if (this.checked) {
                                    $('#selectedexclusion').append('<div id="excludepolymorphismsactive">Variants rated as polymorphisms excluded</div>');
                                    exclusionfilterselected.push('polymorphisms');
                                } else {
                                    $('#excludepolymorphismsactive').remove();
                                    exclusionfilterselected = jQuery.grep(exclusionfilterselected, function(value) {
                                        return value != 'polymorphisms';
                                    });
                                }
                                break;
                        }


                        if ($('#selectedexclusion').children().length == 1) {
                            $('#noexclusionselected').show();
                        } else {
                            $('#noexclusionselected').hide();
                        }

                        $('#completepanelsubtable > tbody > tr').removeClass('exclusionfiltered');
                        $.each($('#completepanelsubtable > tbody > tr'), function(key, row) {
                            if ($.inArray('benign', exclusionfilterselected) !== -1) {
                                var lines = $(this)[0].cells[columnindices['ClinVar_Significance']].innerHTML.split('<br').length;
                                if (($(this)[0].cells[columnindices['ClinVar_Significance']].innerHTML.match(/enign/g) || []).length >= lines) $(this).addClass('exclusionfiltered');
                            }

                            if ($.inArray('common', exclusionfilterselected) !== -1 && (parseFloat($(this)[0].cells[columnindices['G1000_AF']].innerHTML) || 0.0) >= 0.1) $(this).addClass('exclusionfiltered');

                            if ($.inArray('artifacts', exclusionfilterselected) !== -1 && ($(this).hasClass('filterArtifact'))) $(this).addClass('exclusionfiltered');

                            if ($.inArray('polymorphisms', exclusionfilterselected) !== -1 && ($(this).hasClass('filterPolymorphism'))) $(this).addClass('exclusionfiltered');
                        });
                        resizeTable();
                        saveSettings();
                    });






                    $('#genespanel').on('click', '.genefilterCloseButton', function() {
                        if (debug) console.log("Trigger 10");
                        $('#showgenes').click();
                    });

                    $('#filterPanel').on('click', '#showautomatic', function() {
                        if (debug) console.log("Trigger 11");
                        $('#automatedpanel').toggle();
                    });



                    $('#filterPanel').on('click', '.resetqualitybutton', function() {
                        if (debug) console.log("Trigger 14");

                        nr_alt = nr_alt_reset;
                        dp = dp_reset;
                        vaf = var_reset;
                        low_bq = low_bq_reset;
                        bq_diff = bq_diff_reset;

                        $("#nraltslider").slider("value", nr_alt);
                        $("#dpslider").slider("value", dp);
                        $("#vafslider").slider("value", vaf * 1000);
                        $("#lowbqslider").slider("value", low_bq);
                        $("#bqdiffslider").slider("value", bq_diff);

                        $('#nralttext').val($("#nraltslider").slider("value"));
                        nraltChange();
                        $('#dptext').val($("#dpslider").slider("value"));
                        dpChange();
                        $('#vaftext').val($("#vafslider").slider("value") / 1000);
                        vafChange();
                        $('#lowbqtext').val($("#lowbqslider").slider("value"));
                        lowbqChange();
                        $('#bqdifftext').val($("#bqdiffslider").slider("value"));
                        bqdiffChange();

                        applyQualityFilters();
                        // $('#qualitypanel').toggle();
                    });




                    //event listener for Complex-Filter
                    $('#filterPanel').on('click', '#showcomplexfilter', function() {
                        if (debug) console.log("Trigger 16");

                        $('#GUIappreci8').height(window.innerHeight - 70).toggle();
                        if ($(this).find('span').hasClass('glyphicon-chevron-down')) {
                            $(this).find('span').removeClass('glyphicon-chevron-down');
                            $(this).find('span').addClass('glyphicon-chevron-up');
                            $('#grey_background').show();
                            $('#grey_background').addClass("CloseAppreci8Score backgroundActivated");
                        } else {
                            $(this).find('span').removeClass('glyphicon-chevron-up');
                            $(this).find('span').addClass('glyphicon-chevron-down');
                            $('#grey_background').hide().removeClass("CloseAppreci8Score backgroundActivated");
                        }

                        var filterHeight = $('#GUIappreci8').height() - $('#GUI-top').height() - $('.filter-buttons').height() - 3;
                        $('.filters').height("" + filterHeight + "px");

                    });


                    $('#CloseAppreci8Score').on('click', function() {
                        if (debug) console.log("Trigger 16.1 - close the appreci8 score window");
                        $('#showcomplexfilter').click();
                    });

                    $('#filterPanel').on('focusout', '#VAFmin1', function() {
                        if (debug) console.log("Trigger 17");
                        //move-slider
                        var VAFmin1 = parseFloat($('#VAFmin1').val());
                        var VAFmax1 = parseFloat($('#VAFmax1').val());

                        if (VAFmin1 < 0.0 || VAFmin1 > 0.50) {
                            $('#VAFmin1').css({
                                background: '#f0bcb4'
                            });
                        } else if (VAFmin1 > VAFmax1) {
                            $('#VAFmin1').css({
                                background: '#f0bcb4'
                            });
                            $('#VAFmax1').css({
                                background: '#f0bcb4'
                            });
                        } else {
                            $('#VAFmin1').css({
                                background: 'white'
                            });
                            $('#VAFmax1').css({
                                background: 'white'
                            });
                            $("#vafrangeslider1").slider("values", [VAFmin1 * 100, VAFmax1 * 100]);
                        }
                    });
                    $('#filterPanel').on('focusout', '#VAFmin2', function() {
                        if (debug) console.log("Trigger 18");
                        //move-slider
                        var VAFmin2 = parseFloat($('#VAFmin2').val());
                        var VAFmax2 = parseFloat($('#VAFmax2').val());

                        if (VAFmin2 < 0.50 || VAFmin2 > 1.00) {
                            $('#VAFmin2').css({
                                background: '#f0bcb4'
                            });
                        } else if (VAFmin2 > VAFmax2) {
                            $('#VAFmin2').css({
                                background: '#f0bcb4'
                            });
                            $('#VAFmax2').css({
                                background: '#f0bcb4'
                            });
                        } else {
                            $('#VAFmin2').css({
                                background: 'white'
                            });
                            $('#VAFmax2').css({
                                background: 'white'
                            });
                            $("#vafrangeslider2").slider("values", [VAFmin2 * 100, VAFmax2 * 100]);
                        }
                    });
                    $('#filterPanel').on('focusout', '#VAFmax1', function() {
                        if (debug) console.log("Trigger 19");
                        //move-slider
                        var VAFmin1 = parseFloat($('#VAFmin1').val());
                        var VAFmax1 = parseFloat($('#VAFmax1').val());

                        if (VAFmax1 < 0.0 || VAFmax1 > 0.50) {
                            $('#VAFmax1').css({
                                background: '#f0bcb4'
                            });
                        } else if (VAFmin2 > VAFmax2) {
                            $('#VAFmin1').css({
                                background: '#f0bcb4'
                            });
                            $('#VAFmax1').css({
                                background: '#f0bcb4'
                            });
                        } else {
                            $('#VAFmin1').css({
                                background: 'white'
                            });
                            $('#VAFmax1').css({
                                background: 'white'
                            });
                            $("#vafrangeslider1").slider("values", [VAFmin1 * 100, VAFmax1 * 100]);
                        }
                    });
                    $('#filterPanel').on('focusout', '#VAFmax2', function() {
                        if (debug) console.log("Trigger 20");
                        //move-slider
                        var VAFmin2 = parseFloat($('#VAFmin2').val());
                        var VAFmax2 = parseFloat($('#VAFmax2').val());

                        if (VAFmax2 < 0.50 || VAFmax2 > 1.00) {
                            $('#VAFmax2').css({
                                background: '#f0bcb4'
                            });
                        } else if (VAFmin2 > VAFmax2) {
                            $('#VAFmin2').css({
                                background: '#f0bcb4'
                            });
                            $('#VAFmax2').css({
                                background: '#f0bcb4'
                            });
                        } else {
                            $('#VAFmin2').css({
                                background: 'white'
                            });
                            $('#VAFmax2').css({
                                background: 'white'
                            });
                            $("#vafrangeslider2").slider("values", [VAFmin2 * 100, VAFmax2 * 100]);
                        }
                    });

                    $('#qualitypanel').on('change', function() {
                        if (debug) console.log("Trigger 22.5");
                        resizeTable();
                    });

                    $('#filterPanel').on('change', 'input:checkbox', function() {
                        if (debug) console.log("Trigger 23");
                        if ($(this).prop('checked')) { //check all childs
                            //$(this).parent().find('input:checkbox').prop('checked', true);
                            if ($(this).parent().attr('class') === 'filter-subentry') {
                                $(this).parent().parent().find('input:checkbox:first').prop('checked', true);
                            }
                        } else { //uncheck all childs
                            $(this).parent().find('input:checkbox').prop('checked', false);
                        }
                    });
                    

                    $('#filterPanel').on('click', '.filter-block input', function() {
                        if (debug) console.log("Trigger 25");
                        if ($(this).is(':checked')) {
                            $(this).parent().removeClass('inactive');
                        } else {
                            $(this).parent().addClass('inactive');
                        }
                    });



                    $("#panels").on("click", "#columnfiltersbutton", function() {
                        if (debug) console.log("Trigger 26");
                        //toggle column-filters
                        $('.tablesorter').trigger('filterReset');
                        $('.tablesorter-filter-row').toggle();
                        setResizableColumnWidth();
                    });

                    $("#panels").on("click", "#downloadcsvbutton", function() {
                        if (debug) console.log("Trigger 27");
                        var csvstring = '';
                        var header = Array();
                        var selectedcolumns = new Array();
                        for (i = 1; i < $('#' + chosentab + 'subtable').data('tablesorter').columns; i++) {
                            if ($('#' + chosentab + 'subtable th:eq(' + i + ')').is(':visible')) {
                                selectedcolumns.push(i);
                            }
                        }

                        $("#completepanelsubtable tr th").each(function(i, v) {
                            header[i] = $(this).text();
                        });

                        var data = Array();

                        $("#completepanelsubtable tr").each(function(i, v) {
                            if (i > 1 &&
                                !$(this).hasClass('regionfiltered') &&
                                !$(this).hasClass('typefiltered') &&
                                !$(this).hasClass('exclusionfiltered') &&
                                !$(this).hasClass('genefiltered') &&
                                !$(this).hasClass('automatedfiltered') &&
                                !$(this).hasClass('filtered')) {
                                var temprow = Array();
                                $(this).children('td').each(function(j, vv) {
                                    // temprow[j] = $(this).html().replace(/<br>/g, ', ');
                                    temprow[j] = $(this).text().replace(/<br>/g, ', ');
                                    temprow[j] = temprow[j].replace(/\&gt;/g, '>');
                                    temprow[j] = temprow[j].replace(/\&amp;/g, '&');
                                    temprow[j] = temprow[j].replace(/\&nbsp;/g, ' ');
                                    temprow[j] = temprow[j].replace(/"/g, '""');
                                    //if(debug) console.log("temprow: " + temprow[j]);



                                });
                                data.push(temprow);
                            }
                        });

                        for (var i = 0; i < selectedcolumns.length; i++) {
                            csvstring += '\"' + header[selectedcolumns[i]] + '\";';
                        }
                        csvstring = csvstring.substring(0, csvstring.length - 1); //trim last semicolon

                        for (var i = 0; i < data.length; i++) {
                            csvstring += '\n';
                            for (var j = 0; j < selectedcolumns.length; j++) {
                                csvstring += '"' + data[i][selectedcolumns[j]] + '";';
                            }
                            csvstring = csvstring.substring(0, csvstring.length - 1);
                        }

                        /***** FILEDOWNLOAD *****/
                        var element = document.createElement('a');
                        element.setAttribute('href', 'data:application/octet-stream;charset=utf-8,' + encodeURIComponent(csvstring));
                        element.setAttribute('download', 'patient' + pid + '_sample' + sid + '.csv');
                        element.style.display = 'none';
                        document.body.appendChild(element);
                        element.click();
                        document.body.removeChild(element);
                        /***** FILEDOWNLOAD END *****/

                    });


                    //a double-click should be the same as a single click on the lens button of that row
                    $("#panels").on("dblclick tablerowclicked", "tr", function() {
                        if (debug) console.log("Trigger 28 - doubleClick on table -> trigger additional info button");
                        $(this).first().find("#lupe").click()
                    });


                    //  show additional information popover/window, if the user clicks on a lens button
                    //  Or remove the last popover if the same buton was clicked again (thats what happens in the background if the user closes the popover) 
                    $(".lens").on("click", function() {
                        if (debug) console.log("Trigger 29");
                        if ($(this).parent().parent()[0].parentElement.parentElement.id == 'completepanelsubtable') {

                            additionalPanelCallId++;

                            //if it's not the first time a lens was clicked:
                            if (lastActivatedTableRow != undefined) {
                                $('#grey_background').hide().removeClass("CloseAdditionalInfo backgroundActivated"); //we don't need to remember anymore that a popover is in the foreground
                                //delete the last popover if it still exists
                                lastActivatedTableRow.find("#lupe").popover("destroy");

                                //don't create a new popover if the user pressed the same button again
                                //remember that the user already closes this popover, so we need to show it again if the user presses this button a third time
                                if (lastActivatedTableRow.css("border-left-color") == $(this).parent().parent().css("border-left-color") && !hiddenPopover) {
                                    hiddenPopover = true;
                                    scrollTableRight();
                                    return;
                                }


                                //Change the border colour of the clicked row
                                //We use this to find the last shown additional info in some cases! So don't delete
                                lastActivatedTableRow.css('border', 'none');


                            }
                            hiddenPopover = false;
                            scrollTableLeft();

                            lastActivatedTableRow = $(this).parent().parent();
                            $(this).parent().parent().css({
                                "border-color": clickedRowBorderColour,
                                "border-weight": "1px",
                                "border-style": "solid"
                            });



                            var numberCivic = $(this).parent().parent()[0].cells[columnindices['NrCivic']].innerHTML;
                            //$('#additionalinfopanel').empty().append('<h4>Additional Information</h4>');

                            //GENOME BROWSER BUTTON //
                            //add header
                            $('#additionalinfopanel').empty().append('<div id="genomeBrowserButtonDiv" style="margin: 20px 0px;"><button type="button" class="btn btn-default" style="position: absolute;left: 40%;margin-top: -9px;" chr="chr' + $(this).parent().parent()[0].cells[columnindices['chr']].innerHTML +
                                '" start="' + (Number($(this).parent().parent()[0].cells[columnindices['pos']].innerHTML) - igvBpWidth) +
                                '" stop="' + (Number($(this).parent().parent()[0].cells[columnindices['pos']].innerHTML) + igvBpWidth) +
                                '" id="viewingenomebrowserbutton">View in genome browser</button></div>');


                            // General GENE INFORMATION //
                            var genePosition = $(this).parent().parent()[0].cells[columnindices['pos']].innerHTML;
                            var chromosome = $(this).parent().parent()[0].cells[columnindices['chr']].innerHTML;
                            var genename = $(this).parent().parent()[0].cells[columnindices['Gene']].innerText.split('(')[0];
                            var gene = genename;
                            gene = gene.split(',')[0];
                            gene = gene.split('-')[0];
                            var entrez_id = "";
                            $('#additionalinfopanel').append('<div id="geneInformation" class="additionalInfoPanel"><p><table class="additionalinfotable" id="fullwidth">' +
                                '<tr><td class="labelcol"><b>Gene</b></td>' +
                                '<td>' + genename + '</td></tr>' +
                                '<tr><td colspan="2"><img src="common/ajax-loader.gif" style="display:block; margin:auto;" /></td></tr></table></p>' +
                                '</div>' +
                                '<div id="geneInformation2" class="additionalInfoPanel"><p><table class="additionalinfotable" id="fullwidth">' +
                                '<tr><td class="labelcol"><b>Function Description (dbNSFP)</b></td>' +
                                '<tr><td><img src="common/ajax-loader.gif" style="display:block; margin:auto;" /></td></tr></table></p>' +
                                '</div>' +
                                '<div id="geneInformation4" class="additionalInfoPanel"><p><table class="additionalinfotable" id="fullwidth">' +
                                '<tr><td class="labelcol" colspan="4"><b>Gene References</b></td>' +
                                '<tr><td><a href=http://www.genecards.org/cgi-bin/carddisp.pl?gene=' + genename + ' target="_blank">Gene cards</a></td>' +
                                '<td><a href=http://cancer.sanger.ac.uk/cosmic/gene/analysis?ln=' + genename + ' target="_blank">COSMIC</a></td>' +
                                '<td><a href=https://www.ncbi.nlm.nih.gov/gene?term=' + genename + '%5BGene+Name%5D+AND+Homo+sapiens%5BOrganism%5D target="_blank">NCBI</a></td>' +
                                '<td><a href="https://civicdb.org/home" target="blank">CIVIc</a></td>' +
                                '</tr></table></p>' +
                                '</div>' +
                                '<div id="geneInformation3" class="additionalInfoPanel"><p><table class="additionalinfotable" id="fullwidth">' +
                                '<tr><td class="labelcol"><b>Affected Pathways</b></td>' +
                                '<tr><td><img src="common/ajax-loader.gif" style="display:block; margin:auto;" /></td></tr></table></p>' +
                                '</div>');


                            //Variant Information from Table above
                            $('#additionalinfopanel').append('<div id="variantInformation" class="additionalInfoPanel"></div>');

                            additionalPanelWidth = 1200;
                            var panelWidth = parseInt($('#tablesubpanel').width()) - 100;
                            var minPanelWidth = 1000;

                            if (panelWidth < additionalPanelWidth) {
                                $('#GenomicLocationTable').parent().parent().removeClass("additionalinfotable").addClass("additionalinfotable2");
                                if (panelWidth > minPanelWidth) {
                                    additionalPanelWidth = panelWidth;
                                } else {
                                    additionalPanelWidth = minPanelWidth;
                                }
                            }

                            var variantinfostring = '<p><table class="additionalinfotable1" width="' + additionalPanelWidth + 'px"><tr><td class="labelcol"><b>Detected Variant</b></td></tr><tr><td>';





                            //NOTE Start: additional info tables


                            var infoSymbol = '<sup><span class="glyphicon glyphicon-info-sign" aria-hidden="true" style="font-size: 12px; color: #8287bc;"></span></sup>';


                            variantinfostring += '<div id="variantGenLocation"">'; //start variantGenLocation                           
                            variantinfostring += '<p><div class="additionalinfotableHeadline"><b>Genomic location:</b></div>The exact position, where the mutation can be found in the genome. dbSNP identifier can be used to refer to a variant.' +
                                '</p><div class="additionalinfotableBox">' +
                                '<table class="additionalinfotable" id=""  style="table-layout: auto;">' +
                                '<tr><td width="220px"><b>Gene</b></td>' +
                                '<td><b>chr</b></td>' +
                                '<td><b>pos</b></td>' +
                                '<td><b>ref</b></td>' +
                                '<td><b>alt</b></td>' +
                                '<td><b>dbSNP</b></td>' +
                                '<td title="dbSNP version, in which this variant was listed for the first time."><b>dbSNP_Version</b>' + infoSymbol + '</td>' +
                                '<td title="PM means, there exists a publication in PubMed for this variant."><b>dbSNP_PM</b>' + infoSymbol + '</td></tr>';
                            variantinfostring += '<tr id="GenomicLocationTable">' +
                                '<td>' + $(this).parent().parent()[0].cells[columnindices['Gene']].innerText + "</td>" +
                                '<td>' + $(this).parent().parent()[0].cells[columnindices['chr']].innerHTML + "</td>" +
                                '<td>' + $(this).parent().parent()[0].cells[columnindices['pos']].innerHTML + "</td>" +
                                '<td>' + $(this).parent().parent()[0].cells[columnindices['ref']].innerHTML + "</td>" +
                                '<td>' + $(this).parent().parent()[0].cells[columnindices['alt']].innerHTML + "</td>" +
                                '<td>' + $(this).parent().parent()[0].cells[columnindices['dbSNP']].innerHTML + "</td>" +
                                '<td>' + $(this).parent().parent()[0].cells[columnindices['dbSNP_Version']].innerHTML + "</td>" +
                                '<td>' + $(this).parent().parent()[0].cells[columnindices['dbSNP_PM']].innerHTML;
                            variantinfostring += '</td></tr>' +
                                '</table></div></div>'; //end variantGenLocation


                            variantinfostring += '<div id="variantProtLocation">'; //start variantProtLocation
                            variantinfostring += '<p><div class="additionalinfotableHeadline"><b>Protein-based location:</b></div>The protein and gene that is affected by this variant. Information varies in dependance of the transcript. (The "canonical" transcript by UCSC is highlighted.)' +
                                '</p><div class="additionalinfotableBox">' +
                                '<table class="additionalinfotable" style="table-layout: auto;">' +
                                '<tr><td width="220px"><b>Transcripts</b></td>' +
                                '<td><b>varTypes</b></td>' +
                                '<td><b>regionTypes</b></td>' +
                                '<td><b>exon</b></td>' +
                                '<td><b>Codons</b></td>' +
                                '<td><b>Proteins</b></td>' +
                                '<td><b>Impacts</b></td></tr>' +
                                '<tr id="ProteinBasedLocationTable">';
                            variantinfostring += '<td>' + $(this).parent().parent()[0].cells[columnindices['Transcripts']].innerHTML + "</td>";
                            variantinfostring += '<td>' + $(this).parent().parent()[0].cells[columnindices['varTypes']].innerHTML.replace("<b>", "").replace("</b>", "") + "</td>";
                            variantinfostring += '<td>' + $(this).parent().parent()[0].cells[columnindices['regionTypes']].innerHTML.replace("<b>", "").replace("</b>", "") + "</td>";
                            variantinfostring += '<td>' + $(this).parent().parent()[0].cells[columnindices['Exons']].innerHTML.replace("<b>", "").replace("</b>", "") + "</td>";
                            variantinfostring += '<td>' + $(this).parent().parent()[0].cells[columnindices['Codons']].innerHTML.replace("<b>", "").replace("</b>", "") + "</td>";
                            variantinfostring += '<td>' + $(this).parent().parent()[0].cells[columnindices['Proteins']].innerHTML.replace("<b>", "").replace("</b>", "") + "</td>";
                            variantinfostring += '<td>' + $(this).parent().parent()[0].cells[columnindices['Impacts']].innerHTML.replace("<b>", "").replace("</b>", "");

                            variantinfostring += '</td></tr>' +
                                '</table></div></div>'; //end variantProtLocation



                            variantinfostring += '<div id="variantDatabases">'; //start variantDatabases
                            variantinfostring += '<p><div class="additionalinfotableHeadline"><b>Databases:</b></div>A set of clinical databases, that provide associations between genomic variants and certain diseases.</p><div class="additionalinfotableBox"><p><b>ClinVar</b>: Collects variants that have been shown to be pathogenic in studies for certain diseases.' +
                                '</p><table class="additionalinfotable" id=""  style="table-layout: auto;">' +
                                '<tr><td width="220px"><b>ClinVar_Significance</b></td>' +
                                '<td><b>ClinVar_Disease</b></td>' +
                                '<td><b>ClinVar_Status</b></td></tr>' +
                                '<tr id="DatabasesTable1">';
                            variantinfostring += '<td>' + $(this).parent().parent()[0].cells[columnindices['ClinVar_Significance']].innerHTML + "</td>";
                            variantinfostring += '<td>' + $(this).parent().parent()[0].cells[columnindices['ClinVar_Disease']].innerHTML + "</td>";
                            variantinfostring += '<td>' + $(this).parent().parent()[0].cells[columnindices['Clinvar_Status']].innerHTML;
                            variantinfostring += '</td></tr>' +
                                '</table></div>';




                            variantinfostring += '<p><div class="additionalinfotableBox"><b>COSMIC</b>: Collects variants found in sequencing studies for certain cancers and indicates the number of samples in which it occurred (no causality).</p><p>' +
                                '<table class="additionalinfotable" id=""  style="table-layout: auto;">' +
                                '<tr><td width="220px"><b>CosmicID</b></td>' +
                                '<td><b>CosmicSites</b></td>' +
                                '<td title="Has the variant been marked as a Polymorphism by COSMIC database?"><b>CosmicSNP</b><sup><span class="glyphicon glyphicon-info-sign" aria-hidden="true" style="font-size: 12px; color: #8287bc;"></span></sup></td>' +
                                '<td><b>Cosmic_NrHaemato</b></td>' +
                                '<td><b>Cosmic_NrSamples</b></td>' +
                                '<td><b>Cosmic_NrSites</b></td></tr>' +
                                '<tr id="DatabasesTable2">';
                            variantinfostring += '<td>' + $(this).parent().parent()[0].cells[columnindices['CosmicID']].innerHTML + "</td>";
                            variantinfostring += '<td>' + $(this).parent().parent()[0].cells[columnindices['CosmicSites']].innerHTML + "</td>";
                            variantinfostring += '<td>' + $(this).parent().parent()[0].cells[columnindices['CosmicSNP']].innerHTML + "</td>";
                            variantinfostring += '<td>' + $(this).parent().parent()[0].cells[columnindices['Cosmic_NrHaemato']].innerHTML + "</td>";
                            variantinfostring += '<td>' + $(this).parent().parent()[0].cells[columnindices['Cosmic_NrSamples']].innerHTML + "</td>";
                            variantinfostring += '<td>' + $(this).parent().parent()[0].cells[columnindices['Cosmic_NrSites']].innerHTML;

                            variantinfostring += '</td></tr>' +
                                '</table></div></div></p>'; //end variantDatabases




                            variantinfostring += '<div id="variantPrevalence">'; //start variantPrevalence
                            variantinfostring += '<p><div class="additionalinfotableHeadline"><b>Prevalence:</b></div>The frequencies how often the variation occured in several routine sequencing projects of healthy humans. Higher occurance in healthy patients (e.g. &ge;0.01 in one or more projects) may be a sign of benignity.' +
                                '</p><div class="additionalinfotableBox">' +
                                '<table class="additionalinfotable2" id=""  style="table-layout: auto;">' +
                                '<tr><td width="220px"><b>1000Genomes AF</b></td>' +
                                '<td><b>1000Genomes eur</b></td>' +
                                '<td><b>ESP6500</b></td>' +
                                '<td><b>ExAC all</b></td>' +
                                '<td><b>ExAC eur</b></td>' +
                                '<td><b>TWINSUK AF</b></td>' +
                                '<td><b>ALSPAC AF</b></td>' +
                                '<td><b>gnomAD ex</b></td>' +
                                '<td><b>gnomAD ex NFE</b></td>' +
                                '<td><b>gnomAD gen</b></td>' +
                                '<td><b>gnomAD gen NFE</b></td></tr>' +
                                '<tr id="Prevalence2Table1">';
                            variantinfostring += '<td>' + $(this).parent().parent()[0].cells[columnindices['G1000_AF']].innerHTML + "</td>";
                            variantinfostring += '<td>' + $(this).parent().parent()[0].cells[columnindices['G1000_eur']].innerHTML + "</td>";
                            variantinfostring += '<td>' + $(this).parent().parent()[0].cells[columnindices['ESP6500']].innerHTML + "</td>";
                            variantinfostring += '<td>' + $(this).parent().parent()[0].cells[columnindices['ExAC_all']].innerHTML + "</td>";
                            variantinfostring += '<td>' + $(this).parent().parent()[0].cells[columnindices['ExAC_eur']].innerHTML + "</td>";
                            variantinfostring += '<td>' + $(this).parent().parent()[0].cells[columnindices['TWINSUK_AF']].innerHTML + "</td>";
                            variantinfostring += '<td>' + $(this).parent().parent()[0].cells[columnindices['ALSPAC_AF']].innerHTML + "</td>";
                            variantinfostring += '<td>' + $(this).parent().parent()[0].cells[columnindices['gnomAD_exomes_AF']].innerHTML + "</td>";
                            variantinfostring += '<td>' + $(this).parent().parent()[0].cells[columnindices['gnomAD_exomes_NFE_AF']].innerHTML + "</td>";
                            variantinfostring += '<td>' + $(this).parent().parent()[0].cells[columnindices['gnomAD_genomes_AF']].innerHTML + "</td>";
                            variantinfostring += '<td>' + $(this).parent().parent()[0].cells[columnindices['gnomAD_genomes_NFE_AF']].innerHTML;

                            variantinfostring += '</td></tr>' +
                                '</table></div></div>'; //end variantPrevalence




                            variantinfostring += '<div id="variantPrediction">'; //start variantPrediction
                            variantinfostring += '<p><div class="additionalinfotableHeadline"><b>Functional prediction:</b></div>Estimated degree of effect that the variant may have on the protein formation, based on various different statistical approaches. The one-letter code gives a rating of deleteriousness from each tool. Use mouse-over to get its full meaning.</p>';
                            /* +
                                                                <div class="additionalinfotableBox">' +
                                                               '<table class="additionalinfotable" id=""  style="table-layout: auto;">' +
                                                               '<tr><td width="220px"><b>Transcripts</b></td><td title="PROVEAN: -14 to 14, lower score is more deleterious, variants below -2.5 are rated as deleterious"><b>Provean_Scores</b>' + infoSymbol + '</td><td title="SIFT: 0 to 1, lower score is more deleterious, variants below 0.05 are rated as damaging"><b>SIFT_Scores</b>' + infoSymbol + '</td></tr><tr id="FunctionalPrediction1">';
                                                           variantinfostring += '<td>' + $(this).parent().parent()[0].cells[columnindices['Transcripts']].innerHTML + "</td>";
                                                           variantinfostring += '<td>' + $(this).parent().parent()[0].cells[columnindices['Provean_Scores']].innerHTML.replace("<b>", "").replace("</b>", "") + "</td>";
                                                           variantinfostring += '<td>' + $(this).parent().parent()[0].cells[columnindices['SIFT_Scores']].innerHTML.replace("<b>", "").replace("</b>", "");

                                                           variantinfostring += '</td></tr>' +
                                                               '</table></div>';*/



                            variantinfostring += '<p><div class="additionalinfotableBox"><table class="additionalinfotable" id=""  style="table-layout: auto;">' +
                                '<tr><td width="220px"><b>Transcripts_dbNSFP</b></td>' +
                                '<td title="PROVEAN: -14 to 14, lower score is more deleterious, variants below -2.5 are rated as deleterious"><b>PROVEAN_score2</b>' + infoSymbol + '</td>' +
                                '<td ><b>PROVEAN_pred</b></td>' +
                                '<td title="SIFT: 0 to 1, lower score is more deleterious, variants below 0.05 are rated as damaging"><b>SIFT_score2</b>' + infoSymbol + '</td>' +
                                '<td ><b>SIFT_pred</b></td>' +
                                '<td title="FATHMM: -18.09 to 11.0, lower score is more deleterious, variants below -1.5 are rated as deleterious"><b>FATHMM_score</b>' + infoSymbol + '</td>' +
                                '<td ><b>FATHMM_pred</b></td></tr>' +
                                '<tr id="FunctionalPrediction2">';
                            variantinfostring += '<td>' + $(this).parent().parent()[0].cells[columnindices['Transcripts_dbNSFP']].innerHTML + "</td>";
                            variantinfostring += '<td class="addPercentSymbol addPercentSymbolProvean">' + $(this).parent().parent()[0].cells[columnindices['PROVEAN_score2']].innerHTML + "</td>";
                            variantinfostring += '<td>' + $(this).parent().parent()[0].cells[columnindices['PROVEAN_pred']].innerHTML + "</td>";
                            variantinfostring += '<td class="addPercentSymbol addPercentSymbolSIFT">' + $(this).parent().parent()[0].cells[columnindices['SIFT_score2']].innerHTML + "</td>";
                            variantinfostring += '<td>' + $(this).parent().parent()[0].cells[columnindices['SIFT_pred']].innerHTML + "</td>";
                            variantinfostring += '<td class="addPercentSymbol addPercentSymbolFATHMM">' + $(this).parent().parent()[0].cells[columnindices['FATHMM_score']].innerHTML + "</td>";
                            variantinfostring += '<td>' + $(this).parent().parent()[0].cells[columnindices['FATHMM_pred']].innerHTML;

                            variantinfostring += '</td></tr>' +
                                '</table></div></p>';





                            variantinfostring += '<p><div class="additionalinfotableBox"><table class="additionalinfotable" id=""  style="table-layout: auto;">' +
                                '<tr><td width="220px"><b>Uniprot_acc</b></td>' +
                                '<td ><b>Uniprot_id</b></td>' +
                                '<td ><b>Uniprot_aapos</b></td>' +
                                '<td title="Polyphen2: 0 to 1, higher score is more deleterious. Prediction: variants >0.85 &quot;probably damaging&quot;, between 0.15 and 0.85 &quot;possibly damaging&quot;, <0.15 &quot;benign&quot;"><b>Polyphen2 HDIV_score</b>' + infoSymbol + '</td>' +
                                '<td ><b>Polyphen2 HDIV_pred</b></td>' +
                                '<td title="Polyphen2: 0 to 1, higher score is more deleterious. Prediction: variants >0.85 &quot;probably damaging&quot;, between 0.15 and 0.85 &quot;possibly damaging&quot;, <0.15 &quot;benign&quot;"><b>Polyphen2 HVAR_score</b>' + infoSymbol + '</td>' +
                                '<td ><b>Polyphen2 HVAR_pred</b></td></tr><tr id="FunctionalPrediction3">';
                            variantinfostring += '<td>' + $(this).parent().parent()[0].cells[columnindices['Uniprot_acc_Polyphen2']].innerHTML + "</td>";
                            variantinfostring += '<td>' + $(this).parent().parent()[0].cells[columnindices['Uniprot_id_Polyphen2']].innerHTML + "</td>";
                            variantinfostring += '<td>' + $(this).parent().parent()[0].cells[columnindices['Uniprot_aapos_Polyphen2']].innerHTML + "</td>";
                            variantinfostring += '<td class="addPercentSymbol addPercentSymbolPolyphen2_HDIV">' + $(this).parent().parent()[0].cells[columnindices['Polyphen2_HDIV_score']].innerHTML + "</td>";
                            variantinfostring += '<td>' + $(this).parent().parent()[0].cells[columnindices['Polyphen2_HDIV_pred']].innerHTML + "</td>";
                            variantinfostring += '<td class="addPercentSymbol addPercentSymbolPolyphen2_HVAR">' + $(this).parent().parent()[0].cells[columnindices['Polyphen2_HVAR_score']].innerHTML + "</td>";
                            variantinfostring += '<td>' + $(this).parent().parent()[0].cells[columnindices['Polyphen2_HVAR_pred']].innerHTML;

                            variantinfostring += '</td></tr>' +
                                '</table></div></p>';



                            variantinfostring += '<p><div class="additionalinfotableBox"><table class="additionalinfotable" id=""  style="table-layout: auto;">' +
                                '<tr>' +
                                '<td width="220px" title="MutationTaster: 0 to 1, higher score is more deleterious. Score was defined from prediction and p-value (if prediction is &quot;disease_causing&quot;, then score=p, if prediction is &quot;polymorphism&quot;, then score=1-p)"><b>MutationTaster_score</b>' + infoSymbol + '</td>' +
                                '<td><b>MutationTaster_pred</b></td>' +
                                '<td><b>MutationTaster_model</b></td>' +
                                '<td><b>MutationTaster_AAE</b></td></tr>' +
                                '<tr id="FunctionalPrediction4">';
                            variantinfostring += '<td class="addPercentSymbol addPercentSymbolMutationTaster">' + $(this).parent().parent()[0].cells[columnindices['MutationTaster_score']].innerHTML + "</td>";
                            variantinfostring += '<td>' + $(this).parent().parent()[0].cells[columnindices['MutationTaster_pred']].innerHTML + "</td>";
                            variantinfostring += '<td>' + $(this).parent().parent()[0].cells[columnindices['MutationTaster_model']].innerHTML + "</td>";
                            variantinfostring += '<td>' + $(this).parent().parent()[0].cells[columnindices['MutationTaster_AAE']].innerHTML;

                            variantinfostring += '</td></tr>' +
                                '</table></div></p>';



                            variantinfostring += '<p><div class="additionalinfotableBox"><table class="additionalinfotable" id=""  style="table-layout: auto;">' +
                                '<tr><td width="220px"><b>MutationAssessor_UniprotID</b></td>' +
                                '<td><b>MutationAssessor_variant</b></td>' +
                                '<td title="MutationAssessor: -5.545 to 5.975, larger score is more deleterious. Predictions: high, medium, low and neutral."><b>MutationAssessor_score</b>' + infoSymbol + '</td>' +
                                '<td><b>MutationAssessor_pred</b></td></tr><tr id="FunctionalPrediction5">';
                            variantinfostring += '<td>' + $(this).parent().parent()[0].cells[columnindices['MutationAssessor_UniprotID']].innerHTML + "</td>";
                            variantinfostring += '<td>' + $(this).parent().parent()[0].cells[columnindices['MutationAssessor_variant']].innerHTML + "</td>";
                            variantinfostring += '<td class="addPercentSymbol addPercentSymbolMutationAssessor">' + $(this).parent().parent()[0].cells[columnindices['MutationAssessor_score']].innerHTML + "</td>";
                            variantinfostring += '<td>' + $(this).parent().parent()[0].cells[columnindices['MutationAssessor_pred']].innerHTML;

                            variantinfostring += '</td></tr>' +
                                '</table></div></p>';





                            variantinfostring += '<p><div class="additionalinfotableBox"><table class="additionalinfotable" id=""  style="table-layout: auto;">' +
                                '<tr><td width="220px"><b>Transcript_id_VEST3</b></td>' +
                                '<td ><b>Transcript_var_VEST3</b></td>' +
                                '<td title="VEST 3.0: 0 to 1, higher score is more deleterious"><b>VEST3_score</b></td></tr><tr id="FunctionalPrediction6">';
                            variantinfostring += '<td>' + $(this).parent().parent()[0].cells[columnindices['Transcript_id_VEST3']].innerHTML + "</td>";
                            variantinfostring += '<td>' + $(this).parent().parent()[0].cells[columnindices['Transcript_var_VEST3']].innerHTML + "</td>";
                            variantinfostring += '<td>' + $(this).parent().parent()[0].cells[columnindices['VEST3_score']].innerHTML + "</td>";

                            variantinfostring += '</td></tr>' +
                                '</table></div></p></div>'; //end variantPrediction







                            variantinfostring += '<div id="variantQuality">'; //start variantQuality
                            variantinfostring += '<p><div class="additionalinfotableHeadline"><b>Sequencing quality:</b></div>Different indicators for the technical quality of the sequencing process at the current locus. Better values increasy the trustworthiness of the variant to be present in the patient.' +
                                '</p><div class="additionalinfotableBox">' +
                                '<table class="additionalinfotable2" id=""  style="table-layout: auto;">' +
                                '<tr>' +
                                '<td title="Variant Allelic Frequency (how many of the detected reads show the variant)" width="220px"><b>VAF</b>' + infoSymbol + '</td>' +
                                '<td title="Coverage (how many reads exist for this locus)"><b>Cvg</b>' + infoSymbol + '</td>' +
                                '<td title="Nr of reads that show the reference allele"><b>NRref</b>' + infoSymbol + '</td>' +
                                '<td title="Nr of reads that show the alternative allele"><b>NRalt</b>' + infoSymbol + '</td>' +
                                '<td title="Base quality (average) of all reads that show the reference allele"><b>BQref</b>' + infoSymbol + '</td>' +
                                '<td title="Base quality (average) of all reads that show the alternative allele (should be similar to BQref)"><b>BQalt</b>' + infoSymbol + '</td>' +
                                '<td title="Nr reads on the forward strand that show the reference allele (we assume to find the variant on both strands with similar frequency)"><b>NRref_fwd</b>' + infoSymbol + '</td>' +
                                '<td title="Nr reads on the forward strand that show the alternative allele (we assume to find the variant on both strands with similar frequency)"><b>NRalt_fwd</b>' + infoSymbol + '</td>' +
                                '<td title="Nr reads on the reverse strand that show the reference allele (we assume to find the variant on both strands with similar frequency)"><b>NRref_rev</b>' + infoSymbol + '</td>' +
                                '<td title="Nr reads on the reverse strand that show the alternative allele (we assume to find the variant on both strands with similar frequency)"><b>NRalt_rev</b>' + infoSymbol + '</td>' +
                                '<td title="Coverage, i.e. number of reads on the forward strand"><b>Cvg_fwd</b>' + infoSymbol + '</td>' +
                                '<td title="Coverage, i.e. number of reads on the reverse strand"><b>Cvg_rev</b>' + infoSymbol + '</td></tr><tr id="SequencingQuality1">';
                            variantinfostring += '<td>' + $(this).parent().parent()[0].cells[columnindices['VAF']].innerHTML + "</td>";
                            variantinfostring += '<td>' + $(this).parent().parent()[0].cells[columnindices['Cvg']].innerHTML + "</td>";
                            variantinfostring += '<td>' + $(this).parent().parent()[0].cells[columnindices['NRref']].innerHTML + "</td>";
                            variantinfostring += '<td>' + $(this).parent().parent()[0].cells[columnindices['NRalt']].innerHTML + "</td>";
                            variantinfostring += '<td>' + $(this).parent().parent()[0].cells[columnindices['BQref']].innerHTML + "</td>";
                            variantinfostring += '<td>' + $(this).parent().parent()[0].cells[columnindices['BQalt']].innerHTML + "</td>";
                            variantinfostring += '<td>' + $(this).parent().parent()[0].cells[columnindices['NRref_fwd']].innerHTML + "</td>";
                            variantinfostring += '<td>' + $(this).parent().parent()[0].cells[columnindices['NRalt_fwd']].innerHTML + "</td>";
                            variantinfostring += '<td>' + $(this).parent().parent()[0].cells[columnindices['NRref_rev']].innerHTML + "</td>";
                            variantinfostring += '<td>' + $(this).parent().parent()[0].cells[columnindices['NRalt_rev']].innerHTML + "</td>";
                            variantinfostring += '<td>' + $(this).parent().parent()[0].cells[columnindices['Cvg_fwd']].innerHTML + "</td>";
                            variantinfostring += '<td>' + $(this).parent().parent()[0].cells[columnindices['Cvg_rev']].innerHTML;

                            variantinfostring += '</td></tr>' +
                                '</table></div></div>'; //end variantQuality






                            //NOTE End: additional info tables









                            variantinfostring += '</td></tr></table></p>';

                            $('#variantInformation').append(variantinfostring);


                            //  Literature TAB
                            //  MUTATION INFORMATION //
                            $('#additionalinfopanel').append('<div id="mutationInformation" class="additionalInfoPanel">' +
                                '<p><table class="additionalinfotable" width="100%"><tr><td class="labelcol"><b>Common Mutations in this gene (Literature references)</b></td></tr></table>' +
                                '<table class="additionalinfotable singlemutationinfotable" id="fullwidth" style="display:none;"></table>' +
                                '<table class="additionalinfotable singlemutationinfotableloader" id="fullwidth"><tr><td>' +
                                '<img src="common/ajax-loader.gif" style="display:block; margin:auto;" />' +
                                '</td></tr></table>' +
                                '</div>');

                            var possibleVariantIDs = [];
                            //LOAD CIVIC DATA//
                            //additionalPanelCallId is necessary because otherwise the success case would change the table even if the user already closed the popover and opened a new one (with different information)
                            var callId = additionalPanelCallId;
                            if (genename != "") { //otherwise we get a error for table rows without a genename (examples in sample 1)
                                $.ajax({
                                    type: 'GET',
                                    url: 'https://civicdb.org/api/genes/' + genename,
                                    data: 'identifier_type=entrez_symbol',
                                    dataType: 'json',
                                    success: function(ret) {
                                        if (callId != additionalPanelCallId) {
                                            return;
                                        }
                                        if (debug) console.log("success case ajax 11");
                                        var varianttypegenestring = "";
                                        if (ret.length && ret.length > 1) {
                                            var geneinfostring = "";
                                            $.each(ret, function(index, tabledata) {
                                                $.each(tabledata['variants'], function(index, variant) {
                                                    possibleVariantIDs.push(variant['id']);
                                                });
                                                geneinfostring += '<p><table class="additionalinfotable" id="fullwidth"><tr><td class="labelcol"><b>Gene</b></td>'

                                                entrez_id = tabledata['entrez_id'];
                                                varianttypegenestring += '<a href=https://www.ncbi.nlm.nih.gov/gene/' + entrez_id + ' target="_blank">' + tabledata['name'] + '</a>';
                                                if (index < ret.length - 1) {
                                                    varianttypegenestring += ', ';
                                                }
                                                geneinfostring += '<td><a href=https://www.ncbi.nlm.nih.gov/gene/' + entrez_id + ' target="_blank">' + tabledata['name'] + '</a></td></tr>' +
                                                    '<tr><td colspan="2">';
                                                if (tabledata['description'].length > 0) {
                                                    geneinfostring += tabledata['description'];
                                                } else {
                                                    geneinfostring += 'No description available.';
                                                }
                                                geneinfostring += '<!--div style="float: right;"--> (<i>Source: <a href=https://civicdb.org/events/genes/' + tabledata['id'] + '/summary#gene target="_blank">CIVic</a></i>)</div></td></tr>' +
                                                    '</table></p>';
                                            });
                                        } else {
                                            tabledata = ret;
                                            $.each(tabledata['variants'], function(index, variant) {
                                                possibleVariantIDs.push(variant['id']);
                                            });
                                            geneinfostring = '<p><table class="additionalinfotable" id="fullwidth"><tr><td class="labelcol"><b>Gene</b></td>'

                                            entrez_id = tabledata['entrez_id'];
                                            varianttypegenestring += '<a href=https://www.ncbi.nlm.nih.gov/gene/' + entrez_id + ' target="_blank">' + tabledata['name'] + ' </a>';
                                            geneinfostring += '<td><a href=https://www.ncbi.nlm.nih.gov/gene/' + entrez_id + ' target="_blank">' + tabledata['name'] + '</a></td></tr>' +
                                                '<tr><td colspan="2">';
                                            if (tabledata['description'].length > 0) {
                                                geneinfostring += tabledata['description'];
                                            } else {
                                                geneinfostring += 'No description available.';
                                            }
                                            geneinfostring += '<!--div style="float: right;"--> (<i>Source: <a href=https://civicdb.org/events/genes/' + tabledata['id'] + '/summary#gene target="_blank">CIVic</a></i>)</div></td></tr>' +
                                                '</table></p>';
                                        }
                                        $('#geneInformation4 td')[4].innerHTML = '<a href=https://civicdb.org/events/genes/' + tabledata['id'] + '/summary#gene target="_blank">CIVic</a>';
                                        $('#geneInformation').empty().append(geneinfostring);
                                        if (varianttypegenestring !== "") {
                                            $('#varianttypegenename').empty().append(varianttypegenestring);
                                            // updatePopover(); //update popover
                                        }

                                        //load additional data for the tables:
                                        $.ajax({
                                            type: "POST",
                                            url: "sampleinteraction.php",
                                            data: "action=getAdditionalTableInfo&gene=" + genename,
                                            dataType: 'json',
                                            async: false,
                                            success: function(ret) {
                                                if (debug) console.log("success case ajax 20");
                                                if (ret[0] == 1) {
                                                    //include full gene name
                                                    var full_name = ret[1]["Gene_full_name"];
                                                    if (full_name != undefined) {
                                                        $('#geneInformation td')[1].innerHTML += ' (' + full_name + ')';
                                                    }

                                                    //set decription
                                                    var func_descr = ret[1]["Function_description"];
                                                    if (func_descr != undefined) {
                                                        func_descr = func_descr.replace('FUNCTION: ', '')
                                                    } else {
                                                        func_descr = 'No description available.';
                                                    }
                                                    $('#geneInformation2 td')[1].innerHTML = func_descr;

                                                    //Affected pathways
                                                    var pathway = ret[1]["Pathway(ConsensusPathDB)"];
                                                    if (pathway != undefined) {
                                                        pathway = pathway.replace(/;/g, '<br>');
                                                    } else {
                                                        pathway = "No pathway information available."
                                                    }
                                                    $('#geneInformation3 td')[1].innerHTML = pathway;
                                                } else {
                                                    displayError(ret[1] + "[23]");
                                                }
                                            },
                                            error: function(xhr, status, errorThrown) {}
                                        });


                                        //LOAD CIVIC Mutation Data//
                                        if (possibleVariantIDs.length == 0) {
                                            var singlemutation = '<table class="additionalinfotable" id="fullwidth"><tr><td>';
                                            singlemutation += 'No variants match filter query.';
                                            singlemutation += "<a href=https://civicdb.org/events/genes/" + tabledata['id'] + '/summary#gene  target="_blank">CIVic</a>';
                                            singlemutation += "</td></tr></table>";
                                            $('.singlemutationinfotable').empty().append(singlemutation);
                                            $('.singlemutationinfotableloader').hide();
                                            $('.singlemutationinfotable').show();
                                        }
                                        $.each(possibleVariantIDs, function(index, id) {
                                            if (debug) console.log("starte ajax 12");
                                            $.ajax({
                                                type: 'GET',
                                                url: 'https://civicdb.org/api/variants/' + id,
                                                dataType: 'json',
                                                success: function(ret) {
                                                    if (callId != additionalPanelCallId) {
                                                        return;
                                                    }
                                                    if (debug) console.log("success case ajax 12");
                                                    if (ret['coordinates']['chromosome'] == chromosome &&
                                                        ret['coordinates']['start'] <= genePosition &&
                                                        ret['coordinates']['stop'] >= genePosition) {
                                                        var singlemutation = '<tbody><tr><td width="220px" style="vertical-align:top;">' +
                                                            '<a href="https://civicdb.org/events/genes/' +
                                                            ret['gene_id'] +
                                                            '/summary/variants/' +
                                                            ret['id'] +
                                                            '/summary#variant" target="_blank">' + ret['name'] + '</a>' +
                                                            '<p style="font-size: 10px;">[' + ret['coordinates']['start'] + '-' + ret['coordinates']['stop'] + ']</p></td><td>';
                                                        if (ret['description'].length > 0) {
                                                            singlemutation += ret['description'];
                                                        } else {
                                                            singlemutation += 'No summary available.'
                                                        }
                                                        singlemutation += ' (<i>Source: <a href="https://civicdb.org/events/genes/' +
                                                            ret['gene_id'] +
                                                            '/summary/variants/' +
                                                            ret['id'] +
                                                            '/summary#variant" target="_blank">CIVic</a></i>)</div>';

                                                        if (true) { //numberCivic > 0) { //show the table with civic information - show references.
                                                            singlemutation += '<p><div id="showcivictable" variant_id="' + ret['id'] +
                                                                '" style="display:none; width:150px;" class="filtertoggle" data-toggle="dropdown">' +
                                                                'References <span class="glyphicon glyphicon-chevron-down"></span></div>' +
                                                                '<div id="civictablepanel_' + ret['id'] + '" style="display: none;"></div></p>';
                                                        }



                                                        singlemutation += '</td></tr>'

                                                        $('.singlemutationinfotable').append(singlemutation);

                                                        //Load Reference Table for each Mutation
                                                        tabledata = [];
                                                        $.each(ret['evidence_items'], function(i, item) {
                                                            tabledata.push({
                                                                disease: '<a href="' + item.disease.url + '" target="_blank">' + item.disease.display_name + '</a>',
                                                                clinical_significance: item.clinical_significance,
                                                                drugs: item.drugs.name,
                                                                evidence_type: item.evidence_type,
                                                                evidence_direction: item.evidence_direction,
                                                                evidence_level: item.evidence_level,
                                                                citation: item.source.citation +
                                                                    '<br>(<a href="' +
                                                                    item.source.source_url +
                                                                    '" target="_blank" title="Pubmed ID">' +
                                                                    item.source.pubmed_id + '</a>)',
                                                                evidence_statement: item.description
                                                            });
                                                        });


                                                        if (tabledata.length > 0) {
                                                            $('#civictablepanel_' + id).append(getTableHtml('civictable_' + id, true));
                                                            setupTablesorterCivicTable('civictable_' + id, 'civiccolumnchooser');
                                                            $('#showcivictable[variant_id="' + id + '"]').show();
                                                        }
                                                    }
                                                    if (index == possibleVariantIDs.length - 1) {
                                                        $('.singlemutationinfotableloader').hide();
                                                        $('.singlemutationinfotable').show();
                                                    }

                                                },
                                                error: function(xhr, status, errorThrown) {
                                                    if (debug) console.log("API https://civicdb.org/: not found. ID:" + id);
                                                    if (index == possibleVariantIDs.length - 1) {
                                                        $('.singlemutationinfotableloader').hide();
                                                        $('.singlemutationinfotable').show();
                                                    }
                                                }
                                            });
                                        });
                                    },
                                    //to access this error case use Gene C10orf2 Sample 3001
                                    error: function() {
                                        if (debug) console.log("Error case: no civic info exists for this gene [1]");

                                        var gene_description = 'No description available.';
                                        gene_description += '<!--div style="float: right;"--> (<i>Source: <a href="https://civicdb.org" target="_blank">CIVic</a></i>)';
                                        $('#geneInformation tr')[1].cells[0].innerHTML = gene_description;

                                        var function_description = 'No description available.';
                                        $('#geneInformation2 td')[1].innerHTML = function_description;

                                        var pathway_description = "No pathway information available."
                                        $('#geneInformation3 td')[1].innerHTML = pathway_description;
                                    }
                                });


                            } else {
                                var geneinfostring = '<p><table class="additionalinfotable" id="fullwidth"><tr><td class="labelcol"><b>Gene</b></td></tr><tr><td>No gene info</td></tr></table><p>';
                                $('#geneInformation').empty().append(geneinfostring);

                                $('#mutationInformation').empty().append('<p><table class="additionalinfotable" width="100%"><tr><td class="labelcol"><b>Common Mutations in this gene (Literature references)</b></td></tr></table>' +
                                    '<table class="additionalinfotable singlemutationinfotable" id="fullwidth" style="display:none;"></table>' +
                                    '<table class="additionalinfotable singlemutationinfotableloader" id="fullwidth"><tr><td>' +
                                    'No gene info' +
                                    '</td></tr></table>');
                            }



                            editAdditionalInfoTables();


                            if ($(this).hasClass('selectedrow')) {
                                $(this).removeClass('selectedrow');
                                selectedrow = 0;
                            } else {
                                $('.selectedrow').removeClass('selectedrow');
                                $(this).addClass('selectedrow');
                                selectedrow = $(this)[0].rowIndex;
                            }


                            updatePopover();
                        }



                    });

                    //view in genome-browser for the table-buttons
                    $('#panels').on('click', '#viewingenomebrowserTABLEbutton', function() {
                        if (debug) console.log("Trigger 30");
                        var chr = 'chr' + $(this).parent().parent()[0].cells[columnindices['chr']].innerHTML;
                        var start = (Number($(this).parent().parent()[0].cells[columnindices['pos']].innerHTML) - igvBpWidth);
                        var stop = (Number($(this).parent().parent()[0].cells[columnindices['pos']].innerHTML) + igvBpWidth);

                        genomeBrowserChange = true;
                        pileuprange = {
                            chr: chr,
                            start: start,
                            stop: stop
                        };

                        if ($('#completepanelsubtablescrollpanel').length > 0) {
                            scrollposition = $('#completepanelsubtablescrollpanel')[0].scrollTop;
                        }
                        chosentab = 'genomebrowser';
                        $('[tab=genomebrowser]').tab('show');
                        tabselected();
                    });

                    //view in genome-browser for the additional info panel button
                    $('#panels').on('click', '#viewingenomebrowserbutton', function() {
                        if (debug) console.log("Trigger 31");
                        genomeBrowserChange = true;
                        pileuprange = {
                            chr: $(this).attr('chr'),
                            start: $(this).attr('start'),
                            stop: $(this).attr('stop')
                        };

                        //if ($('#completepanelsubtablescrollpanel').length > 0) scrollposition = $('#completepanelsubtablescrollpanel')[0].scrollTop;
                        chosentab = 'genomebrowser';
                        $('[tab=genomebrowser]').tab('show');
                        tabselected();
                    });


                    $('#panels').on('click', '#CloseAdditionalInfo', function() {
                        if (debug) console.log("Trigger 31.5");
                        lastActivatedTableRow.find("#lupe").click();
                    });

                    $('#panels').on('click', '.columnchoosersavebutton', function() {
                        if (debug) console.log("Trigger 32");
                        var activated = getActivatedCheckboxes();
                        $('#completepanelsubtable').trigger('refreshColumnSelector', ['selectors', activated]);
                        setResizableColumnWidth();
                        resizeTable();
                        $('.columnchooserbutton').popover('hide');
                        columnSearchShowAll(); //delete the current search filter
                    });

                    $('#panels').on('click', '.columnchooserDefaultButton', function() {
                        if (debug) console.log("Trigger 33");
                        saveSettingsFlag = false;
                        $('#completepanelsubtable').trigger('refreshColumnSelector', ['selectors', selectedColumnsDefault]);

                        var selected = selectedColumnsDefault + "";
                        selected = selected.replace("[", "").replace("]", "").split(",");
                        setupClickboxes(selected);

                        loadedSettings['columnWidth'] = columnWidthDefault;
                        setColumnWidth();

                        columnSearchShowAll();

                        saveSettingsFlag = true;
                        saveSettings();
                        setResizableColumnWidth();
                        resizeTable();
                    });



                    $('#panels').on('click', '.columnchooserselectallbutton', function() {
                        if (debug) console.log("Trigger 34");
                        /* var html = $('#columnchooserClickBoxes').html();
                         if(debug) console.log(html);
                         html = html.replace(/class=""/g, 'class="checked"');
                         if(debug) console.log(html);
                         $('#columnchooserClickBoxes').html(html);*/
                        if ($(this).html().indexOf('select all') == 0) {
                            saveSettingsFlag = false;
                            $('#completepanelsubtable').trigger('refreshColumnSelector', ['selectors', 'all']);
                            saveSettingsFlag = true;

                            var selected = [0];
                            var lengthMax = $('#completepanelsubtable').children().first().find('th').length;
                            for (i = 1; i < lengthMax; i++) {
                                selected.push(i);
                            }
                            setupClickboxes(selected);

                            saveSettings();
                            $(this).html('disselect all')
                        } else {
                            saveSettingsFlag = false;
                            $('#completepanelsubtable').trigger('refreshColumnSelector', ['selectors', selectedColumnsMinimum]);

                            var selected = selectedColumnsMinimum + "";
                            selected = selected.replace("[", "").replace("]", "").split(",");
                            setupClickboxes(selected);

                            saveSettingsFlag = true;
                            saveSettings();
                            $(this).html('select all')
                        }
                        columnSearchShowAll();

                        setResizableColumnWidth();
                        resizeTable();

                    });

                    $('#panels').on('click', '.columnchooserbutton', function() {
                        if (debug) console.log("Trigger 35");
                        if ($('#columnchooserClickBoxes').is(":visible")) {
                            $(this).popover('toggle');
                        } else {
                            $(this).popover('toggle');
                        }
                    });

                    $('#panels').on('click', '#showcivictable', function() {
                        if (debug) console.log("Trigger 36");
                        $('#civictablepanel_' + $(this).attr('variant_id')).toggle();
                        if ($(this).find('span').hasClass('glyphicon-chevron-down')) {
                            $(this).find('span').removeClass('glyphicon-chevron-down');
                            $(this).find('span').addClass('glyphicon-chevron-up');
                        } else {
                            $(this).find('span').removeClass('glyphicon-chevron-up');
                            $(this).find('span').addClass('glyphicon-chevron-down');
                        }
                    });

                    $('#panels').on('click', '#report', function() {
                        if (debug) console.log("Trigger 37");
                        $('#personalReport').toggle()
                        if ($(this).find('span').hasClass('glyphicon-chevron-down')) {
                            $(this).find('span').removeClass('glyphicon-chevron-down');
                            $(this).find('span').addClass('glyphicon-chevron-up');
                        } else {
                            $(this).find('span').removeClass('glyphicon-chevron-up');
                            $(this).find('span').addClass('glyphicon-chevron-down');
                        }
                    });

                    $('#panels').on('input', '#reportTextarea', function() {
                        if (debug) console.log("Trigger 38");
                        reportTextareaChanges = true;
                    });

                    $('#panels').on('change', '#reportTextarea', function() {
                        if (debug) console.log("Trigger 39");
                        $.ajax({
                            type: 'POST',
                            url: 'sampleinteraction.php',
                            data: 'action=updateRating&sid=' + sid + '&rating=' + $('#reportTextarea').val(),
                            dataType: 'json',
                            success: function(ret) {
                                if (debug) console.log("success case ajax 13");
                                if (ret[0] == 1) {
                                    //if(debug) console.log(ret);
                                } else {
                                    displayError(ret[1] + "[24]");
                                }
                                reportTextareaChanges = false;
                            },
                            error: function(xhr, status, errorThrown) {
                                displayError(errorThrown + "[25]");
                            }
                        });
                    });


                    //--- HOTSPOTS PAGE HANDLERS ---//
                    $("#hotspotsPanel").on("click hotspotsboxclicked", ".hotspotsbox", function() {
                        if (debug) console.log("Trigger 40");
                        $('.hotspotsboxactive').each(function() {
                            $(this).removeClass('hotspotsboxactive');
                        });

                        $('.reporttable').each(function() {
                            $(this).hide();
                        });

                        $(this).addClass('hotspotsboxactive');
                        $('#reporttable' + $(this).attr('id').substr(11)).show();
                    });

                    $("#hotspotsPanel").on("click pdfreportclicked", ".pdfreportli", function() {
                        if (debug) console.log("Trigger 41");
                        $("#pdfreportsbutton").empty().append($(this).attr("displayname") + ' <span class="caret"></span>');
                        selectedpdfversion = $(this).attr("version");
                    });

                    $("#hotspotsPanel").on("click", "#pdfdownloadbutton", function() {
                        if (debug) console.log("Trigger 42");
                        window.open('downloadpdf.php?sid=' + sid + '&pid=' + pid + '&version=' + selectedpdfversion, '_blank');
                    });



                    //--- TAB HANDLERS ---//
                    $('.nav-tabs a').click(function(e) {
                        if (debug) console.log("Trigger 43");
                        if (pageloadingflag == false) {
                            pageloadingflag = true;
                            if ($(this).attr('id') === "report") {
                                return;
                            }
                            //save page properties
                            if ($('#completepanelsubtablescrollpanel').length > 0) scrollposition = $('#completepanelsubtablescrollpanel')[0].scrollTop;

                            $(this).tab('show');
                            chosentab = $(this).attr("tab");
                            tabselected();
                        }
                    });

                    $('.nav-tabs a').on("show.bs.popover", function() {
                        if (debug) console.log("Trigger 44");
                        $(this).data("bs.popover").tip().css("max-width", "600px");
                    });


                    //event listener for filterByQuality text input
                    $('#nralttext').change(function() {
                        if (debug) console.log("Trigger 45");
                        $("#nraltslider").slider("value", parseInt($('#nralttext').val()));
                    });
                    $('#dptext').change(function() {
                        if (debug) console.log("Trigger 46");
                        $("#dpslider").slider("value", parseInt($('#dptext').val()));
                    });
                    $('#vaftext').change(function() {
                        if (debug) console.log("Trigger 47");
                        if (parseFloat($('#vaftext').val()) <= 0.01) {
                            if (debug) console.log("true");
                            $("#vafslider").slider("value", parseFloat($('#vaftext').val()) * 1000);
                        } else {
                            if (debug) console.log("false");
                            $("#vafslider").slider("value", (parseFloat($('#vaftext').val()) + 0.2) / 0.02);
                            if (debug) console.log("wert: " + Math.round(("value", parseFloat($('#vaftext').val()) + 0.2) / 0.02));
                        }
                    });
                    $('#lowbqtext').change(function() {
                        if (debug) console.log("Trigger 48");
                        $("#lowbqslider").slider("value", parseInt($('#lowbqtext').val()));
                    });
                    $('#bqdifftext').change(function() {
                        if (debug) console.log("Trigger 49");
                        $("#bqdiffslider").slider("value", parseInt($('#bqdifftext').val()));
                    });

                    $('#completepanelsubtable').bind("sortEnd", function(e, t) {
                        if (debug) console.log("Trigger 57");
                        saveSettings();
                        setResizableColumnWidth();
                    }).bind("columnUpdate", function(e, t) {
                        if (debug) console.log("Trigger 58");
                        saveSettings();
                    });

                    //force-save textarea report before User leaves the site:
                    $(window).on('beforeunload', function() {
                        if (debug) console.log("Trigger 1");
                        if (reportTextareaChanges) {
                            //asyc-ajax Call:
                            $.ajax({
                                type: 'POST',
                                async: false,
                                url: 'sampleinteraction.php',
                                data: 'action=updateRating&sid=' + sid + '&rating=' + $('#reportTextarea').val(),
                                dataType: 'json',
                                success: function(ret) {
                                    if (debug) console.log("success case ajax 7");
                                    if (ret[0] == 1) {
                                        //if(debug) console.log(ret);
                                    } else {
                                        displayError(ret[1] + "[15]");
                                    }
                                    reportTextareaChanges = false;
                                },
                                error: function(xhr, status, errorThrown) {
                                    displayError(errorThrown + "[16]");
                                    return 'Unsaved data in textarea. Do you really want to close this tab?';
                                }
                            });
                        }
                    });


                    $('#completepanelsubtable').on('resizableComplete', function(event) {
                        if (debug) console.log("Trigger resizable Event");
                        setTimeout(function() {
                            setResizableColumnWidth();
                        }, 50);
                    });


                    $('#CloseColumnChooser').on('click', function() {
                        $('.columnchooserbutton').click();
                    });


                    //search function of the column chooser
                    //this methods only shows the columns the user looked for
                    $('#columnChooserSearch').on('keyup', function() {
                        if (debug) console.log("keyup");
                        var searchString = $('#columnChooserSearch').val();
                        if (debug) console.log("searchstring: " + searchString);

                        //hide elements
                        var count = 0;
                        var search;
                        if (searchString.length > 0) {
                            $($('#columnchooserClickBoxes label')).each(function() {
                                if (count == 0) {
                                    count++;
                                    return;
                                }

                                search = "[^x]*" + "(x)*" + searchString; //don't start with letter x as often as u like (zero allowed) = wildcard at the beginning
                                if (!$(this).text().search(new RegExp(search, "i"))) {
                                    $(this).show();
                                } else {
                                    $(this).hide();
                                }
                            });
                            $(".arrow").hide();
                        } else {
                            //show all
                            $($('#columnchooserClickBoxes label')).each(function() {
                                if (count == 0) {
                                    count++;
                                    return;
                                }
                                $(this).show();
                            });
                            $(".arrow").show();
                        }
                    })



                    //deactivate rightclick event for the table header
                    //dont reset column width
                    //the automatic would make all column width huge = ugly
                    $('#completepanelsubtable').children().first().off('contextmenu');
                }

            </script>

            <?php } ?>
