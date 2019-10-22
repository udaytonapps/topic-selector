<?php
namespace TS\DAO;

class TS_DAO {

    private $PDOX;
    private $p;

    public function __construct($PDOX, $p) {
        $this->PDOX = $PDOX;
        $this->p = $p;
    }

    function getTopics($link_id) {
        $query = "SELECT * FROM {$this->p}ts_topic WHERE link_id = :linkId order by topic_num;";
        $arr = array(':linkId' => $link_id);
        return $this->PDOX->allRowsDie($query, $arr);
    }

    function getTopicById($topic_id) {
        $query = "SELECT * FROM {$this->p}ts_topic WHERE topic_id = :topicId;";
        $arr = array(':topicId' => $topic_id);
        return $this->PDOX->rowDie($query, $arr);
    }

    function createTopic($link_id, $topic_text, $num_allowed) {
        $nextNumber = $this->getNexttopicNumber($link_id);
        $query = "INSERT INTO {$this->p}ts_topic (link_id, topic_num, topic_text, num_allowed, num_reserved) VALUES (:linkId, :topicNum, :topicText, :numAllowed, :numReserved);";
        $arr = array(':linkId' => $link_id, ':topicNum' => $nextNumber, ':topicText' => $topic_text, ':numAllowed'=>$num_allowed, ':numReserved'=>0);
        $this->PDOX->queryDie($query, $arr);
        return $this->PDOX->lastInsertId();
    }

    function updateTopic($topic_id, $topic_text, $num_allowed) {
        $query = "UPDATE {$this->p}ts_topic set topic_text = :topicText, num_allowed = :numAllowed WHERE topic_id = :topicId;";
        $arr = array(':topicId' => $topic_id, ':topicText' => $topic_text, ':numAllowed'=>$num_allowed);
        $this->PDOX->queryDie($query, $arr);
    }

    function getNextTopicNumber($link_id) {
        $query = "SELECT MAX(topic_num) as lastNum FROM {$this->p}ts_topic WHERE link_id = :linkId";
        $arr = array(':linkId' => $link_id);
        $lastNum = $this->PDOX->rowDie($query, $arr)["lastNum"];
        return $lastNum + 1;
    }

    function deleteTopic($topic_id) {
        $query = "DELETE FROM {$this->p}ts_topic WHERE topic_id = :topicId;";
        $arr = array(':topicId' => $topic_id);
        $this->PDOX->queryDie($query, $arr);
    }

    function fixUpTopicNumbers($link_id) {
        $query = "SET @topic_num = 0; UPDATE {$this->p}ts_topic set topic_num = (@topic_num:=@topic_num+1) WHERE link_id = :linkId ORDER BY topic_num";
        $arr = array(':linkId' => $link_id);
        $this->PDOX->queryDie($query, $arr);
    }

    function updateTopicNumber($topic_id, $new_number) {
        $query = "UPDATE {$this->p}ts_topic set topic_num = :topicNumber WHERE topic_id = :topicId;";
        $arr = array(':topicId' => $topic_id, ':topicNumber' => $new_number);
        $this->PDOX->queryDie($query, $arr);
    }

    function findDisplayName($user_id) {
        $query = "SELECT displayname FROM {$this->p}lti_user WHERE user_id = :user_id;";
        $arr = array(':user_id' => $user_id);
        $context = $this->PDOX->rowDie($query, $arr);
        return $context["displayname"];
    }
}
