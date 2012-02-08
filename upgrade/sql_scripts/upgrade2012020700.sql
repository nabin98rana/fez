CREATE TABLE %TABLE_PREFIX%record_search_key_conference_id (
  rek_conference_id_id int(11) NOT NULL AUTO_INCREMENT,
  rek_conference_id_pid varchar(64) DEFAULT NULL,
  rek_conference_id_xsdmf_id int(11) DEFAULT NULL,
  rek_conference_id int(11) DEFAULT NULL,
  PRIMARY KEY (rek_conference_id_id),
  KEY rek_conference_id (rek_conference_id),
  KEY rek_conference_id_pid (rek_conference_id_pid)
);

CREATE TABLE %TABLE_PREFIX%record_search_key_publisher_id (
  rek_publisher_id_id int(11) NOT NULL AUTO_INCREMENT,
  rek_publisher_id_pid varchar(64) DEFAULT NULL,
  rek_publisher_id_xsdmf_id int(11) DEFAULT NULL,
  rek_publisher_id int(11) DEFAULT NULL,
  PRIMARY KEY (rek_publisher_id_id),
  KEY rek_publisher_id (rek_publisher_id),
  KEY rek_publisher_id_pid (rek_publisher_id_pid)
);

CREATE TABLE %TABLE_PREFIX%conference_id (
  cfi_id int(11) NOT NULL AUTO_INCREMENT,
  cfi_conference_name varchar(255) NOT NULL,
  cfi_created_date date DEFAULT NULL,
  cfi_updated_date date DEFAULT NULL,
  cfi_details_by varchar(255) DEFAULT NULL,
  PRIMARY KEY (cfi_id),
  KEY idx_cfi_conference_id (cfi_id)
);

CREATE TABLE %TABLE_PREFIX%publisher (
  pub_id int(11) NOT NULL AUTO_INCREMENT,
  pub_name varchar(255) DEFAULT NULL,
  pub_created_date datetime DEFAULT NULL,
  pub_updated_date datetime DEFAULT NULL,
  pub_details_by varchar(255) DEFAULT NULL,
  PRIMARY KEY (pub_id),
  UNIQUE KEY pud_id (pub_id)
);