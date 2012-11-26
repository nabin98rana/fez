CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%auth_datastream_index2 (
  authdi_did varchar(64) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  authdi_role int(11) unsigned NOT NULL DEFAULT '0',
  authdi_arg_id int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (authdi_did,authdi_role,authdi_arg_id)
);

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%auth_datastream_index2_not_inherited (
  authdii_did varchar(64) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  authdii_role int(11) unsigned NOT NULL DEFAULT '0',
  authdii_arg_id int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (authdii_did,authdii_role,authdii_arg_id),
  KEY authii_role_arg_id (authdii_role,authdii_arg_id),
  KEY authii_role (authdii_did,authdii_role),
  KEY authii_pid_arg_id (authdii_did,authdii_arg_id),
  KEY authii_pid (authdii_did),
  KEY authii_arg_id (authdii_arg_id)
);

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%auth_index2_not_inherited (
  authii_pid varchar(64) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  authii_role int(11) unsigned NOT NULL DEFAULT '0',
  authii_arg_id int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (authii_pid,authii_role,authii_arg_id),
  KEY authii_role_arg_id (authii_role,authii_arg_id),
  KEY authii_role (authii_pid,authii_role),
  KEY authii_pid_arg_id (authii_pid,authii_arg_id),
  KEY authii_pid (authii_pid),
  KEY authii_arg_id (authii_arg_id)
);

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%auth_index2_not_inherited__shadow (
  authii_pid varchar(64) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  authii_role int(11) unsigned NOT NULL DEFAULT '0',
  authii_arg_id int(11) unsigned NOT NULL DEFAULT '0',
  authii_edition_stamp datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (authii_pid,authii_role,authii_arg_id,authii_edition_stamp),
  KEY authii_role_arg_id (authii_role,authii_arg_id),
  KEY authii_role (authii_pid,authii_role),
  KEY authii_pid_arg_id (authii_pid,authii_arg_id),
  KEY authii_pid (authii_pid),
  KEY authii_arg_id (authii_arg_id)
);

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%auth_datastream_index2_not_inherited__shadow (
  authdii_did varchar(64) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  authdii_role int(11) unsigned NOT NULL DEFAULT '0',
  authdii_arg_id int(11) unsigned NOT NULL DEFAULT '0',
  authdii_edition_stamp datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (authdii_did,authdii_role,authdii_arg_id,authdii_edition_stamp),
  KEY authii_role_arg_id (authdii_role,authdii_arg_id),
  KEY authii_role (authdii_did,authdii_role),
  KEY authii_pid_arg_id (authdii_did,authdii_arg_id),
  KEY authii_pid (authdii_did),
  KEY authii_arg_id (authdii_arg_id)
);