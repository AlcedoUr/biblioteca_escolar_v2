-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 23-11-2025 a las 03:32:55
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
-- Base de datos: `biblioteca_escolar`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_prestamo`
--

CREATE TABLE `detalle_prestamo` (
  `id` int(11) NOT NULL,
  `id_prestamo` int(11) NOT NULL,
  `id_libro` int(11) NOT NULL,
  `id_persona_causante` int(11) DEFAULT NULL,
  `cantidad` int(11) NOT NULL DEFAULT 1,
  `estado_devolucion` enum('PENDIENTE','BUENO','DAÑADO','PERDIDO') NOT NULL DEFAULT 'PENDIENTE',
  `estado_resolucion` enum('PENDIENTE','RESUELTO') DEFAULT 'PENDIENTE',
  `tipo_resolucion` enum('NINGUNA','REPOSICION','PAGO','REPARACION','CONDONADO') DEFAULT 'NINGUNA',
  `monto_compensacion` decimal(10,2) DEFAULT 0.00,
  `observaciones_incidencia` text DEFAULT NULL,
  `fecha_resolucion` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `detalle_prestamo`
--

INSERT INTO `detalle_prestamo` (`id`, `id_prestamo`, `id_libro`, `id_persona_causante`, `cantidad`, `estado_devolucion`, `estado_resolucion`, `tipo_resolucion`, `monto_compensacion`, `observaciones_incidencia`, `fecha_resolucion`) VALUES
(1, 1, 4, NULL, 1, 'BUENO', 'PENDIENTE', 'NINGUNA', 0.00, NULL, NULL),
(2, 2, 1, NULL, 5, 'BUENO', 'PENDIENTE', 'NINGUNA', 0.00, NULL, NULL),
(3, 3, 6, NULL, 6, 'BUENO', 'PENDIENTE', 'NINGUNA', 0.00, NULL, NULL),
(4, 4, 8, NULL, 10, 'PENDIENTE', 'PENDIENTE', 'NINGUNA', 0.00, NULL, NULL),
(5, 5, 6, NULL, 10, 'PENDIENTE', 'PENDIENTE', 'NINGUNA', 0.00, NULL, NULL),
(6, 6, 1, 6, 10, 'BUENO', 'PENDIENTE', 'NINGUNA', 0.00, NULL, NULL),
(7, 7, 5, NULL, 4, 'BUENO', 'PENDIENTE', 'NINGUNA', 0.00, NULL, NULL),
(8, 8, 8, 2, 8, 'BUENO', 'PENDIENTE', 'NINGUNA', 0.00, NULL, NULL),
(9, 9, 6, NULL, 1, 'BUENO', 'PENDIENTE', 'NINGUNA', 0.00, NULL, NULL),
(10, 9, 5, NULL, 1, 'BUENO', 'PENDIENTE', 'NINGUNA', 0.00, NULL, NULL),
(11, 9, 4, NULL, 1, 'DAÑADO', 'RESUELTO', 'REPARACION', 0.00, '', '2025-11-22 11:20:52'),
(12, 9, 3, NULL, 1, 'PENDIENTE', 'PENDIENTE', 'NINGUNA', 0.00, NULL, NULL),
(13, 9, 2, NULL, 1, 'PENDIENTE', 'PENDIENTE', 'NINGUNA', 0.00, NULL, NULL),
(14, 10, 6, 3, 1, 'PERDIDO', 'PENDIENTE', 'NINGUNA', 0.00, NULL, NULL),
(15, 11, 5, 2, 1, 'DAÑADO', 'PENDIENTE', 'NINGUNA', 0.00, NULL, NULL),
(16, 12, 5, NULL, 1, 'PERDIDO', 'RESUELTO', 'REPOSICION', 0.00, '', '2025-11-22 11:20:42'),
(17, 13, 9, 3, 1, 'PERDIDO', 'RESUELTO', 'PAGO', 35.00, '', '2025-11-22 11:20:38'),
(18, 7, 5, NULL, 1, 'DAÑADO', 'RESUELTO', 'CONDONADO', 0.00, '', '2025-11-22 11:20:58'),
(19, 13, 9, 2, 19, 'BUENO', 'PENDIENTE', 'NINGUNA', 0.00, NULL, NULL),
(20, 8, 8, 2, 1, 'PERDIDO', 'PENDIENTE', 'NINGUNA', 0.00, NULL, NULL),
(21, 8, 8, 5, 1, 'PERDIDO', 'PENDIENTE', 'NINGUNA', 0.00, NULL, NULL),
(22, 14, 2, 5, 1, 'BUENO', 'PENDIENTE', 'NINGUNA', 0.00, NULL, NULL),
(23, 15, 2, 5, 1, 'BUENO', 'PENDIENTE', 'NINGUNA', 0.00, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `libros`
--

CREATE TABLE `libros` (
  `id` int(11) NOT NULL,
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
  `url_digital` varchar(500) DEFAULT NULL,
  `estado_fisico` enum('BUENO','REGULAR','MALO') DEFAULT 'BUENO'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `libros`
--

INSERT INTO `libros` (`id`, `isbn`, `titulo`, `autor`, `categoria`, `editorial`, `anio`, `stock_total`, `stock_disponible`, `fecha_registro`, `imagen_portada`, `url_digital`, `estado_fisico`) VALUES
(1, NULL, 'Álgebra de Baldor', 'Aurelio Baldor', 'General', 'Grupo Editorial Patria', 2005, 10, 10, '2025-11-20 09:58:05', NULL, NULL, 'BUENO'),
(2, NULL, 'Cien Años de Soledad', 'Gabriel García Márquez', 'General', 'Sudamericana', 1967, 5, 4, '2025-11-20 09:58:05', NULL, NULL, 'BUENO'),
(3, NULL, 'Historia del Perú', 'Jorge Basadre', 'General', 'Ediciones Historia', 2010, 3, 2, '2025-11-20 09:58:05', NULL, NULL, 'BUENO'),
(4, NULL, 'El Principito', 'Antoine de Saint-Exupéry', 'General', 'Reynal & Hitchcock', 1943, 8, 6, '2025-11-20 09:58:05', NULL, NULL, 'BUENO'),
(5, NULL, 'Biología Moderna', 'Varios Autores', 'Matemática', 'Santillana', 2019, 15, 12, '2025-11-20 09:58:05', NULL, NULL, 'BUENO'),
(6, NULL, 'Matematicas', 'Minedu', 'General', '', NULL, 30, 9, '2025-11-20 10:14:54', NULL, NULL, 'BUENO'),
(8, NULL, 'COMUNICACION ', 'MINEDU', 'Comunicacion', 'PRUEBA', NULL, 20, 8, '2025-11-21 15:31:53', NULL, NULL, 'BUENO'),
(9, NULL, 'ciencias solciales', 'minedu', 'ciencias', '123', NULL, 100, 99, '2025-11-22 00:23:07', NULL, NULL, 'BUENO'),
(13, '123-4-5678-9123-4', 'nuevo', 'yo', 'Matemática', '123', NULL, 10, 10, '2025-11-22 15:30:36', NULL, 'https://repositorio.minedu.gob.pe/bitstream/handle/20.500.12799/11744/El%20c%c3%b3ndor%20y%20los%20loros%20munchas.%20Textos%20seleccionados%20del%20Premio%20Nacionalde%20Narrativa%20y%20Ensayo%20Jos%c3%a9%20Mar%c3%ada%20Arguedas%202025.%20Etapas%20I.E%2c%20UGEL%20y%20DRE.pdf?sequence=1&isAllowed=y', 'BUENO'),
(14, '978-612-345-678-1', 'Matemática para Todos', 'Aurelio Baldor', 'Matemática', 'Grupo Patria', NULL, 20, 20, '2025-11-23 01:10:33', NULL, '', 'BUENO'),
(15, '978-84-376-0494-7', 'La Ciudad y los Perros', 'Mario Vargas Llosa', 'Literatura', 'Alfaguara', NULL, 15, 15, '2025-11-23 01:10:33', NULL, '', 'BUENO'),
(16, '', 'Historia de la República', 'Jorge Basadre', 'Historia', 'Ediciones Historia', NULL, 5, 5, '2025-11-23 01:10:33', NULL, '', 'BUENO'),
(17, '978-0-14-028333-4', 'Biología Moderna', 'Varios Autores', 'Ciencias', 'Santillana', NULL, 30, 30, '2025-11-23 01:10:33', NULL, '', 'BUENO');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `personas`
--

CREATE TABLE `personas` (
  `id` int(11) NOT NULL,
  `nombres` varchar(100) NOT NULL,
  `apellidos` varchar(100) NOT NULL,
  `dni` varchar(20) NOT NULL,
  `tipo` enum('ESTUDIANTE','DOCENTE') NOT NULL,
  `especialidad` varchar(100) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `grado` varchar(20) DEFAULT NULL COMMENT 'Solo para estudiantes',
  `seccion` varchar(10) DEFAULT NULL COMMENT 'Solo para estudiantes',
  `estado_biblioteca` enum('ACTIVO','BLOQUEADO') NOT NULL DEFAULT 'ACTIVO',
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `personas`
--

INSERT INTO `personas` (`id`, `nombres`, `apellidos`, `dni`, `tipo`, `especialidad`, `telefono`, `grado`, `seccion`, `estado_biblioteca`, `fecha_registro`) VALUES
(1, 'Roberto', 'Gomez Bolaños', '10000001', 'DOCENTE', NULL, NULL, NULL, NULL, 'ACTIVO', '2025-11-20 09:58:05'),
(2, 'Florinda', 'Meza', '10000002', 'DOCENTE', NULL, NULL, NULL, NULL, 'ACTIVO', '2025-11-20 09:58:05'),
(3, 'Carlos', 'Villagran', '20000001', 'ESTUDIANTE', NULL, NULL, '5to', 'A', 'ACTIVO', '2025-11-20 09:58:05'),
(4, 'Maria', 'Antonieta', '20000002', 'ESTUDIANTE', NULL, NULL, '1ro', 'B', 'ACTIVO', '2025-11-20 09:58:05'),
(5, 'jose', 'alcedo', '12345678', 'ESTUDIANTE', NULL, NULL, '1ro', 'f', 'ACTIVO', '2025-11-21 16:05:25'),
(6, 'manuel', 'urbano', '12345677', 'DOCENTE', 'Historial Social', '123456789', NULL, NULL, 'ACTIVO', '2025-11-21 16:06:07'),
(7, 'Carlos', 'Ramos Quispe', '74859612', 'ESTUDIANTE', NULL, NULL, '2do', 'B', 'ACTIVO', '2025-11-23 01:24:20'),
(8, 'Jimena', 'Torres Salazar', '73214589', 'ESTUDIANTE', NULL, NULL, '5to', 'C', 'ACTIVO', '2025-11-23 01:24:20'),
(9, 'Luis Alberto', 'Mendoza Rojas', '45879612', 'DOCENTE', NULL, NULL, NULL, NULL, 'ACTIVO', '2025-11-23 01:24:20'),
(10, 'Patricia', 'Gutiérrez Flores', '41258963', 'DOCENTE', NULL, NULL, NULL, NULL, 'ACTIVO', '2025-11-23 01:24:20'),
(11, 'Jaime', 'Cárdenas Soto', '40125896', 'DOCENTE', NULL, NULL, NULL, NULL, 'ACTIVO', '2025-11-23 01:24:20');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `prestamos`
--

CREATE TABLE `prestamos` (
  `id` int(11) NOT NULL,
  `id_persona_solicitante` int(11) NOT NULL,
  `id_usuario_bibliotecario` int(11) NOT NULL,
  `fecha_prestamo` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_devolucion_pactada` date NOT NULL,
  `fecha_devolucion_real` datetime DEFAULT NULL,
  `estado` enum('PENDIENTE','FINALIZADO','CON_INCIDENCIA') NOT NULL DEFAULT 'PENDIENTE',
  `observaciones` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `prestamos`
