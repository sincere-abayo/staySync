/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19-11.4.4-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: hotel_management
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
-- Table structure for table `booking_requests`
--

DROP TABLE IF EXISTS `booking_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `booking_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `booking_id` int(11) NOT NULL,
  `request_text` text NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `booking_id` (`booking_id`),
  CONSTRAINT `booking_requests_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 ;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `booking_requests`
--

LOCK TABLES `booking_requests` WRITE;
/*!40000 ALTER TABLE `booking_requests` DISABLE KEYS */;
INSERT INTO `booking_requests` VALUES
(1,5,'nnmmnhghghghgh','2025-03-06 09:17:40'),
(2,6,'sdvsavasvssdv','2025-03-06 10:16:23'),
(3,7,'jhhjhjfjh hjsjhshd','2025-03-06 14:32:29'),
(4,8,'sdsds dfsdfsd sdfsd','2025-03-06 15:34:49'),
(5,9,'sddf vvzxcv ffas','2025-03-06 15:44:18'),
(6,10,'Special Requests Special Requests','2025-03-06 19:23:11'),
(7,11,'vvxv xcxcvvczxzvxc','2025-03-06 21:52:29'),
(8,12,'dinner','2025-04-01 11:20:04');
/*!40000 ALTER TABLE `booking_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bookings`
--

DROP TABLE IF EXISTS `bookings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bookings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `room_id` int(11) DEFAULT NULL,
  `check_in` date NOT NULL,
  `check_out` date NOT NULL,
  `payment_status` enum('pending','partial','complete') DEFAULT 'pending',
  `booking_status` enum('pending','confirmed','cancelled','completed') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `adults` int(11) NOT NULL DEFAULT 1,
  `kids` int(11) NOT NULL DEFAULT 0,
  `check_in_status` enum('pending','checked_in','checked_out') DEFAULT 'pending',
  `reminder_sent` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `room_id` (`room_id`),
  CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 ;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bookings`
--

LOCK TABLES `bookings` WRITE;
/*!40000 ALTER TABLE `bookings` DISABLE KEYS */;
INSERT INTO `bookings` VALUES
(1,5,75,'2025-03-08','2025-03-12','partial','confirmed','2025-03-06 08:14:33',1,0,'pending',0),
(2,5,77,'2025-03-07','2025-03-08','partial','confirmed','2025-03-06 08:28:48',1,0,'pending',0),
(3,5,77,'2025-03-14','2025-03-11','partial','confirmed','2025-03-06 08:36:39',1,0,'pending',0),
(4,5,75,'2025-03-20','2025-03-21','partial','confirmed','2025-03-06 08:42:00',1,0,'pending',0),
(5,5,74,'2025-03-07','2025-03-12','pending','cancelled','2025-03-06 09:17:40',1,0,'pending',0),
(6,5,77,'2025-03-20','2025-03-23','partial','confirmed','2025-03-06 10:16:23',2,3,'pending',0),
(7,6,75,'2025-03-27','2025-03-29','partial','confirmed','2025-03-06 14:32:29',2,2,'pending',0),
(8,6,74,'2025-04-08','2025-04-18','partial','confirmed','2025-03-06 15:34:49',3,3,'pending',0),
(9,6,33,'2025-04-16','2025-04-20','partial','confirmed','2025-03-06 15:44:18',2,1,'pending',0),
(10,5,74,'2025-03-19','2025-03-26','pending','pending','2025-03-06 19:23:11',2,3,'pending',0),
(11,6,33,'2025-03-07','2025-03-08','partial','confirmed','2025-03-06 21:52:29',3,2,'pending',0),
(12,9,75,'2025-04-04','2025-04-08','partial','confirmed','2025-04-01 11:20:04',2,1,'pending',0);
/*!40000 ALTER TABLE `bookings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `feedback`
--

DROP TABLE IF EXISTS `feedback`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `feedback` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `booking_id` int(11) DEFAULT NULL,
  `rating` int(11) DEFAULT NULL CHECK (`rating` between 1 and 5),
  `comment` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `booking_id` (`booking_id`),
  CONSTRAINT `feedback_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `feedback`
--

LOCK TABLES `feedback` WRITE;
/*!40000 ALTER TABLE `feedback` DISABLE KEYS */;
/*!40000 ALTER TABLE `feedback` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gallery_images`
--

