<?php
require_once "../config.php";

use \Tsugi\Core\LTIX;

$LAUNCH = LTIX::requireData();

if ( $USER->instructor ) {
    if(isset($_GET['top'])) {
        header('Location: ' . addSession('results-assignments.php'));
    } else {
        header('Location: ' . addSession('build.php'));
    }

} else { // student
    header( 'Location: '.addSession('student-home.php') ) ;
}