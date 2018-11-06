<!DOCTYPE HTML>
<head>
  <meta charset="utf-8"/>
  <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags-->
  <title>AML VARAN | <?php echo $pageTitle ?></title>
  <!-- Bootstrap-->
  <link href="common/stylesheets/bootstrap.min.css" rel="stylesheet"/>
  <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries-->
  <!-- WARNING: Respond.js doesn't work if you view the page via file://-->
  <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
  <![endif]-->
  <!-- Custom styles for this template-->
  <link href="common/stylesheets/tablesorter/theme.bootstrap.css" rel="stylesheet"/>
  <link href="common/stylesheets/cvrstylesheet.css" rel="stylesheet"/>
  <link rel="shortcut icon" type="image/x-icon" href="common/favicon.ico">
  <!--link href='https://fonts.googleapis.com/css?family=Noto+Sans:400,400italic,700,700italic' rel='stylesheet' type='text/css'-->
</head>
<body>
    <div id="grey_background" class="grey_background"></div>
  <nav class="navbar navbar-default navbar-static-top">
    <div class="container-fluid">
      <div class="navbar-header" style="padding-left: 50px; padding-right: 30px;">
        <button type="button" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar" class="navbar-toggle collapsed">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span></button>
        <a href="index.php"><img alt="Brand" src="common/logo2klein.png"/></a><a href="index.php" class="navbar-brand" style="padding-right: 0px;">AML VARAN</a>
      </div>
      <div id="navbar" class="navbar-collapse collapse">
        

        <?php   if(isset($_SESSION['LoggedIn']) && isset($_SESSION['Username']) && $_SESSION['LoggedIn']==1): ?>
          <ul class="nav navbar-nav">
            <li id="navUploadDesign"><a href="uploadDesign.php">Manage Designs</a></li>
            <li id="navUploadSample"><a href="uploadSample.php">Upload Sample</a></li>
            <li id="navPatients"><a href="patients.php">View Results</a></li>
            <li id="navShareData"><a href="#">Share Data</a></li>
        </ul>
        <ul class="nav navbar-nav navbar-right">
            <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false" style="font-size:10px; color:#777;">Logged in as <?php echo $_SESSION['Username'] ?><span class="caret"></span></a>
                <ul class="dropdown-menu">
                    <li> <a href="accountsettings.php">Account settings</a></li>
                    <li> <a href="logout.php">Logout</a></li>
                </ul>
            </li>

        </ul>
        <?php else: ?>
            <ul class="nav navbar-nav navbar-right">
              <li><a href="register.php">Register  </a></li>
              <li class="dropdown"><a href="#" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false" class="dropdown-toggle">Log  In <span class="caret">         </span></a>
                <ul style="padding: 15px; padding-bottom: 0px;" class="dropdown-menu">
                  <form action="login.php" method="post" accept-charset="UTF-8">
                    <input id="email" style="margin-bottom: 15px;" type="text" name="email" size="30" placeholder="Email" maxlength="50"/>
                    <input id="password" style="margin-bottom: 15px;" type="password" name="password" size="30" placeholder="Password" maxlength="50"/>
                    <input style="clear: left; width: 100%; height: 32px; font-size: 13px;" type="submit" value="Log  In" class="btn btn-default"/>
                    <a href="password.php">Forgot Password?</a>
                  </form>
                </ul>
              </li>
            </ul>
        <?php endif; ?>
      </div>
    </div>
  </nav>
