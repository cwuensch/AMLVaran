<?php
    include_once "common/base.php";
    $pageTitle = "Upload Sample";
    include_once "common/header.php";

if(isset($_SESSION['LoggedIn']) && isset($_SESSION['Username']) && isset($_SESSION['editPermission']) && $_SESSION['LoggedIn']==1){ ?>

    <div class="container-fluid2">
        <div id="start" class="row row1">
            <div class="col-md-12 column7">
                <div id="errorpanel" class="alert alert-danger" role="alert" style="display:none;">
                    <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
                    <b>Error:</b><br/>
                </div>

                <h2>Upload a new sample</h2>
                <?php if($_SESSION['editPermission'] == 1) { ?>
                <p>Does the sample belong to an existing patient or a new one?</p>

                <div id="patientloader" style="width: 552px;"><img src="common/ajax-loader.gif" style="display:block; margin:auto;" /></div>
                <div id="patientselectpanel" style="display: none;">
                    <select id="patientselect" multiple="multiple" style="width: 400px;">
                    <optgroup id="existingpatients" label="Existing Patients">

                    </optgroup>
                </select>
                    <button type="button" class="btn btn-default" id="createnew">
                    Create New Patient
                </button>
                </div>
                <div class="alert alert-danger" role="alert" id="patientNameError" style="display:none;">
                    <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
                    <span class="sr-only">Error:</span> Enter a patient name to create a new patient.
                </div>
                <div id="patientformloader" style="width: 552px; display: none;"><img src="common/ajax-loader.gif" style="display:block; margin:auto;" /></div>
                <div id="patientform" style="width:450px; display:none;">
                    <div class="form-group">
                        <label for="patientnumber" style="margin-top:12px">Patient Number</label>
                        <input type="text" id="patientnumber" class="form-control" maxlength="50" />

                        <label for="birthdate" style="margin-top:12px">Birth Date</label>
                        <!--input class="datepicker form-control" id="birthdate" placeholder="Required Format: yyyy-mm-dd"/-->
                        <input type="text" id="birthdate" class="form-control dateinput" maxlength="50" placeholder="yyyy-mm-dd" />

                        <div class="alert alert-danger" role="alert" id="birthdateError" style="display:none;">
                            <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
                            <span class="sr-only">Error:</span>
                            <span class="message"> Sample Take Date is a required field!</span>
                        </div>

                        <label for="sex" style="margin-top:12px">Sex</label>
                        <select id="sex" class="form-control">
                        <option value="" selected >Unknown</option>
                        <option value="M">Male</option>
                        <option value="F">Female</option>
                    </select>
                    </div>
                </div>
                <div id="sampleform" style="width:450px; display:none;">
                    <div class="form-group">
                        <label for="diagnosis" style="margin-top:12px">Diagnosis</label>
                        <input type="text" id="diagnosis" class="form-control" maxlength="50" />

                        <label for="comments" style="margin-top:12px">Comments</label>
                        <input type="text" id="comments" class="form-control" maxlength="150" />

                        <label for="sampleTakeDate" style="margin-top:12px">Sample Take Date</label>
                        <input type="text" id="sampleTakeDate" class="form-control dateinput" maxlength="50" placeholder="yyyy-mm-dd" />
                        <!--input type="text" class="datepicker form-control" id="sampleTakeDate" placeholder="Required Format: yyyy-mm-dd"/-->

                        <div class="alert alert-danger" role="alert" id="sampleTakeDateError" style="display:none;">
                            <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
                            <span class="sr-only">Error:</span>
                            <span class="message"> Sample Take Date is a required field!</span>
                        </div>

                        <label for="designselect" style="margin-top:12px">Design</label>
                        <!--select id="designselect" class="form-control"-->
                        <select id="designselect" class="js-example-basic-single" style="width: 450px;"></select>

                        <div class="alert alert-danger" role="alert" id="designselectError" style="display:none;">
                            <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
                            <span class="sr-only">Error:</span>
                            <span class="message"> Design is a required field!</span>
                        </div>

                    </div>
                    <form id="uploadForm" enctype="text/plain" method="post" action="uploadHandler.php">
                        <label for="fileUpload">Select a Fastq, Bam or Vcf file to upload:</label>
                        <input type="file" name="fileUpload" id="fileUpload" />
                    </form>
                    <div class="progress">
                        <div class="progress-bar" id="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="min-width: 2em;">0%</div>
                    </div>

                    <div class="alert alert-danger" role="alert" id="noFileSelected" style="display:none;">
                        <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
                        <span class="sr-only">Error:</span> Please select a fastq, bam or vcf file to upload!
                    </div>
                    <div id="patient_consent_div">
                        <table>
                            <tr>
                                <td style="padding:3px;"><input type="checkbox" class="form-check-input" id="patient_consent"></td>
                                <td>
                                    I confirm that the patient belonging to this sample has given his informed consent that his/her data will be stored and analyzed on (secure) servers of the University hospital of Muenster.
                                </td>
                            </tr>
                        </table>
                    </div>
                    <button id="submit" type="submit" class="btn btn-default">Submit</button>
                </div>


                <?php } else { ?>
                <p>Sorry, the logged in user is not allowed to upload samples.</p>
                <?php }
} else { ?>

                <div class="container-fluid">
                    <div id="start" class="row row1">
                        <div class="col-md-12 column1">
                            <h1>Please log in!</h1>

                            <?php } ?>

                        </div>
                    </div>
                </div>


                <?php include_once "common/footer.php";

