<?php
require_once "../config.php";
require_once('dao/TS_DAO.php');

use TS\DAO\TS_DAO;
use Tsugi\Core\LTIX;

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

echo '<div class="container-fluid">';

$OUTPUT->flashMessages();

$OUTPUT->pageTitle("Topic Selections", true);
if ($USER->instructor) {
    $totalRemaining = 0;
    $totalSlots = 0;
    foreach ($topics as $top) {
        $totalSlots = $totalSlots + $top["num_allowed"];
    }
    $totalRemaining = $totalSlots - $TS_DAO->getTotalReserved($LINK->id);
    ?>
    <p class="lead">Use the table below to view and edit topic selections. There are currently
        <strong><?= $totalRemaining ?></strong> of the total <strong><?= $totalSlots ?></strong> slots remaining.</p>
    <div class="row">
        <div class="col-sm-12">
            <?php
            foreach ($topics as $top) {
                $remain = $top['num_allowed'] - intval($TS_DAO->getNumberReservedForTopic($top["topic_id"]));
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
                        <?php
                        $selectST = $PDOX->prepare("SELECT * FROM {$p}ts_selection WHERE topic_id = :topicId");
                        $selectST->execute(array(":topicId" => $top['topic_id']));
                        $select = $selectST->fetchAll(PDO::FETCH_ASSOC);
                        foreach ($select as $sel) {
                            ?>
                            <div class="dropdown" style="margin:0.5rem;">
                                <button class="btn btn-primary btn-block dropdown-toggle"
                                        style="display:flex;align-items:center;" type="button" data-toggle="dropdown">
                                    <strong class="flx-grow-all text-left"><?= $sel['user_first_name'] ?> <?= $sel['user_last_name'] ?></strong>
                                    <span class="fas fa-angle-down" aria-hidden="true"></span><span class="sr-only">Actions</span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-right">
                                    <li>
                                        <a href="actions/RemoveSelection.php?assign=true&user_email=<?= $sel['user_email'] ?>&topic=<?= $top['topic_id'] ?>">Remove</a>
                                    </li>
                                </ul>
                            </div>
                            <?php
                        }
                        for ($i = 0; $i < $remain; $i++) {
                            ?>
                            <div class="dropdown" style="margin:0.5rem;">
                                <button class="btn btn-default btn-block  dropdown-toggle text-left"
                                        style="display:flex;align-items:center;" type="button" data-toggle="dropdown">
                                    <em class="flx-grow-all text-left">(empty)</em> <span class="fas fa-angle-down"
                                                                                          aria-hidden="true"></span><span
                                            class="sr-only">Actions</span>
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
            ?>
        </div>
    </div>
    <?php
} else {
    header('Location: ' . addSession('student-home.php'));
}
echo '</div>';// end container

$OUTPUT->helpModal("Topic Selector Help", __('
                        <h4>Selections</h4>
                        <p>Use this page to view topic selections, remove student selections, or manually assign students to topics. The counter at the top of the page shows the number of reserved slots along with the number of open slots still remaining.</p>
                        <h5>Removing a Selection</h5>
                        <ol>
                        <li>Click the drop-down arrow to the right of a topic that has been selected by a student.</li>
                        <li>Click the "Remove" link on the menu that appears.</li>
                        </ol>
                        <h5>Assign Students Manually</h5>
                        <ol>
                        <li>Click the drop-down arrow to the right of a topic that is currently listed as (empty).</li>
                        <li>Click the "Assign Student" link on the menu that appears.</li>
                        <li>Select the student that you wish to assign to the topic from the drop-down list on the next page.</li>
                        <li>Click the "Save" button.</li>
                        </ol>
                        '));

$OUTPUT->footerStart();

$OUTPUT->footerEnd();