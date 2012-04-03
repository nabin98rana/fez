ALTER TABLE %TABLE_PREFIX%rid_profile_uploads 
CHANGE `rpu_response` `rpu_response` MEDIUMBLOB;

ALTER TABLE %TABLE_PREFIX%rid_jobs 
CHANGE `rij_response_profilexml` `rij_response_profilexml` MEDIUMBLOB;

ALTER TABLE %TABLE_PREFIX%rid_jobs 
CHANGE `rij_response_publicationsxml` `rij_response_publicationsxml` MEDIUMBLOB;

ALTER TABLE %TABLE_PREFIX%rid_registrations
CHANGE `rre_response` `rre_response` MEDIUMBLOB;

