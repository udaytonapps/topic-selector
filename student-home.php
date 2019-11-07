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

$stu_locked = $LAUNCH->link->settingsGet("stu_locked", false);
$stu_topics = intval($LAUNCH->link->settingsGet("stu_topics", "1"));

$see_locked = $LAUNCH->link->settingsGet("see_locked", false);

$assignST = $PDOX->prepare("SELECT * FROM {$p}ts_selection WHERE user_email = :userEmail AND topic_id in 
                    (SELECT topic_id FROM {$p}ts_topic where link_id = :linkId)");
$assignST->execute(array(":userEmail" => $USER->email, ":linkId" => $LINK->id));
$assign = $assignST->fetchAll(PDO::FETCH_ASSOC);

$num_select = $assign ? count($assign) : 0;

$num_select_remaining = $stu_topics - $num_select;

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

if ($stu_locked == "1") {
    ?>
    <p class="lead">These are the topics created by your instructor. Your name will be listed under the topic(s) your
        instructor has assigned you to.</p>
    <?php
} else {
    ?>
    <p class="lead">
        Use the links to the right of the list below to sign up for <?= $stu_topics > 1 ? $stu_topics.' topics' : 'a topic' ?>.
        You have <span class="label label-warning" style="vertical-align: middle;"><?= $num_select_remaining ?></span> <?= $stu_topics > 1 ? 'selections' : 'selection' ?> remaining.</p>
    <?php
}
if ($topics) {
    ?>
    <div class="row">
        <div class="col-sm-12">
            <?php
            foreach ($topics as $top) {
                $remain = $top['num_allowed'] - intval($TS_DAO->getNumberReservedForTopic($top["topic_id"]))
                ?>
                <div class="row"
                     style="border-top:1px solid #ddd;padding-top:1rem;padding-bottom:1rem;">
                    <div class="col-sm-8">
                        <div style="display:flex;">
                            <div style="flex:2;">
                                <h4><?= $top['topic_text'] ?></h4>
                                <p><?= $top["description"]?></p>
                            </div>
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
                                    if ($stu_locked == "1") {
                                        ?>
                                        <li class="list-group-item list-group-item-success">
                                            <?=$sel['user_first_name'] . ' ' . $sel['user_last_name']?> (My Selection)
                                        </li>
                                        <?php
                                    } else {
                                        ?>
                                        <li class="list-group-item list-group-item-success">
                                            <a href="actions/RemoveSelection.php?user_email=<?= $sel["user_email"] ?>&topic=<?= $top['topic_id'] ?>">
                                                <?=$sel['user_first_name'] . ' ' . $sel['user_last_name']?> (Remove Selection)
                                            </a>
                                        </li>
                                        <?php
                                    }
                                } else {
                                    ?>
                                    <li class="list-group-item list-group-item-danger">
                                        <?php
                                        if ($see_locked) {
                                            echo 'Reserved';
                                        } else {
                                            echo($sel['user_first_name'] . ' ' . $sel['user_last_name']);
                                        }
                                        ?>
                                    </li>
                                    <?php
                                }
                            }
                            for ($i = 0; $i < $remain; $i++) {
                                if ($num_select < $stu_topics && !$alreadyChoseThisTopic && $stu_locked !== "1") {
                                    // Can still select
                                    ?>
                                    <li class="list-group-item">
                                        <a href="actions/AddSelection.php?user_email=<?= $USER->email ?>&topic=<?= $top['topic_id'] ?>">Select
                                            Topic</a>
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
            ?>
        </div>
    </div>
    <?php
} else {
    echo '<h3><em>No topics have been created.</em></h3>';
}

echo '</div>';// end container

if ($USER->instructor) {
    $OUTPUT->helpModal("Topic Selector Help", '
                            <h4>Student View</h4>
                            <p>You are seeing what a student will see when they access this tool. However, your selection(s) will be cleared once you leave student view.</p>
                            <p>Your selection(s) will not show up in any of the results.</p>');
} else {
    $OUTPUT->helpModal("Topic Selector Help", '
                            <h4>What do I do?</h4>
                            <p>Click on an open topic to reserve it for yourself. You selection is saved immediately. Click the "Remove Selection" link next to a topic you\'ve selected if you want to change your selection. You are shown the number of selections you can make at the top of the page.</p>');
}
$OUTPUT->footerStart();

$OUTPUT->footerEnd();