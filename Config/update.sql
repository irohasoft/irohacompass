SET FOREIGN_KEY_CHECKS=0;

ALTER TABLE ib_themes ADD COLUMN page_id int AFTER sort_no;
ALTER TABLE ib_progresses ADD COLUMN content_type varchar(20) AFTER title;

UPDATE ib_themes SET learning_target = introduction WHERE learning_target IS NULL;

SET FOREIGN_KEY_CHECKS=1;
