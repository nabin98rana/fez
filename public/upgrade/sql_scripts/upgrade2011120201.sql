CREATE TABLE `%TABLE_PREFIX%scopus_doctypes` (
  `sdt_id` int(11) NOT NULL AUTO_INCREMENT,
  `sdt_code` varchar(5) DEFAULT NULL,
  `sdt_description` varchar(255) DEFAULT NULL,
  `sdt_created_date` datetime DEFAULT NULL,
  `sdt_updated_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`sdt_id`)
);

INSERT INTO `%TABLE_PREFIX%scopus_doctypes`
(sdt_code, sdt_description, sdt_created_date)
VALUES
 ('ar','Article',NOW()),
 ('ab','Abstract Report',NOW()),
 ('ip','Article in Press',NOW()),
 ('bk','Book',NOW()),
 ('bz','Business Article',NOW()),
 ('cp','Conference Paper',NOW()),
 ('cr','Conference Review',NOW()),
 ('ed','Editorial',NOW()),
 ('er','Erratum',NOW()),
 ('le','Letter',NOW()),
 ('no','Note',NOW()),
 ('pr','Press Release',NOW()),
 ('rp','Report',NOW()),
 ('re','Review',NOW()),
 ('sh','Short Survey',NOW());