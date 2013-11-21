
CREATE DATABASE  IF NOT EXISTS `examples` /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_bin */;
USE `examples`;

--
-- Table structure for table `cron_jobs`
--

DROP TABLE IF EXISTS `cron_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cron_jobs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `task_class_name` varchar(255) COLLATE utf8_bin NOT NULL,
  `date_last_run` datetime NOT NULL,
  `status` smallint(6) NOT NULL,
  `priority` smallint(6) NOT NULL,
  `params` longtext COLLATE utf8_bin COMMENT '(DC2Type:json_array)',
  `description` varchar(1024) COLLATE utf8_bin DEFAULT NULL,
  `name` varchar(255) COLLATE utf8_bin NOT NULL,
  `run_time` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cron_jobs`
--

LOCK TABLES `cron_jobs` WRITE;
/*!40000 ALTER TABLE `cron_jobs` DISABLE KEYS */;
INSERT INTO `cron_jobs` VALUES (1,'Core_Cron_Task_Test1','2013-11-21 21:21:00',1,1,NULL,'Test task 1','Test1',120),(2,'Core_Cron_Task_Test2','2013-11-21 21:21:00',1,1,'','Test task 2','Test2',60);
/*!40000 ALTER TABLE `cron_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cron_histories`
--

DROP TABLE IF EXISTS `cron_histories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cron_histories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cron_job_id` int(11) NOT NULL,
  `status` smallint(6) NOT NULL,
  `run_date` datetime NOT NULL,
  `message` text COLLATE utf8_bin,
  PRIMARY KEY (`id`),
  KEY `fk_table1_cron_jobs_idx` (`cron_job_id`),
  CONSTRAINT `fk_table1_cron_jobs` FOREIGN KEY (`cron_job_id`) REFERENCES `cron_jobs` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;