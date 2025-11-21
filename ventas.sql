-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 21-11-2025 a las 22:36:10
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
-- Base de datos: `bdigital_ventas`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ventas`
--

CREATE TABLE `ventas` (
  `id` int(11) NOT NULL,
  `folio` varchar(20) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `numero_cuenta` varchar(50) DEFAULT NULL,
  `fecha_servicio` date NOT NULL,
  `puerto` varchar(50) DEFAULT NULL,
  `placa` varchar(50) DEFAULT NULL,
  `tipo_servicio` enum('instalacion','soporte','cambio_domicilio','addons') NOT NULL,
  `nombre_titular` varchar(150) NOT NULL,
  `calle` varchar(200) NOT NULL,
  `numero_interior` varchar(20) DEFAULT NULL,
  `numero_exterior` varchar(20) NOT NULL,
  `colonia` varchar(100) NOT NULL,
  `delegacion_municipio` varchar(100) NOT NULL,
  `codigo_postal` varchar(10) NOT NULL,
  `telefono` varchar(20) NOT NULL,
  `celular` varchar(20) DEFAULT NULL,
  `tipo_vivienda` enum('casa','departamento','negocio','empresarial','otro') NOT NULL,
  `tipo_vivienda_otro` varchar(100) DEFAULT NULL,
  `referencias` text DEFAULT NULL,
  `paquete_contratado` varchar(100) NOT NULL,
  `tipo_promocion` varchar(100) DEFAULT NULL,
  `correo_electronico` varchar(100) DEFAULT NULL,
  `identificacion` varchar(50) DEFAULT NULL,
  `contrato_entregado` tinyint(1) DEFAULT 0,
  `ont_modelo` varchar(100) DEFAULT NULL,
  `ont_serie` varchar(100) DEFAULT NULL,
  `otro_equipo_modelo` varchar(100) DEFAULT NULL,
  `otro_equipo_serie` varchar(100) DEFAULT NULL,
  `materiales_utilizados` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`materiales_utilizados`)),
  `notas_instalacion` text DEFAULT NULL,
  `instalador_nombre` varchar(100) DEFAULT NULL,
  `instalador_firma` varchar(255) DEFAULT NULL,
  `instalador_numero` varchar(50) DEFAULT NULL,
  `eval_servicios_explicados` tinyint(1) DEFAULT NULL,
  `eval_manual_entregado` tinyint(1) DEFAULT NULL,
  `eval_trato_recibido` enum('excelente','bueno','regular','malo') DEFAULT NULL,
  `eval_eficiencia` enum('excelente','bueno','regular','malo') DEFAULT NULL,
  `pdf_path` varchar(255) DEFAULT NULL,
  `qr_code_path` varchar(255) DEFAULT NULL,
  `estatus` enum('activa','cancelada','completada') DEFAULT 'activa',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `ventas`
--

INSERT INTO `ventas` (`id`, `folio`, `usuario_id`, `numero_cuenta`, `fecha_servicio`, `puerto`, `placa`, `tipo_servicio`, `nombre_titular`, `calle`, `numero_interior`, `numero_exterior`, `colonia`, `delegacion_municipio`, `codigo_postal`, `telefono`, `celular`, `tipo_vivienda`, `tipo_vivienda_otro`, `referencias`, `paquete_contratado`, `tipo_promocion`, `correo_electronico`, `identificacion`, `contrato_entregado`, `ont_modelo`, `ont_serie`, `otro_equipo_modelo`, `otro_equipo_serie`, `materiales_utilizados`, `notas_instalacion`, `instalador_nombre`, `instalador_firma`, `instalador_numero`, `eval_servicios_explicados`, `eval_manual_entregado`, `eval_trato_recibido`, `eval_eficiencia`, `pdf_path`, `qr_code_path`, `estatus`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(1, 'BGD-20251121-0001', 1, NULL, '2025-11-21', NULL, NULL, 'instalacion', 'ALDAMA BAUTISTA ALVARO', 'silviano enriquez 204', '', '203', 'Doctores', 'Toluca de Lerdo', '50130', '72283373838', '7227453989', 'casa', NULL, NULL, 'Internet 100MB', NULL, 'alvaro69@gmail.com', NULL, 0, 'nfuh2727', '7yrh723r', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'activa', '2025-11-21 21:12:46', '2025-11-21 21:12:46');

--
-- Disparadores `ventas`
--
DELIMITER $$
CREATE TRIGGER `after_venta_insert` AFTER INSERT ON `ventas` FOR EACH ROW BEGIN
    INSERT INTO logs_actividad (usuario_id, accion, descripcion)
    VALUES (NEW.usuario_id, 'NUEVA_VENTA', CONCAT('Se creó la venta con folio: ', NEW.folio));
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `after_venta_update` AFTER UPDATE ON `ventas` FOR EACH ROW BEGIN
    INSERT INTO logs_actividad (usuario_id, accion, descripcion)
    VALUES (NEW.usuario_id, 'ACTUALIZAR_VENTA', CONCAT('Se actualizó la venta con folio: ', NEW.folio));
END
$$
DELIMITER ;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `ventas`
--
ALTER TABLE `ventas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `folio` (`folio`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `idx_folio` (`folio`),
  ADD KEY `idx_fecha` (`fecha_servicio`),
  ADD KEY `idx_titular` (`nombre_titular`),
  ADD KEY `idx_estatus` (`estatus`),
  ADD KEY `idx_codigo_postal` (`codigo_postal`);
ALTER TABLE `ventas` ADD FULLTEXT KEY `idx_fulltext_titular` (`nombre_titular`,`calle`,`colonia`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `ventas`
--
ALTER TABLE `ventas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `ventas`
--
ALTER TABLE `ventas`
  ADD CONSTRAINT `ventas_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
