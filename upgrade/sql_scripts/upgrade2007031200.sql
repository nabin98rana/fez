

ALTER TABLE %TABLE_PREFIX%search_key
  add column `sek_alt_title` varchar(64) default NULL after `sek_title`,
  add column `sek_myfez_visible` tinyint(1) default NULL after `sek_simple_used`;



