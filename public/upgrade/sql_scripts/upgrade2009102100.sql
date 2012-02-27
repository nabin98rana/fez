-- Fix the sessions table to match the new zend sessions handling
ALTER TABLE %TABLE_PREFIX%sessions DROP COLUMN expires;
ALTER TABLE %TABLE_PREFIX%sessions ADD COLUMN created DATETIME NOT NULL AFTER session_data;
ALTER TABLE %TABLE_PREFIX%sessions ADD COLUMN updated DATETIME NOT NULL;
ALTER TABLE %TABLE_PREFIX%sessions CHANGE COLUMN session_data session_data LONGTEXT;
