-- phpMyAdmin SQL Dump
-- version 4.7.9
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 19-12-2018 a las 21:56:40
-- Versión del servidor: 5.7.21
-- Versión de PHP: 5.6.35

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
CREATE DATABASE IF NOT EXISTS `shc` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
USE `shc`;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `applications`
--

DROP TABLE IF EXISTS `applications`;
CREATE TABLE IF NOT EXISTS `applications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient` int(11) NOT NULL,
  `date` date NOT NULL,
  `age` int(11) NOT NULL,
  `vaccine` int(11) NOT NULL,
  `dose` int(11) NOT NULL,
  `createdAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `patient` (`patient`),
  KEY `date` (`date`),
  KEY `age` (`age`),
  KEY `vaccine` (`vaccine`),
  KEY `dose` (`dose`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `appointments`
--

DROP TABLE IF EXISTS `appointments`;
CREATE TABLE IF NOT EXISTS `appointments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `schedule` int(11) NOT NULL,
  `hour` time NOT NULL,
  `patient` int(11) NOT NULL,
  `professional` int(11) NOT NULL,
  `indications` text COLLATE utf8_unicode_ci,
  `confirmed` tinyint(1) DEFAULT '0',
  `confirmedAt` timestamp NULL DEFAULT NULL,
  `reprogrammed` tinyint(1) DEFAULT '0',
  `reprogrammedAt` timestamp NULL DEFAULT NULL,
  `printed` tinyint(1) DEFAULT '0',
  `printedAt` timestamp NULL DEFAULT NULL,
  `reminderWay` int(11) DEFAULT NULL,
  `reminderData` text COLLATE utf8_unicode_ci,
  `reminderSent` tinyint(1) DEFAULT '0',
  `reminderSentAt` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `schedule` (`schedule`),
  KEY `date` (`date`),
  KEY `patient` (`patient`),
  KEY `professional` (`professional`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

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
  `apgar1` int(11) DEFAULT NULL,
  `apgar2` int(11) DEFAULT NULL,
  `gestationalAge` int(11) DEFAULT NULL,
  `comments` text COLLATE utf8_unicode_ci,
  `father` text COLLATE utf8_unicode_ci,
  `mother` text COLLATE utf8_unicode_ci,
  `brothers` text COLLATE utf8_unicode_ci,
  `others` text COLLATE utf8_unicode_ci,
  `createdBy` int(11) NOT NULL DEFAULT '1',
  `createdAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modifiedBy` int(11) NOT NULL DEFAULT '1',
  `modifiedAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `lastname` (`lastname`),
  KEY `name` (`name`),
  KEY `doc` (`doc`),
  KEY `createdBy` (`createdBy`),
  KEY `modifiedBy` (`modifiedBy`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

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
-- Estructura de tabla para la tabla `schedules`
--

DROP TABLE IF EXISTS `schedules`;
CREATE TABLE IF NOT EXISTS `schedules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(256) COLLATE utf8_unicode_ci NOT NULL,
  `periodicity` int(11) NOT NULL DEFAULT '1',
  `appointmentInterval` int(11) NOT NULL,
  `validityStart` date NOT NULL,
  `validityEnd` date DEFAULT NULL,
  `color` varchar(7) COLLATE utf8_unicode_ci DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `schedules_days`
--

DROP TABLE IF EXISTS `schedules_days`;
CREATE TABLE IF NOT EXISTS `schedules_days` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `schedule` int(11) NOT NULL,
  `date` date DEFAULT NULL,
  `weekDay` int(11) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `schedule` (`schedule`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `schedules_days_hours`
--

DROP TABLE IF EXISTS `schedules_days_hours`;
CREATE TABLE IF NOT EXISTS `schedules_days_hours` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `day` int(11) NOT NULL,
  `start` time NOT NULL,
  `end` time NOT NULL,
  PRIMARY KEY (`id`),
  KEY `schedule` (`day`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `schedules_professionals`
--

DROP TABLE IF EXISTS `schedules_professionals`;
CREATE TABLE IF NOT EXISTS `schedules_professionals` (
  `schedule` int(11) NOT NULL,
  `user` int(11) NOT NULL,
  PRIMARY KEY (`schedule`,`user`) USING BTREE,
  KEY `user` (`user`)
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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `lastname`, `name`, `enabled`) VALUES
(1, 'dariogum@hotmail.com', '$2y$10$M4mL7fmJYHS61CbHyoruVeih90ww/MCjH8Bz36dXLLRstGYGA.5Jy', 'Uberti Manassero', 'Darío', 1),
(2, 'medic@shc.com', '$2y$10$ZWBD84Xz6xvNs12D1PErVOkjlEhtl5IyxIxLLVEKGxIL2KLtrKc0e', 'De Prueba', 'Médico', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users_roles`
--

DROP TABLE IF EXISTS `users_roles`;
CREATE TABLE IF NOT EXISTS `users_roles` (
  `user` int(11) NOT NULL,
  `role` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`user`,`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `users_roles`
--

INSERT INTO `users_roles` (`user`, `role`) VALUES
(1, 'administrator'),
(2, 'medic');

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
  `bloodPressure` varchar(7) COLLATE utf8_unicode_ci DEFAULT NULL,
  `diagnosis` text COLLATE utf8_unicode_ci NOT NULL,
  `treatment` text COLLATE utf8_unicode_ci,
  `studiesResults` text COLLATE utf8_unicode_ci,
  `createdBy` int(11) NOT NULL DEFAULT '1',
  `createdAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modifiedBy` int(11) NOT NULL DEFAULT '1',
  `modifiedAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `patient` (`patient`),
  KEY `date` (`date`),
  KEY `createdBy` (`createdBy`),
  KEY `modifiedBy` (`modifiedBy`)
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `applications`
--
ALTER TABLE `applications`
  ADD CONSTRAINT `applications_ibfk_1` FOREIGN KEY (`patient`) REFERENCES `patients` (`id`);

--
-- Filtros para la tabla `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`schedule`) REFERENCES `schedules` (`id`),
  ADD CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`patient`) REFERENCES `patients` (`id`),
  ADD CONSTRAINT `appointments_ibfk_3` FOREIGN KEY (`professional`) REFERENCES `users` (`id`);

--
-- Filtros para la tabla `patients`
--
ALTER TABLE `patients`
  ADD CONSTRAINT `patients_ibfk_1` FOREIGN KEY (`createdBy`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `patients_ibfk_2` FOREIGN KEY (`modifiedBy`) REFERENCES `users` (`id`);

--
-- Filtros para la tabla `schedules_days`
--
ALTER TABLE `schedules_days`
  ADD CONSTRAINT `schedules_days_ibfk_1` FOREIGN KEY (`schedule`) REFERENCES `schedules` (`id`);

--
-- Filtros para la tabla `schedules_days_hours`
--
ALTER TABLE `schedules_days_hours`
  ADD CONSTRAINT `schedules_days_hours_ibfk_1` FOREIGN KEY (`day`) REFERENCES `schedules_days` (`id`);

--
-- Filtros para la tabla `schedules_professionals`
--
ALTER TABLE `schedules_professionals`
  ADD CONSTRAINT `schedules_professionals_ibfk_1` FOREIGN KEY (`schedule`) REFERENCES `schedules` (`id`),
  ADD CONSTRAINT `schedules_professionals_ibfk_2` FOREIGN KEY (`user`) REFERENCES `users` (`id`);

--
-- Filtros para la tabla `users_roles`
--
ALTER TABLE `users_roles`
  ADD CONSTRAINT `users_roles_ibfk_1` FOREIGN KEY (`user`) REFERENCES `users` (`id`);

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
