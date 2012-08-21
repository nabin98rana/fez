CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%auth_quick_rules (
  qac_id int(11) unsigned NOT NULL DEFAULT '0',
  qac_role int(11) DEFAULT NULL,
  qac_arg_id int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%auth_quick_rules_id (
  qai_id int(11) DEFAULT NULL,
  qai_title varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%auth_quick_rules_pid (
  qrp_pid varchar(255) DEFAULT NULL,
  qrp_qac_id int(11) unsigned DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
