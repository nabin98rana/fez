ALTER TABLE %TABLE_PREFIX%author ADD COLUMN aut_org_student_id VARCHAR(255) NULL AFTER aut_org_staff_id; 
ALTER TABLE %TABLE_PREFIX%author ADD INDEX aut_org_student_id (aut_org_student_id); 