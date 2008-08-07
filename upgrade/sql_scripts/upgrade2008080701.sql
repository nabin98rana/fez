ALTER TABLE %TABLE_PREFIX%background_process
CHANGE `bgp_serialized` `bgp_serialized` LONGTEXT DEFAULT NULL COLLATE utf8_general_ci;