DROP TABLE IF EXISTS `gallery_images`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gallery_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `category` enum('room','service','event','amenity') NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `is_featured` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 ;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gallery_images`
--

LOCK TABLES `gallery_images` WRITE;
/*!40000 ALTER TABLE `gallery_images` DISABLE KEYS */;
INSERT INTO `gallery_images` VALUES
(1,'dssdd','event','uploads/gallery/67c8c42da2a2a.jpg','sdsdsdsdds',1,'2025-03-05 21:32:55'),
(4,' Gallery Image','event','uploads/gallery/67c8c415a71c9.png',' Gallery Image  Gallery Image Gallery Image',1,'2025-03-05 21:37:25'),
(5,' Gallery Image','amenity','uploads/gallery/67c8c7662ee9a.png','fdfdfdfdfdf',1,'2025-03-05 21:51:34'),
(6,' Gallery Image','service','uploads/gallery/67c8c7cac8dde.jpg','fddfd dfdfdfdf',1,'2025-03-05 21:53:14'),
(7,' Gallery Image','room','uploads/gallery/680239043ea66.jpeg','vccvcv dfdfdf',1,'2025-03-05 21:53:46'),
(8,'dssdd','event','uploads/gallery/68023910d1378.jpeg','vvdfdf',1,'2025-04-18 11:35:44'),
(9,' Galle','amenity','uploads/gallery/68023985906ac.jpeg','rtrtrt',1,'2025-04-18 11:37:41');
/*!40000 ALTER TABLE `gallery_images` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hotel_services`
--

DROP TABLE IF EXISTS `hotel_services`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hotel_services` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 ;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hotel_services`
--

