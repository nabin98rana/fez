ALTER TABLE %TABLE_PREFIX%xsd_display_matchfields ADD xsdmf_validation_regex mediumtext AFTER xsdmf_validation_maxlength ;
ALTER TABLE %TABLE_PREFIX%xsd_display_matchfields ADD xsdmf_validation_message mediumtext AFTER xsdmf_validation_regex ;

UPDATE %TABLE_PREFIX%xsd_display_matchfields SET xsdmf_validation_regex = '/^2-s2\.0-[0-9]{10,11}/',
xsdmf_validation_message = 'Scopus ID must be between 17 and 18 characters in length, beginning with 2-s2.0- and ending only in numbers eg 2-s2.0-0346778587'
WHERE xsdmf_sek_id = 'core_84';

UPDATE %TABLE_PREFIX%xsd_display_matchfields SET xsdmf_validation_regex = '/[0-9A-Z]{15}/',
xsdmf_validation_message = 'ISI LOCs must be 15 characters in length and only letters and numbers eg 000230291200009 or A1996XD58000036'
WHERE xsdmf_sek_id = 'core_65';