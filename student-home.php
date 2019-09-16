<?php
require_once "../config.php";

use \Tsugi\Core\LTIX;

$LAUNCH = LTIX::requireData();

$title = $LAUNCH->link->settingsGet("title", $LAUNCH->link->title);

include("menu.php");

$OUTPUT->header();

$OUTPUT->bodyStart();

$OUTPUT->topNav($menu);

echo '<div class="container-fluid">';

$OUTPUT->flashMessages();

$OUTPUT->pageTitle($title, true, false);

echo '<p class="lead">Student view page that\'s also used for instructor preview.</p>';

if (!$USER->instructor) {
    echo '<p>View the student version of the splash page (you must reset the tool to get back here). <a href="splash.php">Go to Splash Page</a>';
}

echo '</div>';// end container

if ($USER->instructor) {
    $OUTPUT->helpModal("Instructor Help", '
                            <p>This help will show when an instructor is previewing student view.</p>');
} else {
    $OUTPUT->helpModal("Student Help", '
                            <p>This is the help that students will see.</p>');
}
$OUTPUT->footerStart();

$OUTPUT->footerEnd();