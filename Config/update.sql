SET FOREIGN_KEY_CHECKS=0;

ALTER TABLE ib_themes ADD COLUMN page_id int AFTER sort_no;

ALTER TABLE ib_progresses ADD COLUMN content_type varchar(20) AFTER title;

SET FOREIGN_KEY_CHECKS=1;
