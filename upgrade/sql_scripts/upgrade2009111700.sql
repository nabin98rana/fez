UPDATE
	`%TABLE_PREFIX%config`
SET
	config_name = 'app_herdc_support'
WHERE
	config_name = 'app_herdc_integrity_reports'
;