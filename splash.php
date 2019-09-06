<?php
require_once('../config.php');

use \Tsugi\Core\LTIX;

$LAUNCH = LTIX::requireData();

if ($USER->instructor) {
    $OUTPUT->splashPage(
        "New UI Examples",
        __("This app is to demo<br />some of the new UI elements."),
        "build.php"
    );
} else {
    $OUTPUT->splashPage(
        "UI Example Student View",
        __("Often students will see something different on the splash page<br/> and only when instructors haven't set it up yet.")
    );
}