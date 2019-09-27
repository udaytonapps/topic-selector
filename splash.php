<?php
require_once('../config.php');

use \Tsugi\Core\LTIX;

$LAUNCH = LTIX::requireData();

if ($USER->instructor) {
    $LAUNCH->link->settingsSet("has_seen", true);
    $OUTPUT->splashPage(
        "Topic Selector",
        __("This app allows you to create topics<br />that students can then select<br />or you can assign students to."),
        "build.php"
    );
} else {
    $OUTPUT->splashPage(
        "Topic Selector",
        __("Your instructor has not set up this tool yet.")
    );
}