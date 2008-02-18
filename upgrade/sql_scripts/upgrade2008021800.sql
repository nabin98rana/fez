ALTER TABLE %TABLE_PREFIX%search_key 
    CHANGE COLUMN sek_id sek_id varchar(64),
    ADD COLUMN sek_namespace varchar(64) after sek_id,
    ADD COLUMN sek_incr_id int(11) after sek_namespace;
    
ALTER TABLE %TABLE_PREFIX%xsd_display_matchfields
    CHANGE COLUMN xsdmf_sek_id xsdmf_sek_id varchar(64);
    
UPDATE %TABLE_PREFIX%search_key SET sek_namespace = 'core', sek_incr_id = sek_id, sek_id = 'core_2' WHERE sek_id = 2;
UPDATE %TABLE_PREFIX%search_key SET sek_namespace = 'core', sek_incr_id = sek_id, sek_id = 'core_3' WHERE sek_id = 3;
UPDATE %TABLE_PREFIX%search_key SET sek_namespace = 'core', sek_incr_id = sek_id, sek_id = 'core_4' WHERE sek_id = 4;
UPDATE %TABLE_PREFIX%search_key SET sek_namespace = 'core', sek_incr_id = sek_id, sek_id = 'core_5' WHERE sek_id = 5;
UPDATE %TABLE_PREFIX%search_key SET sek_namespace = 'core', sek_incr_id = sek_id, sek_id = 'core_6' WHERE sek_id = 6;
UPDATE %TABLE_PREFIX%search_key SET sek_namespace = 'core', sek_incr_id = sek_id, sek_id = 'core_7' WHERE sek_id = 7;
UPDATE %TABLE_PREFIX%search_key SET sek_namespace = 'core', sek_incr_id = sek_id, sek_id = 'core_8' WHERE sek_id = 8;
UPDATE %TABLE_PREFIX%search_key SET sek_namespace = 'core', sek_incr_id = sek_id, sek_id = 'core_9' WHERE sek_id = 9;
UPDATE %TABLE_PREFIX%search_key SET sek_namespace = 'core', sek_incr_id = sek_id, sek_id = 'core_10' WHERE sek_id = 10;
UPDATE %TABLE_PREFIX%search_key SET sek_namespace = 'core', sek_incr_id = sek_id, sek_id = 'core_11' WHERE sek_id = 11;
UPDATE %TABLE_PREFIX%search_key SET sek_namespace = 'core', sek_incr_id = sek_id, sek_id = 'core_12' WHERE sek_id = 12;
UPDATE %TABLE_PREFIX%search_key SET sek_namespace = 'core', sek_incr_id = sek_id, sek_id = 'core_13' WHERE sek_id = 13;
UPDATE %TABLE_PREFIX%search_key SET sek_namespace = 'core', sek_incr_id = sek_id, sek_id = 'core_14' WHERE sek_id = 14;
UPDATE %TABLE_PREFIX%search_key SET sek_namespace = 'core', sek_incr_id = sek_id, sek_id = 'core_15' WHERE sek_id = 15;
UPDATE %TABLE_PREFIX%search_key SET sek_namespace = 'core', sek_incr_id = sek_id, sek_id = 'core_16' WHERE sek_id = 16;
UPDATE %TABLE_PREFIX%search_key SET sek_namespace = 'core', sek_incr_id = sek_id, sek_id = 'core_17' WHERE sek_id = 17;
UPDATE %TABLE_PREFIX%search_key SET sek_namespace = 'core', sek_incr_id = sek_id, sek_id = 'core_18' WHERE sek_id = 18;
UPDATE %TABLE_PREFIX%search_key SET sek_namespace = 'core', sek_incr_id = sek_id, sek_id = 'core_19' WHERE sek_id = 19;
UPDATE %TABLE_PREFIX%search_key SET sek_namespace = 'core', sek_incr_id = sek_id, sek_id = 'core_20' WHERE sek_id = 20;
UPDATE %TABLE_PREFIX%search_key SET sek_namespace = 'core', sek_incr_id = sek_id, sek_id = 'core_21' WHERE sek_id = 21;
UPDATE %TABLE_PREFIX%search_key SET sek_namespace = 'core', sek_incr_id = sek_id, sek_id = 'core_22' WHERE sek_id = 22;
UPDATE %TABLE_PREFIX%search_key SET sek_namespace = 'core', sek_incr_id = sek_id, sek_id = 'core_23' WHERE sek_id = 23;
UPDATE %TABLE_PREFIX%search_key SET sek_namespace = 'core', sek_incr_id = sek_id, sek_id = 'core_24' WHERE sek_id = 24;
UPDATE %TABLE_PREFIX%search_key SET sek_namespace = 'core', sek_incr_id = sek_id, sek_id = 'core_25' WHERE sek_id = 25;
UPDATE %TABLE_PREFIX%search_key SET sek_namespace = 'core', sek_incr_id = sek_id, sek_id = 'core_26' WHERE sek_id = 26;
UPDATE %TABLE_PREFIX%search_key SET sek_namespace = 'core', sek_incr_id = sek_id, sek_id = 'core_27' WHERE sek_id = 27;
UPDATE %TABLE_PREFIX%search_key SET sek_namespace = 'core', sek_incr_id = sek_id, sek_id = 'core_28' WHERE sek_id = 28;
UPDATE %TABLE_PREFIX%search_key SET sek_namespace = 'core', sek_incr_id = sek_id, sek_id = 'core_29' WHERE sek_id = 29;
UPDATE %TABLE_PREFIX%search_key SET sek_namespace = 'core', sek_incr_id = sek_id, sek_id = 'core_30' WHERE sek_id = 30;
UPDATE %TABLE_PREFIX%search_key SET sek_namespace = 'core', sek_incr_id = sek_id, sek_id = 'core_31' WHERE sek_id = 31;
UPDATE %TABLE_PREFIX%search_key SET sek_namespace = 'core', sek_incr_id = sek_id, sek_id = 'core_32' WHERE sek_id = 32;
UPDATE %TABLE_PREFIX%search_key SET sek_namespace = 'core', sek_incr_id = sek_id, sek_id = 'core_33' WHERE sek_id = 33;
UPDATE %TABLE_PREFIX%search_key SET sek_namespace = 'core', sek_incr_id = sek_id, sek_id = 'core_34' WHERE sek_id = 34;
UPDATE %TABLE_PREFIX%search_key SET sek_namespace = 'core', sek_incr_id = sek_id, sek_id = 'core_35' WHERE sek_id = 35;
UPDATE %TABLE_PREFIX%search_key SET sek_namespace = 'core', sek_incr_id = sek_id, sek_id = 'core_36' WHERE sek_id = 36;
UPDATE %TABLE_PREFIX%search_key SET sek_namespace = 'core', sek_incr_id = sek_id, sek_id = 'core_37' WHERE sek_id = 37;
UPDATE %TABLE_PREFIX%search_key SET sek_namespace = 'core', sek_incr_id = sek_id, sek_id = 'core_38' WHERE sek_id = 38;
UPDATE %TABLE_PREFIX%search_key SET sek_namespace = 'core', sek_incr_id = sek_id, sek_id = 'core_39' WHERE sek_id = 39;
UPDATE %TABLE_PREFIX%search_key SET sek_namespace = 'core', sek_incr_id = sek_id, sek_id = 'core_40' WHERE sek_id = 40;
UPDATE %TABLE_PREFIX%search_key SET sek_namespace = 'core', sek_incr_id = sek_id, sek_id = 'core_41' WHERE sek_id = 41;
UPDATE %TABLE_PREFIX%search_key SET sek_namespace = 'core', sek_incr_id = sek_id, sek_id = 'core_42' WHERE sek_id = 42;
UPDATE %TABLE_PREFIX%search_key SET sek_namespace = 'core', sek_incr_id = sek_id, sek_id = 'core_43' WHERE sek_id = 43;
UPDATE %TABLE_PREFIX%search_key SET sek_namespace = 'core', sek_incr_id = sek_id, sek_id = 'core_44' WHERE sek_id = 44;
UPDATE %TABLE_PREFIX%search_key SET sek_namespace = 'core', sek_incr_id = sek_id, sek_id = 'core_45' WHERE sek_id = 45;
UPDATE %TABLE_PREFIX%search_key SET sek_namespace = 'core', sek_incr_id = sek_id, sek_id = 'core_46' WHERE sek_id = 46;
UPDATE %TABLE_PREFIX%search_key SET sek_namespace = 'core', sek_incr_id = sek_id, sek_id = 'core_47' WHERE sek_id = 47;
UPDATE %TABLE_PREFIX%search_key SET sek_namespace = 'core', sek_incr_id = sek_id, sek_id = 'core_48' WHERE sek_id = 48;
UPDATE %TABLE_PREFIX%search_key SET sek_namespace = 'core', sek_incr_id = sek_id, sek_id = 'core_49' WHERE sek_id = 49;
UPDATE %TABLE_PREFIX%search_key SET sek_namespace = 'core', sek_incr_id = sek_id, sek_id = 'core_50' WHERE sek_id = 50;
UPDATE %TABLE_PREFIX%search_key SET sek_namespace = 'core', sek_incr_id = sek_id, sek_id = 'core_51' WHERE sek_id = 51;
UPDATE %TABLE_PREFIX%search_key SET sek_namespace = 'core', sek_incr_id = sek_id, sek_id = 'core_52' WHERE sek_id = 52;
UPDATE %TABLE_PREFIX%search_key SET sek_namespace = 'core', sek_incr_id = sek_id, sek_id = 'core_53' WHERE sek_id = 53;
UPDATE %TABLE_PREFIX%search_key SET sek_namespace = 'core', sek_incr_id = sek_id, sek_id = 'core_54' WHERE sek_id = 54;
UPDATE %TABLE_PREFIX%search_key SET sek_namespace = 'core', sek_incr_id = sek_id, sek_id = 'core_55' WHERE sek_id = 55;
UPDATE %TABLE_PREFIX%search_key SET sek_namespace = 'core', sek_incr_id = sek_id, sek_id = 'core_56' WHERE sek_id = 56;
UPDATE %TABLE_PREFIX%search_key SET sek_namespace = 'core', sek_incr_id = sek_id, sek_id = 'core_57' WHERE sek_id = 57;
UPDATE %TABLE_PREFIX%search_key SET sek_namespace = 'core', sek_incr_id = sek_id, sek_id = 'core_58' WHERE sek_id = 58;
UPDATE %TABLE_PREFIX%search_key SET sek_namespace = 'core', sek_incr_id = sek_id, sek_id = 'core_59' WHERE sek_id = 59;
UPDATE %TABLE_PREFIX%search_key SET sek_namespace = 'core', sek_incr_id = sek_id, sek_id = 'core_60' WHERE sek_id = 60;
UPDATE %TABLE_PREFIX%search_key SET sek_namespace = 'core', sek_incr_id = sek_id, sek_id = 'core_61' WHERE sek_id = 61;
UPDATE %TABLE_PREFIX%search_key SET sek_namespace = 'core', sek_incr_id = sek_id, sek_id = 'core_62' WHERE sek_id = 62;
UPDATE %TABLE_PREFIX%search_key SET sek_namespace = 'core', sek_incr_id = sek_id, sek_id = 'core_63' WHERE sek_id = 63;
UPDATE %TABLE_PREFIX%search_key SET sek_namespace = 'core', sek_incr_id = sek_id, sek_id = 'core_64' WHERE sek_id = 64;
UPDATE %TABLE_PREFIX%search_key SET sek_namespace = 'core', sek_incr_id = sek_id, sek_id = 'core_65' WHERE sek_id = 65;
UPDATE %TABLE_PREFIX%search_key SET sek_namespace = 'core', sek_incr_id = sek_id, sek_id = 'core_66' WHERE sek_id = 66;
UPDATE %TABLE_PREFIX%search_key SET sek_namespace = 'core', sek_incr_id = sek_id, sek_id = 'core_67' WHERE sek_id = 67;
UPDATE %TABLE_PREFIX%search_key SET sek_namespace = 'core', sek_incr_id = sek_id, sek_id = 'core_68' WHERE sek_id = 68;
UPDATE %TABLE_PREFIX%search_key SET sek_namespace = 'core', sek_incr_id = sek_id, sek_id = 'core_69' WHERE sek_id = 69;
UPDATE %TABLE_PREFIX%search_key SET sek_namespace = 'core', sek_incr_id = sek_id, sek_id = 'core_70' WHERE sek_id = 70;
UPDATE %TABLE_PREFIX%search_key SET sek_namespace = 'core', sek_incr_id = sek_id, sek_id = 'core_71' WHERE sek_id = 71;
UPDATE %TABLE_PREFIX%search_key SET sek_namespace = 'core', sek_incr_id = sek_id, sek_id = 'core_72' WHERE sek_id = 72;
UPDATE %TABLE_PREFIX%search_key SET sek_namespace = 'core', sek_incr_id = sek_id, sek_id = 'core_73' WHERE sek_id = 73;
UPDATE %TABLE_PREFIX%search_key SET sek_namespace = 'core', sek_incr_id = sek_id, sek_id = 'core_74' WHERE sek_id = 74;
UPDATE %TABLE_PREFIX%search_key SET sek_namespace = 'core', sek_incr_id = sek_id, sek_id = 'core_75' WHERE sek_id = 75;
UPDATE %TABLE_PREFIX%search_key SET sek_namespace = 'core', sek_incr_id = sek_id, sek_id = 'core_76' WHERE sek_id = 76;
UPDATE %TABLE_PREFIX%search_key SET sek_namespace = 'core', sek_incr_id = sek_id, sek_id = 'core_77' WHERE sek_id = 77;

UPDATE %TABLE_PREFIX%search_key SET sek_namespace = '%PID_NAMESPACE%', sek_incr_id = sek_id, sek_id = CONCAT('%PID_NAMESPACE%_',sek_id) WHERE (sek_namespace <> 'core' OR sek_namespace IS NULL);

UPDATE %TABLE_PREFIX%xsd_display_matchfields, %TABLE_PREFIX%search_key SET xsdmf_sek_id = sek_id WHERE xsdmf_sek_id = sek_incr_id;