<?php
    include_once "common/base.php";
    $pageTitle = "Register";
    include_once "common/header.php";

if(isset($_SESSION['LoggedIn']) && isset($_SESSION['Username']) && $_SESSION['LoggedIn']==1){ ?>

    <!--div class="container-fluid">
    <div class="row row1">
      <div class="col-md-12 column1">
            <h1>You are already logged in!</h1>
            <p>To register a new account please <a href="logout.php">log out</a>.</p-->
    <script type="text/javascript">
        window.location = "patients.php";

    </script>

    <?php } else { ?>

    <div class="container-fluid">
        <div class="row row1">
            <div class="col-md-12 column1">
                <h1>Register</h1>
                <div id="Message">

                </div>
                <div id="RegisterForm">
                    <p class="lead">Please enter an Email address and password to register. The entered information will be needed to sign in in order to use the ClinicalVariantReport services.</p>
                    <div class="form-group" style="max-width: 700px;">
                        <label for="Email" style="margin-top: 20px;">Email Address</label>
                        <input type="text" id="Email" class="form-control" maxlength="150" />

                        <div class="alert alert-danger" role="alert" id="EmailError" style="display:none; margin-top: 10px;">
                            <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
                            <span class="sr-only">Error:</span> Please enter a valid Email address.
                        </div>

                        <label for="Password" style="margin-top: 20px;">Password</label>
                        <input type="password" id="Password" class="form-control" maxlength="50" />

                        <div class="alert alert-danger" role="alert" id="PasswordError" style="display:none; margin-top: 10px;">
                            <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
                            <span class="sr-only">Error:</span> Please enter a password with a length of at least 6 characters.
                        </div>

                        <label for="PasswordConfirm" style="margin-top: 20px;">Re-Type Password</label>
                        <input type="password" id="PasswordConfirm" class="form-control" maxlength="50" />

                        <div class="alert alert-danger" role="alert" id="PasswordConfirmError" style="display:none; margin-top: 10px;">
                            <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
                            <span class="sr-only">Error:</span> The re-typed password did not match the previously entered one.
                        </div>

                        <button id="RegisterSubmit" type="submit" class="btn btn-default" style="margin-top: 20px;">Submit</button>
                    </div>
                </div>

                <?php } ?>

            </div>
        </div>
    </div>

    <?php include_once "common/footer.php";
?>


    <script>
        $("#RegisterSubmit").on("click", function() {
            var submitData = true;
            $('#EmailError').hide();
            $('#PasswordError').hide();
            $('#PasswordConfirmError').hide();

            if ($('#Email').val().length < 5 || !(validateEmail($('#Email').val()))) {
                $('#EmailError').show();
                submitData = false;
            }

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
                    data: 'action=createAccount&email=' + $('#Email').val() + '&password=' + $('#Password').val(),
                    dataType: 'json',
                    success: function(ret) {
                        if (ret[0] == 1) {
                            $('#RegisterForm').hide();
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

    </script>
