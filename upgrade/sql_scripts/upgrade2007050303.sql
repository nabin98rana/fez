

UPDATE `%TABLE_PREFIX%xsd_display_matchfields` 
SET xsdmf_indexed = 1,
xsdmf_data_type = 'int',
xsdmf_sek_id = 26
where xsdmf_element = '!name!ID';