--

INSERT INTO `prestamos` (`id`, `id_persona_solicitante`, `id_usuario_bibliotecario`, `fecha_prestamo`, `fecha_devolucion_pactada`, `fecha_devolucion_real`, `estado`, `observaciones`) VALUES
(1, 3, 1, '2025-11-20 04:58:05', '2025-11-27', NULL, 'FINALIZADO', 'Préstamo de prueba para lectura domiciliaria.'),
(2, 2, 1, '2025-11-20 05:18:49', '2025-11-27', NULL, 'FINALIZADO', NULL),
(3, 4, 1, '2025-11-20 20:54:24', '2025-11-28', NULL, 'FINALIZADO', NULL),
(4, 5, 1, '2025-11-21 11:53:11', '2025-11-26', NULL, 'PENDIENTE', NULL),
(5, 6, 1, '2025-11-21 11:55:20', '2025-11-28', NULL, 'PENDIENTE', NULL),
(6, 6, 1, '2025-11-21 17:37:02', '2025-11-21', '2025-11-22 20:05:22', 'FINALIZADO', 'Tipo: En Aula | Devolución límite hoy a las: 10:15 | Destino: Aula 5to \"B\"'),
(7, 3, 1, '2025-11-21 17:49:54', '2025-11-21', '2025-11-22 10:47:31', 'FINALIZADO', 'Tipo: En Aula | Devolución límite hoy a las: 13:05'),
(8, 2, 1, '2025-11-21 17:50:19', '2025-11-21', '2025-11-22 11:36:52', 'FINALIZADO', 'Tipo: En Aula | Devolución límite hoy a las: 13:05 | Destino: Aula 1ro \"A\"'),
(9, 3, 1, '2025-11-21 18:23:15', '2025-11-24', NULL, 'PENDIENTE', 'Tipo: Domicilio'),
(10, 3, 1, '2025-11-21 19:09:18', '2025-11-24', '2025-11-22 11:34:41', 'FINALIZADO', 'Tipo: Domicilio'),
(11, 2, 1, '2025-11-21 19:16:37', '2025-11-24', '2025-11-22 11:22:06', 'FINALIZADO', 'Tipo: Domicilio | Devolución límite a las: 07:30 | Destino: Aula 1ro \"E\"'),
(12, 3, 1, '2025-11-21 19:16:56', '2025-11-24', '2025-11-22 10:52:59', 'FINALIZADO', 'Tipo: Domicilio | Devolución límite a las: 13:05'),
(13, 2, 1, '2025-11-21 19:26:43', '2025-11-26', '2025-11-22 11:16:22', 'FINALIZADO', 'Tipo: Domicilio | Devolución límite a las: 09:45 | Destino: Aula 2do \"B\"'),
(14, 5, 1, '2025-11-22 14:04:51', '2025-11-26', '2025-11-22 19:41:17', 'FINALIZADO', 'Tipo: Domicilio (Días) | Destino: Aula 3ro \"C\" | Devolución límite a las: 10:20'),
(15, 5, 1, '2025-11-22 14:04:53', '2025-11-26', '2025-11-22 19:41:11', 'FINALIZADO', 'Tipo: Domicilio (Días) | Destino: Aula 3ro \"C\" | Devolución límite a las: 10:20');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reservas`
--

CREATE TABLE `reservas` (
  `id` int(11) NOT NULL,
  `id_libro` int(11) NOT NULL,
  `id_usuario_solicitante` int(11) NOT NULL,
  `fecha_reserva` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_uso` date NOT NULL,
  `estado` enum('PENDIENTE','ENTREGADA','CANCELADA','VENCIDA') DEFAULT 'PENDIENTE',
  `cantidad` int(11) NOT NULL DEFAULT 1,
  `hora_inicio` time NOT NULL,
  `hora_fin` time NOT NULL,
  `grado` varchar(20) DEFAULT NULL,
  `seccion` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `reservas`
