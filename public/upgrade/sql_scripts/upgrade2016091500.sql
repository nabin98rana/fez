ALTER TABLE fez_author ADD COLUMN aut_student_username VARCHAR(255) DEFAULT NULL;
ALTER TABLE fez_author ADD CONSTRAINT aut_student_username_unique UNIQUE (aut_student_username);
ALTER TABLE fez_author ADD FULLTEXT KEY aut_student_username_ft (aut_student_username);
