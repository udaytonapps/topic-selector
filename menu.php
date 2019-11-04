<?php
if ($USER->instructor) {
    $menu = new \Tsugi\UI\MenuSet();

    $menu->setHome('Topic Selector', 'index.php');

    $countSelections = $TS_DAO->getTotalReserved($LINK->id);

    if ('student-home.php' != basename($_SERVER['PHP_SELF'])) {
        $menu->addRight('<span class="fas fa-user-graduate" aria-hidden="true"></span> Student View', 'student-home.php');

        $menu->addRight('<span class="fas fa-poll-h" aria-hidden="true"></span> Selections ('.$countSelections.')', "results-assignments.php");

        $menu->addRight('<span class="fas fa-edit" aria-hidden="true"></span> Topics', 'build.php');
    } else {
        $menu->addRight('Exit Student View <span class="fas fa-sign-out-alt" aria-hidden="true"></span>', 'build.php');
    }
} else {
    // No menu for students
    $menu = false;
}