--

INSERT INTO `reservas` (`id`, `id_libro`, `id_usuario_solicitante`, `fecha_reserva`, `fecha_uso`, `estado`, `cantidad`, `hora_inicio`, `hora_fin`, `grado`, `seccion`) VALUES
(1, 8, 4, '2025-11-22 12:57:27', '2025-11-23', 'PENDIENTE', 1, '07:45:00', '08:30:00', 'a', 'c'),
(2, 6, 1, '2025-11-22 13:01:23', '2025-11-25', 'PENDIENTE', 20, '07:45:00', '10:15:00', '1', 'c'),
(3, 2, 1, '2025-11-22 13:20:10', '2025-11-24', 'PENDIENTE', 1, '07:45:00', '10:15:00', '1ro', 'F'),
(4, 2, 1, '2025-11-22 14:03:02', '2025-11-26', 'CANCELADA', 4, '12:45:00', '13:30:00', '5to', 'A');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `id_persona` int(11) DEFAULT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL COMMENT 'En producción usar hash (ej. Argon2/Bcrypt)',
  `rol` enum('ADMINISTRADOR','BIBLIOTECARIO','DOCENTE','ESTUDIANTE') NOT NULL DEFAULT 'ESTUDIANTE',
  `nombre_completo` varchar(100) NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `id_persona`, `username`, `password`, `rol`, `nombre_completo`, `fecha_creacion`) VALUES
