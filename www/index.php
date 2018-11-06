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
                <h3>Background</h3>
                <p>Next-Generation Sequencing (NGS) enables the large-scale and very cost-effective sequencing of genetic samples for the detection of mutations. Already used in research worldwide, it is now entering clinical practice in order to better understand the prognosis of a disease by means of personalized medicine and to be able to optimize the type of treatment in a targeted manner. <br>
                However, the analysis of NGS data usually requires a number of complex bioinformatic processing steps and the resulting mutation lists need to be interpreted by experts.</p>
                <h3>Implementation</h3>
                <p> With AMLVaran, we present a web-based software, that covers the complete analysis process of a targeted NGS sample, that is flexibly customizable both in the choice of variant calling tools and in the score calculation algorithm for filtering the variant lists, and that provides its results in form of an interactive website, equipped with comprehensive annotation data, as well as stored curated information on hotspot regions and known driver mutations. A clear clinical report is also given out, including even rule-based diagnostic recommendations.</p>
                <h3>Documentation</h3>
                <p> This server is meant to give you a showcase of all the functions of AMLVaran. Some demo accounts with published data are availabe. <br>
                Please have a look at our Quick Start Guide, in order to get familiar with the functions of the software.</p>
                <h3>Source Code</h3>
                <p> The source code is provided via GitHub at...</p>
                <h3>Citation</h3>
                <p> Our manuscript is currently under review to be published. If you use the tool for doing your own analyses, please cite ...</p>
            </div>
            </div>
        </div>
    </div>

    <?php } ?>

    <?php include_once "common/footer.php"; ?>
