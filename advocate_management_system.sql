/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19-11.4.4-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: advocate_management_system
-- ------------------------------------------------------
-- Server version	11.4.4-MariaDB-3

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*M!100616 SET @OLD_NOTE_VERBOSITY=@@NOTE_VERBOSITY, NOTE_VERBOSITY=0 */;

--
-- Table structure for table `advocates`
--

DROP TABLE IF EXISTS `advocates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `advocates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `license_number` varchar(50) NOT NULL,
  `specialization` varchar(100) DEFAULT NULL,
  `experience_years` int(11) DEFAULT NULL,
  `education` text DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `hourly_rate` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  UNIQUE KEY `license_number` (`license_number`),
  CONSTRAINT `advocates_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `advocates`
--

LOCK TABLES `advocates` WRITE;
/*!40000 ALTER TABLE `advocates` DISABLE KEYS */;
INSERT INTO `advocates` VALUES
(25,42,'3','Land Services and Estate Purchasing',3,'3','',3.00);
/*!40000 ALTER TABLE `advocates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `appointments`
--

DROP TABLE IF EXISTS `appointments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `appointments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `advocate_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `case_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `appointment_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `status` enum('pending','confirmed','cancelled','completed') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `advocate_id` (`advocate_id`),
  KEY `client_id` (`client_id`),
  KEY `case_id` (`case_id`),
  CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`advocate_id`) REFERENCES `advocates` (`id`),
  CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`),
  CONSTRAINT `appointments_ibfk_3` FOREIGN KEY (`case_id`) REFERENCES `cases` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `appointments`
--

