<?php
require_once "../config.php";
require_once('dao/TS_DAO.php');

use TS\DAO\TS_DAO;
use Tsugi\Core\LTIX;
use Tsugi\UI\SettingsForm;

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
foreach ($instructors as $instructor) {
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
        <p class="lead">Create topics for students to select. Use the Settings link to adjust options, such as how many topics students can select.</p>
        <section id="theTopics">
            <div class="h3 inline flx-cntnr flx-row flx-nowrap flx-start">
                <div class="flx-basis-0 topic-text" style="flex:5;">
                    <div class="flx-cntnr" style="margin-bottom:1rem;">
                        <div class="flx-basis-0" style="flex:2">
                            <h3 class="small-hdr"><small>Topic Title</small></h3>
                        </div>
                        <div class="flx-basis-0" style="flex:1">
                            <h3 class="small-hdr"><small>Slots</small></h3>
                        </div>
                        <div class="flx-basis-0" style="flex:2">
                            <h3 class="small-hdr"><small>Description</small></h3>
                        </div>
                        <div class="flx-grow-"
                    </div>
                </div>
                <div class="flx-basis-0 flx-grow-1 text-right">
                </div>
            </div>
            <?php
            foreach ($topics as $topic) {
                ?>
                <div id="topicRow<?= $topic["topic_id"] ?>"
                     class="h3 inline flx-cntnr flx-row flx-nowrap flx-start topic-row"
                     data-topic-number="<?= $topic['topic_num'] ?>">
                    <div class="topic-text flx-basis-0" style="flex:5">
                        <div class="flx-cntnr topic-text-span" style="align-items: center;"
                             onclick="editTopicText(<?= $topic["topic_id"] ?>)"
                             id="topicText<?= $topic["topic_id"] ?>" tabindex="0">
                            <div class="flx-basis-0" style="flex:2">
                                <p class="topic-title"><?= $topic["topic_text"] ?></p>
                            </div>
                            <div class="flx-basis-0" style="flex:1">
                                <p class="topic-slots"><?= $topic["num_allowed"] ?></p>
                            </div>
                            <div class="flx-basis-0" style="flex:2">
                                <p class="topic-description h5 inline" style="margin-bottom:0.5rem;"><?= $topic["description"] ?></p>
                            </div>
                        </div>
                        <form id="topicTextForm<?= $topic["topic_id"] ?>"
                              onsubmit="return confirmDeleteTopicBlank(<?= $topic["topic_id"] ?>)"
                              action="actions/AddOrEditTopic.php" method="post" style="display:none;">
                            <input type="hidden" name="topicId" value="<?= $topic["topic_id"] ?>">
                            <div class="flx-cntnr">
                                <div class="flx-basis-0" style="flex:2">
                                    <label for="topicTextInput<?= $topic["topic_id"] ?>" class="sr-only">Topic
                                        Text</label>
                                    <input class="form-control" style="width: 90%;"
                                           id="topicTextInput<?= $topic["topic_id"] ?>"
                                           name="topicText" value="<?= $topic["topic_text"] ?>" required>
                                </div>
                                <div class="flx-basis-0" style="flex:1">
                                    <label for="topicStuAllowed<?= $topic["topic_id"] ?>" class="sr-only">Slots
                                        Available</label>
                                    <input class="form-control" type="number" style="width: 80%;min-width:84px;"
                                           id="topicStuAllowed<?= $topic["topic_id"] ?>"
                                           name="num_allowed" value="<?= $topic["num_allowed"] ?>">
                                </div>
                                <div class="flx-basis-0" style="flex:2">
                                    <label for="topicDescription<?= $topic["topic_id"] ?>" class="sr-only">Topic
                                        Description</label>
                                    <textarea class="form-control" id="topicDescription<?= $topic["topic_id"] ?>"
                                              name="topicDescription" rows="2"><?= $topic["description"] ?></textarea>
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
                            <div class="flx-basis-0" style="flex:2">
                                <label for="topicTextInput-1" class="sr-only">Topic Text</label>
                                <input class="form-control" type="text" style="width: 90%;"
                                       id="topicTextInput-1"
                                       name="topicText" placeholder="Topic Title" required>
                            </div>
                            <div class="flx-basis-0" style="flex:1">
                                <label for="topicStuAllowed-1" class="sr-only">Slots Available</label>
                                <input class="form-control" type="number" style="width: 80%;min-width:84px;"
                                       id="topicStuAllowed-1"
                                       name="num_allowed" value="1">
                            </div>
                            <div class="flx-basis-0" style="flex:2">
                                <label for="topicDescription-1" class="sr-only">Topic
                                    Description</label>
                                <textarea class="form-control" id="topicDescription-1"
                                          name="topicDescription" rows="2" placeholder="Topic Description"></textarea>
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

$OUTPUT->helpModal("Topic Selector Help", __('
                        <h4>Topics</h4>
                        <p>Use this page to add topics, the maximum number of slots for each topic, and an optional description that your students can select. Once you add a topic it is immediately available to students. Additional topics or other changes can be made once student selections have already been made but deleting a topic will also remove the selections.</p>
                        <h5>Settings</h5>
                        <p>Click the "Settings" link at the top of the page to adjust overall tool specific settings. Click the "Save Changes" button if any changes are made.</p>
                        <p style="padding-left:4rem;"><strong>Tool Title</strong> - The name of the heading that is shown at the top of the tool. [Default = Topic Selector]</p>
                        <p style="padding-left:4rem;"><strong>Number of Topics Each Student Can Be Assigned To</strong> - How many slots each student can select in the tool. [Default = 1]</p>
                        <p style="padding-left:4rem;"><strong>Prevent students from selecting topics</strong> - If enabled, students will not be able to make selections. Instructors may want to use when they’re ready to stop taking selections from students or if they plan to assign specific students to each slot under the ‘Selections’ page. [Default = Off]</p>
                        <p style="padding-left:4rem;"><strong>Prevent students from seeing other student’s selections</strong> - If enabled, students will not be able to see the names of the students that have selected specific topics. They will simply see the topic/slot as ‘Reserved’.  [Default = Off]</p>
                        <h5>Adding a Topic</h5>
                        <ol>
                        <li>Click "Add Topic".</li>
                        <li>Enter the Topic Title.</li>
                        <li>Enter the maximum number of slots that can be selected for that topic.</li>
                        <li>Add a description of the topic. (Optional)</li>
                        <li>Click the save icon or press "Enter" on your keyboard.</li>
                        </ol>
                        <h5>Editing Topics</h5>
                        <p>Use the pencil icon to the right of an added topic to edit its title, maximum number of slots, and/or description.</p>
                        <h5>Reordering Topics</h5>
                        <p>Use the arrow icon to the right of an added topic to move it up in the list. Using the up arrow icon next to the topic at the top of the list will push that topic to the bottom of the list.</p>
                        <h5>Deleting Topics</h5>
                        <p>Use the trash can icon to the right of an added topic to remove it from the list. Removing topics will also remove any selections that have been made for that topic.</p>'));

$OUTPUT->footerStart();
?>
    <script src="scripts/topicselector.js" type="text/javascript"></script><br>
<?php

$OUTPUT->footerEnd();