-- phpMyAdmin SQL Dump
-- version 4.7.9
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 24-09-2018 a las 17:11:40
-- Versión del servidor: 5.7.21
-- Versión de PHP: 7.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `shc`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `patients`
--

DROP TABLE IF EXISTS `patients`;
CREATE TABLE IF NOT EXISTS `patients` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lastname` varchar(256) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(256) COLLATE utf8_unicode_ci NOT NULL,
  `birthday` date DEFAULT NULL,
  `gender` int(11) DEFAULT NULL,
  `docType` int(11) DEFAULT NULL,
  `doc` varchar(256) COLLATE utf8_unicode_ci DEFAULT NULL,
  `phone1` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `phone2` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `country` int(11) DEFAULT NULL,
  `state` int(11) DEFAULT NULL,
  `city` int(11) DEFAULT NULL,
  `street` varchar(256) COLLATE utf8_unicode_ci DEFAULT NULL,
  `number` int(11) DEFAULT NULL,
  `floor` varchar(16) COLLATE utf8_unicode_ci DEFAULT NULL,
  `apartment` varchar(16) COLLATE utf8_unicode_ci DEFAULT NULL,
  `socialSecurity1` int(11) DEFAULT NULL,
  `socialSecurity1Number` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `socialSecurity2` int(11) DEFAULT NULL,
  `socialSecurity2Number` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `birthType` int(11) DEFAULT NULL,
  `weightNewborn` decimal(10,0) DEFAULT NULL,
  `bloodType` int(11) DEFAULT NULL,
  `rhFactor` int(11) DEFAULT NULL,
  `apgar` int(11) DEFAULT NULL,
  `gestationalAge` int(11) DEFAULT NULL,
  `comments` text COLLATE utf8_unicode_ci,
  `father` text COLLATE utf8_unicode_ci,
  `mother` text COLLATE utf8_unicode_ci,
  `brothers` text COLLATE utf8_unicode_ci,
  `others` text COLLATE utf8_unicode_ci,
  `createdBy` int(11) NOT NULL,
  `createdAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modifiedBy` int(11) NOT NULL,
  `modifiedAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `lastname` (`lastname`),
  KEY `name` (`name`),
  KEY `doc` (`doc`),
  KEY `createdBy` (`createdBy`),
  KEY `modifiedBy` (`modifiedBy`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Disparadores `patients`
--
DROP TRIGGER IF EXISTS `PatientsModifiedAt`;
DELIMITER $$
CREATE TRIGGER `PatientsModifiedAt` BEFORE UPDATE ON `patients` FOR EACH ROW SET NEW.modifiedAt = NOW()
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `social_securities`
--

DROP TABLE IF EXISTS `social_securities`;
CREATE TABLE IF NOT EXISTS `social_securities` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(256) COLLATE utf8_unicode_ci NOT NULL,
  `accepted` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(256) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(256) COLLATE utf8_unicode_ci NOT NULL,
  `lastname` varchar(256) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(256) COLLATE utf8_unicode_ci NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `visits`
--

DROP TABLE IF EXISTS `visits`;
CREATE TABLE IF NOT EXISTS `visits` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient` int(11) NOT NULL,
  `date` date NOT NULL,
  `weight` decimal(10,0) DEFAULT NULL,
  `height` decimal(10,0) DEFAULT NULL,
  `perimeter` decimal(10,0) DEFAULT NULL,
  `diagnosis` text COLLATE utf8_unicode_ci NOT NULL,
  `treatment` text COLLATE utf8_unicode_ci,
  `createdBy` int(11) NOT NULL DEFAULT '1',
  `createdAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modifiedBy` int(11) NOT NULL DEFAULT '1',
  `modifiedAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `patient` (`patient`),
  KEY `date` (`date`),
  KEY `createdBy` (`createdBy`),
  KEY `modifiedBy` (`modifiedBy`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Disparadores `visits`
--
DROP TRIGGER IF EXISTS `VisitsModifiedAt`;
DELIMITER $$
CREATE TRIGGER `VisitsModifiedAt` BEFORE UPDATE ON `visits` FOR EACH ROW SET NEW.modifiedAt = NOW()
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `visits_files`
--

DROP TABLE IF EXISTS `visits_files`;
CREATE TABLE IF NOT EXISTS `visits_files` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `visit` int(11) NOT NULL,
  `name` varchar(256) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `visit` (`visit`)
) ENGINE=InnoDB AUTO_INCREMENT=138 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `patients`
--
ALTER TABLE `patients`
  ADD CONSTRAINT `patients_ibfk_1` FOREIGN KEY (`createdBy`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `patients_ibfk_2` FOREIGN KEY (`modifiedBy`) REFERENCES `users` (`id`);

--
-- Filtros para la tabla `visits`
--
ALTER TABLE `visits`
  ADD CONSTRAINT `visits_ibfk_1` FOREIGN KEY (`patient`) REFERENCES `patients` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `visits_ibfk_2` FOREIGN KEY (`createdBy`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `visits_ibfk_3` FOREIGN KEY (`modifiedBy`) REFERENCES `users` (`id`);

--
-- Filtros para la tabla `visits_files`
--
ALTER TABLE `visits_files`
  ADD CONSTRAINT `visit` FOREIGN KEY (`visit`) REFERENCES `visits` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
