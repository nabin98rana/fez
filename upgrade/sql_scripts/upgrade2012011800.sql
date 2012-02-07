ALTER TABLE %TABLE_PREFIX%rid_jobs 
ADD rij_response_profilelink VARCHAR(255) NULL, 
ADD rij_response_profilexml BLOB NULL, 
ADD rij_response_publicationslink VARCHAR(255) NULL, 
ADD rij_response_publicationsxml BLOB NULL, 
ADD rij_time_xmlcleaned DATETIME NULL;
