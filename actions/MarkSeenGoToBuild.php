<?php
require_once "../../config.php";

use \Tsugi\Core\LTIX;

// Retrieve the launch data if present
$LAUNCH = LTIX::requireData();

$p = $CFG->dbprefix;

if ( $USER->instructor ) {

    $LAUNCH->link->settingsSet("has_seen", true);

    header( 'Location: '.addSession('../build.php') ) ;
} else {
    header( 'Location: '.addSession('../student-home.php') ) ;
}
