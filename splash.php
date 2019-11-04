<?php
require_once('../config.php');

use \Tsugi\Core\LTIX;

$LAUNCH = LTIX::requireData();

if ($USER->instructor) {
    $OUTPUT->splashPage(
        "Topic Selector",
        __("This app allows instructors to create topics or groups<br />that students can then select or be assigned to."),
        "actions/MarkSeenGoToBuild.php"
    );
} else {
    $OUTPUT->splashPage(
        "Topic Selector",
        __("Your instructor has not set up this tool yet.")
    );
}