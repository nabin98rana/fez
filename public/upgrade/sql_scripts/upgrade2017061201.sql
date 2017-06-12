ALTER TABLE `%TABLE_PREFIX%author` ADD INDEX `aut_email` (`aut_email`);
ALTER TABLE `hr_personal_details_vw` ADD INDEX (`EMAIL`);
ALTER TABLE `%TABLE_PREFIX%user` ADD INDEX (`usr_email`);