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

include("menu.php");

$OUTPUT->header();
?>
    <link rel="stylesheet" href="style/topicselector.css">
<?php
$OUTPUT->bodyStart();

$OUTPUT->topNav($menu);

echo '<div class="container-fluid">';

$OUTPUT->flashMessages();

$OUTPUT->pageTitle("Results <small>Topic Assignments</small>");
if($USER->instructor) {
    ?>
    <div class="col-sm-3 col-md-3 col-lg-3 col-xs-3"></div>
    <div class="col-sm-6 col-md-6 col-lg-6 col-xs-6">
        <h2>Topic</h2>
        <?php
        foreach ($topics as $top) {
            $remain = $top['num_allowed'] - $top['num_reserved'];
            if ($remain > 0) {
                ?>
                <div class="card" style="border: 1px solid #9e9e9e; margin-bottom: 5px; border-radius: 5px">
                    <div class="container">
                        <div class="card-header">
                            <p class="topic-title"><?=$top['topic_text']?> (<?=$remain?>)</p>
                        </div>
                        <div class="card-body">
                            <?php
                            $selectST  = $PDOX->prepare("SELECT * FROM {$p}selection WHERE topic_id = :topicId");
                            $selectST->execute(array(":topicId" => $top['topic_id']));
                            $select = $selectST->fetchAll(PDO::FETCH_ASSOC);
                            foreach($select as $sel) {
                                ?>
                                <p><?=$sel['user_first_name']?> <?=$sel['user_last_name']?> <span class="far fa-window-close fa-2x"></span></p>
                                <?php
                            }
                            for($i=0; $i<$remain; $i++) {
                                ?>
                                <p><a href="assignStu.php?top=<?=$top['topic_id']?>"><i>(empty)</i></a></p>
                                <?php
                            }
                            ?>
                        </div>
                    </div>
                </div>
                <?php
            } else {
                ?>
                <div class="card" style="border: 1px solid #9e9e9e; margin-bottom: 5px; border-radius: 5px">
                    <div class="container">
                        <div class="card-header">
                            <p class="topic-title"><?= $top['topic_text'] ?></p>
                        </div>
                        <div class="card-body">

                        </div>
                    </div>
                </div>
                <?php
            }
        }
        ?>
    </div>
    <div class="col-sm-3 col-md-3 col-lg-3 col-xs-3"></div>

    <?php
} else {
    header( 'Location: '.addSession('student-home.php') ) ;
}
echo '</div>';// end container

$OUTPUT->footerStart();

$OUTPUT->footerEnd();