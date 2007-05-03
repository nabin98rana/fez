

UPDATE `%TABLE_PREFIX%xsd_display_matchfields` 
SET xsdmf_indexed = 0, 
xsdmf_sek_id = NULL 
WHERE xsdmf_element = '!subject!topic' and xsdmf_sek_id = 4;
