<?php
    include_once "common/base.php";
    $pageTitle = "Home";
    include_once "common/header.php";

if(isset($_SESSION['LoggedIn']) && isset($_SESSION['Username']) && $_SESSION['LoggedIn']==1) { ?>


    <script type="text/javascript">
        //Show index.php only the first time.
        window.location = "patients.php";

    </script>

    <?php } else { ?>

    <div class="container-fluid">
        <div id="start" class="row row1">
            <div class="col-md-12 column1">
            <h2>Welcome to AMLVaran...</h2>
                <h3 style="color:red">You are not logged in!</h3>
                <h3>About AMLVaran</h3>
                <p> AMLVaran, the AML <i>Var</i>iant <i>An</i>alyzer is a web-based software platform for variant analysis on targeted NGS data, addressing the requirements of a clinical setting.<br> This platform covers the complete workflow from raw sequencing data to interactive clinical reports.<br> It provides a flexible, modular analysis pipeline that can combine an arbitrary number of variant calling tools, and offers a generic model for variant filtering through a customizable scoring scheme.<br> AMLVaran includes a user-friendly interface that presents results in form of a structured clinical report with interactive features, which support further research. Furthermore, comprehensive curated data on therapy-relevant hotspot regions are incorporated, and presence, absence, or coverage of known driver mutations related to a chosen disease entity is provided.<br> AMLVaran was tested for use with AML data, but is intended as a generic system adaptable to other cancer types. Since the software is designed for clinical application, AMLVaran's focus is the generation of accurate and reliable results. </p>
                <h3>Notice</h3>
                <p> <b>This software is intended for RESEARCH USE only!</b><br> The software components need to be adapted to local reqiurements.<br> Especially the variant calling parameters are to be adapted for the type of data to be used.<br> Importantly, the system needs to be assembled and validated locally before use!<br> This code is provided 'AS IS' and any express or implied warranties, including, but not limited to, the implied warranties of merchantability and fitness for a particular purpose are disclaimed. </p>
                <h3>Documentation</h3>
                <p> This server is meant to give you a showcase of all the functions of AMLVaran. Some demo accounts with published data are availabe. <br>
                Please have a look at our <a href="doc/QuickStart.pdf">Quick Start Guide</a>, in order to get familiar with the functions of the software.</p>
                <h3>Source Code</h3>
                <p> The source code is provided via GitHub at <a href="https://github.com/cwuensch/AMLVaran">https://github.com/cwuensch/AMLVaran</a>.</p>
                <h3>Citation</h3>
                <p> Our manuscript is currently under review to be published. As soon as the manuscript is accepted, the GitHub repository will be turned public.</p>
            </div>
            </div>
        </div>
    </div>

    <?php } ?>

    <?php include_once "common/footer.php"; ?>
