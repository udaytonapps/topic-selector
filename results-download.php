<?php

require_once('../config.php');

use \Tsugi\Core\LTIX;
use \QW\DAO\QW_DAO;

// Retrieve the launch data if present
$LAUNCH = LTIX::requireData();

$p = $CFG->dbprefix;

include("menu.php");

// Start of the output
$OUTPUT->header();

$OUTPUT->bodyStart();

$OUTPUT->topNav($menu);

echo '<div class="container-fluid">';

$OUTPUT->flashMessages();

$OUTPUT->pageTitle("Results <small>- Download</small>", true, false);

?>
    <p class="lead">Click on the link below to download the student results.</p>
    <h4>
        <a href="actions/ExportToFile.php">
            <span class="fa fa-download" aria-hidden="true"></span> TopicSelector-<?=$CONTEXT->title?>-Results.xls
        </a>
    </h4>
<?php

echo '</div>';

$OUTPUT->helpModal("Quick Write Help", __('
                        <h4>Downloading Results</h4>
                        <p>Click on the link to download an Excel file with all of the results for this Topic Selector.</p>'));

$OUTPUT->footerStart();

$OUTPUT->footerEnd();
