-- MySQL dump 10.13  Distrib 8.0.19, for Win64 (x86_64)
--
-- Host: localhost    Database: biblioteca_escolar
-- ------------------------------------------------------
-- Server version	5.5.5-10.4.32-MariaDB

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
-- Table structure for table `detalle_prestamo`
--

DROP TABLE IF EXISTS `detalle_prestamo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `detalle_prestamo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_prestamo` int(11) NOT NULL,
  `id_libro` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL DEFAULT 1,
  `estado_devolucion` enum('PENDIENTE','BUENO','DAÑADO','PERDIDO') NOT NULL DEFAULT 'PENDIENTE',
  PRIMARY KEY (`id`),
  KEY `fk_detalle_prestamo` (`id_prestamo`),
  KEY `fk_detalle_libro` (`id_libro`),
  CONSTRAINT `fk_detalle_libro` FOREIGN KEY (`id_libro`) REFERENCES `libros` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_detalle_prestamo` FOREIGN KEY (`id_prestamo`) REFERENCES `prestamos` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detalle_prestamo`
--

LOCK TABLES `detalle_prestamo` WRITE;
/*!40000 ALTER TABLE `detalle_prestamo` DISABLE KEYS */;
INSERT INTO `detalle_prestamo` VALUES (1,1,4,1,'BUENO'),(2,2,1,5,'BUENO'),(3,3,6,6,'BUENO');
/*!40000 ALTER TABLE `detalle_prestamo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `libros`
--

DROP TABLE IF EXISTS `libros`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `libros` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `isbn` varchar(20) DEFAULT NULL,
  `titulo` varchar(200) NOT NULL,
  `autor` varchar(150) NOT NULL,
  `categoria` varchar(50) DEFAULT 'General',
  `editorial` varchar(100) DEFAULT NULL,
  `anio` int(11) DEFAULT NULL,
  `stock_total` int(11) NOT NULL DEFAULT 0,
  `stock_disponible` int(11) NOT NULL DEFAULT 0,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `imagen_portada` varchar(255) DEFAULT NULL,
  `estado_fisico` enum('BUENO','REGULAR','MALO') DEFAULT 'BUENO',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `libros`
--

