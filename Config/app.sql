SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for ib_logs
-- ----------------------------
CREATE TABLE `ib_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `log_type` varchar(50) DEFAULT NULL,
  `log_content` varchar(1000) DEFAULT NULL,
  `controller` varchar(200) DEFAULT NULL,
  `action` varchar(200) DEFAULT NULL,
  `params` varchar(200) DEFAULT NULL,
  `sec` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `user_ip` varchar(50) DEFAULT NULL,
  `user_agent` varchar(1000) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for ib_progresses
-- ----------------------------
CREATE TABLE `ib_progresses` (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `group_id` int(8) NOT NULL DEFAULT '0',
  `task_id` int(8) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `progress_type` varchar(20) DEFAULT NULL,
  `title` varchar(200) NOT NULL DEFAULT '',
  `body` text NOT NULL,
  `rate` int(11) DEFAULT NULL,
  `file` varchar(200) DEFAULT NULL,
  `file_name` varchar(200) DEFAULT NULL,
  `image` varchar(200) DEFAULT NULL,
  `options` varchar(200) DEFAULT NULL,
  `correct` varchar(200) NOT NULL DEFAULT '',
  `score` int(8) NOT NULL DEFAULT '0',
  `comment` text,
  `created` datetime NOT NULL,
  `modified` datetime DEFAULT NULL,
  `sort_no` int(8) NOT NULL DEFAULT '0',
  `page_id` int(11) DEFAULT NULL,
  `page_image` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for ib_tasks
-- ----------------------------
CREATE TABLE `ib_tasks` (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `group_id` int(8) NOT NULL DEFAULT '0',
  `theme_id` int(8) DEFAULT NULL,
  `user_id` int(8) NOT NULL,
  `title` varchar(200) NOT NULL DEFAULT '',
  `url` varchar(200) DEFAULT NULL,
  `kind` varchar(20) NOT NULL DEFAULT '',
  `body` text,
  `file` varchar(200) DEFAULT NULL,
  `file_name` varchar(200) DEFAULT NULL,
  `priority` int(11) DEFAULT NULL,
  `rate` int(11) DEFAULT NULL,
  `status` int(11) DEFAULT NULL,
  `deadline` datetime DEFAULT NULL,
  `opened` datetime DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` datetime DEFAULT NULL,
  `deleted` datetime DEFAULT NULL,
  `sort_no` int(8) NOT NULL DEFAULT '0',
  `comment` text,
  `page_id` int(11) DEFAULT NULL,
  `page_image` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for ib_records