if(isset($_SESSION['LoggedIn']) && isset($_SESSION['Username']) && $_SESSION['LoggedIn']==1){ ?>

                <link href="common/stylesheets/bootstrap-datepicker3.min.css" rel="stylesheet" />
                <link href="common/stylesheets/select2.min.css" rel="stylesheet" />
                <script src="common/javascripts/bootstrap-datepicker.js"></script>
                <script src="common/javascripts/jquery.form.min.js"></script>
                <script src="common/javascripts/select2.min.js"></script>


                <script>
                    var tempname = '';
                    var newpatient = false;
                    var patient = new Array();
                    var sample = new Array();

                    <?php if($_SESSION['editPermission'] == 1) { ?>

                    $(document).ready(function() {
                        $('#navUploadSample').addClass('active');

                        $('#patientselect').select2({
                            placeholder: "Select a patient",
                            maximumSelectionLength: 1,
                            maximumInputLength: 100,
                            "language": {
                                "noResults": function() {
                                    return "No existing Patient found";
                                }
                            }
                        });


                        prepareDesignlist()

                        $('#designselect').select2({
                            placeholder: "Select a design",
                        });

                        $('#createnew').prop('disabled', false);
                        preparePatientslist();
                    });

                    $('body').on('input', '.select2-search__field', function(e) {
                        tempname = $('.select2-search__field').val();
                    });

                    $('#createnew').on('click', function() {
                        //Routine wird nur bei neuem Patienten ausgeführt
                        if (tempname.length > 0) {
                            $('#createnew').prop('disabled', true);
                            $('#patientselect').append('<option value="new" id="newpatientoption">' + tempname + '</option>');
                            $('#patientselect').val('new');
                            $('#patientselect').trigger('change');

                            patient['Patientname'] = tempname;
                            newpatient = true;
                            $('#patientNameError').hide();
                            showForms();
                        } else {
                            $('#patientNameError').show();
                        }
                    });

                    $('#patientselect').on('select2:unselecting', function(e) {
                        hideForms();
                    });

                    $('#patientselect').on('select2:select', function(e) {
                        //Routine wird nur bei existierenden Patienten ausgeführt
                        $('#patientNameError').hide();
                        $('#patientformloader').show();

                        patient['PatientID'] = $('#patientselect').val()[0];
                        patient['Patientname'] = $('#patientselect option[value=' + patient['PatientID'] + ']').html();

                        $.ajax({
                            type: "POST",
                            url: "patientinteraction.php",
                            data: "action=getPatient&pid=" + patient['PatientID'],
                            dataType: 'json',
                            success: function(ret) {
                                if (ret[0] == 1) {
                                    patient['Patientnumber'] = ret[1]['Patientnumber'];
                                    patient['Birthdate'] = ret[1]['Birthdate'];
                                    patient['Sex'] = ret[1]['Sex'];

                                    newpatient = false;
                                    showForms();
                                } else {
                                    displayError(ret[1]);
                                }
                            },
                            error: function(xhr, status, errorThrown) {
                                displayError(errorThrown);
                            }
                        });
                    });

                    $('#patientpanel').on('click patientclicked', 'li', function() {
                        $('#patientsbutton').empty().append($(this).attr('Patientname') + '<span class="caret"></span>');
                    });

                    $('.dateinput').on('focusout', function() {
                        var datecheck = checkDate($(this).val());
                        if (datecheck[0] == 1) {
                            $(this).val(datecheck[1]);
                            $('#' + $(this).prop('id') + 'Error').hide();
                            formsvalid = true;
                        } else {
                            $('#' + $(this).prop('id') + 'Error > .message').text(" " + datecheck[1])
                            $('#' + $(this).prop('id') + 'Error').show();
                            formsvalid = false;
                        }
                        if ($(this).val().length === 0) {
                            $('#' + $(this).prop('id') + 'Error').hide();
                        }
                    });

                    $('#submit').on('click', function() {
                        if ($('#patient_consent:checked').length == 0) {
                            highlight_div('#patient_consent_div');
                            return;
                        }
                        //formulardaten einholen (damit sie nicht während der verarbeitung verändert werden können)
                        patient['Patientnumber'] = $('#patientnumber').val();
                        patient['Birthdate'] = $('#birthdate').val();
                        patient['Sex'] = $('#sex').val();

                        sample['Diagnosis'] = $('#diagnosis').val();
                        sample['Comments'] = $('#comments').val();
                        sample['SampleTakeDate'] = $('#sampleTakeDate').val();
                        sample['DesignID'] = $('#designselect').val();

                        $('#sampleTakeDateError').hide();
                        $('#noFileSelected').hide();
                        $('#birthdateError').hide();
                        $('#designselectError').hide();
                        var formsvalid = true;

                        if (sample['DesignID'].length == 0) {
                            $('#designselectError').show();
                            formsvalid = false;
                        }

                        var datecheck = checkDate(sample['SampleTakeDate']);
                        if (datecheck[0] == 0) {
                            $('#sampleTakeDateError > .message').text(" " + datecheck[1])
                            $('#sampleTakeDateError').show();
                            formsvalid = false;
                        }
                        if (patient['Birthdate'].length > 0) {
                            datecheck = checkDate(patient['Birthdate']);
                            if (datecheck[0] == 0) {
                                $('#birthdateError > .message').text(" " + datecheck[1])
                                $('#birthdateError').show();
                                formsvalid = false;
                            }
                        }

                        if (sample['SampleTakeDate'].length == 0) {
                            $('#sampleTakeDateError > .message').text(" Sample Take Date is a required field!")
                            $('#sampleTakeDateError').show();
                            formsvalid = false;
                        }

                        if ($('#fileUpload').val().length == 0 ||
                            (!((/\.vcf$/i).test($('#fileUpload').val())) &&
                                !((/\.fastq$/i).test($('#fileUpload').val())) &&
                                !((/\.bam$/i).test($('#fileUpload').val())))) {
                            $('#noFileSelected').show();
                            formsvalid = false;
                        }

                        if (formsvalid) $('#submit').prop('disabled', true);

                        if (newpatient) {
                            if (formsvalid) createPatient();
                        } else {
                            if (formsvalid) createSample();
                        }
                    });

                    $('#uploadForm').submit(function(e) {
                        e.preventDefault();
                        $(this).ajaxSubmit({
                            data: {
                                pid: patient['PatientID'],
                                sid: sample['SampleID']
                            },
                            beforeSubmit: function() {
                                $("#progress-bar").width('0%');
                                console.log('Upload started');
                            },
                            uploadProgress: function(event, position, total, percentComplete) {
                                $("#progress-bar").width(percentComplete + '%');
                                $("#progress-bar").html(percentComplete + '%');
                            },
                            success: function(ret) {
                                console.log(ret);
                                window.location.replace('patients.php');
                                
                            },
                            resetForm: true
                        });
                        return false;
                    });

                    function showForms() {
                        $('#patientformloader').hide();
                        $('#sampleTakeDateError').hide();
                        $('#noFileSelected').hide();

                        if (newpatient) {
                            $('#patientnumber').val('').prop('disabled', false);
                            $('#birthdate').val('').prop('disabled', false);
                            $('#sex').val('').prop('disabled', false);
                            $('#patientform').css('opacity', '0').fadeTo(300, 1, 'swing');
                        } else {
                            $('#patientnumber').val(patient['Patientnumber']).prop('disabled', true);
                            $('#birthdate').val(patient['Birthdate']).prop('disabled', true);
                            $('#sex').val(patient['Sex']).prop('disabled', true);
                            $('#patientform').css('opacity', '0').fadeTo(300, 1, 'swing');
                        }

                        $('#diagnosis').val('').prop('disabled', false);
                        $('#comments').val('').prop('disabled', false);
                        $('#sampleTakeDate').val('').prop('disabled', false);
                        $('#fileUpload').val('').prop('disabled', false);
                        $('#sampleform').css('opacity', '0').fadeTo(300, 0, 'swing', function() {
                            $(this).fadeTo(400, 1, 'swing');
                        });

                        $('.datepicker').datepicker({
                            format: "yyyy-mm-dd",
                            startView: 2
                        });
                    }

                    function hideForms() {
                        $('#newpatientoption').remove();
                        $('#patientselect').trigger('change');
                        $('#createnew').prop('disabled', false);
                        $('#patientform').css('opacity', '1').fadeTo(300, 0, 'swing');
                        $('#sampleform').css('opacity', '1').fadeTo(300, 0, 'swing');
                    }

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

                    function preparePatientslist() {
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
                                            patientsHTML += '<option value="' + item['PatientID'] + '">' + item['Patientname'] + '</option>';
                                        });

                                        $('#existingpatients').empty().append(patientsHTML);

                                    } else {
                                        $('#existingpatients').attr('label', 'There are no existing patients yet');
                                    }

                                    $('#patientloader').hide();
                                    $('#patientselectpanel').css('opacity', '0').fadeTo(300, 1, 'swing');
                                } else {
                                    displayError(ret[1]);
                                }
                            },
                            error: function(xhr, status, errorThrown) {
                                displayError(errorThrown);
                            }
                        });
                    }

                    function createPatient() {
                        if (patient['Birthdate'] == null || patient['Birthdate'].length == 0) {
                            patient['Birthdate'] = null;
                        }
                        if (patient['Patientnumber'] == null || patient['Patientnumber'].length == 0) {
                            patient['Patientnumber'] = null;
                        }
                        if (patient['Sex'] == null || patient['Sex'].length == 0) {
                            patient['Sex'] = " ";
                        }
                        $.ajax({
                            type: 'POST',
                            url: 'patientinteraction.php',
                            data: 'action=createPatient&pname=' + patient['Patientname'] + '&pnumber=' + patient['Patientnumber'] + '&bd=' + patient['Birthdate'] + '&sex=' + patient['Sex'],
                            dataType: 'json',
                            success: function(ret) {
                                if (ret[0] == 1) {
                                    patient['PatientID'] = ret[1];
                                    console.log('Patient created. PatientID: ' + patient['PatientID']);
                                    createSample();
                                } else {
                                    alert(ret[1]);
                                }
                            },
                            error: function(xhr, status, errorThrown) {
                                console.warn(xhr.responseText);
                                alert(errorThrown + "e");
                            }
                        });
                    }

                    function createSample() {
                        $.ajax({
                            type: 'POST',
                            url: 'sampleinteraction.php',
                            data: 'action=createSample&pid=' + patient['PatientID'] + '&dataarray=' + JSON.stringify([sample['Diagnosis'], sample['Comments'], sample['SampleTakeDate'], sample['DesignID']]),
                            dataType: 'json',
                            success: function(ret) {
                                if (ret[0] == 1) {
                                    sample['SampleID'] = ret[1];
                                    console.log('Sample created. SampleID: ' + sample['SampleID']);
                                    $('#uploadForm').submit();
                                } else {
                                    alert(ret[1]);
                                }
                            },
                            error: function(xhr, status, errorThrown) {
                                alert(errorThrown);
                            }
                        });
                    }

                    /**
                     * Checks if the date format is correct.
                     * Day between 1 and 31; Month between 1 and 12; Year > 1900
                     * Accepted Date-Formats: dd.mm.yyyy; yyyy-mm-dd; mm/dd/yyyy
                     *
                     * @param object $db
                     * @return  An array containing a status code and the iso-date-format or an error message
                     */
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

                    <?php } ?>

                    function displayError(errorhtml) {
                        if (errorhtml.includes('log in')) {
                            $('#errorpanel').empty();
                            window.setTimeout("location.reload()", 0);
                        }
                        $(window).scrollTop(0);
                        $('#errorpanel').empty().show().append(errorhtml);
                    }

                    function isIE() {
                        var myNav = navigator.userAgent.toLowerCase();
                        return (myNav.indexOf('msie') != -1) ? parseInt(myNav.split('msie')[1]) : false;
                    }

                    function highlight_div(selector) {
                        $(selector).css('backgroundColor', '#FF9999')[0].scrollIntoView();
                        $(selector).animate({
                            'opacity': '0.6'
                        }, 1000, function() {
                            $(this).css({
                                'backgroundColor': '',
                                'opacity': '1'
                            });
                        });
                    }

                </script>


                <?php } ?>
