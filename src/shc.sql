-- phpMyAdmin SQL Dump
-- version 4.7.9
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 26-11-2018 a las 23:55:55
-- Versión del servidor: 5.7.21
-- Versión de PHP: 5.6.35

SET FOREIGN_KEY_CHECKS=0;
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
  `reminderSent` tinyint(1) NOT NULL DEFAULT '0',
  `reminderSentAt` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `schedule` (`schedule`),
  KEY `date` (`date`),
  KEY `patient` (`patient`),
  KEY `professional` (`professional`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Truncar tablas antes de insertar `appointments`
--

TRUNCATE TABLE `appointments`;
--
-- Volcado de datos para la tabla `appointments`
--

INSERT INTO `appointments` (`id`, `date`, `schedule`, `hour`, `patient`, `professional`, `indications`, `confirmed`, `confirmedAt`, `reprogrammed`, `reprogrammedAt`, `printed`, `printedAt`, `reminderWay`, `reminderData`, `reminderSent`, `reminderSentAt`) VALUES
(1, '2018-11-26', 1, '10:00:00', 6, 2, NULL, 0, NULL, 0, NULL, 0, NULL, NULL, NULL, 0, NULL);

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
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Truncar tablas antes de insertar `schedules`
--

TRUNCATE TABLE `schedules`;
--
-- Volcado de datos para la tabla `schedules`
--

INSERT INTO `schedules` (`id`, `name`, `periodicity`, `appointmentInterval`, `validityStart`, `validityEnd`, `color`) VALUES
(1, 'Pediatría', 1, 15, '2018-11-01', NULL, '#ff0000'),
(2, 'Traumatología', 2, 20, '2018-11-01', NULL, '#00ff00');

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
  PRIMARY KEY (`id`),
  KEY `schedule` (`schedule`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Truncar tablas antes de insertar `schedules_days`
--

TRUNCATE TABLE `schedules_days`;
--
-- Volcado de datos para la tabla `schedules_days`
--

INSERT INTO `schedules_days` (`id`, `schedule`, `date`, `weekDay`) VALUES
(1, 1, NULL, 0),
(2, 1, NULL, 1),
(3, 1, NULL, 2),
(4, 1, NULL, 3),
(5, 1, NULL, 4),
(6, 1, NULL, 5),
(7, 1, NULL, 6),
(8, 2, '2018-11-26', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `schedules_days_hours`
--

DROP TABLE IF EXISTS `schedules_days_hours`;
CREATE TABLE IF NOT EXISTS `schedules_days_hours` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `schedule` int(11) NOT NULL,
  `date` date DEFAULT NULL,
  `weekday` int(11) DEFAULT NULL,
  `start` time NOT NULL,
  `end` time NOT NULL,
  PRIMARY KEY (`id`),
  KEY `schedule` (`schedule`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Truncar tablas antes de insertar `schedules_days_hours`
--

TRUNCATE TABLE `schedules_days_hours`;
--
-- Volcado de datos para la tabla `schedules_days_hours`
--

INSERT INTO `schedules_days_hours` (`id`, `schedule`, `date`, `weekday`, `start`, `end`) VALUES
(1, 1, NULL, 0, '08:30:00', '12:00:00'),
(2, 1, NULL, 1, '08:30:00', '12:00:00'),
(3, 1, NULL, 2, '08:30:00', '12:00:00'),
(4, 1, NULL, 3, '08:30:00', '12:00:00'),
(5, 1, NULL, 4, '08:30:00', '12:00:00'),
(6, 1, NULL, 5, '08:30:00', '12:00:00'),
(7, 1, NULL, 6, '08:30:00', '12:00:00'),
(8, 2, '2018-11-26', NULL, '16:00:00', '20:00:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `schedules_professionals`
--

DROP TABLE IF EXISTS `schedules_professionals`;
CREATE TABLE IF NOT EXISTS `schedules_professionals` (
  `schedule` int(11) NOT NULL,
  `user` int(11) NOT NULL,
  KEY `schedule` (`schedule`,`user`),
  KEY `user` (`user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Truncar tablas antes de insertar `schedules_professionals`
--

TRUNCATE TABLE `schedules_professionals`;
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
-- Truncar tablas antes de insertar `users`
--

TRUNCATE TABLE `users`;
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
-- Truncar tablas antes de insertar `users_roles`
--

TRUNCATE TABLE `users_roles`;
--
-- Volcado de datos para la tabla `users_roles`
--

INSERT INTO `users_roles` (`user`, `role`) VALUES
(1, 'administrator'),
(2, 'medic');

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`schedule`) REFERENCES `schedules` (`id`),
  ADD CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`patient`) REFERENCES `patients` (`id`),
  ADD CONSTRAINT `appointments_ibfk_3` FOREIGN KEY (`professional`) REFERENCES `users` (`id`);

--
-- Filtros para la tabla `schedules_days`
--
ALTER TABLE `schedules_days`
  ADD CONSTRAINT `schedules_days_ibfk_1` FOREIGN KEY (`schedule`) REFERENCES `schedules` (`id`);

--
-- Filtros para la tabla `schedules_days_hours`
--
ALTER TABLE `schedules_days_hours`
  ADD CONSTRAINT `schedules_days_hours_ibfk_1` FOREIGN KEY (`schedule`) REFERENCES `schedules` (`id`);

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
SET FOREIGN_KEY_CHECKS=1;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
