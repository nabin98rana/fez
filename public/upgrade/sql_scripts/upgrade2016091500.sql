ALTER TABLE %TABLE_PREFIX%author ADD COLUMN aut_student_username VARCHAR(255) DEFAULT NULL;
ALTER TABLE %TABLE_PREFIX%author ADD CONSTRAINT aut_student_username_unique UNIQUE (aut_student_username);
ALTER TABLE %TABLE_PREFIX%author ADD FULLTEXT KEY aut_student_username_ft (aut_student_username);
