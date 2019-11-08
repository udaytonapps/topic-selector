<?php
require_once "../config.php";

use \Tsugi\Core\LTIX;

$LAUNCH = LTIX::requireData();

$has_seen = $LAUNCH->link->settingsGet("has_seen", false);

if ($has_seen == NULL) {
    $LAUNCH->link->settingsSet("has_seen", false);
    $has_seen = $LAUNCH->link->has_seen;
}

if ( $USER->instructor ) {
    if($has_seen == true) {
        if(isset($_GET['top'])) {
            header('Location: ' . addSession('results-assignments.php'));
        } else {
            header('Location: ' . addSession('build.php'));
        }
    } else {
        header('Location: ' . addSession('splash.php'));
    }

} else { // student
    header( 'Location: '.addSession('student-home.php') ) ;
}