-- phpMyAdmin SQL Dump
-- version 4.5.4.1deb2ubuntu2.1
-- http://www.phpmyadmin.net
--
<<<<<<< HEAD
-- Servidor: localhost
-- Tiempo de generación: 02-01-2019 a las 13:49:10
-- Versión del servidor: 5.7.24-0ubuntu0.16.04.1
-- Versión de PHP: 7.2.12-1+ubuntu16.04.1+deb.sury.org+1
=======
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 19-12-2018 a las 21:56:40
-- Versión del servidor: 5.7.21
-- Versión de PHP: 5.6.35
>>>>>>> 1694447cb89cea8390510e57670b9ff99a92345f

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
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

CREATE TABLE `appointments` (
  `id` int(11) NOT NULL,
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
<<<<<<< HEAD
  `reminderSentAt` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
=======
  `reminderSentAt` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `schedule` (`schedule`),
  KEY `date` (`date`),
  KEY `patient` (`patient`),
  KEY `professional` (`professional`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
>>>>>>> 1694447cb89cea8390510e57670b9ff99a92345f

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `patients`
--

<<<<<<< HEAD
CREATE TABLE `patients` (
  `id` int(11) NOT NULL,
  `oldId` int(11) DEFAULT NULL,
=======
DROP TABLE IF EXISTS `patients`;
CREATE TABLE IF NOT EXISTS `patients` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
>>>>>>> 1694447cb89cea8390510e57670b9ff99a92345f
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
<<<<<<< HEAD
  `socialSecurity1Plan` varchar(256) COLLATE utf8_unicode_ci DEFAULT NULL,
  `socialSecurity1Number` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `socialSecurity2` int(11) DEFAULT NULL,
  `socialSecurity2Plan` varchar(256) COLLATE utf8_unicode_ci DEFAULT NULL,
=======
  `socialSecurity1Number` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `socialSecurity2` int(11) DEFAULT NULL,
>>>>>>> 1694447cb89cea8390510e57670b9ff99a92345f
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
<<<<<<< HEAD
  `modifiedAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
=======
  `modifiedAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `lastname` (`lastname`),
  KEY `name` (`name`),
  KEY `doc` (`doc`),
  KEY `createdBy` (`createdBy`),
  KEY `modifiedBy` (`modifiedBy`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
>>>>>>> 1694447cb89cea8390510e57670b9ff99a92345f

--
-- Disparadores `patients`
--
<<<<<<< HEAD
=======
DROP TRIGGER IF EXISTS `PatientsModifiedAt`;
>>>>>>> 1694447cb89cea8390510e57670b9ff99a92345f
DELIMITER $$
CREATE TRIGGER `PatientsModifiedAt` BEFORE UPDATE ON `patients` FOR EACH ROW SET NEW.modifiedAt = NOW()
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `schedules`
--

CREATE TABLE `schedules` (
  `id` int(11) NOT NULL,
  `name` varchar(256) NOT NULL,
  `periodicity` int(11) NOT NULL DEFAULT '1',
  `appointmentInterval` int(11) NOT NULL,
  `validityStart` date NOT NULL,
  `validityEnd` date DEFAULT NULL,
<<<<<<< HEAD
  `color` varchar(7) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
=======
  `color` varchar(7) COLLATE utf8_unicode_ci DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
>>>>>>> 1694447cb89cea8390510e57670b9ff99a92345f

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `schedules_days`
--

CREATE TABLE `schedules_days` (
  `id` int(11) NOT NULL,
  `schedule` int(11) NOT NULL,
  `date` date DEFAULT NULL,
  `weekDay` int(11) DEFAULT NULL,
<<<<<<< HEAD
  `active` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `schedules_days_hours`
--

CREATE TABLE `schedules_days_hours` (
  `id` int(11) NOT NULL,
  `day` int(11) NOT NULL,
  `start` time NOT NULL,
  `end` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

=======
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `schedule` (`schedule`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

>>>>>>> 1694447cb89cea8390510e57670b9ff99a92345f
-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `schedules_professionals`
--

<<<<<<< HEAD
CREATE TABLE `schedules_professionals` (
  `schedule` int(11) NOT NULL,
  `user` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(256) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(256) COLLATE utf8_unicode_ci NOT NULL,
  `lastname` varchar(256) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(256) COLLATE utf8_unicode_ci NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users_roles`
--

CREATE TABLE `users_roles` (
  `user` int(11) NOT NULL,
  `role` varchar(16) CHARACTER SET latin1 NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

=======
DROP TABLE IF EXISTS `schedules_days_hours`;
CREATE TABLE IF NOT EXISTS `schedules_days_hours` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `day` int(11) NOT NULL,
  `start` time NOT NULL,
  `end` time NOT NULL,
  PRIMARY KEY (`id`),
  KEY `schedule` (`day`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

>>>>>>> 1694447cb89cea8390510e57670b9ff99a92345f
-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `visits`
--

<<<<<<< HEAD
CREATE TABLE `visits` (
  `id` int(11) NOT NULL,
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
  `modifiedAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Disparadores `visits`
--
DELIMITER $$
CREATE TRIGGER `VisitsModifiedAt` BEFORE UPDATE ON `visits` FOR EACH ROW SET NEW.modifiedAt = NOW()
$$
DELIMITER ;

=======
DROP TABLE IF EXISTS `schedules_professionals`;
CREATE TABLE IF NOT EXISTS `schedules_professionals` (
  `schedule` int(11) NOT NULL,
  `user` int(11) NOT NULL,
  PRIMARY KEY (`schedule`,`user`) USING BTREE,
  KEY `user` (`user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

>>>>>>> 1694447cb89cea8390510e57670b9ff99a92345f
-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `visits_files`
--

CREATE TABLE `visits_files` (
  `id` int(11) NOT NULL,
  `visit` int(11) NOT NULL,
  `name` varchar(256) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
<<<<<<< HEAD
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `appointments`
=======
-- Volcado de datos para la tabla `users`
>>>>>>> 1694447cb89cea8390510e57670b9ff99a92345f
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `date` (`date`),
  ADD KEY `schedule` (`schedule`),
  ADD KEY `patient` (`patient`),
  ADD KEY `professional` (`professional`);

--
-- Indices de la tabla `patients`
--
ALTER TABLE `patients`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lastname` (`lastname`),
  ADD KEY `name` (`name`),
  ADD KEY `doc` (`doc`),
  ADD KEY `createdBy` (`createdBy`),
  ADD KEY `modifiedBy` (`modifiedBy`);

--
-- Indices de la tabla `schedules`
--
ALTER TABLE `schedules`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `schedules_days`
--
ALTER TABLE `schedules_days`
  ADD PRIMARY KEY (`id`),
  ADD KEY `schedule` (`schedule`);

--
-- Indices de la tabla `schedules_days_hours`
--
ALTER TABLE `schedules_days_hours`
  ADD PRIMARY KEY (`id`),
  ADD KEY `day` (`day`);

--
<<<<<<< HEAD
-- Indices de la tabla `schedules_professionals`
--
ALTER TABLE `schedules_professionals`
  ADD PRIMARY KEY (`schedule`,`user`),
  ADD KEY `user` (`user`);

--
-- Indices de la tabla `users`
=======
-- Volcado de datos para la tabla `users_roles`
>>>>>>> 1694447cb89cea8390510e57670b9ff99a92345f
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indices de la tabla `users_roles`
--
ALTER TABLE `users_roles`
  ADD PRIMARY KEY (`user`,`role`);

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
-- Indices de la tabla `visits`
--
ALTER TABLE `visits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `patient` (`patient`),
  ADD KEY `date` (`date`),
  ADD KEY `createdBy` (`createdBy`),
  ADD KEY `modifiedBy` (`modifiedBy`);

--
-- Indices de la tabla `visits_files`
--
ALTER TABLE `visits_files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `visit` (`visit`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT de la tabla `patients`
--
ALTER TABLE `patients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5276;
--
-- AUTO_INCREMENT de la tabla `schedules`
--
ALTER TABLE `schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT de la tabla `schedules_days`
--
ALTER TABLE `schedules_days`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT de la tabla `schedules_days_hours`
--
ALTER TABLE `schedules_days_hours`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT de la tabla `visits`
--
ALTER TABLE `visits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=437;
--
-- AUTO_INCREMENT de la tabla `visits_files`
--
ALTER TABLE `visits_files`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
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
<<<<<<< HEAD
=======
COMMIT;
>>>>>>> 1694447cb89cea8390510e57670b9ff99a92345f

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
