<?php
require_once "../config.php";
require_once('dao/TS_DAO.php');

use TS\DAO\TS_DAO;
use Tsugi\Core\LTIX;

$LAUNCH = LTIX::requireData();
$p = $CFG->dbprefix;
$TS_DAO = new TS_DAO($PDOX, $p);

$topics = $TS_DAO->getTopics($LINK->id);

$title = $LAUNCH->link->settingsGet("title", $LAUNCH->link->title);

$stu_allowed = $LAUNCH->link->settingsGet("stu_allowed", true);
$stu_topics = $LAUNCH->link->settingsGet("stu_topics", 1);

$see_others = $LAUNCH->link->settingsGet("see_others", true);

$assignST = $PDOX->prepare("SELECT * FROM {$p}ts_selection WHERE user_email = :userEmail");
$assignST->execute(array(":userEmail" => $USER->email));
$assign = $assignST->fetchAll(PDO::FETCH_ASSOC);

$num_select = $assign ? count($assign) : 0;

include("menu.php");

$OUTPUT->header();
?>
    <link rel="stylesheet" href="style/topicselector.css">
<?php
$OUTPUT->bodyStart();

$OUTPUT->topNav($menu);

echo '<div class="container-fluid">';

$OUTPUT->flashMessages();

$OUTPUT->pageTitle($title, true, false);

if ($stu_allowed == "1") {
    ?>
    <p class="lead">Select the topic<?= $stu_topics > 1 ? 's' : '' ?> you would like to sign up for. Your instructor has
        allowed each student to sign up for <?= $stu_topics ?> topics.</p>
    <?php
} else {
    ?>
    <p class="lead">These are the topics created by your instructor. Your name will be listed under the topic(s) your
        instructor has assigned you to.</p>
    <?php
}
if ($topics) {
    foreach ($topics as $top) {
        $remain = $top['num_allowed'] - $top['num_reserved'];
        ?>
        <div class="row"
             style="border-top:1px solid #ddd;padding-top:1rem;padding-bottom:1rem;margin-bottom:1rem;margin-top:1rem;">
            <div class="col-sm-8">
                <div style="display:flex;">
                    <h4 style="flex:2;"><?= $top['topic_text'] ?></h4>
                    <?php
                    if ($remain > 0) {
                        ?>
                        <div style="flex:1;" class="text-right h5 text-muted"><?= $remain ?> remaining</div>
                        <?php
                    } else {
                        ?>
                        <div style="flex:1;" class="text-right h5 text-danger">FULL</div>
                        <?php
                    }
                    ?>
                </div>
            </div>
            <div class="col-sm-4">
                <ul class="list-group">
                    <?php
                    $selectST = $PDOX->prepare("SELECT * FROM {$p}ts_selection WHERE topic_id = :topicId");
                    $selectST->execute(array(":topicId" => $top['topic_id']));
                    $select = $selectST->fetchAll(PDO::FETCH_ASSOC);
                    $alreadyChoseThisTopic = false;
                    foreach ($select as $sel) {
                        if ($sel["user_email"] == $USER->email) {
                            $alreadyChoseThisTopic = true;
                            ?>
                            <li class="list-group-item list-group-item-success">
                                <a href="actions/RemoveSelection.php?user_email=<?= $USER->email ?>&topic=<?= $top['topic_id'] ?>">Selected</a>
                            </li>

                            <?php
                        } else {
                            ?>
                            <li class="list-group-item list-group-item-danger">
                                <?php
                                if ($see_others) {
                                    echo ($sel['user_first_name'].' '.$sel['user_last_name']);
                                } else {
                                    echo 'Reserved';
                                }
                                ?>
                            </li>
                            <?php
                        }
                    }
                    for ($i = 0; $i < $remain; $i++) {
                        if ($num_select < $stu_allowed && !$alreadyChoseThisTopic) {
                            // Can still select
                            ?>
                            <li class="list-group-item">
                                <a href="actions/AddSelection.php?user_email=<?= $USER->email ?>&topic=<?= $top['topic_id'] ?>">Select Topic</a>
                            </li>
                            <?php
                        } else {
                            ?>
                            <li class="list-group-item">
                                Empty
                            </li>
                            <?php
                        }
                    }
                    ?>
                </ul>
            </div>
        </div>
        <?php
    }
} else {
    echo '<h3><em>No topics have been created.</em></h3>';
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