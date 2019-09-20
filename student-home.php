<?php
require_once "../config.php";
require_once('dao/TS_DAO.php');

use \Tsugi\Core\LTIX;
use \TS\DAO\TS_DAO;

$LAUNCH = LTIX::requireData();
$p = $CFG->dbprefix;
$TS_DAO = new TS_DAO($PDOX, $p);

$t_buildST  = $PDOX->prepare("SELECT * FROM {$p}topic_build WHERE link_id = :linkId");
$t_buildST->execute(array(":linkId" => $LINK->id));
$t_build = $t_buildST->fetch(PDO::FETCH_ASSOC);

$topics = $TS_DAO->getTopics($t_build['list_id']);

$title = $LAUNCH->link->settingsGet("title", $LAUNCH->link->title);

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

echo '<p class="lead">Student view page that\'s also used for instructor preview.</p>';

if (!$USER->instructor) {
    ?>
    <div class="col-sm-3 col-md-3 col-lg-3 col-xs-3"></div>
    <div class="col-sm-6 col-md-6 col-lg-6 col-xs-6">
        <h2>Topic</h2>
        <?php
        foreach ($topics as $top) {
            $remain = $top['num_allowed'] - $top['num_reserved'];
            $selectST  = $PDOX->prepare("SELECT * FROM {$p}selection WHERE topic_id = :topicId");
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
                        <p class="topic-title"><?= $top['topic_text'] ?> (<?=$remain?>)</p>
                    </div>
                    <div class="card-body">

                        <?php
                        if($remain>0) {
                            if($user_exists == true) {
                                ?>
                                <a class="card-title" href="actions/RemoveSelection.php?user_email=<?=$USER->email?>&topic=<?=$top['topic_id']?>">
                                    <span class="far fa-minus-square fa-3x"></span></a>
                                <?php
                            } else {
                                ?>
                                <a class="card-title" href="actions/AddSelection.php?user_email=<?=$USER->email?>&topic=<?=$top['topic_id']?>">
                                    <span class="far fa-check-square fa-3x"></span></a>
                                <?php
                            }
                        } else {
                            ?>
                            <a class="card-title" href="#"><span class="far fa-window-close fa-3x" disabled="true" </span></a>
                            <?php
                        }
                            ?>
                    </div>
                </div>
            </div>
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