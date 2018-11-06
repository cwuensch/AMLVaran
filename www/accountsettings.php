<?php
    include_once "common/base.php";
    $pageTitle = "Account Settings";
    include_once "common/header.php";

if(isset($_GET['v']) && isset($_GET['e'])) { ?>
    <div class="container-fluid">
        <div class="row row1">
            <div class="col-md-12 column1">
                <div id="Message">

                </div>
                <?php } else {
if(isset($_SESSION['LoggedIn']) && isset($_SESSION['Username']) && $_SESSION['LoggedIn']==1){ ?>

                <div class="container-fluid">
                    <div class="row row1">
                        <div class="col-md-12 column1">
                            <h1>Account Settings</h1>
                            <div id="Message">

                            </div>
                            <div id="UpdateEmailForm">
                                <p class="lead" style="margin: 40px 0px 0px;">If you want to change your Email address please enter your new one in the form below.</p>
                                <div class="form-group" style="max-width: 700px;">
                                    <label for="Email" style="margin-top: 20px;">Email Address</label>
                                    <input type="text" id="Email" class="form-control" maxlength="150" />

                                    <div class="alert alert-danger" role="alert" id="EmailError" style="display:none; margin-top: 10px;">
                                        <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
                                        <span class="sr-only">Error:</span> Please enter a valid Email address.
                                    </div>

                                    <label for="EmailConfirm" style="margin-top: 20px;">Re-Type Email Address</label>
                                    <input type="EmailConfirm" id="EmailConfirm" class="form-control" maxlength="150" />

                                    <div class="alert alert-danger" role="alert" id="EmailConfirmError" style="display:none; margin-top: 10px;">
                                        <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
                                        <span class="sr-only">Error:</span> The re-typed Email Address did not match the previously entered one.
                                    </div>

                                    <button id="UpdateEmailSubmit" type="submit" class="btn btn-default" style="margin-top: 20px;">Update Email Address</button>

                                </div>
                            </div>
                            <div id="UpdatePasswordForm">
                                <p class="lead" style="margin: 40px 0px 0px;">If you want to change your password please enter your new password in the form below and confirm this change with your old password.</p>
                                <div class="form-group" style="max-width: 700px;">

                                    <label for="NewPassword" style="margin-top: 20px;">New Password</label>
                                    <input type="password" id="NewPassword" class="form-control" maxlength="50" />

                                    <div class="alert alert-danger" role="alert" id="PasswordError" style="display:none; margin-top: 10px;">
                                        <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
                                        <span class="sr-only">Error:</span> Please enter a password with a length of at least 6 characters.
                                    </div>

                                    <label for="NewPasswordConfirm" style="margin-top: 20px;">Re-Type Password</label>
                                    <input type="password" id="NewPasswordConfirm" class="form-control" maxlength="50" />

                                    <div class="alert alert-danger" role="alert" id="PasswordConfirmError" style="display:none; margin-top: 10px;">
                                        <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
                                        <span class="sr-only">Error:</span> The re-typed password did not match the previously entered one.
                                    </div>

                                    <label for="Password" style="margin-top: 20px;">Current password</label>
                                    <input type="password" id="Password" class="form-control" maxlength="50" />

                                    <button id="UpdatePasswordSubmit" type="submit" class="btn btn-default" style="margin-top: 20px;">Update Password</button>

                                </div>
                            </div>

                            <?php } else { ?>
                            <div class="container-fluid">
                                <div class="row row1">
                                    <div class="col-md-12 column1">
                                        <h1>Please log in!</h1>
                                        <?php }} ?>

                                    </div>
                                </div>
                            </div>



                            <?php include_once "common/footer.php";
?>


                            <script>
                                <?php if(isset($_GET['v']) && isset($_GET['e'])) {
    unset($_SESSION['LoggedIn']);
    unset($_SESSION['Username']);
    unset($_SESSION['UserID']); ?>

                                $(document).ready(function() {
                                    $.ajax({
                                        type: 'POST',
                                        url: 'userinteraction.php',
                                        data: 'action=validateNewEmail&v=' + '<?php echo $_GET['
                                        v ']; ?>' + '&e=' + '<?php echo $_GET['
                                        e ']; ?>',
                                        dataType: 'json',
                                        success: function(ret) {
                                            if (ret[0] == 1) {
                                                $('#Message').empty();
                                                $('#Message').append(ret[1]);
                                                window.setTimeout('window.location = "login.php"', 5000);
                                            } else {
                                                $('#Message').empty();
                                                $('#Message').append('<div class="alert alert-danger" role="alert" style="margin-top: 10px;"><span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>' + ret[1] + '</div>');
                                            }
                                        },
                                        error: function(xhr, status, errorThrown) {
                                            alert(errorThrown);
                                        }
                                    });
                                });

                                <?php } else { ?>

                                $("#UpdateEmailSubmit").on("click", function() {
                                    var submitData = true;
                                    $('#EmailError').hide();
                                    $('#EmailConfirmError').hide();

                                    if ($('#Email').val().length < 5 || !(validateEmail($('#Email').val()))) {
                                        $('#EmailError').show();
                                        submitData = false;
                                    }

                                    if ($('#Email').val() != $('#EmailConfirm').val()) {
                                        $('#EmailConfirmError').show();
                                        submitData = false;
                                    }

                                    if (submitData) {
                                        $.ajax({
                                            type: 'POST',
                                            url: 'userinteraction.php',
                                            data: 'action=changeEmail&email=' + $('#Email').val(),
                                            dataType: 'json',
                                            success: function(ret) {
                                                if (ret[0] == 1) {
                                                    $('#Message').empty();
                                                    $('#Message').append(ret[1]);
                                                } else {
                                                    $('#Message').empty();
                                                    $('#Message').append('<div class="alert alert-danger" role="alert" style="margin-top: 10px;"><span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>' + ret[1] + '</div>');
                                                }
                                            },
                                            error: function(xhr, status, errorThrown) {
                                                alert(errorThrown);
                                            }
                                        });
                                    }
                                });

                                $("#UpdatePasswordSubmit").on("click", function() {
                                    var submitData = true;
                                    $('#PasswordError').hide();
                                    $('#PasswordConfirmError').hide();

                                    if ($('#NewPassword').val().length < 6) {
                                        $('#PasswordError').show();
                                        submitData = false;
                                    }

                                    if ($('#NewPassword').val() != $('#NewPasswordConfirm').val()) {
                                        $('#PasswordConfirmError').show();
                                        submitData = false;
                                    }

                                    if (submitData) {
                                        $.ajax({
                                            type: 'POST',
                                            url: 'userinteraction.php',
                                            data: 'action=changePassword&newPassword=' + $('#NewPassword').val() + '&password=' + $('#Password').val(),
                                            dataType: 'json',
                                            success: function(ret) {
                                                if (ret[0] == 1) {
                                                    $('#Message').empty();
                                                    $('#Message').append(ret[1]);
                                                } else {
                                                    $('#Message').empty();
                                                    $('#Message').append('<div class="alert alert-danger" role="alert" style="margin-top: 10px;"><span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>' + ret[1] + '</div>');
                                                }
                                            },
                                            error: function(xhr, status, errorThrown) {
                                                alert(errorThrown);
                                            }
                                        });
                                    }
                                });

                                function validateEmail(email) {
                                    var re = /[a-z0-9!#$%&'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?/;
                                    return re.test(email);
                                }

                                <?php } ?>

                            </script>
