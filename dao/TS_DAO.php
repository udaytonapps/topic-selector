<?php
namespace TS\DAO;

class TS_DAO {

    private $PDOX;
    private $p;

    public function __construct($PDOX, $p) {
        $this->PDOX = $PDOX;
        $this->p = $p;
    }

    function findTopicsForImport($user_id, $list_id) {
        $query = "SELECT q.*, m.title as tooltitle, c.title as sitetitle FROM {$this->p}topic q join {$this->p}topic_build m on q.list_id = m.list_id join {$this->p}lti_link c on m.link_id = c.link_id WHERE m.user_id = :userId AND m.list_id != :list_id";
        $arr = array(':userId' => $user_id, ":list_id" => $list_id);
        return $this->PDOX->allRowsDie($query, $arr);
    }

    function hasSeenSplash($ts_id) {
        $query = "SELECT seen_splash FROM {$this->p}ts_main WHERE ts_id = :tsId";
        $arr = array(':tsId' => $ts_id);
        return $this->PDOX->rowDie($query, $arr)["seen_splash"];
    }

    function markAsSeen($ts_id) {
        $query = "UPDATE {$this->p}ts_main set seen_splash = 1 WHERE ts_id = :tsId;";
        $arr = array(':tsId' => $ts_id);
        $this->PDOX->queryDie($query, $arr);
    }

    function getMainTitle($ts_id) {
        $query = "SELECT title FROM {$this->p}ts_main WHERE ts_id = :tsId";
        $arr = array(':tsId' => $ts_id);
        return $this->PDOX->rowDie($query, $arr)["title"];
    }

    function updateMainTitle($ts_id, $title, $current_time) {
        $query = "UPDATE {$this->p}ts_main set title = :title, modified = :currentTime WHERE ts_id = :tsId;";
        $arr = array(':title' => $title, ':currentTime' => $current_time, ':tsId' => $ts_id);
        $this->PDOX->queryDie($query, $arr);
    }

    function getTopics($list_id) {
        $query = "SELECT * FROM {$this->p}topic WHERE list_id = :listId order by topic_num;";
        $arr = array(':listId' => $list_id);
        return $this->PDOX->allRowsDie($query, $arr);
    }

    function getTopicById($topic_id) {
        $query = "SELECT * FROM {$this->p}topic WHERE topic_id = :topicId;";
        $arr = array(':topicId' => $topic_id);
        return $this->PDOX->rowDie($query, $arr);
    }

    function createTopic($list_id, $topic_text, $num_allowed) {
        $nextNumber = $this->getNexttopicNumber($list_id);
        $query = "INSERT INTO {$this->p}topic (list_id, topic_num, topic_text, num_allowed, num_reserved) VALUES (:listId, :topicNum, :topicText, :numAllowed, :numReserved);";
        $arr = array(':listId' => $list_id, ':topicNum' => $nextNumber, ':topicText' => $topic_text, ':numAllowed'=>$num_allowed, ':numReserved'=>0);
        $this->PDOX->queryDie($query, $arr);
        return $this->PDOX->lastInsertId();
    }

    function updateTopic($topic_id, $topic_text, $num_allowed) {
        $query = "UPDATE {$this->p}topic set topic_text = :topicText num_allowed = :numAllowed WHERE topic_id = :topicId;";
        $arr = array(':topicId' => $topic_id, ':topicText' => $topic_text, ':numAllowed'=>$num_allowed);
        $this->PDOX->queryDie($query, $arr);
    }

    function getNextTopicNumber($list_id) {
        $query = "SELECT MAX(topic_num) as lastNum FROM {$this->p}topic WHERE list_id = :listId";
        $arr = array(':listId' => $list_id);
        $lastNum = $this->PDOX->rowDie($query, $arr)["lastNum"];
        return $lastNum + 1;
    }

    function deleteTopic($topic_id) {
        $query = "DELETE FROM {$this->p}topic WHERE topic_id = :topicId;";
        $arr = array(':topicId' => $topic_id);
        $this->PDOX->queryDie($query, $arr);
    }

    function fixUpTopicNumbers($list_id) {
        $query = "SET @topic_num = 0; UPDATE {$this->p}topic set topic_num = (@topic_num:=@topic_num+1) WHERE list_id = :listId ORDER BY topic_num";
        $arr = array(':listId' => $list_id);
        $this->PDOX->queryDie($query, $arr);
    }

    function updateTopicNumber($topic_id, $new_number) {
        $query = "UPDATE {$this->p}topic set topic_num = :topicNumber WHERE topic_id = :topicId;";
        $arr = array(':topicId' => $topic_id, ':topicNumber' => $new_number);
        $this->PDOX->queryDie($query, $arr);
    }

    function findEmail($user_id) {
        $query = "SELECT email FROM {$this->p}lti_user WHERE user_id = :user_id;";
        $arr = array(':user_id' => $user_id);
        $context = $this->PDOX->rowDie($query, $arr);
        return $context["email"];
    }

    function findDisplayName($user_id) {
        $query = "SELECT displayname FROM {$this->p}lti_user WHERE user_id = :user_id;";
        $arr = array(':user_id' => $user_id);
        $context = $this->PDOX->rowDie($query, $arr);
        return $context["displayname"];
    }

    function findInstructors($context_id) {
        $query = "SELECT user_id FROM {$this->p}lti_membership WHERE context_id = :context_id AND role = '1000';";
        $arr = array(':context_id' => $context_id);
        return $this->PDOX->allRowsDie($query, $arr);
    }

    function isUserInstructor($context_id, $user_id) {
        $query = "SELECT role FROM {$this->p}lti_membership WHERE context_id = :context_id AND user_id = :user_id;";
        $arr = array(':context_id' => $context_id, ':user_id' => $user_id);
        $role = $this->PDOX->rowDie($query, $arr);
        return $role["role"] == '1000';
    }
}
