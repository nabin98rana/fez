CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%file_attachments (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `hash` varchar(50) NOT NULL,
  `filename` varchar(200) NOT NULL,
  `version` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `metaid` int(11) NOT NULL,
  `state` enum('A','D') NOT NULL DEFAULT 'A',
  `size` int(20) NOT NULL DEFAULT '0',
  `pid` varchar(15) NOT NULL DEFAULT '0',
  `mimetype` varchar(100) DEFAULT NULL,
  `controlgroup` char(1) NOT NULL DEFAULT 'M',
  `xdis_id` int(11) DEFAULT '5',
  PRIMARY KEY (`id`)
);

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%file_attachments__shadow (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `hash` varchar(50) NOT NULL,
  `filename` varchar(200) NOT NULL,
  `version` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `metaid` int(11) NOT NULL DEFAULT '0',
  `state` enum('A','D') NOT NULL DEFAULT 'A',
  `size` int(20) NOT NULL DEFAULT '0',
  `pid` varchar(15) NOT NULL DEFAULT '0',
  `mimetype` varchar(100) DEFAULT NULL,
  `controlgroup` char(1) NOT NULL DEFAULT 'M',
  `xdis_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
);

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%digital_object (
  `pidns` varchar(5) NOT NULL,
  `pidint` int(11) NOT NULL,
  `created` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`pidns`,`pidint`)
);