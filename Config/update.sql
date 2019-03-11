SET FOREIGN_KEY_CHECKS=0;

ALTER TABLE ib_themes ADD COLUMN page_id int AFTER sort_no;
ALTER TABLE ib_progresses ADD COLUMN content_type varchar(20) AFTER title;
ALTER TABLE ib_progresses ADD COLUMN emotion_icon varchar(20) AFTER rate;

UPDATE ib_themes SET learning_target = introduction WHERE learning_target IS NULL;

SET FOREIGN_KEY_CHECKS=1;

CREATE TABLE IF NOT EXISTS `ib_smiles` (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `user_id` int(8) NOT NULL DEFAULT '0',
  `progress_id` int(8) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_smiles_user_progress` (`user_id`,`progress_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

