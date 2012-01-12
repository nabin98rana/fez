CREATE TABLE %TABLE_PREFIX%rid_profile_uploads (
  rpu_id int(11) NOT NULL AUTO_INCREMENT,
  rpu_email_filename varchar(255) NOT NULL,
  rpu_email_file_date datetime NOT NULL,
  rpu_response_url varchar(255) NOT NULL,
  rpu_response blob NOT NULL,
  rpu_created_date datetime NOT NULL,
  rpu_updated_date datetime NOT NULL,
  PRIMARY KEY (rpu_id),
  UNIQUE KEY rpu_id (rpu_id)
);


CREATE TABLE %TABLE_PREFIX%rid_registrations (
  rre_id int(11) NOT NULL AUTO_INCREMENT,
  rre_aut_id int(11) NOT NULL,
  rre_response blob NOT NULL,
  rre_created_date datetime NOT NULL,
  rre_updated_date datetime NOT NULL,
  PRIMARY KEY (rre_id),
  UNIQUE KEY rre_id (rre_id),
  KEY rre_aut_id (rre_aut_id)
);
