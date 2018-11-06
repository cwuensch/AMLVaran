<?php
    include_once "common/base.php";
    $pageTitle = "Upload new Design";
    include_once "common/header.php";

if(isset($_SESSION['LoggedIn']) && isset($_SESSION['Username']) && isset($_SESSION['editPermission']) && $_SESSION['LoggedIn']==1){ ?>

    <div class="container-fluid2">
        <div id="start" class="row row1">
            <div class="col-md-12 column7">
                <div id="errorpanel" class="alert alert-danger" role="alert" style="display:none;">
                    <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
                    <b>Error:</b><br/>
                </div>

                <h2>Upload a new design</h2>
                <?php if($_SESSION['editPermission'] == 1) { ?>

                <div id="designform" style="width:450px;">
                    <div class="form-group">
                        <label for="sequencer" style="margin-top:12px">Sequencer</label>
                        <input type="text" id="sequencer" class="form-control" maxlength="50" />

                        <label for="panel" style="margin-top:12px">Panel</label>
                        <input type="text" id="panel" class="form-control" maxlength="50" />

                        <label for="technique" style="margin-top:12px">Technique</label>
                        <input type="text" id="technique" class="form-control" maxlength="50" />

                        <label for="remarks" style="margin-top:12px">Remarks</label>
                        <input type="text" id="remarks" class="form-control" maxlength="50" />


                        <div class="alert alert-danger" role="alert" id="designFormError" style="display:none;">
                            <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
                            <span class="sr-only">Error:</span> All fields are required!
                        </div>
                    </div>
                    <form id="uploadForm" enctype="text/plain" method="post" action="uploadHandler.php">
                        <label for="fileUpload">Select a file to upload:</label>
                        <div>
                            Please upload your target regions in .bed format using at least 4 columns, with the fourth column containing the gene name
                        </div>
                        <input type="file" name="fileUpload" id="fileUpload" />
                    </form>
                    <div class="progress">
                        <div class="progress-bar" id="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="min-width: 2em;">0%</div>
                    </div>
                    <div class="alert alert-danger" role="alert" id="noFileSelected" style="display:none;">
                        <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
                        <span class="sr-only">Error:</span> Please select a bed txt file to upload!
                    </div>
                    <button id="submit" type="submit" class="btn btn-default">Submit</button>
                </div>


                <?php } else { ?>
                <p>Sorry, the logged in user is not allowed to upload designs.</p>
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

                <script src="common/javascripts/jquery.form.min.js"></script>

                <script>
                    var design = new Array();

                    <?php if($_SESSION['editPermission'] == 1) { ?>

                    $(document).ready(function() {
                        $('#navUploadDesign').addClass('active');
                    });

                    $('#submit').on('click', function() {
                        //formulardaten einholen (damit sie nicht während der verarbeitung verändert werden können)
                        design['Sequencer'] = $('#sequencer').val();
                        design['Panel'] = $('#panel').val();
                        design['Technique'] = $('#technique').val();
                        design['Remarks'] = $('#remarks').val();

                        $('#designFormError').hide();
                        $('#noFileSelected').hide();
                        var formsvalid = true;

                        if (design['Sequencer'].length == 0 || design['Panel'].length == 0 || design['Technique'].length == 0 || design['Remarks'].length == 0) {
                            $('#designFormError').show();
                            formsvalid = false;
                        }

                        if ($('#fileUpload').val().length == 0 || (!((/\.txt$/i).test($('#fileUpload').val())) && !((/\.bed$/i).test($('#fileUpload').val())))) {
                            $('#noFileSelected').show();
                            formsvalid = false;
                        }

                        if (formsvalid) {
                            $('#submit').prop('disabled', true);
                            createDesign();
                        }
                    });

                    $('#uploadForm').submit(function(e) {
                        e.preventDefault();
                        $(this).ajaxSubmit({
                            data: {
                                did: design['DesignID']
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
                                $('#submit').prop('disabled', false);
                            },
                            resetForm: true
                        });
                        return false;
                    });

                    function createDesign() {
                        $.ajax({
                            type: 'POST',
                            url: 'designinteraction.php',
                            data: 'action=createDesign&dataarray=' + JSON.stringify([design['Sequencer'], design['Panel'], design['Technique'], design['Remarks']]),
                            dataType: 'json',
                            success: function(ret) {
                                if (ret[0] == 1) {
                                    design['DesignID'] = ret[1];
                                    console.log('Design created. DesignID: ' + design['DesignID']);
                                    $('#uploadForm').submit();
                                } else {
                                    alert(ret[1]);
                                    $('#submit').prop('disabled', false);
                                }
                            },
                            error: function(xhr, status, errorThrown) {
                                alert(errorThrown);
                                $('#submit').prop('disabled', false);
                            }
                        });
                    }


                    <?php } ?>
                    <?php } ?>

                </script>
