-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 11-11-2024 a las 18:18:34
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `empleados`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asistencias`
--

CREATE TABLE `asistencias` (
  `id` int(11) NOT NULL,
  `empleado_id` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `hora` time NOT NULL,
  `tipo` enum('consumio','no_consumio') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empleado`
--

CREATE TABLE `empleado` (
  `id` int(11) NOT NULL,
  `no_trabajador` int(11) NOT NULL,
  `no_credencial` varchar(10) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `turno` varchar(20) NOT NULL,
  `hora_entrada` time NOT NULL,
  `eliminado` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `escaneos`
--

CREATE TABLE `escaneos` (
  `id` int(11) NOT NULL,
  `empleado_id` int(11) NOT NULL,
  `fecha_hora` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `asistencias`
--
ALTER TABLE `asistencias`
  ADD PRIMARY KEY (`id`),
  ADD KEY `empleado_id` (`empleado_id`);

--
-- Indices de la tabla `empleado`
--
ALTER TABLE `empleado`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `escaneos`
--
ALTER TABLE `escaneos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_fecha` (`fecha_hora`),
  ADD KEY `idx_empleado` (`empleado_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `asistencias`
--
ALTER TABLE `asistencias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `empleado`
--
ALTER TABLE `empleado`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `escaneos`
--
ALTER TABLE `escaneos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `asistencias`
--
ALTER TABLE `asistencias`
  ADD CONSTRAINT `asistencias_ibfk_1` FOREIGN KEY (`empleado_id`) REFERENCES `empleado` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `escaneos`
--
ALTER TABLE `escaneos`
  ADD CONSTRAINT `escaneos_ibfk_1` FOREIGN KEY (`empleado_id`) REFERENCES `empleado` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;


-- Insertar 50 empleados con datos variados
INSERT INTO empleado (no_trabajador, no_credencial, nombre, turno, hora_entrada) VALUES
(1001, '0001001B', 'Juan Pérez García', 'Matutino', '07:00:00'),
(1002, '0001002B', 'María López Sánchez', 'Vespertino', '14:00:00'),
(1003, '0001003B', 'Carlos Rodríguez Martínez', 'Matutino', '07:00:00'),
(1004, '0001004B', 'Ana González Torres', 'Vespertino', '14:00:00'),
(1005, '0001005B', 'Roberto Díaz Ruiz', 'Matutino', '07:00:00'),
(1006, '0001006B', 'Laura Martínez Castro', 'Vespertino', '14:00:00'),
(1007, '0001007B', 'Miguel Hernández Flores', 'Matutino', '07:00:00'),
(1008, '0001008B', 'Patricia Sánchez Vargas', 'Vespertino', '14:00:00'),
(1009, '0001009B', 'José Torres Mendoza', 'Matutino', '07:00:00'),
(1010, '0001010B', 'Isabel Ramírez Luna', 'Vespertino', '14:00:00'),
(1011, '0001011B', 'Fernando Castro Silva', 'Matutino', '07:00:00'),
(1012, '0001012B', 'Carmen Vargas Rojas', 'Vespertino', '14:00:00'),
(1013, '0001013B', 'Ricardo Morales Paz', 'Matutino', '07:00:00'),
(1014, '0001014B', 'Silvia Ortiz Cruz', 'Vespertino', '14:00:00'),
(1015, '0001015B', 'Alberto Jiménez Vega', 'Matutino', '07:00:00'),
(1016, '0001016B', 'Diana Romero Soto', 'Vespertino', '14:00:00'),
(1017, '0001017B', 'Gabriel Flores Mora', 'Matutino', '07:00:00'),
(1018, '0001018B', 'Mónica Cruz Lima', 'Vespertino', '14:00:00'),
(1019, '0001019B', 'Raúl Mendoza Ríos', 'Matutino', '07:00:00'),
(1020, '0001020B', 'Verónica Luna Paredes', 'Vespertino', '14:00:00'),
(1021, '0001021B', 'Eduardo Silva Campos', 'Matutino', '07:00:00'),
(1022, '0001022B', 'Beatriz Rojas Medina', 'Vespertino', '14:00:00'),
(1023, '0001023B', 'Arturo Paz Guzmán', 'Matutino', '07:00:00'),
(1024, '0001024B', 'Rosa Cruz Valdez', 'Vespertino', '14:00:00'),
(1025, '0001025B', 'Manuel Vega Ramos', 'Matutino', '07:00:00'),
(1026, '0001026B', 'Julia Soto Herrera', 'Vespertino', '14:00:00'),
(1027, '0001027B', 'Francisco Mora León', 'Matutino', '07:00:00'),
(1028, '0001028B', 'Elena Lima Cervantes', 'Vespertino', '14:00:00'),
(1029, '0001029B', 'Andrés Ríos Aguirre', 'Matutino', '07:00:00'),
(1030, '0001030B', 'Lucía Paredes Navarro', 'Vespertino', '14:00:00'),
(1031, '0001031B', 'Hugo Campos Acosta', 'Matutino', '07:00:00'),
(1032, '0001032B', 'Martha Medina Ortega', 'Vespertino', '14:00:00'),
(1033, '0001033B', 'Diego Guzmán Castillo', 'Matutino', '07:00:00'),
(1034, '0001034B', 'Sandra Valdez Rangel', 'Vespertino', '14:00:00'),
(1035, '0001035B', 'Pablo Ramos Mendoza', 'Matutino', '07:00:00'),
(1036, '0001036B', 'Teresa Herrera Santos', 'Vespertino', '14:00:00'),
(1037, '0001037B', 'Alejandro León Juárez', 'Matutino', '07:00:00'),
(1038, '0001038B', 'Cristina Cervantes Vargas', 'Vespertino', '14:00:00'),
(1039, '0001039B', 'Jorge Aguirre Morales', 'Matutino', '07:00:00'),
(1040, '0001040B', 'Adriana Navarro Flores', 'Vespertino', '14:00:00'),
(1041, '0001041B', 'Luis Acosta Martínez', 'Matutino', '07:00:00'),
(1042, '0001042B', 'Gabriela Ortega Sánchez', 'Vespertino', '14:00:00'),
(1043, '0001043B', 'Roberto Castillo Pérez', 'Matutino', '07:00:00'),
(1044, '0001044B', 'Carolina Rangel González', 'Vespertino', '14:00:00'),
(1045, '0001045B', 'Daniel Mendoza Torres', 'Matutino', '07:00:00'),
(1046, '0001046B', 'Mariana Santos Díaz', 'Vespertino', '14:00:00'),
(1047, '0001047B', 'Sergio Juárez Hernández', 'Matutino', '07:00:00'),
(1048, '0001048B', 'Patricia Vargas Ramírez', 'Vespertino', '14:00:00'),
(1049, '0001049B', 'Javier Morales Castro', 'Matutino', '07:00:00'),
(1050, '0001050B', 'Claudia Flores Morales', 'Vespertino', '14:00:00');

-- Primero establecemos la zona horaria
SET time_zone = '-06:00';

-- Variables para las fechas (últimos 5 días)
SET @fecha1 = DATE_SUB(CURDATE(), INTERVAL 4 DAY);
SET @fecha2 = DATE_SUB(CURDATE(), INTERVAL 3 DAY);
SET @fecha3 = DATE_SUB(CURDATE(), INTERVAL 2 DAY);
SET @fecha4 = DATE_SUB(CURDATE(), INTERVAL 1 DAY);
SET @fecha5 = CURDATE();

-- Insertar registros en la tabla asistencias
INSERT INTO asistencias (empleado_id, fecha, hora, tipo) 
SELECT 
    id,
    @fecha1,
    CASE 
        WHEN turno = 'Matutino' THEN ADDTIME(hora_entrada, SEC_TO_TIME(FLOOR(RAND() * 600)))
        ELSE ADDTIME(hora_entrada, SEC_TO_TIME(FLOOR(RAND() * 600)))
    END,
    CASE 
        WHEN RAND() < 0.8 THEN 'consumio'
        ELSE 'no_consumio'
    END
FROM empleado
WHERE id <= 50;

INSERT INTO asistencias (empleado_id, fecha, hora, tipo)
SELECT 
    id,
    @fecha2,
    CASE 
        WHEN turno = 'Matutino' THEN ADDTIME(hora_entrada, SEC_TO_TIME(FLOOR(RAND() * 600)))
        ELSE ADDTIME(hora_entrada, SEC_TO_TIME(FLOOR(RAND() * 600)))
    END,
    CASE 
        WHEN RAND() < 0.8 THEN 'consumio'
        ELSE 'no_consumio'
    END
FROM empleado
WHERE id <= 50 AND RAND() < 0.95; -- 95% de asistencia

INSERT INTO asistencias (empleado_id, fecha, hora, tipo)
SELECT 
    id,
    @fecha3,
    CASE 
        WHEN turno = 'Matutino' THEN ADDTIME(hora_entrada, SEC_TO_TIME(FLOOR(RAND() * 600)))
        ELSE ADDTIME(hora_entrada, SEC_TO_TIME(FLOOR(RAND() * 600)))
    END,
    CASE 
        WHEN RAND() < 0.8 THEN 'consumio'
        ELSE 'no_consumio'
    END
FROM empleado
WHERE id <= 50 AND RAND() < 0.90; -- 90% de asistencia

INSERT INTO asistencias (empleado_id, fecha, hora, tipo)
SELECT 
    id,
    @fecha4,
    CASE 
        WHEN turno = 'Matutino' THEN ADDTIME(hora_entrada, SEC_TO_TIME(FLOOR(RAND() * 600)))
        ELSE ADDTIME(hora_entrada, SEC_TO_TIME(FLOOR(RAND() * 600)))
    END,
    CASE 
        WHEN RAND() < 0.8 THEN 'consumio'
        ELSE 'no_consumio'
    END
FROM empleado
WHERE id <= 50 AND RAND() < 0.93; -- 93% de asistencia

INSERT INTO asistencias (empleado_id, fecha, hora, tipo)
SELECT 
    id,
    @fecha5,
    CASE 
        WHEN turno = 'Matutino' THEN ADDTIME(hora_entrada, SEC_TO_TIME(FLOOR(RAND() * 600)))
        ELSE ADDTIME(hora_entrada, SEC_TO_TIME(FLOOR(RAND() * 600)))
    END,
    CASE 
        WHEN RAND() < 0.8 THEN 'consumio'
        ELSE 'no_consumio'
    END
FROM empleado
WHERE id <= 50 AND RAND() < 0.97; -- 97% de asistencia

-- Insertar registros en la tabla escaneos
INSERT INTO escaneos (empleado_id, fecha_hora)
SELECT 
    a.empleado_id,
    TIMESTAMP(a.fecha, a.hora)
FROM asistencias a;

-- Agregar algunos escaneos adicionales (intentos fallidos o múltiples escaneos)
INSERT INTO escaneos (empleado_id, fecha_hora)
SELECT 
    empleado_id,
    TIMESTAMP(
        fecha, 
        ADDTIME(hora, SEC_TO_TIME(FLOOR(RAND() * 30)))
    )
FROM asistencias
WHERE RAND() < 0.1; -- 10% de escaneos adicionales

