<?php
    include_once "common/base.php";
    $pageTitle = "Forgot Password";
    include_once "common/header.php";

if(isset($_SESSION['LoggedIn']) && isset($_SESSION['Username']) && $_SESSION['LoggedIn']==1){ ?>

    <div class="container-fluid">
        <div class="row row1">
            <div class="col-md-12 column1">
                <meta http-equiv='refresh' content='0;accountsettings.php'>

                <?php } else { ?>

                <div class="container-fluid">
                    <div id="about" class="row row1">
                        <div class="col-md-12 column2">
                            <h2>Forgot Password?</h2>

                            <div id="errorpanel" class="alert alert-danger" role="alert" style="display:none;">
                                <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
                                <b>Error:</b><br/>
                            </div>

                            <div id="maincontent">
                                <p>Please enter your Email adress in the form below and our system will send you instructions to set up a new password.</p>
                                <input type="text" id="Email" class="form-control" maxlength="150" />

                                <div class="alert alert-danger" role="alert" id="EmailError" style="display:none; margin-top: 10px;">
                                    <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
                                    <span class="sr-only">Error:</span> Please enter a valid Email address.
                                </div>

                                <div class="alert alert-danger" role="alert" id="notRegistered" style="display:none; margin-top: 10px;">
                                    <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
                                    <span class="sr-only">Error:</span> The Email address you have entered is not registered in our system.
                                </div>

                                <button id="submit" type="submit" class="btn btn-default" style="margin-top: 20px;">Submit</button>
                            </div>


                            <?php } ?>

                        </div>
                    </div>
                </div>

                <?php include_once "common/footer.php"; ?>

                <script>
                    <?php if(!empty($_GET['v']) && !empty($_GET['e'])) { ?>

                    $(document).ready(function() {
                        var pwHTML = '<p>Please choose a new password and press submit.</p>' +
                            '<label for="Password" style="margin-top: 20px;">Password</label>' +
                            '<input type="password" id="Password" class="form-control" maxlength="50"/>'

                            +
                            '<div class="alert alert-danger" role="alert" id="PasswordError" style="display:none; margin-top: 10px;">' +
                            '<span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>' +
                            '<span class="sr-only">Error:</span>' +
                            'Please enter a password with a length of at least 6 characters.' +
                            '</div>'

                            +
                            '<label for="PasswordConfirm" style="margin-top: 20px;">Re-Type Password</label>' +
                            '<input type="password" id="PasswordConfirm" class="form-control" maxlength="50"/>'

                            +
                            '<div class="alert alert-danger" role="alert" id="PasswordConfirmError" style="display:none; margin-top: 10px;">' +
                            '<span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>' +
                            '<span class="sr-only">Error:</span>' +
                            'The re-typed password did not match the previously entered one.' +
                            '</div>'

                            +
                            '<button id="passwordSubmit" type="submit" class="btn btn-default" style="margin-top: 20px;">Submit</button>';

                        $('#maincontent').empty().append(pwHTML);
                    });

                    $('#maincontent').on('click', '#passwordSubmit', function() {
                        var submitData = true;
                        $('#PasswordError').hide();
                        $('#PasswordConfirmError').hide();

                        if ($('#Password').val().length < 6) {
                            $('#PasswordError').show();
                            submitData = false;
                        }

                        if ($('#Password').val() != $('#PasswordConfirm').val()) {
                            $('#PasswordConfirmError').show();
                            submitData = false;
                        }

                        if (submitData) {
                            $.ajax({
                                type: 'POST',
                                url: 'userinteraction.php',
                                data: 'action=updateForgottenPassword&v=<?php echo $_GET['
                                v ']; ?>&e=<?php echo $_GET['
                                e ']; ?>&password=' + $('#Password').val(),
                                dataType: 'json',
                                success: function(ret) {
                                    if (ret[0] == 1) {
                                        $('#maincontent').empty().append('<p>Your password has successfully been changed. You can now log in.</p>');
                                    } else {
                                        displayError(ret[1]);
                                    }
                                },
                                error: function(xhr, status, errorThrown) {
                                    displayError(errorThrown);
                                }
                            });
                        }
                    });

                    <?php } ?>

                    $('#submit').on('click', function() {
                        $('#EmailError').hide();
                        $('#notRegistered').hide();

                        if ($('#Email').val().length < 5 || !(validateEmail($('#Email').val()))) {
                            $('#EmailError').show();
                        } else {
                            var email = $('#Email').val()
                            $.ajax({
                                type: 'POST',
                                url: 'userinteraction.php',
                                data: 'action=checkEmail&email=' + email,
                                dataType: 'json',
                                success: function(ret) {
                                    if (ret[0] == 1) {
                                        if (ret[1] == 1) {
                                            sendPasswordLink(email);
                                        } else {
                                            $('#notRegistered').show();
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
                    });

                    function sendPasswordLink(email) {
                        $.ajax({
                            type: 'POST',
                            url: 'userinteraction.php',
                            data: 'action=sendPasswordLink&email=' + email,
                            dataType: 'json',
                            success: function(ret) {
                                if (ret[0] == 1) {
                                    $('#maincontent').empty().append('<p>We have send you an Email with further instructions. Please check your inbox.</p>');
                                } else {
                                    displayError(ret[1]);
                                }
                            },
                            error: function(xhr, status, errorThrown) {
                                displayError(errorThrown);
                            }
                        });
                    }

                    function validateEmail(email) {
                        var re = /[a-z0-9!#$%&'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?/;
                        return re.test(email);
                    }

                    function displayError(errorhtml) {
                        $('#errorpanel').show().append(errorhtml);
                    }

                </script>
