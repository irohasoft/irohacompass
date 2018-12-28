SET FOREIGN_KEY_CHECKS=0;

ALTER TABLE ib_themes ADD COLUMN page_id int AFTER sort_no;
ALTER TABLE ib_themes ADD COLUMN page_image text AFTER page_id;

SET FOREIGN_KEY_CHECKS=1;
