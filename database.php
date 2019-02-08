<?php

// The SQL to uninstall this tool
$DATABASE_UNINSTALL = array(
);

// The SQL to create the tables if they don't exist
$DATABASE_INSTALL = array(
    array( "{$CFG->dbprefix}topic_list",
        "create table {$CFG->dbprefix}topic_list (
    list_id       INTEGER NOT NULL AUTO_INCREMENT,
    link_id       INTEGER NOT NULL,
    num_topics    INTEGER NOT NULL,
    topic_list    VARCHAR(255) NULL,
    stu_reserve   BOOL,
    allow_stu     BOOL,
    
    PRIMARY KEY(list_id)
) ENGINE = InnoDB DEFAULT CHARSET=utf8"),
    array( "{$CFG->dbprefix}topic",
        "create table {$CFG->dbprefix}topic (
    topic_id          INTEGER NOT NULL AUTO_INCREMENT,
    list_id           INTEGER NOT NULL,
    user_id           INTEGER NOT NULL,
    date_selected     datetime NOT NULL,
    topic_text        TEXT NULL,
    num_reserve       INTEGER NOT NULL,

    CONSTRAINT `{$CFG->dbprefix}topic`
        FOREIGN KEY (`list_id`)
        REFERENCES `{$CFG->dbprefix}topic_list` (`list_id`)
        ON DELETE CASCADE,

    PRIMARY KEY(topic_id)
) ENGINE = InnoDB DEFAULT CHARSET=utf8")
);