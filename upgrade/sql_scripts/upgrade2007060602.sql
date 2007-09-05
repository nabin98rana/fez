ALTER TABLE %TABLE_PREFIX%search_key 
  ADD COLUMN `sek_lookup_function` varchar(255) default NULL;

UPDATE %TABLE_PREFIX%search_key 
  SET sek_lookup_function = 'Controlled_Vocab::getTitle' 
  WHERE sek_title = 'Subject';

UPDATE %TABLE_PREFIX%search_key 
  SET sek_lookup_function = 'Status::getTitle' 
  WHERE sek_title = 'Status';

UPDATE %TABLE_PREFIX%search_key 
  SET sek_lookup_function = 'Object_Type::getTitle' 
  WHERE sek_title = 'Object Type';

UPDATE %TABLE_PREFIX%search_key 
  SET sek_lookup_function = 'XSD_Display::getTitle' 
  WHERE sek_title = 'Display Type';

UPDATE %TABLE_PREFIX%search_key 
  SET sek_lookup_function = 'User::getFullName' 
  WHERE sek_title = 'Depositor';

UPDATE %TABLE_PREFIX%search_key 
  SET sek_lookup_function = 'User::getFullName' 
  WHERE sek_title = 'Assigned User ID';

UPDATE %TABLE_PREFIX%search_key 
  SET sek_lookup_function = 'Group::getTitle' 
  WHERE sek_title = 'Assigned Group ID';

UPDATE %TABLE_PREFIX%search_key 
  SET sek_lookup_function = 'Author::getFullName' 
  WHERE sek_title = 'Author ID';
