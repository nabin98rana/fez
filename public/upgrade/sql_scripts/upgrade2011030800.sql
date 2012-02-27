UPDATE %TABLE_PREFIX%author
SET aut_org_username = NULL
WHERE aut_org_username = '';

ALTER TABLE %TABLE_PREFIX%author DROP INDEX aut_org_username;

CREATE UNIQUE INDEX aut_org_username ON %TABLE_PREFIX%author (aut_org_username);
