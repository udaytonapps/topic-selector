<?php
require_once "../config.php";
require_once('dao/TS_DAO.php');

use \Tsugi\Core\LTIX;
use \Tsugi\UI\SettingsForm;
use \TS\DAO\TS_DAO;

$LAUNCH = LTIX::requireData();
$p = $CFG->dbprefix;

$TS_DAO = new TS_DAO($PDOX, $p);

$topics = $TS_DAO->getTopics($LINK->id);

if (SettingsForm::isSettingsPost()) {
    if (!isset($_POST["title"]) || trim($_POST["title"]) === '') {
        $_SESSION["error"] = __('Title cannot be blank.');
    } else if (!isset($_POST["stu_topics"]) || trim($_POST["stu_topics"] === '')) {
        $_SESSION["error"] = __('Number of topics cannot be blank.');
    } else {
        SettingsForm::handleSettingsPost();
        $_SESSION["success"] = __('All settings saved.');
    }
    header('Location: ' . addSession('index.php'));
    return;
}

$title = $LAUNCH->link->settingsGet("title", false);

if (!$title) {
    $LAUNCH->link->settingsSet("title", $LAUNCH->link->title);
    $title = $LAUNCH->link->title;
}

$stuTops = $LAUNCH->link->settingsGet("stu_topics", false);

if (!$stuTops) {
    $LAUNCH->link->settingsSet("stu_topics", "1");
}

SettingsForm::start();
SettingsForm::text('title', __('Tool Title'));
SettingsForm::text('stu_topics', __('Number of Topics Each Student Can be Assigned To'));
SettingsForm::checkbox('stu_locked', __('Prevent students from selecting topics'));
SettingsForm::checkbox('see_locked', __('Prevent students from seeing other student\'s selections'));
SettingsForm::end();

// Remove instructor selection
$instructors = $TS_DAO->findInstructors($CONTEXT->id);
foreach($instructors as $instructor) {
    $email = $TS_DAO->findEmail($instructor["user_id"]);
    $clearQry = "DELETE FROM {$p}ts_selection WHERE user_email = :userEmail AND topic_id in (SELECT topic_id from {$p}ts_topic WHERE link_id = :linkId)";
    $arr = array('userEmail' => $email, ':linkId' => $LINK->id);
    $PDOX->queryDie($clearQry, $arr);
}

include("menu.php");

$OUTPUT->header();
?>
    <link rel="stylesheet" type="text/css" href="style/topicselector.css">
<?php
$OUTPUT->bodyStart();

