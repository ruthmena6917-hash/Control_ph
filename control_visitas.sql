-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 07-04-2026 a las 21:35:54
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
-- Base de datos: `control_visitas`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `apartamentos`
--

CREATE TABLE `apartamentos` (
  `id` int(11) NOT NULL,
  `numero` varchar(10) NOT NULL,
  `piso` int(11) NOT NULL DEFAULT 1,
  `tipo` varchar(30) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `apartamentos`
--

INSERT INTO `apartamentos` (`id`, `numero`, `piso`, `tipo`, `activo`) VALUES
(1, '101', 1, '1 cuarto', 1),
(2, '102', 1, '2 cuartos', 1),
(3, '201', 2, '1 cuarto', 1),
(4, '202', 2, 'estudio', 1),
(5, '301', 3, '2 cuartos', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `autenticacion`
--

CREATE TABLE `autenticacion` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `ultimo_login` datetime DEFAULT NULL,
  `intentos_fallidos` int(11) NOT NULL DEFAULT 0,
  `bloqueado` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `autenticacion`
--

INSERT INTO `autenticacion` (`id`, `usuario_id`, `username`, `password_hash`, `ultimo_login`, `intentos_fallidos`, `bloqueado`) VALUES
(1, 1, 'gerente', '$2y$10$SLrlvrfl9BSU2BHcfFkGbu7ae0O/JtTfcpTwoG3qfdjpDYbo2Dh9S', NULL, 0, 0),
(2, 2, 'luis.rios', '$2y$10$4P7vRhNvVAmtSJe5mup2p.zKfXRdJe9hLbXycLKSH4hEheiCyzlJS', NULL, 0, 0),
(3, 3, 'ana.torres', '$2y$10$I3Z49fYt3s3P8CksyrNeHOywwg9Yvps5fq9agrHifAFWuAxvNvILK', NULL, 0, 0),
(4, 4, 'maria.g', '$2y$10$2O1JqXMIUNTC5V/OKDkm0.fix/yVBY5KJbF3uOzwpZEvCzyE6R8vy', NULL, 0, 0),
(5, 5, 'pedro.c', '$2y$10$1a0p2MuZM9A.FEx9rrd7Ce7nNHfK.6gMVt8T5DFOgcr1/HnMi56SS', NULL, 0, 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `bitacora_accesos`
--

CREATE TABLE `bitacora_accesos` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `accion` enum('login','logout') NOT NULL,
  `fecha_hora` datetime NOT NULL DEFAULT current_timestamp(),
  `ip` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `bitacora_accesos`
--

INSERT INTO `bitacora_accesos` (`id`, `usuario_id`, `accion`, `fecha_hora`, `ip`) VALUES
(1, 1, 'login', '2026-04-02 15:16:35', NULL),
(2, 1, 'login', '2026-04-02 15:21:12', NULL),
(3, 1, 'login', '2026-04-02 15:27:12', NULL),
(4, 1, 'login', '2026-04-02 15:27:21', NULL),
(5, 1, 'login', '2026-04-02 15:27:33', NULL),
(6, 1, 'login', '2026-04-02 15:30:34', NULL),
(7, 1, 'login', '2026-04-02 15:30:46', NULL),
(8, 1, 'login', '2026-04-02 15:33:16', NULL),
(9, 1, 'login', '2026-04-02 15:37:36', NULL),
(10, 1, 'logout', '2026-04-02 15:43:57', NULL),
(11, 2, 'login', '2026-04-05 14:58:09', NULL),
(12, 2, 'login', '2026-04-06 13:45:27', NULL),
(13, 3, 'login', '2026-04-06 13:48:32', NULL),
(14, 2, 'login', '2026-04-07 13:35:27', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empleados`
--

CREATE TABLE `empleados` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `cargo` varchar(100) NOT NULL,
  `fecha_ingreso` date NOT NULL,
  `fecha_salida` date DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `empleados`
--

INSERT INTO `empleados` (`id`, `usuario_id`, `cargo`, `fecha_ingreso`, `fecha_salida`, `activo`) VALUES
(1, 2, 'Guardia de seguridad', '2022-03-01', NULL, 1),
(2, 3, 'Guardia de seguridad', '2023-05-10', NULL, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `residentes`
--

CREATE TABLE `residentes` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `apartamento_id` int(11) NOT NULL,
  `fecha_ingreso` date NOT NULL,
  `fecha_salida` date DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `residentes`
--

INSERT INTO `residentes` (`id`, `usuario_id`, `apartamento_id`, `fecha_ingreso`, `fecha_salida`, `activo`) VALUES
(1, 4, 1, '2023-01-15', NULL, 1),
(2, 5, 3, '2022-06-01', NULL, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `descripcion` varchar(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id`, `nombre`, `descripcion`) VALUES
(1, 'gerente', 'Administra el residencial completo'),
(2, 'seguridad', 'Controla entradas y salidas en portería'),
(3, 'residente', 'Vive en el edificio, puede registrar visitas'),
(4, 'visitante', 'Persona externa sin acceso al sistema');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `servicios`
--

CREATE TABLE `servicios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `proveedor` varchar(100) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `creado_por` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `servicios`
--

INSERT INTO `servicios` (`id`, `nombre`, `descripcion`, `proveedor`, `telefono`, `activo`, `creado_por`) VALUES
(1, 'Limpieza general', 'Limpieza de áreas comunes', 'CleanPro S.A.', '300-0001', 1, 1),
(2, 'Plomería', 'Reparación de tuberías', 'Fontanería Ruiz', '300-0002', 1, 1),
(3, 'Electricidad', 'Mantenimiento eléctrico', 'ElectroPanama', '300-0003', 1, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `solicitudes_servicio`
--

CREATE TABLE `solicitudes_servicio` (
  `id` int(11) NOT NULL,
  `servicio_id` int(11) NOT NULL,
  `apartamento_id` int(11) NOT NULL,
  `fecha_programada` datetime NOT NULL,
  `estado` enum('pendiente','en_curso','completado','cancelado') NOT NULL DEFAULT 'pendiente',
  `notas` text DEFAULT NULL,
  `creado_por` int(11) NOT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `turnos`
--

CREATE TABLE `turnos` (
  `id` int(11) NOT NULL,
  `empleado_id` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `hora_inicio` time NOT NULL,
  `hora_fin` time NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `turnos`
--

INSERT INTO `turnos` (`id`, `empleado_id`, `fecha`, `hora_inicio`, `hora_fin`, `activo`) VALUES
(1, 1, '2026-03-31', '06:00:00', '14:00:00', 1),
(2, 2, '2026-03-31', '14:00:00', '22:00:00', 0),
(3, 1, '2026-03-31', '06:00:00', '14:00:00', 1),
(4, 2, '2026-03-31', '14:00:00', '22:00:00', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `rol_id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `cedula` varchar(20) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `rol_id`, `nombre`, `cedula`, `telefono`, `email`, `activo`, `fecha_creacion`) VALUES
(1, 1, 'Carlos Méndez', '8-123-456', '6000-0001', 'gerente@residencial.com', 1, '2026-03-31 13:17:45'),
(2, 2, 'Luis Ríos', '4-567-890', '6000-0002', 'luis.rios@residencial.com', 1, '2026-03-31 13:17:45'),
(3, 2, 'Ana Torres', '4-678-901', '6000-0003', 'ana.torres@residencial.com', 1, '2026-03-31 13:17:45'),
(4, 3, 'María González', '8-234-567', '6000-0004', 'maria.g@email.com', 1, '2026-03-31 13:17:45'),
(5, 3, 'Pedro Castillo', '8-345-678', '6000-0005', 'pedro.c@email.com', 1, '2026-03-31 13:17:45');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vehiculos_visita`
--

CREATE TABLE `vehiculos_visita` (
  `id` int(11) NOT NULL,
  `visita_id` int(11) NOT NULL,
  `placa` varchar(20) NOT NULL,
  `tipo` varchar(30) DEFAULT NULL,
  `color` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `visitas`
--

CREATE TABLE `visitas` (
  `id` int(11) NOT NULL,
  `visitante_nombre` varchar(100) NOT NULL,
  `visitante_cedula` varchar(20) NOT NULL,
  `placa_vehiculo` varchar(20) DEFAULT NULL,
  `visitante_telefono` varchar(20) DEFAULT NULL,
  `residente_id` int(11) NOT NULL,
  `apartamento_id` int(11) NOT NULL,
  `fecha_programada` datetime NOT NULL,
  `fecha_entrada_real` datetime DEFAULT NULL,
  `fecha_salida` datetime DEFAULT NULL,
  `estado` enum('pendiente','en_edificio','finalizada','cancelada') NOT NULL DEFAULT 'pendiente',
  `registrado_por` int(11) NOT NULL,
  `validado_por` int(11) DEFAULT NULL,
  `turno_id` int(11) DEFAULT NULL,
  `notas` text DEFAULT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `visitas`
--

INSERT INTO `visitas` (`id`, `visitante_nombre`, `visitante_cedula`, `placa_vehiculo`, `visitante_telefono`, `residente_id`, `apartamento_id`, `fecha_programada`, `fecha_entrada_real`, `fecha_salida`, `estado`, `registrado_por`, `validado_por`, `turno_id`, `notas`, `fecha_creacion`) VALUES
(1, 'Juan Pérez', '8-999-001', NULL, NULL, 1, 1, '2026-03-31 14:18:26', NULL, NULL, 'pendiente', 4, NULL, NULL, NULL, '2026-03-31 13:18:26'),
(2, 'Sofía Mora', '8-999-002', NULL, NULL, 1, 1, '2026-03-31 11:18:26', NULL, NULL, 'en_edificio', 4, NULL, NULL, NULL, '2026-03-31 13:18:26'),
(3, 'Roberto Díaz', '8-999-003', NULL, NULL, 2, 3, '2026-03-31 08:18:26', NULL, NULL, 'finalizada', 5, NULL, NULL, NULL, '2026-03-31 13:18:26');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `apartamentos`
--
ALTER TABLE `apartamentos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_apartamentos_numero` (`numero`);

--
-- Indices de la tabla `autenticacion`
--
ALTER TABLE `autenticacion`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_autenticacion_username` (`username`),
  ADD UNIQUE KEY `uq_autenticacion_usuario` (`usuario_id`);

--
-- Indices de la tabla `bitacora_accesos`
--
ALTER TABLE `bitacora_accesos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_bitacora_usuario` (`usuario_id`);

--
-- Indices de la tabla `empleados`
--
ALTER TABLE `empleados`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_empleados_usuario` (`usuario_id`);

--
-- Indices de la tabla `residentes`
--
ALTER TABLE `residentes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_residentes_usuario` (`usuario_id`),
  ADD KEY `fk_residentes_apartamento` (`apartamento_id`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_roles_nombre` (`nombre`);

--
-- Indices de la tabla `servicios`
--
ALTER TABLE `servicios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_servicios_creado_por` (`creado_por`);

--
-- Indices de la tabla `solicitudes_servicio`
--
ALTER TABLE `solicitudes_servicio`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_solicitud_servicio` (`servicio_id`),
  ADD KEY `fk_solicitud_apartamento` (`apartamento_id`),
  ADD KEY `fk_solicitud_creado_por` (`creado_por`);

--
-- Indices de la tabla `turnos`
--
ALTER TABLE `turnos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_turnos_empleado` (`empleado_id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_usuarios_cedula` (`cedula`),
  ADD UNIQUE KEY `uq_usuarios_email` (`email`),
  ADD KEY `fk_usuarios_rol` (`rol_id`);

--
-- Indices de la tabla `vehiculos_visita`
--
ALTER TABLE `vehiculos_visita`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_vehiculos_visita` (`visita_id`);

--
-- Indices de la tabla `visitas`
--
ALTER TABLE `visitas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_visitas_residente` (`residente_id`),
  ADD KEY `fk_visitas_apartamento` (`apartamento_id`),
  ADD KEY `fk_visitas_registrado_por` (`registrado_por`),
  ADD KEY `fk_visitas_validado_por` (`validado_por`),
  ADD KEY `fk_visitas_turno` (`turno_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `apartamentos`
--
ALTER TABLE `apartamentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `autenticacion`
--
ALTER TABLE `autenticacion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `bitacora_accesos`
--
ALTER TABLE `bitacora_accesos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `empleados`
--
ALTER TABLE `empleados`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `residentes`
--
ALTER TABLE `residentes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `servicios`
--
ALTER TABLE `servicios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `solicitudes_servicio`
--
ALTER TABLE `solicitudes_servicio`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `turnos`
--
ALTER TABLE `turnos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `vehiculos_visita`
--
ALTER TABLE `vehiculos_visita`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `visitas`
--
ALTER TABLE `visitas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `autenticacion`
--
ALTER TABLE `autenticacion`
  ADD CONSTRAINT `fk_autenticacion_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `bitacora_accesos`
--
ALTER TABLE `bitacora_accesos`
  ADD CONSTRAINT `fk_bitacora_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `empleados`
--
ALTER TABLE `empleados`
  ADD CONSTRAINT `fk_empleados_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `residentes`
--
ALTER TABLE `residentes`
  ADD CONSTRAINT `fk_residentes_apartamento` FOREIGN KEY (`apartamento_id`) REFERENCES `apartamentos` (`id`),
  ADD CONSTRAINT `fk_residentes_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `servicios`
--
ALTER TABLE `servicios`
  ADD CONSTRAINT `fk_servicios_creado_por` FOREIGN KEY (`creado_por`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `solicitudes_servicio`
--
ALTER TABLE `solicitudes_servicio`
  ADD CONSTRAINT `fk_solicitud_apartamento` FOREIGN KEY (`apartamento_id`) REFERENCES `apartamentos` (`id`),
  ADD CONSTRAINT `fk_solicitud_creado_por` FOREIGN KEY (`creado_por`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `fk_solicitud_servicio` FOREIGN KEY (`servicio_id`) REFERENCES `servicios` (`id`);

--
-- Filtros para la tabla `turnos`
--
ALTER TABLE `turnos`
  ADD CONSTRAINT `fk_turnos_empleado` FOREIGN KEY (`empleado_id`) REFERENCES `empleados` (`id`);

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `fk_usuarios_rol` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`);

--
-- Filtros para la tabla `vehiculos_visita`
--
ALTER TABLE `vehiculos_visita`
  ADD CONSTRAINT `fk_vehiculos_visita` FOREIGN KEY (`visita_id`) REFERENCES `visitas` (`id`);

--
-- Filtros para la tabla `visitas`
--
ALTER TABLE `visitas`
  ADD CONSTRAINT `fk_visitas_apartamento` FOREIGN KEY (`apartamento_id`) REFERENCES `apartamentos` (`id`),
  ADD CONSTRAINT `fk_visitas_registrado_por` FOREIGN KEY (`registrado_por`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `fk_visitas_residente` FOREIGN KEY (`residente_id`) REFERENCES `residentes` (`id`),
  ADD CONSTRAINT `fk_visitas_turno` FOREIGN KEY (`turno_id`) REFERENCES `turnos` (`id`),
  ADD CONSTRAINT `fk_visitas_validado_por` FOREIGN KEY (`validado_por`) REFERENCES `usuarios` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
