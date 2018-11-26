<?php
    include_once "common/base.php";
    $pageTitle = "Patients";
    include_once "common/header.php";

if(isset($_SESSION['LoggedIn']) && isset($_SESSION['Username']) && isset($_SESSION['editPermission']) && $_SESSION['LoggedIn']==1){ ?>

    <div class="container-fluid">
        <div id="errorpanel" class="alert alert-danger" role="alert" style="display:none; margin-top: 10px;">
            <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
            <b>Error:</b><br/>
        </div>
        <div id="start" class="row row1">
            <div class="col-md-5 columnwide">
                <div style="float:right;max-width:450px;width:100%;">
                    <h2 style="float:left;">Patients overview</h2>
                    <div id="patientslistbuttons" style="margin-left:270px; margin-top:22px;">
                        <?php if($_SESSION['editPermission'] == 1) { ?>
                        <!--span class="glyphicon glyphicon-plus glyphmediumbutton" aria-hidden="true" data-toggle="tooltip" data-placement="top" title="Add a new patient"></span-->
                        <button type="button" class="AddPatientButton btn btn-default dropdown-toggle" data-toggle="tooltip" data-placement="top">Add a new patient</button>
                        <?php } else { ?>
                        <span class="glyphicon glyphicon-plus glyphmediumbutton glyphbuttondisabled" aria-hidden="true" data-toggle="tooltip" data-placement="top" title="This user cannot add patients"></span>
                        <?php } ?>
                    </div>
                    <div id="sortlist" style="margin-top:20px;">
                        <div class="dropdown">
                            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" id="samplesbutton">Sort by <span class="caret"></span></button>
                            <ul class="dropdown-menu scrollable-menu" role="menu">
                                <li sortby="created" style="cursor:pointer;" displayname="creation date"><a>creation date</a></li>
                                <li sortby="newestsample" style="cursor:pointer;" displayname="newest samples"><a>newest samples</a></li>
                                <li sortby="samplecount" style="cursor:pointer;" displayname="sample count"><a>sample count</a></li>
                                <li sortby="patientname" style="cursor:pointer;" displayname="patient name"><a>patient name</a></li>
                                <li sortby="birthdate" style="cursor:pointer;" displayname="birth date"><a>birth date</a></li>
                                <li sortby="patientnumber" style="cursor:pointer;" displayname="patientnumber"><a>patientnumber</a></li>
                            </ul>
                        </div>
                    </div>
                    <div id="patientslistloader" style="margin-top:15px;"><img src="common/ajax-loader.gif" style="display:block; margin:auto;" /></div>
                    <div id="patientslist" class="patientslistpanel" style="margin-top:15px;"></div>
                </div>
            </div>
            <div class="col-md-7 columnwide">
                <div style="float:left;max-width:750px;width:100%;">
                    <div id="patientsinfo" class="patientpanelPatients"></div>
                    <h2 style="float:left;">Samples overview</h2>
                    <div id="sampleslistbuttons" style="margin-left:275px; margin-top:22px;">
                        <?php if($_SESSION['editPermission'] == 1) { ?>
                        <button id="AddSampleButton" type="button" class="btn btn-default dropdown-toggle" data-toggle="tooltip" data-placement="top" data-original-title="Select a patient first to add a sample" title="">Add a new Sample</button>
                        <!--span class="glyphicon glyphicon-plus glyphmediumbutton glyphbuttondisabled" aria-hidden="true" data-toggle="tooltip" data-placement="top" title="Select a patient first to add a sample"></span-->
                        <?php } else { ?>
                        <button id="AddSampleButton" type="button" class="btn btn-default dropdown-toggle" data-toggle="tooltip" data-placement="top" data-original-title="This user cannot add samples" title="">Add a new Sample</button>
                        <!--span class="glyphicon glyphicon-plus glyphmediumbutton glyphbuttondisabled" aria-hidden="true" data-toggle="tooltip" data-placement="top" title="This user cannot add samples"></span-->
                        <?php } ?>
                    </div>
                    <div id="tablepanel" class="tablepanelPatients"></div>
                    <div id="tableloader"><img src="common/ajax-loader.gif" style="display:block; margin:auto;" /></div>
                </div>

                <?php } else { ?>

                <div class="container-fluid">
                    <div id="start" class="row row1">
                        <div class="col-md-12 column1">
                            <h1>Please log in!</h1>

                            <?php } ?>

                        </div>
                    </div>
                </div>


                <div class="modal fade" id="NewPatientModal" tabindex="-1" role="dialog">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                <h4 class="modal-title" id="myModalLabel">Add a new patient</h4>
                            </div>
                            <div class="modal-body" id="NewPatientModalContent">
                                <div class="form-group">
                                    <label for="NewPatientname">Name (pseudoym)</label>
                                    <input type="text" id="NewPatientname" class="form-control" maxlength="50" />
                                </div>
                                <div class="alert alert-danger" role="alert" id="NewPatientnameError" style="display:none;">
                                    <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
                                    <span class="sr-only">Error:</span> Name (pseudonym) is a required field!
                                </div>
                                <div class="form-group">
                                    <label for="NewPatientnumber">Patient Number *</label>
                                    <input type="text" id="NewPatientnumber" class="form-control" maxlength="50" />
                                </div>
                                <div class="form-group">
                                    <label for="NewBirthdate">Birth Date *</label>
                                    <input class="form-control dateinput" id="NewBirthdate" placeholder="yyyy-mm-dd" />
                                </div>
                                <div class="alert alert-danger" role="alert" id="NewBirthdateError" style="display:none;">
                                    <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
                                    <span class="sr-only">Error:</span> Invalid Date-Format
                                </div>
                                <div class="form-group">
                                    <label for="NewSex">Sex *</label>
                                    <select id="NewSex" class="form-control">
                                        <option>Unknown</option>
                                        <option>Male</option>
                                        <option>Female</option>
                                    </select>
                                </div>
                                <button id="NewPatientSubmit" type="submit" class="btn btn-default">Submit</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="NewSampleModal" tabindex="-1" role="dialog">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                <h4 class="modal-title" id="myModalLabel">Add a new sample</h4>
                            </div>
                            <div class="modal-body" id="NewSampleModalContent">
                                <div class="form-group">
                                    <label for="NewDiagnosis">Diagnosis</label>
                                    <input type="text" id="NewDiagnosis" class="form-control" maxlength="50" />
                                </div>
                                <div class="form-group">
                                    <label for="NewComments">Comments</label>
                                    <input type="text" id="NewComments" class="form-control" maxlength="150" />
                                </div>
                                <div class="form-group">
                                    <label for="NewSampleTakeDate">Sample Take Date</label>
                                    <input class="dateinput form-control" id="NewSampleTakeDate" placeholder="yyyy-mm-dd" />
                                </div>
                                <div class="alert alert-danger" role="alert" id="NewSampleTakeDateError" style="display:none;">
                                    <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
                                    <span class="sr-only">Error:</span> Sample Take Date is a required field!
                                </div>
                                <label for="designselect" style="margin-top:12px;">Design</label><br>
                                <!--select id="designselect" class="form-control"-->
                                <select id="designselect" class="js-example-basic-single" style="width: 450px;"></select>

                                <div class="alert alert-danger" role="alert" id="designselectError" style="display:none;">
                                    <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
                                    <span class="sr-only">Error:</span>
                                    <span class="message"> Design is a required field!</span>
                                </div>
                                <form enctype="text/plain" method="post" action="uploadHandler.php" id="uploadForm" style="margin-top:11px;">
                                    <label for="fileUpload">Select a fastq, bam or vcf file to upload:</label>
                                    <input type="file" name="fileUpload" id="fileUpload" />
                                </form>
                                <div class="progress">
                                    <div class="progress-bar" id="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="min-width: 2em;">0%</div>
                                </div>
                                <div class="alert alert-danger" role="alert" id="NoFileSelected" style="display:none;">
                                    <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
                                    <span class="sr-only">Error:</span> Please select a fastq, bam or vcf file to upload!
                                </div>
                                <div id="patient_consent_div" style="margin-bottom:12px;">
                                    <table>
                                        <tr>
                                            <td style="padding:3px;"><input type="checkbox" class="form-check-input" id="patient_consent"></td>
                                            <td>
                                                I confirm that the patient belonging to this sample has given his informed consent that his/her data will be stored and analyzed on (secure) servers of the University hospital of Muenster.
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                <button id="NewSampleSubmit" type="submit" class="btn btn-default">Submit</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="SampleProcessingModal" tabindex="-1" role="dialog">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                <h4 class="modal-title" id="myModalLabel">Still processing</h4>
                            </div>
                            <div class="modal-body" id="SampleProcessingModalContent">
                                You can not access the sample detail page while it is processing. This progress might take a few hours.
                            </div>
                        </div>
                    </div>
                </div>


                <div class="modal fade" id="RemoveSampleModal" tabindex="-1" role="dialog">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                <h4 class="modal-title" id="myModalLabel">Do you really want to delete this sample?</h4>
                            </div>
                            <div class="modal-body" id="SampleProcessingModalContent">
                                <button type="button" class="btn btn-sm btn-default dropdown-toggle" data-toggle="tooltip" data-placement="top" onClick="removeSample()">Yes!</button>
                                <button type="button" class="btn btn-sm btn-default dropdown-toggle" data-toggle="tooltip" data-placement="top" onClick="$('#RemoveSampleModal').modal('hide')">No!</button>
                            </div>
                        </div>
                    </div>
                </div>


                <?php include_once "common/footer.php";

