<?php
if ($USER->instructor) {
    $menu = new \Tsugi\UI\MenuSet();

    $menu->setHome('Topic Selector', 'index.php');

    if ('student-home.php' != basename($_SERVER['PHP_SELF'])) {
        $menu->addRight('<span class="fas fa-user-graduate" aria-hidden="true"></span> Student View', 'student-home.php');

        $results = array(
            new \Tsugi\UI\MenuEntry("Print View", "print-view.php"),
            new \Tsugi\UI\MenuEntry("Edit Topics", "editTopics.php"),
            new \Tsugi\UI\MenuEntry("Clear Topics", "clearTopics.php"),
            new \Tsugi\UI\MenuEntry("Clear Selections", "clearSelections.php")
        );

        $menu->addRight('<span class="fas fa-poll-h" aria-hidden="true"></span> Options', $results);

        $menu->addRight('<span class="fas fa-edit" aria-hidden="true"></span> Build', 'build.php');
    } else {
        $menu->addRight('Exit Student View <span class="fas fa-sign-out-alt" aria-hidden="true"></span>', 'build.php');
    }
} else {
    // No menu for students
    $menu = false;
}