(1, NULL, 'admin', '12345678', 'ADMINISTRADOR', 'Administrador del Sistema', '2025-11-20 09:58:04'),
(2, NULL, 'bibliotecario', 'biblio123', 'BIBLIOTECARIO', 'Encargado de Biblioteca', '2025-11-21 00:45:38'),
(3, NULL, 'profe1', 'profe123', 'DOCENTE', 'Profesor Juan Perez', '2025-11-21 00:45:38'),
(4, 1, 'groberto', '10000001', 'DOCENTE', 'Roberto Gomez Bolaños', '2025-11-22 17:39:41');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `detalle_prestamo`
--
ALTER TABLE `detalle_prestamo`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_detalle_prestamo` (`id_prestamo`),
  ADD KEY `fk_detalle_libro` (`id_libro`),
  ADD KEY `fk_detalle_causante` (`id_persona_causante`);

--
-- Indices de la tabla `libros`
--
ALTER TABLE `libros`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `personas`
--
ALTER TABLE `personas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `dni` (`dni`);

--
-- Indices de la tabla `prestamos`
--
ALTER TABLE `prestamos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_prestamos_persona` (`id_persona_solicitante`),
  ADD KEY `fk_prestamos_usuario` (`id_usuario_bibliotecario`);

--
-- Indices de la tabla `reservas`
--
ALTER TABLE `reservas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_reserva_libro` (`id_libro`),
  ADD KEY `fk_reserva_usuario` (`id_usuario_solicitante`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `fk_usuario_persona` (`id_persona`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `detalle_prestamo`
--
ALTER TABLE `detalle_prestamo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT de la tabla `libros`
--
ALTER TABLE `libros`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de la tabla `personas`
--
ALTER TABLE `personas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `prestamos`
--
ALTER TABLE `prestamos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `reservas`
--
ALTER TABLE `reservas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `detalle_prestamo`
--
ALTER TABLE `detalle_prestamo`
  ADD CONSTRAINT `fk_detalle_causante` FOREIGN KEY (`id_persona_causante`) REFERENCES `personas` (`id`),
  ADD CONSTRAINT `fk_detalle_libro` FOREIGN KEY (`id_libro`) REFERENCES `libros` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_detalle_prestamo` FOREIGN KEY (`id_prestamo`) REFERENCES `prestamos` (`id`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `prestamos`
--
ALTER TABLE `prestamos`
  ADD CONSTRAINT `fk_prestamos_persona` FOREIGN KEY (`id_persona_solicitante`) REFERENCES `personas` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_prestamos_usuario` FOREIGN KEY (`id_usuario_bibliotecario`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `reservas`
--
ALTER TABLE `reservas`
  ADD CONSTRAINT `fk_reserva_libro` FOREIGN KEY (`id_libro`) REFERENCES `libros` (`id`),
  ADD CONSTRAINT `fk_reserva_usuario` FOREIGN KEY (`id_usuario_solicitante`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `fk_usuario_persona` FOREIGN KEY (`id_persona`) REFERENCES `personas` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