if(isset($_SESSION['LoggedIn']) && isset($_SESSION['Username']) && $_SESSION['LoggedIn']==1){ ?>


                <script src="common/javascripts/jquery.form.min.js"></script>
                <script type="text/javascript" src="common/javascripts/tablesorter/jquery.tablesorter.js"></script>
                <script type="text/javascript" src="common/javascripts/tablesorter/jquery.tablesorter.widgets.js"></script>
                <link href="common/stylesheets/select2.min.css" rel="stylesheet" />
                <script src="common/javascripts/select2.min.js"></script>

                <link href="common/stylesheets/bootstrap-datepicker3.min.css" rel="stylesheet" />
                <script src="common/javascripts/bootstrap-datepicker.js"></script>

                <script>
                    var pid = '';
                    var tabledata;
                    var patientdata = new Array();
                    var temppatient = new Array();
                    var tempsid;
                    var editmode = false;
                    var version;

                    //save which sample sall be deleted
                    var sampleID;
                    var patientID;

                    $(document).ready(function() {
                        $('#navPatients').addClass('active');

                        $('[data-toggle="tooltip"]').tooltip();

                        $('#patientsinfo').append('<h2>Instructions</h2><p>Below are listed all samples that have been analysed by AMLVaran.</p><p>By clicking on a patient on the <b>left</b>, only samples of the selected patient will be listed.</p><p>Click on a sample <b>below</b> to open the analysis results.</p>');

                        updatePatientsList(null);

                        updateTabledata();

                        prepareDesignlist();

                        $('#designselect').select2({
                            placeholder: "Select a design",
                        });

                    });

                    $("body").on("click patientclicked", ".listitem", function() {
                        $('#errorpanel').hide();

                        if ($(this).hasClass('listitem-clicked')) {
                            $('.listitem-clicked').removeClass('listitem-clicked');
                            pid = '';
                            $('#patientsinfo').empty().append('<h2>Instructions</h2><p>Below are listed all samples that have been analysed by AMLVaran.</p><p>By clicking on a patient on the <b>left</b>, only samples of the selected patient will be listed.</p><p>Click on a sample <b>below</b> to open the analysis results.</p>');

                            <?php if($_SESSION['editPermission'] == 1) { ?>
                            $('#AddSampleButton').attr("data-original-title", "Select a patient first to add a sample");
                            <?php } ?>
                        } else {
                            $('.listitem-clicked').removeClass('listitem-clicked');
                            $(this).addClass('listitem-clicked');
                            pid = $(this).attr("id");

                            <?php if($_SESSION['editPermission'] == 1) { ?>
                            $('#AddSampleButton').on('click', function() {
                                $('#errorpanel').hide();
                                $('#NewSampleModal').modal('show');
                            });
                            $('#AddSampleButton').attr("data-original-title", "Add a new sample");
                            <?php } ?>

                            $.ajax({
                                type: "POST",
                                url: "patientinteraction.php",
                                data: "action=getPatient&pid=" + pid,
                                dataType: 'json',
                                success: function(ret) {
                                    if (ret[0] == 1) {
                                        patientdata = ret[1];
                                        var patientHTML = '<div style="margin-bottom:20px;"><h4 style="float:left;">Patient information</h4>';

                                        <?php if($_SESSION['editPermission'] == 1) { ?>
                                        patientHTML += '<span class="glyphicon glyphicon-pencil glyphsmallbutton" aria-hidden="true" data-toggle="tooltip" data-placement="top" title="Edit patient info" style="margin-left:30px;"></span></div>';
                                        <?php } else { ?>
                                        patientHTML += '<span class="glyphicon glyphicon-pencil glyphsmallbutton glyphbuttondisabled" aria-hidden="true" data-toggle="tooltip" data-placement="top" title="This user cannot edit patients" style="margin-left:30px;"></span></div>';
                                        <?php } ?>

                                        patientHTML += '<table style="font-size:14px; float:left;"><tr><td style="width:170px">Name:</td><td id="Patientname">' + patientdata['Patientname'] + '</td></tr><tr><td>Patient number:</td><td id="Patientnumber">' + patientdata['Patientnumber'] + '</td></tr><tr><td>Birth date:</td><td id="Birthdate">' + patientdata['Birthdate'] + '</td></tr><tr><td>Sex:</td><td id="Sex">' + patientdata['Sex'] + '</td></tr><tr><td>Created:</td><td id="Created">' + patientdata['Created'] + '</td></tr><tr><td>&nbsp;<td></tr><tr><td colspan="2">Click on a sample <b>below</b> to open the analysis results.</td></tr></table>';
                                        patientHTML += '<span class="glyphicon glyphicon-ok glyphsmallbutton" aria-hidden="true" data-toggle="tooltip" data-placement="top" title="Submit"' +
                                            'style="margin-left:30px; margin-top:80px; display:none; color:green;" id="editok"></span><span class="glyphicon glyphicon-remove glyphsmallbutton" aria-hidden="true"' +
                                            'data-toggle="tooltip" data-placement="top" title="Cancel editing" style="margin-left:30px; margin-top:80px; display:none; color:red;" id="editcancel"></span>';
                                        $("#patientsinfo").empty().append(patientHTML);
                                        $('[data-toggle="tooltip"]').tooltip();
                                        editmode = false;
                                    } else {
                                        displayError(ret[1]);
                                    }
                                },
                                error: function(xhr, status, errorThrown) {
                                    displayError(errorThrown);
                                }
                            });
                        }

                        showtable();
                    });

                    <?php if($_SESSION['editPermission'] == 1) { ?>

                    $("#patientslistbuttons").on("click", ".glyphicon", function() {
                        $('#errorpanel').hide();
                        $('#NewPatientModal').modal('show');
                        /*$('.datepicker').datepicker({
                            format: "yyyy-mm-dd",
                            startView: 2
                        });*/
                    });

                    $('.AddPatientButton').on("click", function() {
                        $('#NewPatientModal').modal('show');
                    })

                    $("#NewPatientSubmit").on("click", function() {
                        $('#errorpanel').hide();
                        if ($('#NewPatientname').val().length > 0) {
                            temppatient['Patientname'] = $('#NewPatientname').val();
                            temppatient['Patientnumber'] = $('#NewPatientnumber').val();
                            temppatient['Birthdate'] = $('#NewBirthdate').val();
                            temppatient['Sex'] = ' ';
                            switch ($('#NewSex').val()) {
                                case 'Male':
                                    temppatient['Sex'] = 'M';
                                    break;
                                case 'Female':
                                    temppatient['Sex'] = 'F';
                                    break;
                            }

                            var datecheck = checkDate($('#NewSampleTakeDate').val());
                            if (datecheck[0] == 0) {
                                temppatient['Birthdate'] = " ";
                            }


                            $.ajax({
                                type: 'POST',
                                url: 'patientinteraction.php',
                                data: //JSON.stringify({action: createPatient,pname: temppatient['Patientname'], pnumber: temppatient['Patientnumber'], bd: temppatient['Birthdate'], sex: temppatient['Sex']}),
                                    'action=createPatient&pname=' + temppatient['Patientname'] + '&pnumber=' + temppatient['Patientnumber'] + '&bd=' + temppatient['Birthdate'] + '&sex=' + temppatient['Sex'],
                                dataType: 'json',
                                success: function(ret) {
                                    if (ret[0] == 1) {
                                        $('#NewPatientname').val('');
                                        $('#NewPatientnumber').val('');
                                        $('#NewBirthdate').val('');
                                        $('#NewSex').val('Unknown');
                                        $('#NewPatientnameError').hide();
                                        $('#NewPatientModal').modal('hide');

                                        updatePatientsList(ret[1]);

                                    } else {
                                        displayError(ret[1]);
                                    }
                                },
                                error: function(xhr, status, errorThrown) {
                                    console.log(Response);
                                    displayError(errorThrown); //xhr + " \n" +errorThrown + "\n" + status);
                                }
                            });
                        } else {
                            $('#NewPatientnameError').show();
                        }

                    });



                    $("#NewSampleSubmit").on("click", function() {
                        $('#errorpanel').hide();

                        if ($('#patient_consent:checked').length == 0) {
                            highlight_div('#patient_consent_div');
                            return;
                        }

                        if ($('#NewSampleTakeDate').val().length > 0) {
                            $('#NewSampleTakeDateError').hide();

                            var datecheck = checkDate($('#NewSampleTakeDate').val());
                            if (datecheck[0] != 0) {





                                if ($('#fileUpload').val().length > 0 &&
                                    (((/\.zip$/i).test($('#fileUpload').val())) ||
                                        !((/\.fastq$/i).test($('#fileUpload').val())) ||
                                        !((/\.bam$/i).test($('#fileUpload').val())))) {
                                    $('#NoFileSelected').hide();
                                    if ($('#designselect').val().length != 0) {
                                        $('#designselectError').hide();

                                        var dataarray = [$('#NewDiagnosis').val(), $('#NewComments').val(), $('#NewSampleTakeDate').val(), $('#designselect').val()];

                                        $.ajax({
                                            type: 'POST',
                                            url: 'sampleinteraction.php',
                                            data: 'action=createSample&pid=' + pid + '&dataarray=' + JSON.stringify(dataarray),
                                            dataType: 'json',
                                            success: function(ret) {
                                                if (ret[0] == 1) {
                                                    tempsid = ret[1];
                                                    updateTabledata();
                                                    updatePatientsList(pid);
                                                    $('#uploadForm').submit();
                                                } else {
                                                    displayError(ret[1]);
                                                }
                                            },
                                            error: function(xhr, status, errorThrown) {
                                                displayError(errorThrown);
                                            }
                                        });
                                    } else {
                                        $('#designselectError').show();
                                    }
                                } else {
                                    $('#NoFileSelected').show();
                                }
                            } else {
                                $('#NewSampleTakeDateError').text(" " + datecheck[1]).show();
                            }
                        } else {
                            $('#NewSampleTakeDateError').show();
                        }
                    });

                    $('#uploadForm').submit(function(e) {
                        if ($('#fileUpload').val().length > 0) {
                            e.preventDefault();
                            $(this).ajaxSubmit({
                                data: {
                                    pid: pid,
                                    sid: tempsid
                                },
                                beforeSubmit: function() {
                                    $("#progress-bar").width('0%');
                                },
                                uploadProgress: function(event, position, total, percentComplete) {
                                    $("#progress-bar").width(percentComplete + '%');
                                    $("#progress-bar").html(percentComplete + '%');
                                },
                                success: function(ret) {
                                    $('#NewDiagnosis').val('');
                                    $('#NewComments').val('');
                                    $('#NewSampleTakeDate').val('');
                                    $('#NewSampleTakeDateError').hide();
                                    $('#NoFileSelected').hide();
                                    $('#NewSampleModal').modal('hide');
                                    $("#progress-bar").width('0%');
                                    $("#progress-bar").html('0%');
                                    console.log(ret);
                                    updateTabledata();
                                    prepareDesignlist();
                                },
                                resetForm: true
                            });
                            return false;
                        }
                    });

                    $("#patientsinfo").on("click", ".glyphicon", function() {
                        $('#errorpanel').hide();
                        if ($(this).hasClass('glyphicon-pencil')) {
                            if (!editmode) {
                                $('#Patientname').empty().append('<input id="EditPatientname" type="text" value="' + patientdata['Patientname'] + '" maxlength="50"/>');

                                $('#Patientnumber').empty().append('<input type="text" id="EditPatientnumber" value="' + patientdata['Patientnumber'] + '" maxlength="50"/>');

                                $('#Birthdate').empty().append('<input class="dateinput" id="EditBirthdate" placeholder="yyyy-mm-dd" value="' + patientdata['Birthdate'] + '"/>');

                                if ($('#Sex').html() == 'M') {
                                    $('#Sex').empty().append('<select id="EditSex"><option selected>Male</option><option>Female</option><option>Unknown</option></select>');
                                } else if ($('#Sex').html() == 'F') {
                                    $('#Sex').empty().append('<select id="EditSex"><option>Male</option><option selected>Female</option><option>Unknown</option></select>');
                                } else {
                                    $('#Sex').empty().append('<select id="EditSex"><option>Male</option><option>Female</option><option selected>Unknown</option></select>');
                                }

                                /*$('.datepicker').datepicker({
                                    format: "yyyy-mm-dd",
                                    startView: 2
                                });*/

                                editmode = true;
                                $('#editok').show();
                                $('#editcancel').show();
                            }
                        }
                        if ($(this).hasClass('glyphicon-remove')) {
                            $('#Patientname').empty().append(patientdata['Patientname']);
                            $('#Patientnumber').empty().append(patientdata['Patientnumber']);
                            $('#Birthdate').empty().append(patientdata['Birthdate']);
                            $('#Sex').empty().append(patientdata['Sex']);
                            editmode = false;
                            $('#editok').hide();
                            $('#editcancel').hide();
                        }
                        if ($(this).hasClass('glyphicon-ok')) {
                            temppatient['Patientname'] = $('#EditPatientname').val();
                            temppatient['Patientnumber'] = $('#EditPatientnumber').val();
                            temppatient['Birthdate'] = $('#EditBirthdate').val();
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
                                        if (ret[0] == 1) {
                                            patientdata['Patientname'] = temppatient['Patientname'];
                                            patientdata['Patientnumber'] = temppatient['Patientnumber'];
                                            patientdata['Birthdate'] = temppatient['Birthdate'];
                                            patientdata['Sex'] = temppatient['Sex'];

                                            $('#Patientname').empty().append(patientdata['Patientname']);
                                            $('#Patientnumber').empty().append(patientdata['Patientnumber']);
                                            $('#Birthdate').empty().append(patientdata['Birthdate']);
                                            $('#Sex').empty().append(patientdata['Sex']);

                                            $('.listitem#' + pid)[0].children[0].innerHTML = patientdata['Patientname'];
                                            $('.listitem#' + pid)[0].children[2].innerHTML = 'Birth date: ' + (patientdata['Birthdate'] || '-') + '&nbsp;&nbsp;&nbsp;&nbsp;Patient number: ' + (patientdata['Patientnumber'] || '-');

                                            $.each(tabledata, function(i, item) {
                                                if (item['PatientID'] == pid) item['Patientname'] = patientdata['Patientname'];
                                            });
                                            showtable();

                                            editmode = false;
                                            $('#editok').hide();
                                            $('#editcancel').hide();
                                        } else {
                                            displayError(ret[1]);
                                        }
                                    },
                                    error: function(xhr, status, errorThrown) {
                                        displayError(errorThrown);
                                    }
                                });
                            } else {
                                $('#Patientname').empty().append(patientdata['Patientname']);
                                $('#Patientnumber').empty().append(patientdata['Patientnumber']);
                                $('#Birthdate').empty().append(patientdata['Birthdate']);
                                $('#Sex').empty().append(patientdata['Sex']);
                                editmode = false;
                                $('#editok').hide();
                                $('#editcancel').hide();
                            }
                        }
                    });

                    <?php } ?>

                    $("#sortlist").on("click sortclicked", "li", function() { //NOTE click
                        $('#errorpanel').hide();
                        $("#samplesbutton").empty().append('Sort by: ' + $(this).attr("displayname") + ' <span class="caret"></span>');

                        var attr = $(this).attr("sortby");
                        var divList = $(".listitem");



                        divList.sort(function(a, b) {
                            switch (attr) {
                                case "newestsample":
                                    return $(b).attr(attr).localeCompare($(a).attr(attr));
                                    break;
                                case "created":
                                    return $(b).attr(attr).localeCompare($(a).attr(attr));
                                    break;
                                case "samplecount":
                                    return $(b).attr(attr) - $(a).attr(attr);
                                    break;
                                case "patientname":
                                    return $(a).attr(attr).localeCompare($(b).attr(attr));
                                    break;
                                case "birthdate":
                                    return $(b).attr(attr).localeCompare($(a).attr(attr));
                                    break;
                                case "patientnumber":
                                    if ($(a).attr(attr) == '-' || $(b).attr(attr) == '-') return ($(a).attr(attr) == '-') - ($(b).attr(attr) == '-');
                                    else return $(a).attr(attr).localeCompare($(b).attr(attr));
                                    break;
                            }
                        });
                        $("#patientslist").empty().append(divList);
                    });

                    $("#tablepanel").on("click", "tr", function() {
                        $('#errorpanel').hide();
                        if ($(this).attr("class") == "odd" || $(this).attr("class") == "even") {
                            if ($(this).attr('StateCode') === "100") {
                                window.location.href = 'results.php?pid=' + $(this).attr('PatientID') + '&sid=' + $(this).attr('SampleID');
                            } else {
                                $('#SampleProcessingModal').modal('show');
                            }
                        }
                    });


                    $('.dateinput').on('focusout', function() {
                        var datecheck = checkDate($(this).val());
                        if (datecheck[0] == 1) {
                            $(this).val(datecheck[1]);
                            $('#' + $(this).prop('id') + 'Error').hide();
                            formsvalid = true;
                        } else {
                            $('#' + $(this).prop('id') + 'Error').text(" " + datecheck[1])
                            $('#' + $(this).prop('id') + 'Error').show();
                            formsvalid = false;
                        }
                        if ($(this).val().length === 0) {
                            $('#' + $(this).prop('id') + 'Error').hide();
                        }
                    });







                    function showtable() {
                        var tablehtml = '';
                        if (tabledata.length > 0) {
                            tablehtml +=
                                '<div style="overflow: auto; height:402px;">' +
                                '    <table id="sampletable" class="tablesorter">' +
                                '        <thead>' +
                                '            <tr>';
                            $.each(tabledata[0], function(i, item) {
                                if (i != 'SampleID' && i != 'PatientID' && i != 'Worker') {
                                    if (i === 'StateCode') {
                                        tablehtml += '<th>State</th>';
                                    } else {
                                        tablehtml += '<th>' + i + '</th>';
                                    }
                                }
                            });
                            tablehtml += '<th>Delete</th>' +
                                '            </tr>' +
                                '        </thead>' +
                                '        <tbody>';
                            $.each(tabledata, function(i, item) {
                                if (pid.length == 0 || item['PatientID'] == pid) {
                                    tablehtml += '<tr style="cursor:pointer;" SampleID="' + item['SampleID'] + '" PatientID="' + item['PatientID'] + '" StateCode="' + item['StateCode'] + '">';
                                    $.each(item, function(key, value) {
                                        if (key != 'SampleID' && key != 'PatientID' && key != 'Worker') {
                                            if (key === 'StateCode') {
                                                if (item['Worker'] != null) {
                                                    value = parseInt(value);
                                                    switch (true) {
                                                        case value == 0:
                                                            tablehtml += '<td>No File Uploaded</td>';
                                                            break;
                                                        case value < 10:
                                                            tablehtml += '<td>Preprocessing</td>';
                                                            break;
                                                        case value < 20:
                                                            tablehtml += '<td>Alignment</td>';
                                                            break;
                                                        case value < 30:
                                                            tablehtml += '<td>Coverage calculation</td>';
                                                            break;
                                                        case value < 40:
                                                            tablehtml += '<td>Variant Calling</td>';
                                                            break;
                                                        case value < 50:
                                                            tablehtml += '<td>Integrating caller results</td>';
                                                            break;
                                                        case value < 60:
                                                            tablehtml += '<td>Target Filtering</td>';
                                                            break;
                                                        case value < 80:
                                                            tablehtml += '<td>Postprocessing</td>';
                                                            break;
                                                        case value < 90:
                                                            tablehtml += '<td>Import Coverage</td>';
                                                            break;
                                                        case value == 100:
                                                            tablehtml += '<td>Finished</td>';
                                                            break;
                                                        default:
                                                            tablehtml += '<td>Error</td>';
                                                            break;
                                                    }
                                                } else {
                                                    tablehtml += '<td>Waiting...</td>';
                                                }
                                            } else {
                                                if (value != null) {
                                                    tablehtml += '<td>' + value + '</td>';
                                                } else {
                                                    tablehtml += '<td> </td>';
                                                }
                                            }
                                        }
                                    });
                                    tablehtml += '<td style="text-align: center;vertical-align: middle;"><button type="button" class="removeSampleButton btn btn-xs btn-default dropdown-toggle" data-toggle="tooltip" data-placement="top" data-original-title="Delete this sample" title="">' +
                                        '<span class="glyphicon glyphicon-minus"></span></button></td>';
                                }

                                tablehtml += '</tr>';
                            });
                            tablehtml += '        </tbody>' +
                                '    </table>' +
                                '</div>';

                            $("#tableloader").hide();
                            $("#tablepanel").show();
                            $("#tablepanel").empty().append(tablehtml);

                            $("#sampletable").tablesorter({
                                theme: 'bootstrap',
                                headerTemplate: '{content} {icon}',
                                initWidgets: true,
                                widgets: ['zebra', 'uitheme', 'resizable'],

                                widgetOptions: {
                                    zebra: ['even', 'odd'],
                                    resizable: false
                                }
                            });

                            $('.removeSampleButton').on("click", function(event) {
                                sampleID = $(this).parent().parent().attr('SampleID');
                                patientID = $(this).parent().parent().attr('PatientID');
                                $('#RemoveSampleModal').modal('show');
                                event.stopPropagation();
                            });
                        } else {
                            tablehtml = "There are no records to show";
                            $("#tablepanel").empty().append(tablehtml);
                            $("#tableloader").hide();
                            $("#tablepanel").show();
                        }
                    }

                    function updatePatientsList(clickid) {
                        $.ajax({
                            type: "POST",
                            url: "patientinteraction.php",
                            data: "action=getPatients",
                            dataType: 'json',
                            success: function(ret) {
                                if (ret[0] == 1) {
                                    var patients = ret[1];
                                    var patientsHTML = '';
                                    if (patients.length > 0) {
                                        patientsHTML = '';
                                        $.each(patients, function(i, item) {
                                            patientsHTML += '<div class="listitem" id="' + item['PatientID'] + '" patientname="' + item['Patientname'] + '" birthdate="' + (item['Birthdate'] || '-') + '" patientnumber="' +
                                                (item['Patientnumber'] || '-') + '" created="' + (item['Created'] || '-') + '" samplecount="' + (item['Samplecount'] || '-') + '" newestsample="' +
                                                (item['Newestsample'] || '-') + '"><h4 style="float:left;">' + item['Patientname'] + '</h4><span class="badge" style="background-color:#bbbbbb;float:right;margin-top:20px;">' +
                                                (item['Samplecount'] || '0') + ' Samples</span><p style="margin-top:40px;">Birth date: ' + (item['Birthdate'] || '-') + '&nbsp;&nbsp;&nbsp;&nbsp;Patient number: ' +
                                                (item['Patientnumber'] || '-') + '&nbsp;&nbsp;&nbsp;&nbsp;Sex: ' + (item['Sex'] || '-') + '</p></div>';
                                        });

                                    } else {
                                        patientsHTML = '<p>Sorry, it seems like there are no patients yet.</p>';
                                    }
                                    $("#patientslistloader").hide();
                                    $("#patientslist").empty().append(patientsHTML);
                                    $("#patientslist").show();
                                    // $("#sortlist li").first().trigger("sortclicked"); //auskommentieren, wenn Sortierung am Anfang durch SQL Abfrage vorgegeben wird

                                    if (clickid != null) {
                                        $('#' + clickid).trigger("patientclicked");
                                    }
                                } else {
                                    displayError(ret[1]);
                                }
                            },
                            error: function(xhr, status, errorThrown) {
                                displayError(errorThrown);
                            }
                        });
                    }

                    function updateTabledata() {
                        $.ajax({
                            type: "POST",
                            url: "sampleinteraction.php",
                            data: "action=getSamplesByUid",
                            dataType: 'json',
                            success: function(ret) {
                                if (ret[0] == 1) {
                                    tabledata = ret[1];
                                    showtable();
                                } else {
                                    displayError(ret[1]);
                                }
                            },
                            error: function(xhr, status, errorThrown) {
                                displayError(errorThrown);
                            }
                        });
                    }


                    function removeSample() {

                        console.log("Sample ID: " + sampleID);
                        $.ajax({
                            type: "POST",
                            url: "sampleinteraction.php",
                            data: "action=removeSample&sid=" + sampleID + "&pid=" + patientID,
                            dataType: 'json',
                            success: function(ret) {
                                if (ret[0] == 1) {
                                    console.log("Success");
                                    updateTabledata();
                                    updatePatientsList(pid);
                                } else {
                                    displayError(ret[1]);
                                }
                            },
                            error: function(xhr, status, errorThrown) {
                                displayError(errorThrown);
                            }
                        });
                        $('#RemoveSampleModal').modal('hide');
                    }


                    //copy from uploadSample
                    function prepareDesignlist() {
                        $.ajax({
                            type: "POST",
                            url: "designinteraction.php",
                            data: "action=getDesigns",
                            dataType: 'json',
                            success: function(ret) {
                                if (ret[0] == 1) {
                                    var designs = ret[1];
                                    var designsHTML = '';
                                    if (designs.length > 0) {
                                        designsHTML = '<option></option>';

                                        $.each(designs, function(i, item) {

                                            /*designsHTML += '<option value="' + item['DesignID'] +
                                                '">Sequencer: ' + item['Sequencer'] +
                                                '; Panel: ' + item['Panel'] + '; Technique: ' + item['Technique'] +
                                                '; Remarks: ' + item['Remarks'] + '</option>';*/
                                            designsHTML += '<option value="' + item['DesignID'] +
                                                '">' + item['Panel'] + '</option>';
                                        });
                                        $('#designselect').empty().append(designsHTML);
                                    } else {
                                        $('#designselect').attr('label', 'There are no existing designs yet');
                                    }
                                } else {
                                    displayError(ret[1]);
                                }
                            },
                            error: function(xhr, status, errorThrown) {
                                displayError(errorThrown);
                            }
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
                            return [0, "Invalid Date-Format"];
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


                    function displayError(errorhtml) {

                        try {
                            if (errorhtml.indexOf('log in') >= 0) {
                                $('#errorpanel').empty();
                                window.setTimeout("location.reload()", 0);
                            }
                        } catch (err) {
                            console.log('displayError(): ', err);
                        }
                        $(window).scrollTop(0);


                        $('#errorpanel').show().append('<li style="margin-left: 10px;">' + errorhtml + '</li>');
                        console.log('errorhtml: ', errorhtml);
                    }

                </script>


                <?php } ?>