LOCK TABLES `libros` WRITE;
/*!40000 ALTER TABLE `libros` DISABLE KEYS */;
INSERT INTO `libros` VALUES (1,NULL,'Álgebra de Baldor','Aurelio Baldor','General','Grupo Editorial Patria',2005,10,10,'2025-11-20 09:58:05',NULL,'BUENO'),(2,NULL,'Cien Años de Soledad','Gabriel García Márquez','General','Sudamericana',1967,5,5,'2025-11-20 09:58:05',NULL,'BUENO'),(3,NULL,'Historia del Perú','Jorge Basadre','General','Ediciones Historia',2010,3,3,'2025-11-20 09:58:05',NULL,'BUENO'),(4,NULL,'El Principito','Antoine de Saint-Exupéry','General','Reynal & Hitchcock',1943,8,7,'2025-11-20 09:58:05',NULL,'BUENO'),(5,NULL,'Biología Moderna','Varios Autores','Matemática','Santillana',2019,15,15,'2025-11-20 09:58:05',NULL,'BUENO'),(6,NULL,'Matematicas','Minedu','General','',NULL,30,20,'2025-11-20 10:14:54',NULL,'BUENO'),(8,NULL,'COMUNICACION ','MINEDU','COMUNICACION','PRUEBA',NULL,20,20,'2025-11-21 15:31:53',NULL,'BUENO');
/*!40000 ALTER TABLE `libros` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personas`
--

DROP TABLE IF EXISTS `personas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `personas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombres` varchar(100) NOT NULL,
  `apellidos` varchar(100) NOT NULL,
  `dni` varchar(20) NOT NULL,
  `tipo` enum('ESTUDIANTE','DOCENTE') NOT NULL,
  `especialidad` varchar(100) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `grado` varchar(20) DEFAULT NULL COMMENT 'Solo para estudiantes',
  `seccion` varchar(10) DEFAULT NULL COMMENT 'Solo para estudiantes',
  `estado_biblioteca` enum('ACTIVO','BLOQUEADO') NOT NULL DEFAULT 'ACTIVO',
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `dni` (`dni`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personas`
--

LOCK TABLES `personas` WRITE;
/*!40000 ALTER TABLE `personas` DISABLE KEYS */;
INSERT INTO `personas` VALUES (1,'Roberto','Gomez Bolaños','10000001','DOCENTE',NULL,NULL,NULL,NULL,'ACTIVO','2025-11-20 09:58:05'),(2,'Florinda','Meza','10000002','DOCENTE',NULL,NULL,NULL,NULL,'ACTIVO','2025-11-20 09:58:05'),(3,'Carlos','Villagran','20000001','ESTUDIANTE',NULL,NULL,'5to','A','ACTIVO','2025-11-20 09:58:05'),(4,'Maria','Antonieta','20000002','ESTUDIANTE',NULL,NULL,'1ro','B','ACTIVO','2025-11-20 09:58:05'),(5,'jose','alcedo','12345678','ESTUDIANTE',NULL,NULL,'1ro','f','ACTIVO','2025-11-21 16:05:25'),(6,'manuel','urbano','12345677','DOCENTE','Historial Social','123456789',NULL,NULL,'ACTIVO','2025-11-21 16:06:07');
/*!40000 ALTER TABLE `personas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `prestamos`
--

DROP TABLE IF EXISTS `prestamos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `prestamos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_persona_solicitante` int(11) NOT NULL,
  `id_usuario_bibliotecario` int(11) NOT NULL,
  `fecha_prestamo` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_devolucion_pactada` date NOT NULL,
  `fecha_devolucion_real` datetime DEFAULT NULL,
  `estado` enum('PENDIENTE','FINALIZADO','CON_INCIDENCIA') NOT NULL DEFAULT 'PENDIENTE',
  `observaciones` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_prestamos_persona` (`id_persona_solicitante`),
  KEY `fk_prestamos_usuario` (`id_usuario_bibliotecario`),
  CONSTRAINT `fk_prestamos_persona` FOREIGN KEY (`id_persona_solicitante`) REFERENCES `personas` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_prestamos_usuario` FOREIGN KEY (`id_usuario_bibliotecario`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `prestamos`
--

LOCK TABLES `prestamos` WRITE;
/*!40000 ALTER TABLE `prestamos` DISABLE KEYS */;
INSERT INTO `prestamos` VALUES (1,3,1,'2025-11-20 04:58:05','2025-11-27',NULL,'FINALIZADO','Préstamo de prueba para lectura domiciliaria.'),(2,2,1,'2025-11-20 05:18:49','2025-11-27',NULL,'FINALIZADO',NULL),(3,4,1,'2025-11-20 20:54:24','2025-11-28',NULL,'FINALIZADO',NULL);
/*!40000 ALTER TABLE `prestamos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_persona` int(11) DEFAULT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL COMMENT 'En producción usar hash (ej. Argon2/Bcrypt)',
  `rol` enum('ADMINISTRADOR','BIBLIOTECARIO','DOCENTE','ESTUDIANTE') NOT NULL DEFAULT 'ESTUDIANTE',
  `nombre_completo` varchar(100) NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `fk_usuario_persona` (`id_persona`),
  CONSTRAINT `fk_usuario_persona` FOREIGN KEY (`id_persona`) REFERENCES `personas` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES (1,NULL,'admin','12345678','ADMINISTRADOR','Administrador del Sistema','2025-11-20 09:58:04'),(2,NULL,'bibliotecario','biblio123','BIBLIOTECARIO','Encargado de Biblioteca','2025-11-21 00:45:38'),(3,NULL,'profe1','profe123','DOCENTE','Profesor Juan Perez','2025-11-21 00:45:38');
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'biblioteca_escolar'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-11-21 11:17:49
