CREATE OR REPLACE VIEW %TABLE_PREFIX%era_matched_journals AS
	SELECT
		mtj_pid AS pid,
		mtj_eraid AS eraid
	FROM
		%TABLE_PREFIX%matched_journals
	WHERE
		mtj_status != 'B';

CREATE OR REPLACE VIEW %TABLE_PREFIX%era_matched_conferences AS
	SELECT
		mtc_pid AS pid,
		mtc_eraid AS eraid
	FROM
		%TABLE_PREFIX%matched_conferences
	WHERE
		mtc_status != 'B';
