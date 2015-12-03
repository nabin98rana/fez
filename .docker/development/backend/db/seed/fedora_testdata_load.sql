-- MySQL dump 10.13  Distrib 5.7.9, for osx10.11 (x86_64)
--
-- Host: fedoradb    Database: fedora3
-- ------------------------------------------------------
-- Server version	5.6.27

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `datastreamPaths`
--

DROP TABLE IF EXISTS `datastreamPaths`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `datastreamPaths` (
  `tokenDbID` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(199) NOT NULL DEFAULT '',
  `path` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`tokenDbID`),
  UNIQUE KEY `token` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `datastreamPaths`
--

LOCK TABLES `datastreamPaths` WRITE;
/*!40000 ALTER TABLE `datastreamPaths` DISABLE KEYS */;
/*!40000 ALTER TABLE `datastreamPaths` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dcDates`
--

DROP TABLE IF EXISTS `dcDates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dcDates` (
  `pid` varchar(64) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `dcDate` bigint(20) NOT NULL,
  KEY `pid` (`pid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dcDates`
--

LOCK TABLES `dcDates` WRITE;
/*!40000 ALTER TABLE `dcDates` DISABLE KEYS */;
/*!40000 ALTER TABLE `dcDates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `doFields`
--

DROP TABLE IF EXISTS `doFields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `doFields` (
  `pid` varchar(64) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `label` varchar(255) DEFAULT NULL,
  `state` varchar(1) NOT NULL DEFAULT 'A',
  `ownerId` varchar(64) DEFAULT NULL,
  `cDate` bigint(20) NOT NULL,
  `mDate` bigint(20) NOT NULL,
  `dcmDate` bigint(20) DEFAULT NULL,
  `dcTitle` text,
  `dcCreator` text,
  `dcSubject` text,
  `dcDescription` text,
  `dcPublisher` text,
  `dcContributor` text,
  `dcDate` text,
  `dcType` text,
  `dcFormat` text,
  `dcIdentifier` text,
  `dcSource` text,
  `dcLanguage` text,
  `dcRelation` text,
  `dcCoverage` text,
  `dcRights` text,
  KEY `pid` (`pid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `doFields`
--

LOCK TABLES `doFields` WRITE;
/*!40000 ALTER TABLE `doFields` DISABLE KEYS */;
INSERT INTO `doFields` VALUES ('UQ:3','info:fedora/fedora-system:def/model#label','a','',1448929448693,1448929452030,1448929448693,' test community sample .',NULL,NULL,'  .',NULL,NULL,NULL,' fez_community .',NULL,' uq:3 .',NULL,NULL,NULL,NULL,NULL),('UQ:4','test collection sample','a','',1448929482182,1448929485513,1448929482182,' test collection sample .',NULL,NULL,'  .',NULL,NULL,NULL,' fez_collection .',NULL,' uq:4  .',NULL,NULL,NULL,NULL,NULL),('UQ:5','transposing of water policies from developed to developing countries: the case of user pays','a','',1448930318803,1448930322612,1448930318803,' transposing of water policies from developed to developing countries: the case of user pays .',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,' uq:5 .',NULL,NULL,NULL,NULL,NULL),('fedora-system:ContentModel-3.0','content model object for content model objects','a','fedoraadmin',1214975383796,1449110163390,1214975384015,' content model object for content model objects .',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,' fedora-system:contentmodel-3.0 .',NULL,NULL,NULL,NULL,NULL),('fedora-system:FedoraObject-3.0','content model object for all objects','a','fedoraadmin',1214975383796,1449110163686,1214975384359,' content model object for all objects .',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,' fedora-system:fedoraobject-3.0 .',NULL,NULL,NULL,NULL,NULL),('fedora-system:ServiceDefinition-3.0','content model object for service definition objects','a','fedoraadmin',1214975383796,1449110163763,1214975384375,' content model object for service definition objects .',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,' fedora-system:servicedefinition-3.0 .',NULL,NULL,NULL,NULL,NULL),('fedora-system:ServiceDeployment-3.0','content model object for service deployment objects','a','fedoraadmin',1214975383796,1449110163816,1214975384406,' content model object for service deployment objects .',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,' fedora-system:servicedeployment-3.0 .',NULL,NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `doFields` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `doRegistry`
--

DROP TABLE IF EXISTS `doRegistry`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `doRegistry` (
  `doPID` varchar(64) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `systemVersion` int(11) NOT NULL DEFAULT '0',
  `ownerId` varchar(64) DEFAULT NULL,
  `objectState` varchar(1) NOT NULL DEFAULT 'A',
  `label` varchar(255) DEFAULT '',
  PRIMARY KEY (`doPID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `doRegistry`
--

LOCK TABLES `doRegistry` WRITE;
/*!40000 ALTER TABLE `doRegistry` DISABLE KEYS */;
INSERT INTO `doRegistry` VALUES ('UQ:3',4,'the ownerID field is no longer used','A','the label field is no longer used'),('UQ:4',4,'the ownerID field is no longer used','A','the label field is no longer used'),('UQ:5',4,'the ownerID field is no longer used','A','the label field is no longer used'),('fedora-system:ContentModel-3.0',1,'the ownerID field is no longer used','A','the label field is no longer used'),('fedora-system:FedoraObject-3.0',1,'the ownerID field is no longer used','A','the label field is no longer used'),('fedora-system:ServiceDefinition-3.0',1,'the ownerID field is no longer used','A','the label field is no longer used'),('fedora-system:ServiceDeployment-3.0',1,'the ownerID field is no longer used','A','the label field is no longer used');
/*!40000 ALTER TABLE `doRegistry` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fcrepoRebuildStatus`
--

DROP TABLE IF EXISTS `fcrepoRebuildStatus`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fcrepoRebuildStatus` (
  `rebuildDate` bigint(20) NOT NULL,
  `complete` tinyint(1) NOT NULL,
  UNIQUE KEY `rebuildDate` (`rebuildDate`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fcrepoRebuildStatus`
--

LOCK TABLES `fcrepoRebuildStatus` WRITE;
/*!40000 ALTER TABLE `fcrepoRebuildStatus` DISABLE KEYS */;
INSERT INTO `fcrepoRebuildStatus` VALUES (1448927611721,1);
/*!40000 ALTER TABLE `fcrepoRebuildStatus` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `modelDeploymentMap`
--

DROP TABLE IF EXISTS `modelDeploymentMap`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `modelDeploymentMap` (
  `cModel` varchar(64) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `sDef` varchar(64) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `sDep` varchar(64) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `modelDeploymentMap`
--

LOCK TABLES `modelDeploymentMap` WRITE;
/*!40000 ALTER TABLE `modelDeploymentMap` DISABLE KEYS */;
/*!40000 ALTER TABLE `modelDeploymentMap` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `objectPaths`
--

DROP TABLE IF EXISTS `objectPaths`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `objectPaths` (
  `tokenDbID` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(64) NOT NULL DEFAULT '',
  `path` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`tokenDbID`),
  UNIQUE KEY `token` (`token`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `objectPaths`
--

LOCK TABLES `objectPaths` WRITE;
/*!40000 ALTER TABLE `objectPaths` DISABLE KEYS */;
INSERT INTO `objectPaths` VALUES (5,'UQ:3','/data/objects/2015/1201/10/24/UQ_3'),(6,'UQ:4','/data/objects/2015/1201/10/24/UQ_4'),(7,'UQ:5','/data/objects/2015/1201/10/38/UQ_5'),(12,'fedora-system:ContentModel-3.0','/data/objects/2015/1203/12/36/fedora-system_ContentModel-3.0'),(13,'fedora-system:FedoraObject-3.0','/data/objects/2015/1203/12/36/fedora-system_FedoraObject-3.0'),(14,'fedora-system:ServiceDefinition-3.0','/data/objects/2015/1203/12/36/fedora-system_ServiceDefinition-3.0'),(15,'fedora-system:ServiceDeployment-3.0','/data/objects/2015/1203/12/36/fedora-system_ServiceDeployment-3.0');
/*!40000 ALTER TABLE `objectPaths` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pidGen`
--

DROP TABLE IF EXISTS `pidGen`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pidGen` (
  `namespace` varchar(255) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `highestID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pidGen`
--

LOCK TABLES `pidGen` WRITE;
/*!40000 ALTER TABLE `pidGen` DISABLE KEYS */;
INSERT INTO `pidGen` VALUES ('UQ',5);
/*!40000 ALTER TABLE `pidGen` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2015-12-03 12:42:54
