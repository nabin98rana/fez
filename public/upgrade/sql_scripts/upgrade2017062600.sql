ALTER TABLE fez_auth_index2 CHANGE `authi_pid` `authi_pid` VARCHAR(64) NOT NULL  DEFAULT '';
ALTER TABLE fez_auth_index2_lister CHANGE `authi_pid` `authi_pid` VARCHAR(64) NOT NULL  DEFAULT '';
ALTER TABLE fez_auth_rule_group_users CHANGE `argu_usr_id` `argu_usr_id` INT(11)  UNSIGNED  NOT NULL;
ALTER TABLE fez_auth_rule_group_users CHANGE `argu_arg_id` `argu_arg_id` INT(11)  UNSIGNED  NOT NULL;

ALTER TABLE fez_auth_rule_groups CHANGE `arg_id` `arg_id` INT(11)  UNSIGNED  NOT NULL AUTO_INCREMENT;

ALTER TABLE fez_auth_rules CHANGE `ar_id` `ar_id` INT(11)  UNSIGNED  NOT NULL AUTO_INCREMENT;

ALTER TABLE fez_auth_rule_group_rules CHANGE `argr_arg_id` `argr_arg_id` INT(11)  UNSIGNED  NOT NULL;
ALTER TABLE fez_auth_rule_group_rules CHANGE `argr_ar_id` `argr_ar_id` INT(11)  UNSIGNED  NOT NULL;

ALTER TABLE fez_group_user ADD CONSTRAINT `gpu_usr_id__foreign` FOREIGN KEY (`gpu_usr_id`) REFERENCES `fez_user` (`usr_id`);
ALTER TABLE fez_group_user ADD CONSTRAINT `gpu_grp_id__foreign` FOREIGN KEY (`gpu_grp_id`) REFERENCES `fez_group` (`grp_id`);

ALTER TABLE fez_auth_rule_group_users ADD CONSTRAINT `argu_usr_id__foreign` FOREIGN KEY (`argu_usr_id`) REFERENCES `fez_user` (`usr_id`);
ALTER TABLE fez_auth_rule_group_users ADD CONSTRAINT `argu_arg_id__foreign` FOREIGN KEY (`argu_arg_id`) REFERENCES `fez_auth_rule_groups` (`arg_id`);
ALTER TABLE fez_auth_rule_group_rules ADD CONSTRAINT `argr_arg_id__foreign` FOREIGN KEY (`argr_arg_id`) REFERENCES `fez_auth_rule_groups` (`arg_id`);
ALTER TABLE fez_auth_rule_group_rules ADD CONSTRAINT `argr_ar_id__foreign` FOREIGN KEY (`argr_ar_id`) REFERENCES `fez_auth_rules` (`ar_id`);

ALTER TABLE fez_auth_index2 ADD CONSTRAINT `authi_pid__foreign` FOREIGN KEY (`authi_pid`) REFERENCES `fez_record_search_key` (`rek_pid`) ON DELETE CASCADE;
ALTER TABLE fez_auth_index2 ADD CONSTRAINT `authi_arg_id__foreign` FOREIGN KEY (`authi_arg_id`) REFERENCES `fez_auth_rule_groups` (`arg_id`);
ALTER TABLE fez_auth_index2 ADD CONSTRAINT `authi_role__foreign` FOREIGN KEY (`authi_role`) REFERENCES `fez_auth_roles` (`aro_id`);

ALTER TABLE fez_auth_index2_lister ADD CONSTRAINT `authi_pid_lister_foreign` FOREIGN KEY (`authi_pid`) REFERENCES `fez_record_search_key` (`rek_pid`) ON DELETE CASCADE;
ALTER TABLE fez_auth_index2_lister ADD CONSTRAINT `authi_arg_id_lister__foreign` FOREIGN KEY (`authi_arg_id`) REFERENCES `fez_auth_rule_groups` (`arg_id`);