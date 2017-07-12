CREATE TABLE %TABLE_PREFIX%jobs (
  id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  queue varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  payload longtext COLLATE utf8_unicode_ci NOT NULL,
  attempts tinyint(3) unsigned NOT NULL,
  reserved_at int(10) unsigned DEFAULT NULL,
  available_at int(10) unsigned NOT NULL,
  created_at int(10) unsigned NOT NULL,
  PRIMARY KEY (id),
  KEY fez_jobs_queue_reserved_at_index (queue,reserved_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;