<?php
require_once "../config.php";
require_once('dao/TS_DAO.php');

use \Tsugi\Core\LTIX;
use \TS\DAO\TS_DAO;

$LAUNCH = LTIX::requireData();
$p = $CFG->dbprefix;
$TS_DAO = new TS_DAO($PDOX, $p);

$topics = $TS_DAO->getTopics($LINK->id);

include("menu.php");

$OUTPUT->header();
?>
    <link rel="stylesheet" href="style/topicselector.css">
<?php
$OUTPUT->bodyStart();

$OUTPUT->topNav($menu);

echo '<div class="container-fluid col-lg-8 col-md-10 col-sm-12">';

$OUTPUT->flashMessages();

$OUTPUT->pageTitle("Results <small>Topic Assignments</small>");
if ($USER->instructor) {
    foreach ($topics as $top) {
        $remain = $top['num_allowed'] - $top['num_reserved'];
        ?>
        <div class="row" style="border-top:1px solid #ddd;padding-top:1rem;padding-bottom:1rem;margin-bottom:1rem;margin-top:1rem;">
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
            <?php
            $selectST = $PDOX->prepare("SELECT * FROM {$p}ts_selection WHERE topic_id = :topicId");
            $selectST->execute(array(":topicId" => $top['topic_id']));
            $select = $selectST->fetchAll(PDO::FETCH_ASSOC);
            foreach ($select as $sel) {
                ?>
                <div class="dropdown" style="margin:0.5rem;">
                    <button class="btn btn-primary btn-block dropdown-toggle" style="display:flex;align-items:center;" type="button" data-toggle="dropdown">
                        <strong class="flx-grow-all text-left"><?= $sel['user_first_name'] ?> <?= $sel['user_last_name'] ?></strong> <span class="fas fa-angle-down" aria-hidden="true"></span><span class="sr-only">Actions</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-right">
                        <li><a href="actions/RemoveSelection.php?user_email=<?= $sel['user_email'] ?>&topic=<?= $top['topic_id'] ?>">Remove</a></li>
                    </ul>
                </div>
                <?php
            }
            for ($i = 0; $i < $remain; $i++) {
                ?>
                <div class="dropdown" style="margin:0.5rem;">
                    <button class="btn btn-default btn-block  dropdown-toggle text-left" style="display:flex;align-items:center;" type="button" data-toggle="dropdown">
                        <em class="flx-grow-all text-left">(empty)</em> <span class="fas fa-angle-down" aria-hidden="true"></span><span class="sr-only">Actions</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-right">
                        <li><a href="assignStu.php?top=<?= $top['topic_id'] ?>">Assign Student</a></li>
                    </ul>
                </div>
                <?php
            }
            ?>
            </div>
        </div>
        <?php
    }
} else {
    header('Location: ' . addSession('student-home.php'));
}
echo '</div>';// end container

$OUTPUT->footerStart();

$OUTPUT->footerEnd();