LOCK TABLES `hotel_services` WRITE;
/*!40000 ALTER TABLE `hotel_services` DISABLE KEYS */;
INSERT INTO `hotel_services` VALUES
(2,'kljk','fgfgfg','fa-swimming-pool','uploads/services/67c8bca31515a.png','2025-03-05 20:56:20'),
(3,'erererer','ererererer','fa-swimming-pool','uploads/services/67c8bd08c9738.jpg','2025-03-05 21:07:20'),
(5,'Fine Dining','Experience exquisite culinary delights at our signature restaurant featuring international cuisine.','null','uploads/services/67c8bf429a554.png','2025-03-05 21:16:14'),
(6,'Swimming Pool','Take a refreshing dip in our temperature-controlled infinity pool with stunning city views.','null','uploads/services/67c8bf49b650e.png','2025-03-05 21:16:14'),
(7,'Fitness Center','24/7 access to state-of-the-art fitness equipment and personal training services.','null','uploads/services/67c8bfc855c40.jpg','2025-03-05 21:16:14'),
(8,'Concierge Service','Let our experienced concierge team assist you with reservations, tours, and local recommendations.','null','uploads/services/67c8bfd4be5d8.jpg','2025-03-05 21:16:14'),
(9,'Business Center','Full-service business center with meeting rooms and professional support services.','null','uploads/services/67c8bfdb4b047.png','2025-03-05 21:16:14'),
(10,'Valet Parking','Convenient valet parking service available 24/7 for all hotel guests.','null','uploads/services/67c8bfe4acaf7.jpg','2025-03-05 21:16:14'),
(11,'Room Service','24-hour in-room dining service featuring a diverse menu of local and international dishes.','null','uploads/services/67c8c00718f19.jpg','2025-03-05 21:16:14'),
(12,'Laundry Service','Same-day laundry and dry-cleaning services available for your convenience.','null','uploads/services/67c8c0266abe5.png','2025-03-05 21:16:14'),
(13,'Airport Transfer','Luxury vehicle transfer service to and from the airport with professional chauffeurs.','null','uploads/services/67c8c02dda140.png','2025-03-05 21:16:14'),
(14,'Kids Club','Supervised activities and entertainment for children in a safe and fun environment.','null','uploads/services/67c8c0348b11e.png','2025-03-05 21:16:14'),
(15,'Event Planning','Professional event planning services for weddings, conferences, and special occasions.','null','uploads/services/67c8c045e79e0.png','2025-03-05 21:16:14'),
(16,'Spa ','g dip in our temperature-controlled infinity pool with stunning city vie','fa-swimming-pool','uploads/services/68023752ba435.jpeg','2025-04-18 10:27:26'),
(17,'Wellness','Spa & Wellness Spa & Wellness Spa & Wellness','fa-swimming-pool','uploads/services/680237064b47a.jpeg','2025-04-18 10:40:26'),
(18,'Spa & Wellness','Spa & Wellness','fa-swimming-pool','uploads/services/680236fa01a7b.jpeg','2025-04-18 10:41:59'),
(19,'dfdfdffdf','hghghghgh','fa-swimming-pool','uploads/services/680236ec50068.jpeg','2025-04-18 10:46:36'),
(20,'dfdfdffdf','sdsdsd','fa-swimming-pool','uploads/services/680236e1da360.jpeg','2025-04-18 11:14:22'),
(21,'Spa & Wellness','fdfdfdfdf','fa-swimming-pool','uploads/services/680236d8275f5.jpeg','2025-04-18 11:20:47');
/*!40000 ALTER TABLE `hotel_services` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `loyalty_points`
--

DROP TABLE IF EXISTS `loyalty_points`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `loyalty_points` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `points` int(11) DEFAULT 0,
  `last_updated` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `loyalty_points_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 ;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `loyalty_points`
--

LOCK TABLES `loyalty_points` WRITE;
/*!40000 ALTER TABLE `loyalty_points` DISABLE KEYS */;
INSERT INTO `loyalty_points` VALUES
(1,4,0,'2025-03-05 23:10:13'),
(2,5,0,'2025-03-06 08:03:09'),
(3,6,0,'2025-03-06 14:31:27'),
(4,7,0,'2025-04-01 10:26:09'),
(5,8,0,'2025-04-01 10:32:25'),
(6,9,0,'2025-04-01 11:18:22');
/*!40000 ALTER TABLE `loyalty_points` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `booking_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` enum('card','mobile_money') NOT NULL,
  `payment_details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payment_details`)),
  `transaction_id` varchar(100) DEFAULT NULL,
  `payment_status` enum('pending','completed','failed') DEFAULT 'pending',
  `payment_date` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `booking_id` (`booking_id`),
  CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 ;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payments`
--

LOCK TABLES `payments` WRITE;
/*!40000 ALTER TABLE `payments` DISABLE KEYS */;
INSERT INTO `payments` VALUES
(1,7,25.30,'card',NULL,'TXN1741275007487','completed','2025-03-06 15:30:07'),
(2,7,25.30,'card',NULL,'TXN1741275030956','completed','2025-03-06 15:30:30'),
(3,8,4334.00,'card',NULL,'TXN1741275305257','completed','2025-03-06 15:35:05'),
(4,9,1276.00,'mobile_money','{\"phone\":\"0786745698\",\"network\":\"mtn\"}','TXN1741276110261','completed','2025-03-06 15:48:30'),
(5,6,66.00,'mobile_money','{\"phone\":\"0732286284\",\"network\":\"vodafone\"}','TXN1741276706288','completed','2025-03-06 15:58:26'),
(6,4,12.65,'mobile_money','{\"phone\":\"0732286284\",\"network\":\"airtel\"}','TXN1741278742577','completed','2025-03-06 16:32:22'),
(7,2,22.00,'card','{\"card\":\"5678\",\"expiry\":\"12\\/12\"}','TXN1741281100529','completed','2025-03-06 17:11:40'),
(8,3,-66.00,'card','{\"card\":\"1111\",\"expiry\":\"1212\"}','TXN1741281255797','completed','2025-03-06 17:14:15'),
(9,1,50.60,'card','{\"card\":\"4567\",\"expiry\":\"12\\/26\",\"cvv\":\"235\"}','TXN1741289477355','completed','2025-03-06 19:31:17'),
(10,11,319.00,'mobile_money','{\"phone\":\"078697125\",\"network\":\"mtn\"}','TXN1741297983247','completed','2025-03-06 21:53:03'),
(11,12,50.60,'mobile_money','{\"phone\":\"0786745698\",\"network\":\"mtn\"}','TXN1743506648541','completed','2025-04-01 11:24:08');
/*!40000 ALTER TABLE `payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reviews`
--

DROP TABLE IF EXISTS `reviews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reviews` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `booking_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` between 1 and 5),
  `review_text` text NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_booking_review` (`booking_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`),
  CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reviews`
--

LOCK TABLES `reviews` WRITE;
/*!40000 ALTER TABLE `reviews` DISABLE KEYS */;
/*!40000 ALTER TABLE `reviews` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `room_cleaning`
--

DROP TABLE IF EXISTS `room_cleaning`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `room_cleaning` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `room_id` int(11) DEFAULT NULL,
  `cleaning_type` varchar(50) DEFAULT NULL,
  `last_cleaned` datetime DEFAULT NULL,
  `next_scheduled` datetime DEFAULT NULL,
  `staff_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `room_id` (`room_id`),
  CONSTRAINT `room_cleaning_ibfk_1` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 ;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `room_cleaning`
--

LOCK TABLES `room_cleaning` WRITE;
/*!40000 ALTER TABLE `room_cleaning` DISABLE KEYS */;
INSERT INTO `room_cleaning` VALUES
(2,74,'Regular',NULL,'2025-03-22 11:19:00',NULL);
/*!40000 ALTER TABLE `room_cleaning` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `room_images`
--

DROP TABLE IF EXISTS `room_images`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `room_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `room_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `is_primary` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `room_id` (`room_id`),
  CONSTRAINT `room_images_ibfk_1` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 ;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `room_images`
--

LOCK TABLES `room_images` WRITE;
/*!40000 ALTER TABLE `room_images` DISABLE KEYS */;
INSERT INTO `room_images` VALUES
(3,74,'uploads/rooms/67c828fa31f01.jpeg',0,'2025-03-05 10:35:38'),
(4,74,'uploads/rooms/67c82904ef207.jpeg',0,'2025-03-05 10:35:48'),
(5,77,'uploads/rooms/67c860bf64dfd.jpeg',0,'2025-03-05 14:33:35'),
(6,77,'uploads/rooms/67c860c850438.jpeg',0,'2025-03-05 14:33:44'),
(7,77,'uploads/rooms/67c860ddaac3b.jpeg',0,'2025-03-05 14:34:05');
/*!40000 ALTER TABLE `room_images` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `room_maintenance`
--

DROP TABLE IF EXISTS `room_maintenance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `room_maintenance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `room_id` int(11) DEFAULT NULL,
  `maintenance_type` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `scheduled_date` date DEFAULT NULL,
  `completed_date` date DEFAULT NULL,
  `status` enum('pending','in_progress','completed') DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `room_id` (`room_id`),
  CONSTRAINT `room_maintenance_ibfk_1` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 ;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `room_maintenance`
--

LOCK TABLES `room_maintenance` WRITE;
/*!40000 ALTER TABLE `room_maintenance` DISABLE KEYS */;
INSERT INTO `room_maintenance` VALUES
(2,74,'Upgrade','iuiuiui','2025-03-25',NULL,'pending');
/*!40000 ALTER TABLE `room_maintenance` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `room_services`
--

DROP TABLE IF EXISTS `room_services`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `room_services` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `room_id` int(11) DEFAULT NULL,
  `service_name` varchar(100) DEFAULT NULL,
  `service_description` text DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `room_id` (`room_id`),
  CONSTRAINT `room_services_ibfk_1` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 ;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `room_services`
--

LOCK TABLES `room_services` WRITE;
/*!40000 ALTER TABLE `room_services` DISABLE KEYS */;
INSERT INTO `room_services` VALUES
(2,33,'dfdfdffdf','dfdfdfdf',34.00),
(3,74,'wifi dd','dfnm dfnm dfjnm fd',23.00);
/*!40000 ALTER TABLE `room_services` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rooms`
--

DROP TABLE IF EXISTS `rooms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rooms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `room_number` varchar(20) NOT NULL,
  `room_type` varchar(50) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `description` text DEFAULT NULL,
  `size` int(11) NOT NULL,
  `capacity` int(11) NOT NULL,
  `amenities` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `status` enum('available','booked','maintenance') DEFAULT 'available',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `floor_number` int(11) DEFAULT NULL,
  `bed_config` varchar(100) DEFAULT NULL,
  `view_type` varchar(50) DEFAULT NULL,
  `is_accessible` tinyint(1) DEFAULT 0,
  `rating` decimal(3,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `room_number` (`room_number`)
) ENGINE=InnoDB AUTO_INCREMENT=79 DEFAULT CHARSET=utf8mb4 ;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rooms`
--

LOCK TABLES `rooms` WRITE;
/*!40000 ALTER TABLE `rooms` DISABLE KEYS */;
INSERT INTO `rooms` VALUES
(33,'1256','Suite',580.00,'fgfg uploads/rooms/67c4535b5b16a.jpeg uploads/rooms/67c4535b5b16a.jpeg',45,5,'fggfg','uploads/rooms/68023aa817e24.jpeg','available','2025-03-02 12:47:23',97,'2 Queen','Garden',1,NULL),
(74,'444','Family',788.00,'wererererer',21,3,'wifi, tv','uploads/rooms/67c828e521364.jpeg','available','2025-03-03 07:22:50',232,'1 King 1 Single','Pool',1,NULL),
(75,'8600','Suite',23.00,'hello desc image',36,24,'sdjjsdjhjhjh','uploads/rooms/67c8281d11939.jpeg','available','2025-03-03 08:21:48',254,'3 Single','Garden',1,NULL),
(77,'500','Deluxe',40.00,'fddf',34,34,'0','uploads/rooms/67c829ad158e0.jpeg','available','2025-03-05 10:38:37',43,'2 Queen','Ocean',1,NULL),
(78,'86002','Executive',3.00,'wewewewe',3,3,'0','uploads/rooms/680239ad88993.jpeg','available','2025-04-18 11:38:21',23,'1 King','Garden',1,NULL);
/*!40000 ALTER TABLE `rooms` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `staff`
--

DROP TABLE IF EXISTS `staff`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `staff` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `role` varchar(50) NOT NULL,
  `department` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `staff_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `staff`
--

LOCK TABLES `staff` WRITE;
/*!40000 ALTER TABLE `staff` DISABLE KEYS */;
/*!40000 ALTER TABLE `staff` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `subscribers`
--

DROP TABLE IF EXISTS `subscribers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `subscribers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(100) NOT NULL,
  `status` enum('active','unsubscribed') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 ;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `subscribers`
--

LOCK TABLES `subscribers` WRITE;
/*!40000 ALTER TABLE `subscribers` DISABLE KEYS */;
INSERT INTO `subscribers` VALUES
(1,'abayosincere11@gmail.com','active','2025-03-05 22:07:35');
/*!40000 ALTER TABLE `subscribers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('client','manager','staff') DEFAULT 'client',
  `email_verified` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 ;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES
(1,'nas techgroup','admincw@gmail.com','0786729283','$2y$10$PMnHVImOumbH5nAdkLoVGOG1ZVoswiU1UVCiS104zn5y58EKnr7Ry','client',0,'2025-02-24 14:33:26'),
(2,'Abirebeye Abayo Sincere Aime Margot','abayosincere11@gmail.com','0732286284','$2y$10$VKE2264ONT7XF.zqEaVp1eF3x0PBF7Ftcp7UtAeDOuRQQwEvIfJoa','client',0,'2025-02-24 14:36:45'),
(3,'John Manager','manager@staysync.com','0712345678','$2y$10$M6fXzyL4ugb5ygwfZZ17VO2.0JZNyWjfhJLlHhtwVJ/E4AYUCIXfG','manager',1,'2025-02-24 15:02:30'),
(4,'Abirebeye Abayo Sincere Aime Margot','client@gmail.com','0732286296','$2y$10$McOBmVTN7Eq7BnL69yRdyuypaNsueGbe8HUP5k6kTVWLSl.lIXD9q','client',0,'2025-03-05 22:44:55'),
(5,'Abirebeye Abayo Sincere Aime Margot','sincere@gmail.com','0732286265','$2y$10$McOBmVTN7Eq7BnL69yRdyuypaNsueGbe8HUP5k6kTVWLSl.lIXD9q','client',0,'2025-03-06 08:02:25'),
(6,'Ishimwe jean','ishimwe@gmail.com','0736598745','$2y$10$McOBmVTN7Eq7BnL69yRdyuypaNsueGbe8HUP5k6kTVWLSl.lIXD9q','client',0,'2025-03-06 14:31:02'),
(7,'Abirebeye Abayo Sincere Aime Margot','sss@gmail.com','0732281234','$2y$10$mYTHuH5dDcXotghrn0P7VO1JdD1uru6kp6Juy/ry6q9wufbE8xbt.','client',0,'2025-04-01 10:26:08'),
(8,'Abirebeye Abayo Sincere Aime Margot','abayos@gmail.com','1234567890','$2y$10$j5hhD53F9DH3LVwMwJf3keRsC75z7baxzDwq3ZNYDh3K59oPkO.p6','client',0,'2025-04-01 10:32:25'),
(9,'Abirebeye Abayo Sincere Aime Margot','abayo@gmail.com','0732286123','$2y$10$VfqQhLzr4YN6ilkw72hWBuMKLWfnU29c5//kSiAqFcA5vyxAJjPPG','client',0,'2025-04-01 11:18:21');
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

-- Dump completed on 2025-04-18 14:39:38