-- ----------------------------
CREATE TABLE `ib_records` (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `group_id` int(8) NOT NULL DEFAULT '0',
  `theme_id` int(8) DEFAULT NULL,
  `user_id` int(8) NOT NULL DEFAULT '0',
  `task_id` int(8) DEFAULT NULL,
  `theme_rate` int(3) DEFAULT NULL,
  `rate` int(3) DEFAULT NULL,
  `is_complete` smallint(1) DEFAULT NULL,
  `progress` smallint(1) DEFAULT '0',
  `kind` smallint(1) DEFAULT NULL,
  `study_sec` int(3) DEFAULT NULL,
  `record_type` varchar(200) DEFAULT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for ib_settings
-- ----------------------------
CREATE TABLE `ib_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_name` varchar(100) NOT NULL,
  `setting_value` varchar(1000) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for ib_notes
-- ----------------------------
CREATE TABLE `ib_notes` (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `body` text,
  `opened` datetime DEFAULT NULL,
  `closed` datetime DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime NOT NULL,
  `user_id` int(8) NOT NULL,
  `group_id` int(8) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for ib_infos_groups
-- ----------------------------
CREATE TABLE `ib_infos_groups` (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `info_id` int(8) NOT NULL DEFAULT '0',
  `group_id` int(8) NOT NULL DEFAULT '0',
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  `comment` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for ib_infos
-- ----------------------------
CREATE TABLE `ib_infos` (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `body` text,
  `opened` datetime DEFAULT NULL,
  `closed` datetime DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime NOT NULL,
  `user_id` int(8) NOT NULL,
  `group_id` int(8) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for ib_cake_sessions
-- ----------------------------
CREATE TABLE `ib_cake_sessions` (
  `id` varchar(255) NOT NULL DEFAULT '',
  `data` text NOT NULL,
  `expires` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for ib_themes
-- ----------------------------
CREATE TABLE `ib_themes` (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `group_id` int(8) NOT NULL DEFAULT '0',
  `title` varchar(200) NOT NULL DEFAULT '',
  `opened` datetime DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` datetime DEFAULT NULL,
  `deleted` datetime DEFAULT NULL,
  `sort_no` int(8) NOT NULL DEFAULT '0',
  `learning_target` text,
  `introduction` text,
  `comment` text,
  `page_id` int(11) DEFAULT NULL,
  `page_image` text,
  `user_id` int(8) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for ib_users
-- ----------------------------
CREATE TABLE `ib_users` (
  `id` int(20) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL DEFAULT '',
  `password` varchar(200) NOT NULL DEFAULT '',
  `name` varchar(50) NOT NULL DEFAULT '',
  `role` varchar(20) NOT NULL DEFAULT '',
  `email` varchar(50) NOT NULL DEFAULT '',
  `comment` text,
  `last_logined` datetime DEFAULT NULL,
  `last_accessed` datetime DEFAULT NULL,
  `started` datetime DEFAULT NULL,
  `ended` datetime DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  `deleted` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `login_id` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for ib_leafs
-- ----------------------------
CREATE TABLE `ib_leafs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `leaf_id` varchar(36) NOT NULL,
  `leaf_title` text,
  `leaf_content` text,
  `leaf_top` int(11) DEFAULT NULL,
  `leaf_left` int(11) DEFAULT NULL,
  `leaf_zorder` int(11) DEFAULT NULL,
  `leaf_kind` int(11) DEFAULT NULL,
  `leaf_color` text,
  `note_id` varchar(36) DEFAULT NULL,
  `page_id` varchar(36) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  `user_id` varchar(60) DEFAULT NULL,
  `leaf_width` int(11) DEFAULT '0',
  `leaf_height` int(11) DEFAULT '0',
  `leaf_fold` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `leaf_id` (`leaf_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for ib_users_groups
-- ----------------------------
CREATE TABLE `ib_users_groups` (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `user_id` int(8) NOT NULL DEFAULT '0',
  `group_id` int(8) NOT NULL DEFAULT '0',
  `created` datetime NOT NULL,
  `modified` datetime DEFAULT NULL,
  `comment` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=326 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for ib_users_themes
-- ----------------------------
CREATE TABLE `ib_users_themes` (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `user_id` int(8) NOT NULL DEFAULT '0',
  `theme_id` int(8) DEFAULT NULL,
  `started` date DEFAULT NULL,
  `ended` date DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  `comment` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for ib_groups_themes
-- ----------------------------
CREATE TABLE `ib_groups_themes` (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `group_id` int(8) NOT NULL DEFAULT '0',
  `theme_id` int(8) DEFAULT NULL,
  `started` date DEFAULT NULL,
  `ended` date DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  `comment` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for ib_groups
-- ----------------------------
CREATE TABLE `ib_groups` (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL DEFAULT '',
  `comment` text,
  `created` datetime NOT NULL,
  `modified` datetime DEFAULT NULL,
  `deleted` datetime DEFAULT NULL,
  `status` int(1) NOT NULL DEFAULT '1',
  `logo` varchar(200) DEFAULT NULL,
  `copyright` varchar(200) DEFAULT NULL,
  `module` varchar(50) DEFAULT '00000000',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for ib_links
-- ----------------------------
CREATE TABLE `ib_links` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `link_id` varchar(36) NOT NULL,
  `leaf_id` varchar(36) NOT NULL DEFAULT '0',
  `leaf_id2` varchar(36) NOT NULL DEFAULT '0',
  `note_id` varchar(36) DEFAULT NULL,
  `page_id` varchar(36) NOT NULL DEFAULT '0',
  `user_id` varchar(60) NOT NULL DEFAULT '0',
  `created` date DEFAULT NULL,
  `link_kind` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `link_id` (`link_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

SET FOREIGN_KEY_CHECKS=1;

-- ----------------------------
-- Table structure for `ib_cake_sessions`
-- ----------------------------
CREATE TABLE `ib_cake_sessions` (
  `id` varchar(255) NOT NULL DEFAULT '',
  `data` text NOT NULL,
  `expires` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `ib_settings` VALUES ('1', 'title', 'システム名', 'iroha Compass');
INSERT INTO `ib_settings` VALUES ('2', 'copyright', 'コピーライト', 'Copyright (C) 2018 iroha Soft Co.,Ltd. All rights reserved.');
INSERT INTO `ib_settings` VALUES ('3', 'color', 'テーマカラー', '#337ab7');
INSERT INTO `ib_settings` VALUES ('4', 'information', 'お知らせ', '全体のお知らせを表示します。\r\nこのお知らせは管理機能の「システム設定」にて変更可能です。');
INSERT INTO `ib_settings` VALUES ('5', 'mail_title', '進捗の更新メールのタイトル', '[iroha Compass] 進捗の更新');
INSERT INTO `ib_settings` VALUES ('6', 'admin_name', '送信者名', 'iroha Compass');
INSERT INTO `ib_settings` VALUES ('7', 'admin_from', '送信者メールアドレス', 'sendmail@dummy.com');
