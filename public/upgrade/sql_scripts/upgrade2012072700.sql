ALTER TABLE %TABLE_PREFIX%news ADD COLUMN nws_admin_only TINYINT(1) DEFAULT '0' NULL AFTER nws_updated_date; 
