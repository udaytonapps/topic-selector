<?php
require_once "../config.php";
require_once('dao/TS_DAO.php');

use \Tsugi\Core\LTIX;
use \TS\DAO\TS_DAO;

$LAUNCH = LTIX::requireData();
$p = $CFG->dbprefix;
$TS_DAO = new TS_DAO($PDOX, $p);

$topics = $TS_DAO->getTopics($LINK->id);

$title = $LAUNCH->link->settingsGet("title", $LAUNCH->link->title);

$stu_allowed = $LAUNCH->link->settingsGet("stu_allowed", false);
$stu_topics = $LAUNCH->link->settingsGet("stu_topics", false);

$assignST  = $PDOX->prepare("SELECT * FROM {$p}ts_selection WHERE user_email = :userEmail");
$assignST->execute(array(":userEmail" => $USER->email));
$assign = $assignST->fetchAll(PDO::FETCH_ASSOC);

$num_select = 0;
foreach($assign as $a) {
    $num_select++;
}

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

if (!$USER->instructor) {
    if($stu_allowed == "1") {
        if($stu_topics > 1) {
            echo '<p class="lead">Select the topics you would like to sign up for. Your instructor has allowed each student to sign up for ' . $stu_topics . ' topics.</p>';
        } else {
            echo '<p class="lead">Select the topic you would like to sign up for. Your instructor has allowed each student to sign up for ' . $stu_topics . ' topic.</p>';
        }
    } else {
        echo '<p class="lead">These are the topics created by your instructor. Your name will be listed under the topic(s) your instructor has assigned you to.</p>';
    }
    ?>
    <div class="col-sm-3 col-md-3 col-lg-3 col-xs-3"></div>
    <div class="col-sm-6 col-md-6 col-lg-6 col-xs-6">
        <h2>Topic</h2>
        <?php
        if($topics) {
            foreach ($topics as $top) {
                $remain = $top['num_allowed'] - $top['num_reserved'];
                $selectST  = $PDOX->prepare("SELECT * FROM {$p}ts_selection WHERE topic_id = :topicId");
                $selectST->execute(array(":topicId" => $top['topic_id']));
                $select = $selectST->fetchAll(PDO::FETCH_ASSOC);
                $user_exists = false;
                foreach($select as $sel) {
                    if($sel['user_email'] == $USER->email) {
                        $user_exists = true;
                    }
                }
                ?>
                <div class="card" style="border: 1px solid #9e9e9e; margin-bottom: 5px; border-radius: 5px">
                    <div class="container">
                        <div class="card-header">
                            <?php
                            if($remain > 0) {
                                ?>
                                <p class="topic-title"><?= $top['topic_text'] ?> (<?=$remain?>)</p>
                                <?php
                            } else {
                                ?>
                                <p class="topic-title"><?= $top['topic_text'] ?> (FULL)</p>
                                <?php
                            }
                            ?>
                        </div>
                        <div class="card-body">
                            <?php
                            if ($stu_allowed == "1" && $remain > 0 && $num_select < $stu_topics) {
                                if ($user_exists == true) {
                                    ?>
                                    <a class="card-title" href="actions/RemoveSelection.php?user_email=<?=$USER->email?>&topic=<?=$top['topic_id']?>">
                                        <span class="far fa-minus-square fa-3x"></span></a>
                                    <?php
                                } else {
                                    ?>
                                    <a class="card-title"
                                       href="actions/AddSelection.php?user_email=<?= $USER->email ?>&topic=<?= $top['topic_id'] ?>">
                                        <span class="far fa-check-square fa-3x"></span></a>
                                    <?php
                                }
                            } else if($stu_allowed == "1") {
                                if ($user_exists == true) {
                                    ?>
                                    <a class="card-title" href="actions/RemoveSelection.php?user_email=<?=$USER->email?>&topic=<?=$top['topic_id']?>">
                                        <span class="far fa-minus-square fa-3x"></span></a>
                                    <?php
                                } else {
                                    ?>
                                    <a class="card-title is_disabled">
                                        <span class="far fa-check-square fa-3x"></span></a>
                                    <?php
                                }
                            } else {
                                if ($user_exists == true) {
                                    ?>
                                    <span><?= $sel['user_first_name'] ?> <?= $sel['user_last_name'] ?></span>
                                    <?php
                                } else {
                                    ?>
                                    <a class="card-title is_disabled">
                                        <span class="far fa-check-square fa-3x"></span></a>
                                    <?php
                                }
                            }
                            ?>
                        </div>
                    </div>
                </div>
                <?php
            }
        }
        else {
            ?>
            <p style="color: #6B6464"><i>No topics have yet been created.</i></p>
            <?php
        }
        ?>
    </div>
    <div class="col-sm-3 col-md-3 col-lg-3 col-xs-3"></div>

    <?php
} else {
    echo '<p class="lead">This is what students will see when they visit topic selector.</p>';
    ?>
    <div class="col-sm-3 col-md-3 col-lg-3 col-xs-3"></div>
    <div class="col-sm-6 col-md-6 col-lg-6 col-xs-6">
        <h2>Topic</h2>
        <?php
        if($topics) {
            foreach ($topics as $top) {
                $remain = $top['num_allowed'] - $top['num_reserved'];
                $selectST = $PDOX->prepare("SELECT * FROM {$p}ts_selection WHERE topic_id = :topicId");
                $selectST->execute(array(":topicId" => $top['topic_id']));
                $select = $selectST->fetchAll(PDO::FETCH_ASSOC);
                $user_exists = false;
                foreach ($select as $sel) {
                    if ($sel['user_email'] == $USER->email) {
                        $user_exists = true;
                    }
                }
                ?>
                <div class="card" style="border: 1px solid #9e9e9e; margin-bottom: 5px; border-radius: 5px">
                    <div class="container">
                        <div class="card-header">
                            <?php
                            if ($remain > 0) {
                                ?>
                                <p class="topic-title"><?= $top['topic_text'] ?> (<?= $remain ?>)</p>
                                <?php
                            } else {
                                ?>
                                <p class="topic-title"><?= $top['topic_text'] ?> (FULL)</p>
                                <?php
                            }
                            ?>
                        </div>
                        <div class="card-body">
                            <?php
                            if ($stu_allowed == "1" && $remain > 0 && $num_select < $stu_topics) {
                                if ($user_exists == true) {
                                    ?>
                                    <a class="card-title" href="actions/RemoveSelection.php?user_email=<?=$USER->email?>&topic=<?=$top['topic_id']?>">
                                        <span class="far fa-minus-square fa-3x"></span></a>
                                    <?php
                                } else {
                                    ?>
                                    <a class="card-title"
                                       href="actions/AddSelection.php?user_email=<?= $USER->email ?>&topic=<?= $top['topic_id'] ?>">
                                        <span class="far fa-check-square fa-3x"></span></a>
                                    <?php
                                }
                            } else if($stu_allowed == "1") {
                                if ($user_exists == true) {
                                    ?>
                                    <a class="card-title" href="actions/RemoveSelection.php?user_email=<?=$USER->email?>&topic=<?=$top['topic_id']?>">
                                        <span class="far fa-minus-square fa-3x"></span></a>
                                    <?php
                                } else {
                                    ?>
                                    <a class="card-title is_disabled">
                                        <span class="far fa-check-square fa-3x"></span></a>
                                    <?php
                                }
                            } else {
                                if ($user_exists == true) {
                                    ?>
                                    <span><?= $sel['user_first_name'] ?> <?= $sel['user_last_name'] ?></span>
                                    <?php
                                } else {
                                    ?>
                                    <a class="card-title is_disabled">
                                        <span class="far fa-check-square fa-3x"></span></a>
                                    <?php
                                }
                            }
                            ?>
                        </div>
                    </div>
                </div>
                <?php
            }
        }
        else {
            ?>
            <p style="color: #6B6464"><i>No topics have yet been created.</i></p>
            <?php
        }
        ?>
    </div>
    <div class="col-sm-3 col-md-3 col-lg-3 col-xs-3"></div>

    <?php
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