LOCK TABLES `appointments` WRITE;
/*!40000 ALTER TABLE `appointments` DISABLE KEYS */;
/*!40000 ALTER TABLE `appointments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `case_history`
--

DROP TABLE IF EXISTS `case_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `case_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `case_id` int(11) NOT NULL,
  `action_type` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `performed_by` int(11) NOT NULL,
  `action_date` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `case_id` (`case_id`),
  KEY `performed_by` (`performed_by`),
  CONSTRAINT `case_history_ibfk_1` FOREIGN KEY (`case_id`) REFERENCES `cases` (`id`) ON DELETE CASCADE,
  CONSTRAINT `case_history_ibfk_2` FOREIGN KEY (`performed_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `case_history`
--

LOCK TABLES `case_history` WRITE;
/*!40000 ALTER TABLE `case_history` DISABLE KEYS */;
INSERT INTO `case_history` VALUES
(1,3,'Case created','New case has been created',42,'2025-04-12 17:41:10'),
(2,3,'Case updated','Case details have been updated',42,'2025-04-12 18:09:37'),
(3,3,'Case updated','Case details have been updated',42,'2025-04-12 18:09:48'),
(4,3,'Case updated','Case details have been updated',42,'2025-04-12 18:10:25'),
(5,3,'Case updated','Case details have been updated',42,'2025-04-12 18:13:40'),
(6,2,'Case updated','Case details have been updated',42,'2025-04-12 18:16:58'),
(7,3,'Case updated','Case details have been updated',42,'2025-04-12 18:17:56'),
(8,2,'Case updated','Case details have been updated',42,'2025-04-12 18:21:03'),
(9,1,'Case updated','Case details have been updated',42,'2025-04-12 18:21:52'),
(10,2,'Document uploaded','Document \'df\' has been uploaded',42,'2025-04-12 18:57:26'),
(11,2,'Document uploaded','Document \'df\' was uploaded',42,'2025-04-12 18:57:26'),
(12,3,'Document uploaded','Document \'  70 year old  metastatic melanoma patient was looking for experts on Hyperbaric Oxygen Therapy.\' has been uploaded',42,'2025-04-12 19:46:56'),
(13,3,'Document uploaded','Document \'  70 year old  metastatic melanoma patient was looking for experts on Hyperbaric Oxygen Therapy.\' was uploaded',42,'2025-04-12 19:46:56'),
(14,2,'Event created','Event \' some of the companies, \' scheduled for 2025-04-14',42,'2025-04-12 21:28:06'),
(15,3,'Task created','Task \'to call the media\' has been created',42,'2025-04-13 17:49:11'),
(16,3,'Task created','Task \'to call the media\' was created',42,'2025-04-13 17:49:11'),
(17,3,'Task created','Task \'fgfgfg\' has been created',42,'2025-04-13 17:49:33'),
(18,3,'Task created','Task \'fgfgfg\' was created',42,'2025-04-13 17:49:33'),
(19,4,'Case created','New case has been created',42,'2025-04-13 17:51:01'),
(20,4,'Task created','Task \'He had taken her to the doctor several times and not tests were ever done.  Finally he took her to the local ER when she was in acute distress. Tests were done and a biopsy was preformed.\' has been created',42,'2025-04-13 17:51:50'),
(21,4,'Task created','Task \'He had taken her to the doctor several times and not tests were ever done.  Finally he took her to the local ER when she was in acute distress. Tests were done and a biopsy was preformed.\' was created',42,'2025-04-13 17:51:50'),
(22,4,'Task updated','Task \'He had taken her to the doctor several times and not tests were ever done.  Finally he took her to the local ER when she was in acute distress. Tests were done and a biopsy was preformed.\' has been updated',42,'2025-04-13 18:11:27'),
(23,4,'Task updated','Task \'He had taken her to the doctor several times and not tests were ever done.  Finally he took her to the local ER when she was in acute distress. Tests were done and a biopsy was preformed.\' was updated',42,'2025-04-13 18:11:27'),
(24,4,'Task updated','Task \'He had taken her to the doctor several times and not tests were ever done.  Finally he took her to the local ER when she was in acute distress. Tests were done and a biopsy was preformed.\' has been updated',42,'2025-04-13 18:11:33'),
(25,4,'Task completed','Task \'He had taken her to the doctor several times and not tests were ever done.  Finally he took her to the local ER when she was in acute distress. Tests were done and a biopsy was preformed.\' was marked as completed',42,'2025-04-13 18:11:33'),
(26,4,'Task updated','Task \'He had taken her to the doctor several times and not tests were ever done.  Finally he took her to the local ER when she was in acute distress. Tests were done and a biopsy was preformed.\' has been updated',42,'2025-04-13 18:11:43'),
(27,4,'Task completed','Task \'He had taken her to the doctor several times and not tests were ever done.  Finally he took her to the local ER when she was in acute distress. Tests were done and a biopsy was preformed.\' was marked as completed',42,'2025-04-13 18:11:43'),
(28,4,'Task updated','Task \'He had taken her to the doctor several times and not tests were ever done.  Finally he took her to the local ER when she was in acute distress. Tests were done and a biopsy was preformed.\' has been updated',42,'2025-04-13 18:23:02'),
(29,4,'Task completed','Task \'He had taken her to the doctor several times and not tests were ever done.  Finally he took her to the local ER when she was in acute distress. Tests were done and a biopsy was preformed.\' was marked as completed',42,'2025-04-13 18:23:02'),
(30,4,'Task updated','Task \'He had taken her to the doctor several times and not tests were ever done.  Finally he took her to the local ER when she was in acute distress. Tests were done and a biopsy was preformed.\' has been updated',42,'2025-04-13 18:23:10'),
(31,4,'Task completed','Task \'He had taken her to the doctor several times and not tests were ever done.  Finally he took her to the local ER when she was in acute distress. Tests were done and a biopsy was preformed.\' was marked as completed',42,'2025-04-13 18:23:10'),
(32,4,'Task updated','Task \'He had taken her to the doctor several times and not tests were ever done.  Finally he took her to the local ER when she was in acute distress. Tests were done and a biopsy was preformed.\' has been updated',42,'2025-04-13 18:23:52'),
(33,4,'Task completed','Task \'He had taken her to the doctor several times and not tests were ever done.  Finally he took her to the local ER when she was in acute distress. Tests were done and a biopsy was preformed.\' was marked as completed',42,'2025-04-13 18:23:52'),
(34,4,'Task updated','Task \'He had taken her to the doctor several times and not tests were ever done.  Finally he took her to the local ER when she was in acute distress. Tests were done and a biopsy was preformed.\' has been updated',42,'2025-04-13 18:25:04'),
(35,4,'Task completed','Task \'He had taken her to the doctor several times and not tests were ever done.  Finally he took her to the local ER when she was in acute distress. Tests were done and a biopsy was preformed.\' was marked as completed',42,'2025-04-13 18:25:04'),
(36,4,'Task updated','Task \'He had taken her to the doctor several times and not tests were ever done.  Finally he took her to the local ER when she was in acute distress. Tests were done and a biopsy was preformed.\' has been updated',42,'2025-04-13 18:26:51'),
(37,4,'Task completed','Task \'He had taken her to the doctor several times and not tests were ever done.  Finally he took her to the local ER when she was in acute distress. Tests were done and a biopsy was preformed.\' was marked as completed',42,'2025-04-13 18:26:51'),
(38,4,'Task updated','Task \'He had taken her to the doctor several times and not tests were ever done.  Finally he took her to the local ER when she was in acute distress. Tests were done and a biopsy was preformed.\' has been updated',42,'2025-04-13 18:27:04'),
(39,4,'Task completed','Task \'He had taken her to the doctor several times and not tests were ever done.  Finally he took her to the local ER when she was in acute distress. Tests were done and a biopsy was preformed.\' was marked as completed',42,'2025-04-13 18:27:04'),
(40,4,'Task updated','Task \'He had taken her to the doctor several times and not tests were ever done.  Finally he took her to the local ER when she was in acute distress. Tests were done and a biopsy was preformed.\' has been updated',42,'2025-04-13 18:28:28'),
(41,4,'Task completed','Task \'He had taken her to the doctor several times and not tests were ever done.  Finally he took her to the local ER when she was in acute distress. Tests were done and a biopsy was preformed.\' was marked as completed',42,'2025-04-13 18:28:28'),
(42,4,'Task completed','Task \'He had taken her to the doctor several times and not tests were ever done.  Finally he took her to the local ER when she was in acute distress. Tests were done and a biopsy was preformed.\' has been marked as completed',42,'2025-04-13 18:38:45'),
(43,4,'Task completed','Task \'He had taken her to the doctor several times and not tests were ever done.  Finally he took her to the local ER when she was in acute distress. Tests were done and a biopsy was preformed.\' was marked as completed',42,'2025-04-13 18:38:45'),
(44,4,'Task updated','Task \'He had taken her to the doctor several times and not tests were ever done.  Finally he took her to the local ER when she was in acute distress. Tests were done and a biopsy was preformed.\' has been updated',42,'2025-04-13 18:39:13'),
(45,4,'Task updated','Task \'He had taken her to the doctor several times and not tests were ever done.  Finally he took her to the local ER when she was in acute distress. Tests were done and a biopsy was preformed.\' was updated',42,'2025-04-13 18:39:13'),
(46,4,'Event created','Event \'Arranged for a second opinion at Stanford\' scheduled for 2025-04-15',42,'2025-04-13 19:41:46');
/*!40000 ALTER TABLE `case_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cases`
--

DROP TABLE IF EXISTS `cases`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cases` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `case_number` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `client_id` int(11) NOT NULL,
  `advocate_id` int(11) NOT NULL,
  `case_type` varchar(100) NOT NULL,
  `court_name` varchar(100) DEFAULT NULL,
  `filing_date` date DEFAULT NULL,
  `hearing_date` date DEFAULT NULL,
  `status` enum('pending','active','closed','won','lost') DEFAULT 'pending',
  `priority` enum('low','medium','high') DEFAULT 'medium',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `case_number` (`case_number`),
  KEY `client_id` (`client_id`),
  KEY `advocate_id` (`advocate_id`),
  CONSTRAINT `cases_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`),
  CONSTRAINT `cases_ibfk_2` FOREIGN KEY (`advocate_id`) REFERENCES `advocates` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cases`
--

LOCK TABLES `cases` WRITE;
/*!40000 ALTER TABLE `cases` DISABLE KEYS */;
INSERT INTO `cases` VALUES
(1,'CASE-2025-19235','testing ','Creates an array of client IDs the advocate has worked with for quick lookup',3,25,'Labor','Nyandungu','2025-04-10','2025-04-11','closed','medium','2025-04-12 17:29:58','2025-04-12 18:21:52'),
(2,'CASE-2025-61285','testing ','Creates an array of client IDs the advocate has worked with for quick lookup',3,25,'Labor','Nyandungu','2025-04-10','2025-04-11','lost','high','2025-04-12 17:31:32','2025-04-12 18:21:03'),
(3,'CASE-2025-56146','testing te case','Creates an array of client IDs the advocate has worked with for quick lookup',3,25,'Labor','Nyandungu','2025-04-10','2025-04-11','won','medium','2025-04-12 17:41:10','2025-04-12 18:17:56'),
(4,'CASE-2025-81554','Son has 84 year old Mom with intense “belly” pain','He had taken her to the doctor several times and not tests were ever done.  Finally he took her to the local ER when she was in acute distress. Tests were done and a biopsy was preformed.\r\n\r\n',2,25,'Family','Nyandungu','2025-04-15','2025-04-15','pending','low','2025-04-13 17:51:01','2025-04-13 17:51:01');
/*!40000 ALTER TABLE `cases` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `clients`
--

DROP TABLE IF EXISTS `clients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `clients` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `occupation` varchar(100) DEFAULT NULL,
  `company` varchar(100) DEFAULT NULL,
  `reference_source` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  CONSTRAINT `clients_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `clients`
--

LOCK TABLES `clients` WRITE;
/*!40000 ALTER TABLE `clients` DISABLE KEYS */;
INSERT INTO `clients` VALUES
(1,3,'Stuent','IPRC Kigali College','sdsdsdssdd',''),
(2,4,'Stuent','IPRC Kigali College','sdsdsdssdd',''),
(3,7,'Stuent','IPRC Kigali College','sdsdsdshjjhjhjhjh',''),
(4,39,'Stuent','IPRC Kigali College','sdsdsdssdd',''),
(5,41,'Stuent','IPRC Kigali College','sdsdsdssdd','');
/*!40000 ALTER TABLE `clients` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `documents`
--

DROP TABLE IF EXISTS `documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `documents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `case_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_type` varchar(50) DEFAULT NULL,
  `file_size` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `uploaded_by` int(11) NOT NULL,
  `upload_date` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `case_id` (`case_id`),
  KEY `uploaded_by` (`uploaded_by`),
  CONSTRAINT `documents_ibfk_1` FOREIGN KEY (`case_id`) REFERENCES `cases` (`id`) ON DELETE CASCADE,
  CONSTRAINT `documents_ibfk_2` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `documents`
--

LOCK TABLES `documents` WRITE;
/*!40000 ALTER TABLE `documents` DISABLE KEYS */;
INSERT INTO `documents` VALUES
(1,2,'document for case','','pdf',183581,'document for case document for casedocument for case document for case',42,'2025-04-12 18:39:23'),
(2,2,'df','uploads/documents/67fab7558195b_Itangazo ry&#039;igikorwa cyo kwibuka kuwa 5 tariki ya 11 Mata 2025.pdf','pdf',183581,'dfdf',42,'2025-04-12 18:56:21'),
(3,2,'df','uploads/documents/67fab7969cf79_Itangazo ry&#039;igikorwa cyo kwibuka kuwa 5 tariki ya 11 Mata 2025.pdf','pdf',183581,'dfdf',42,'2025-04-12 18:57:26'),
(4,3,'  70 year old  metastatic melanoma patient was looking for experts on Hyperbaric Oxygen Therapy.','uploads/documents/67fac33057581_Itangazo ry&#039;igikorwa cyo kwibuka kuwa 5 tariki ya 11 Mata 2025.pdf','pdf',183581,'Patient has been a client of Patient Advocate for many years so their medical records were organized in MyMedicalRecords.com electronic records management system.\r\nResearch was done to determine where the most expert doctor was located for this particular hyperbaric treatment.\r\nRecords were sent, coordination of appointments and travel was arranged.\r\nPatient Advocate  escorted this patient out of state for this treatment, and coordinated tests and treatment plan with this medical team.\r\nPatient Advocate continued to monitor the patient during his treatment process.',42,'2025-04-12 19:46:56');
/*!40000 ALTER TABLE `documents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `events`
--

DROP TABLE IF EXISTS `events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `event_date` date NOT NULL,
  `event_time` time NOT NULL,
  `end_time` time DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `case_id` int(11) DEFAULT NULL,
  `client_id` int(11) DEFAULT NULL,
  `advocate_id` int(11) DEFAULT NULL,
  `event_type` varchar(50) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Scheduled',
  `reminder_sent` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `case_id` (`case_id`),
  KEY `client_id` (`client_id`),
  KEY `advocate_id` (`advocate_id`),
  CONSTRAINT `events_ibfk_1` FOREIGN KEY (`case_id`) REFERENCES `cases` (`id`) ON DELETE SET NULL,
  CONSTRAINT `events_ibfk_2` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE SET NULL,
  CONSTRAINT `events_ibfk_3` FOREIGN KEY (`advocate_id`) REFERENCES `advocates` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `events`
--

LOCK TABLES `events` WRITE;
/*!40000 ALTER TABLE `events` DISABLE KEYS */;
INSERT INTO `events` VALUES
(1,'meeat with client','efsdsdsdsdsdsssdsdmeeat with client meeat with client meeat with client meeat with client meeat with client','2025-04-04','18:06:00','20:05:00','kigali Rwanda',NULL,NULL,25,'Reminder','Scheduled',0,'2025-04-12 16:11:49','2025-04-12 16:11:49'),
(2,'talk with fils ','I want to meet with him','2025-04-13','18:23:00','20:23:00','Kicukiro',NULL,NULL,25,'Meeting','Scheduled',0,'2025-04-12 16:23:25','2025-04-12 16:23:25'),
(3,'coordinated his records','Patient has been a client of Patient Advocate for many years so their medical records were organized in MyMedicalRecords.com electronic records management system.\r\nResearch was done to determine where the most expert doctor was located for this particular hyperbaric treatment.\r\nRecords were sent, coordination of appointments and travel was arranged.','2025-04-12','22:25:00','23:59:00','kigali Rwanda',3,3,25,'Meeting','Scheduled',0,'2025-04-12 20:19:30','2025-04-12 20:19:30'),
(4,'coordinated his records','Patient has been a client of Patient Advocate for many years so their medical records were organized in MyMedicalRecords.com electronic records management system.\r\nResearch was done to determine where the most expert doctor was located for this particular hyperbaric treatment.\r\nRecords were sent, coordination of appointments and travel was arranged.','2025-04-12','22:25:00','23:59:00','kigali Rwanda',3,3,25,'Meeting','Scheduled',0,'2025-04-12 20:20:11','2025-04-12 20:20:11'),
(5,' some of the companies, ','nt Advocate for many years so their medical records were organized in MyMedicalRecords.com electronic records management system.\r\nResearch was done to determine where the most expert doctor was located for this particular hyperbaric treatment.\r\nRecords were sent, coordination of appointm','2025-04-14','23:30:00','23:30:00','Kicukiro',2,3,25,'Reminder','Scheduled',0,'2025-04-12 21:27:29','2025-04-12 21:27:29'),
(6,' some of the companies, ','nt Advocate for many years so their medical records were organized in MyMedicalRecords.com electronic records management system.\r\nResearch was done to determine where the most expert doctor was located for this particular hyperbaric treatment.\r\nRecords were sent, coordination of appointm','2025-04-14','23:30:00','23:30:00','Kicukiro',2,3,25,'Reminder','Scheduled',0,'2025-04-12 21:28:06','2025-04-12 21:28:06'),
(7,'Arranged for a second opinion at Stanford','He had been waiting for the newest treatment regime. The doctor has given him the prescription with all the potential side-effects, and now the patient is concerned about starting treatment. His question was: “How do I know if I will be the one to have all these side-effects?”','2025-04-15','12:40:00','13:01:00','Kicukiro',4,3,25,'Deadline','Scheduled',0,'2025-04-13 19:41:46','2025-04-13 19:41:46');
/*!40000 ALTER TABLE `events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `invoices`
--

DROP TABLE IF EXISTS `invoices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `invoices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_number` varchar(50) NOT NULL,
  `case_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `advocate_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `tax_amount` decimal(10,2) DEFAULT 0.00,
  `total_amount` decimal(10,2) NOT NULL,
  `issue_date` date NOT NULL,
  `due_date` date NOT NULL,
  `status` enum('draft','sent','paid','overdue','cancelled') DEFAULT 'draft',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `invoice_number` (`invoice_number`),
  KEY `case_id` (`case_id`),
  KEY `client_id` (`client_id`),
  KEY `advocate_id` (`advocate_id`),
  CONSTRAINT `invoices_ibfk_1` FOREIGN KEY (`case_id`) REFERENCES `cases` (`id`),
  CONSTRAINT `invoices_ibfk_2` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`),
  CONSTRAINT `invoices_ibfk_3` FOREIGN KEY (`advocate_id`) REFERENCES `advocates` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `invoices`
--

LOCK TABLES `invoices` WRITE;
/*!40000 ALTER TABLE `invoices` DISABLE KEYS */;
/*!40000 ALTER TABLE `invoices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `notification_type` varchar(50) DEFAULT NULL,
  `related_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_date` date NOT NULL,
  `payment_method` enum('cash','credit_card','bank_transfer','check','other') NOT NULL,
  `transaction_id` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `invoice_id` (`invoice_id`),
  CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payments`
--

LOCK TABLES `payments` WRITE;
/*!40000 ALTER TABLE `payments` DISABLE KEYS */;
/*!40000 ALTER TABLE `payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tasks`
--

DROP TABLE IF EXISTS `tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tasks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `due_date` date NOT NULL,
  `priority` varchar(50) DEFAULT 'Medium',
  `status` varchar(50) DEFAULT 'Pending',
  `case_id` int(11) DEFAULT NULL,
  `client_id` int(11) DEFAULT NULL,
  `advocate_id` int(11) DEFAULT NULL,
  `assigned_to` int(11) DEFAULT NULL,
  `assigned_by` int(11) DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `case_id` (`case_id`),
  KEY `client_id` (`client_id`),
  KEY `advocate_id` (`advocate_id`),
  KEY `assigned_to` (`assigned_to`),
  KEY `assigned_by` (`assigned_by`),
  CONSTRAINT `tasks_ibfk_1` FOREIGN KEY (`case_id`) REFERENCES `cases` (`id`) ON DELETE SET NULL,
  CONSTRAINT `tasks_ibfk_2` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE SET NULL,
  CONSTRAINT `tasks_ibfk_3` FOREIGN KEY (`advocate_id`) REFERENCES `advocates` (`id`) ON DELETE SET NULL,
  CONSTRAINT `tasks_ibfk_4` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `tasks_ibfk_5` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tasks`
--

LOCK TABLES `tasks` WRITE;
/*!40000 ALTER TABLE `tasks` DISABLE KEYS */;
INSERT INTO `tasks` VALUES
(1,'to call the media','Got a medical history and coordinated his records ','2025-04-12','Medium','Pending',3,NULL,NULL,3,42,NULL,'2025-04-13 17:48:01','2025-04-13 17:48:01'),
(2,'to call the media','Got a medical history and coordinated his records ','2025-04-12','Medium','Pending',3,NULL,NULL,3,42,NULL,'2025-04-13 17:48:26','2025-04-13 17:48:26'),
(3,'to call the media','Got a medical history and coordinated his records ','2025-04-12','Medium','Pending',3,NULL,NULL,3,42,NULL,'2025-04-13 17:49:11','2025-04-13 17:49:11'),
(4,'fgfgfg','gggggggggg','2025-04-21','High','Pending',3,NULL,NULL,42,42,NULL,'2025-04-13 17:49:33','2025-04-13 17:49:33'),
(5,'He had taken her to the doctor several times and not tests were ever done.  Finally he took her to the local ER when she was in acute distress. Tests were done and a biopsy was preformed.','He had taken her to the doctor several times and not tests were ever done.  Finally he took her to the local ER when she was in acute distress. Tests were done and a biopsy was preformed.\r\n\r\n','2025-04-21','Medium','Completed',4,NULL,NULL,42,42,'2025-04-13 18:38:45','2025-04-13 17:51:50','2025-04-13 18:38:45');
/*!40000 ALTER TABLE `tasks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` enum('admin','advocate','client') NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES
(1,'admin','$2y$10$Ewb73cK6DuX9zUIxq.i.eO4tmJcnaq/U2lWItHElR8g10SlElKiEW','admin@example.com','admin','System','Administrator',NULL,NULL,NULL,1,'2025-03-30 22:02:08','2025-03-31 00:09:07'),
(3,'sincere','$2y$10$IiGtD1vf8bN4qUltTrpQzu.7d/BSyzOJcPNNI7Mx1lerDZ96a5uky','abayosincere@gmail.com','client','Abirebeye','Margot','0722332222','kicukiro',NULL,1,'2025-03-30 23:48:02','2025-03-30 23:48:02'),
(4,'client','$2y$10$IyL5lhscSCrtr/EER6G8D.f5DOpDtHqROfdNTBki4cOQpFkZRFchW','client@gmail.com','client','Abirebeye Abayo Sincere Aime Margot','Margot','0732286284','gahanga',NULL,1,'2025-04-09 00:07:57','2025-04-09 00:07:57'),
(7,'afro','$2y$10$VY0gOi/mpHROE8yk1y4kuuz8aermn0gz3EJ1vRfHH0JqCflHjCzgK','abayosinc@gmail.com','client','Abirebeye','Margot','078965412','',NULL,1,'2025-04-10 08:16:21','2025-04-12 18:08:06'),
(39,'fils962501','$2y$10$fHQfqc6OBZU8KWQd0JqPo.YQcoA9hvBYYMJC0yoJRxDbfX.cnu39.','fils@gmail.com630718','client','Abirebeye Abayo Sincere Aime Margot','Margot','0732286284','gahanga',NULL,1,'2025-04-12 14:47:57','2025-04-12 14:47:57'),
(41,'advocate917905','$2y$10$sCwV8IYi79T79WltghUuuu9altBZNLG1PgQr27FyKA7ZujjRx1jKW','advocate@gmail.com150315','client','Abirebeye Abayo Sincere Aime Margot','Sincere Aime Margot','','',NULL,1,'2025-04-12 15:00:26','2025-04-12 15:00:26'),
(42,'advocate180','$2y$10$NK99GPk51nsPQcIg.JX58eO33zldppKXk0BvsCN3L/SujS9qWlc6O','advocate@gmail.com858','advocate','Abirebeye Abayo Sincere Aime Margot','Margot','0732286284','gahanga',NULL,1,'2025-04-12 15:02:23','2025-04-12 15:02:23');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*M!100616 SET NOTE_VERBOSITY=@OLD_NOTE_VERBOSITY */;

-- Dump completed on 2025-04-18 19:10:37