$OUTPUT->topNav($menu);
?>
    <div class="container-fluid">
        <?php
        $OUTPUT->flashMessages();

        $OUTPUT->pageTitle($title, true, true);

        ?>
        <p class="lead">Create topics for students to select.</p>
        <p>You can choose the number of students that may sign up for a topic. The number of topics that a student may
            sign
            up for defaults to 1, and students are allowed to select a topic by default. These options can be changed in
            Settings.</p>
        <section id="theTopics">
            <div class="h3 inline flx-cntnr flx-row flx-nowrap flx-start">
                <div class="flx-grow-all topic-text">
                    <div class="flx-cntnr">
                        <div class="flx-basis-0" style="flex:3">
                            <h3 class="small-hdr"><small>Topic Title</small></h3>
                        </div>
                        <div class="flx-basis-0" style="flex:3">
                            <h3 class="small-hdr"><small>Maximum Slots Available</small></h3>
                        </div>
                    </div>
                </div>
            </div>
            <?php
            foreach ($topics as $topic) {
                ?>
                <div id="topicRow<?= $topic["topic_id"] ?>"
                     class="h3 inline flx-cntnr flx-row flx-nowrap flx-start topic-row"
                     data-topic-number="<?= $topic['topic_num'] ?>">
                    <div class="topic-text flx-basis-0" style="flex:5">
                        <div class="flx-cntnr topic-text-span" onclick="editTopicText(<?= $topic["topic_id"] ?>)"
                             id="topicText<?= $topic["topic_id"] ?>" tabindex="0">
                            <div class="flx-basis-0" style="flex:3">
                                <p class="topic-title"><?= $topic["topic_text"] ?></p>
                            </div>
                            <div class="flx-basis-0" style="flex:2">
                                <p class="topic-slots"><?= $topic["num_allowed"] ?></p>
                            </div>
                        </div>
                        <form id="topicTextForm<?= $topic["topic_id"] ?>"
                              onsubmit="return confirmDeleteTopicBlank(<?= $topic["topic_id"] ?>)"
                              action="actions/AddOrEditTopic.php" method="post" style="display:none;">
                            <input type="hidden" name="topicId" value="<?= $topic["topic_id"] ?>">
                            <div class="flx-cntnr">
                                <div class="flx-basis-0" style="flex:3">
                                    <label for="topicTextInput<?= $topic["topic_id"] ?>" class="sr-only">Topic
                                        Text</label>
                                    <input class="form-control" style="width: 95%;"
                                           id="topicTextInput<?= $topic["topic_id"] ?>"
                                           name="topicText" value="<?= $topic["topic_text"] ?>" required>
                                </div>
                                <div class="flx-basis-0" style="flex:2">
                                    <label for="topicStuAllowed<?= $topic["topic_id"] ?>" class="sr-only">Slots
                                        Available</label>
                                    <input class="form-control" type="number"
                                           id="topicStuAllowed<?= $topic["topic_id"] ?>"
                                           name="num_allowed" value="<?= $topic["num_allowed"] ?>">
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="actions flx-basis-0 flx-grow-1 text-right">
                        <a id="topicEditAction<?= $topic["topic_id"] ?>" href="javascript:void(0);"
                           onclick="editTopicText(<?= $topic["topic_id"] ?>)">
                            <span class="fa fa-fw fa-pencil" aria-hidden="true"></span>
                            <span class="sr-only">Edit Topic Text</span>
                        </a>
                        <a id="topicReorderAction<?= $topic["topic_id"] ?>" href="javascript:void(0);"
                           onclick="moveTopicUp(<?= $topic["topic_id"] ?>)">
                            <span class="fa fa-fw fa-chevron-circle-up" aria-hidden="true"></span>
                            <span class="sr-only">Move Topic Up</span>
                        </a>
                        <a id="topicDeleteAction<?= $topic["topic_id"] ?>" href="javascript:void(0);"
                           onclick="deleteTopic(<?= $topic["topic_id"] ?>)">
                            <span aria-hidden="true" class="fa fa-fw fa-trash"></span>
                            <span class="sr-only">Delete Topic</span>
                        </a>
                        <a id="topicSaveAction<?= $topic["topic_id"] ?>" href="javascript:void(0);"
                           style="display:none;">
                            <span aria-hidden="true" class="fa fa-fw fa-save"></span>
                            <span class="sr-only">Save Topic</span>
                        </a>
                        <a id="topicCancelAction<?= $topic["topic_id"] ?>" href="javascript:void(0);"
                           style="display: none;">
                            <span aria-hidden="true" class="fa fa-fw fa-times"></span>
                            <span class="sr-only">Cancel Topic</span>
                        </a>
                    </div>
                </div>
                <?php
            }
            ?>
            <div id="newTopicRow" class="h3 inline flx-cntnr flx-row flx-nowrap flx-start topic-row"
                 style="display:none;"
                 data-topic-number="<?= $topics ? count($topics) + 1 : 1 ?>">
                <div class="topic-text flx-basis-0" style="flex:5">
                    <form id="topicTextForm-1"
                          action="actions/AddOrEditTopic.php" method="post">
                        <input type="hidden" name="topicId" value="-1">
                        <div class="flx-cntnr">
                            <div class="flx-basis-0" style="flex:3">
                                <label for="topicTextInput-1" class="sr-only">Topic Text</label>
                                <input class="form-control" type="text" style="width: 95%;"
                                       id="topicTextInput-1"
                                       name="topicText" placeholder="Topic Title" required>
                            </div>
                            <div class="flx-basis-0" style="flex:2">
                                <label for="topicStuAllowed-1" class="sr-only">Slots Available</label>
                                <input class="form-control" type="number"
                                       id="topicStuAllowed-1"
                                       name="num_allowed" value="1">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="actions flx-basis-0 flx-grow-1 text-right">
                    <a id="topicSaveAction-1" href="javascript:void(0);">
                        <span aria-hidden="true" class="fa fa-fw fa-save"></span>
                        <span class="sr-only">Save Topic</span>
                    </a>
                    <a id="topicCancelAction-1" href="javascript:void(0);">
                        <span aria-hidden="true" class="fa fa-fw fa-times"></span>
                        <span class="sr-only">Cancel Topic</span>
                    </a>
                </div>
            </div>
        </section>
        <section id="addTopics">
        <span class="h3"><a href="javascript:void(0);" id="addTopicLink" onclick="showNewTopicRow();"
                            class="btn btn-success"><span class="fa fa-plus"
                                                          aria-hidden="true"></span> Add Topic</a></span>
        </section>
    </div>

    <input type="hidden" id="sess" value="<?php echo($_GET["PHPSESSID"]) ?>">
<?php

$OUTPUT->helpModal("Example Help Modal", __('
                        <h4>Help Content</h4>
                        <p>Use this modal to add help to the current page.</p>'));

$OUTPUT->footerStart();
?>
    <script src="scripts/topicselector.js" type="text/javascript"></script><br>
<?php

$OUTPUT->footerEnd();