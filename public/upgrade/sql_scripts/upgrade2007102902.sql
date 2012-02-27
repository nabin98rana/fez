ALTER TABLE %TABLE_PREFIX%user
  ADD COLUMN usr_super_administrator tinyint(1) default '0';

UPDATE %TABLE_PREFIX%user
  SET usr_super_administrator = '1' 
  WHERE usr_administrator = '1';
