DROP TABLE IF EXISTS %TABLE_PREFIX%digital_object;
CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%pid_gen (
  pdg_namespace varchar(255) NOT NULL,
  pdg_highest_id int(11) NOT NULL,
  PRIMARY KEY (pdg_namespace)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
INSERT INTO %TABLE_PREFIX%pid_gen
SELECT SUBSTRING_INDEX(rek_pid, ':', 1) AS ns,  MAX((SUBSTRING_INDEX(rek_pid, ':', -1)) + 0)  FROM fez_record_search_key GROUP BY ns HAVING ns != '';
REPLACE INTO %TABLE_PREFIX%pid_gen
SELECT SUBSTRING_INDEX(rek_pid, ':', 1) AS ns,  MAX((SUBSTRING_INDEX(rek_pid, ':', -1)) + 0)  FROM fez_record_search_key__shadow GROUP BY ns HAVING ns != '';
