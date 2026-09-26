-- MySQL dump 10.13  Distrib 8.0.46, for Linux (x86_64)
--
-- Host: localhost    Database: SmallProject
-- ------------------------------------------------------
-- Server version	8.0.46-0ubuntu0.24.04.4

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `Contacts`
--

DROP TABLE IF EXISTS `Contacts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Contacts` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `FirstName` varchar(50) NOT NULL DEFAULT '',
  `LastName` varchar(50) NOT NULL DEFAULT '',
  `Phone` varchar(50) NOT NULL DEFAULT '',
  `Email` varchar(50) NOT NULL DEFAULT '',
  `UserID` int NOT NULL DEFAULT '0',
  `date_added` date DEFAULT (curdate()),
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Contacts`
--

LOCK TABLES `Contacts` WRITE;
/*!40000 ALTER TABLE `Contacts` DISABLE KEYS */;
INSERT INTO `Contacts` VALUES (3,'Michael','Brown','407-555-0103','michael.brown@example.com',1,'2026-09-08'),(4,'Sarah','Davis','407-555-0104','sarah.davis@example.com',1,'2026-09-08'),(5,'David','Wilson','407-555-0105','david.wilson@example.com',1,'2026-09-08'),(6,'Priya','Patel','321-555-0201','priya.patel@example.com',2,'2026-09-08'),(7,'Arjun','Rao','321-555-0202','arjun.rao@example.com',2,'2026-09-08'),(8,'Neha','Shah','321-555-0203','neha.shah@example.com',2,'2026-09-08'),(9,'Ravi','Kumar','321-555-0204','ravi.kumar@example.com',2,'2026-09-08'),(10,'Anita','Reddy','321-555-0205','anita.reddy@example.com',2,'2026-09-08'),(13,'John','Smith','407-555-0101','john@example.com',1,'2026-09-18'),(15,'Melinda','Mai','(123)123-1234','testing@gmail.com',1,'2026-09-21'),(19,'Testing','Test','123-123-1234','idk@gmail.com',14,'2026-09-25');
/*!40000 ALTER TABLE `Contacts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Users`
--

DROP TABLE IF EXISTS `Users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Users` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `FirstName` varchar(50) NOT NULL DEFAULT '',
  `LastName` varchar(50) NOT NULL DEFAULT '',
  `Login` varchar(50) NOT NULL DEFAULT '',
  `Password` varchar(50) NOT NULL DEFAULT '',
  `PhoneNumber` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Users`
--

LOCK TABLES `Users` WRITE;
/*!40000 ALTER TABLE `Users` DISABLE KEYS */;
INSERT INTO `Users` VALUES (1,'Sam','Hill','SamH','Test',''),(2,'Sindhu','Sesham','Sindhu','12345',''),(3,'Jane','Doe','janedoe99','SecurePassword123',''),(8,'Jane','Doe','janedoe@gmail.com','hi','1234567890'),(9,'Tom','Smith','smithy@gmail.com','tom456','352-796-0089'),(10,'hi','i','hi@gmail.com','com','4563563567'),(11,'hi','ii','gamil','com','1234567890'),(12,'test','3','33@gamil.com','com','3333333333'),(13,'test','1','email@.com','test','00000000'),(14,'Melinda','Mai','melinda@gmail.com','123','123-123-1234'),(15,'Sam','Harris','SamH@gmail.com','123456','123 456 7890'),(16,'John','Doeee','example@gmail.com','testing','123-456-7890'),(17,'Mikayla','Philpot','mikaylagp@hotmail.com','Benji23!','4079613002'),(18,'Sam','Harris','SamHarris@gmail.com','password','444 444 4444'),(19,'Melinda','Mai','meli@gmail.com','1234','123-123-1234');
/*!40000 ALTER TABLE `Users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-09-25 18:12:06
