<?php
require_once "../config.php";

use \Tsugi\Core\LTIX;

$LAUNCH = LTIX::requireData();

if ( $USER->instructor ) {
    header( 'Location: '.addSession('build.php') ) ;

} else { // student
    header( 'Location: '.addSession('student-home.php') ) ;
}