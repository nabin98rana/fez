update %TABLE_PREFIX%xsd_display_matchfields set xsdmf_validation_regex = '/^2-s2\\.0-[0-9]{10,11}$/' where xsdmf_validation_regex = '/^2-s2.0-[0-9]{10,11}/';

update %TABLE_PREFIX%xsd_display_matchfields set xsdmf_validation_regex = '/^[0-9A-Z]{15}$/' where xsdmf_validation_regex = '/[0-9A-Z]{15}/';
