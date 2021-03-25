-- phpMyAdmin SQL Dump
-- version 5.0.3
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 24-03-2021 a las 13:28:43
-- Versión del servidor: 5.6.47
-- Versión de PHP: 7.4.13

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `gestor_clyb`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `chat`
--

CREATE TABLE `chat` (
  `id` bigint(20) NOT NULL,
  `de` bigint(20) NOT NULL,
  `para` bigint(20) NOT NULL,
  `texto` text COLLATE utf8_spanish_ci NOT NULL,
  `fecha` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `id` bigint(20) NOT NULL,
  `idu` bigint(20) NOT NULL,
  `nombre` text COLLATE utf8_spanish_ci NOT NULL,
  `apellidos` text COLLATE utf8_spanish_ci NOT NULL,
  `negocio` text COLLATE utf8_spanish_ci NOT NULL,
  `direccion` text COLLATE utf8_spanish_ci NOT NULL,
  `cp` text COLLATE utf8_spanish_ci NOT NULL,
  `email` text COLLATE utf8_spanish_ci NOT NULL,
  `telefono` text COLLATE utf8_spanish_ci NOT NULL,
  `foto` int(1) NOT NULL,
  `tiponegocio` text COLLATE utf8_spanish_ci NOT NULL,
  `cif` text COLLATE utf8_spanish_ci NOT NULL,
  `web` text COLLATE utf8_spanish_ci NOT NULL,
  `instagram` text COLLATE utf8_spanish_ci NOT NULL,
  `facebook` text COLLATE utf8_spanish_ci NOT NULL,
  `clave` text COLLATE utf8_spanish_ci NOT NULL,
  `idstripe` text COLLATE utf8_spanish_ci NOT NULL,
  `idtarjetastripe` text COLLATE utf8_spanish_ci NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `idscb` text COLLATE utf8_spanish_ci NOT NULL,
  `idstb` text COLLATE utf8_spanish_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes_potenciales`
--

CREATE TABLE `clientes_potenciales` (
  `id` bigint(20) NOT NULL,
  `idu` bigint(20) NOT NULL,
  `nombre` text COLLATE utf8_spanish_ci NOT NULL,
  `apellidos` text COLLATE utf8_spanish_ci NOT NULL,
  `negocio` text COLLATE utf8_spanish_ci NOT NULL,
  `email` text COLLATE utf8_spanish_ci NOT NULL,
  `telefono` text COLLATE utf8_spanish_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `codigos`
--

CREATE TABLE `codigos` (
  `id` bigint(20) NOT NULL,
  `idcliente` bigint(20) NOT NULL,
  `pass` text COLLATE utf8_spanish_ci NOT NULL,
  `movil` text COLLATE utf8_spanish_ci NOT NULL,
  `codigo` text COLLATE utf8_spanish_ci NOT NULL,
  `ip` text COLLATE utf8_spanish_ci NOT NULL,
  `fecha` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `intentos` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `envios`
--

CREATE TABLE `envios` (
  `id` bigint(20) NOT NULL,
  `idu` bigint(20) NOT NULL,
  `email` text COLLATE utf8_spanish_ci NOT NULL,
  `plantilla` bigint(20) NOT NULL,
  `fichero` bigint(20) NOT NULL,
  `fechaenvio` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `facturas`
--

CREATE TABLE `facturas` (
  `id` bigint(20) NOT NULL,
  `idc` bigint(20) NOT NULL,
  `idp` bigint(20) NOT NULL,
  `serie` text COLLATE utf8_spanish_ci NOT NULL,
  `numero` int(11) NOT NULL,
  `fecha` datetime NOT NULL,
  `importe` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `facturas_conceptos`
--

CREATE TABLE `facturas_conceptos` (
  `id` bigint(11) NOT NULL,
  `idf` int(11) NOT NULL,
  `concepto` text COLLATE utf8_spanish_ci NOT NULL,
  `importe` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ficheros`
--

CREATE TABLE `ficheros` (
  `id` bigint(20) NOT NULL,
  `nombre` text COLLATE utf8_spanish_ci NOT NULL,
  `fichero` text COLLATE utf8_spanish_ci NOT NULL,
  `perfiles` text COLLATE utf8_spanish_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `logs`
--

CREATE TABLE `logs` (
  `id` bigint(20) NOT NULL,
  `valor1` int(11) NOT NULL,
  `valor2` int(11) NOT NULL,
  `valor3` int(11) NOT NULL,
  `texto1` text COLLATE utf8_spanish_ci NOT NULL,
  `texto2` text COLLATE utf8_spanish_ci NOT NULL,
  `texto3` text COLLATE utf8_spanish_ci NOT NULL,
  `ip` text COLLATE utf8_spanish_ci NOT NULL,
  `fecha` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `muro`
--

CREATE TABLE `muro` (
  `id` bigint(20) NOT NULL,
  `autor_ico` tinyint(1) NOT NULL,
  `autor_nombre` text COLLATE utf8_spanish_ci NOT NULL,
  `titulo` text COLLATE utf8_spanish_ci NOT NULL,
  `subtitulo` text COLLATE utf8_spanish_ci NOT NULL,
  `descripcion` text COLLATE utf8_spanish_ci NOT NULL,
  `foto` tinyint(1) NOT NULL,
  `video` text COLLATE utf8_spanish_ci NOT NULL,
  `servicioid` bigint(11) NOT NULL,
  `orden` int(11) NOT NULL DEFAULT '999999999',
  `activo` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `perfiles`
--

CREATE TABLE `perfiles` (
  `id` int(11) NOT NULL,
  `nombre` text COLLATE utf8_spanish_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `plantillas`
--

CREATE TABLE `plantillas` (
  `id` bigint(20) NOT NULL,
  `nombre` text COLLATE utf8_spanish_ci NOT NULL,
  `asunto` text COLLATE utf8_spanish_ci NOT NULL,
  `contenido` text COLLATE utf8_spanish_ci NOT NULL,
  `perfiles` text COLLATE utf8_spanish_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `presupuestos`
--

CREATE TABLE `presupuestos` (
  `id` bigint(20) NOT NULL,
  `idc` bigint(20) NOT NULL,
  `ids` bigint(20) NOT NULL,
  `alternativo` text COLLATE utf8_spanish_ci NOT NULL,
  `texto_superior` text COLLATE utf8_spanish_ci NOT NULL,
  `texto_inferior` text COLLATE utf8_spanish_ci NOT NULL,
  `importe_fijo` double NOT NULL,
  `importe_mensual` double NOT NULL,
  `fechacreacion` datetime NOT NULL,
  `fechacontratacion` datetime NOT NULL,
  `fecharenovacion` datetime NOT NULL,
  `fechacancelacion` datetime NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `presupuestos_conceptos`
--

CREATE TABLE `presupuestos_conceptos` (
  `id` bigint(11) NOT NULL,
  `idp` int(11) NOT NULL,
  `concepto` text COLLATE utf8_spanish_ci NOT NULL,
  `importe` double NOT NULL,
  `mensual` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id` bigint(20) NOT NULL,
  `idc` bigint(20) NOT NULL,
  `producto` text COLLATE utf8_spanish_ci NOT NULL,
  `descripcion` text COLLATE utf8_spanish_ci NOT NULL,
  `importe` double NOT NULL,
  `mensual` tinyint(1) NOT NULL,
  `fechacreacion` datetime NOT NULL,
  `fechacontratacion` datetime NOT NULL,
  `fecharenovacion` datetime NOT NULL,
  `fechacancelacion` datetime NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos_old`
--

CREATE TABLE `productos_old` (
  `id` bigint(20) NOT NULL,
  `idc` bigint(20) NOT NULL,
  `producto` text COLLATE utf8_spanish_ci NOT NULL,
  `descripcion` text COLLATE utf8_spanish_ci NOT NULL,
  `importe` double NOT NULL,
  `mensual` tinyint(1) NOT NULL,
  `fechacreacion` datetime NOT NULL,
  `fechacontratacion` datetime NOT NULL,
  `fecharenovacion` datetime NOT NULL,
  `fechacancelacion` datetime NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `servicios`
--

CREATE TABLE `servicios` (
  `id` bigint(20) NOT NULL,
  `nombre` text COLLATE utf8_spanish_ci NOT NULL,
  `resumen` text COLLATE utf8_spanish_ci NOT NULL,
  `descripcion` text COLLATE utf8_spanish_ci NOT NULL,
  `orden` int(11) NOT NULL DEFAULT '999999',
  `version` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tarjetas`
--

CREATE TABLE `tarjetas` (
  `id` bigint(20) NOT NULL,
  `idc` bigint(20) NOT NULL,
  `tarjeta` text COLLATE utf8_spanish_ci NOT NULL,
  `mes` int(11) NOT NULL,
  `ano` int(11) NOT NULL,
  `idstripe` text COLLATE utf8_spanish_ci NOT NULL,
  `predeterminada` tinyint(1) NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `transacciones`
--

CREATE TABLE `transacciones` (
  `id` bigint(20) NOT NULL,
  `idc` bigint(20) NOT NULL,
  `idp` bigint(20) NOT NULL,
  `idstripe` text COLLATE utf8_spanish_ci NOT NULL,
  `importe` double NOT NULL,
  `cargado` double NOT NULL,
  `estado` text COLLATE utf8_spanish_ci NOT NULL,
  `fecha` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `transacciones_old`
--

CREATE TABLE `transacciones_old` (
  `id` bigint(20) NOT NULL,
  `idc` bigint(20) NOT NULL,
  `idp` bigint(20) NOT NULL,
  `idstripe` text COLLATE utf8_spanish_ci NOT NULL,
  `importe` double NOT NULL,
  `cargado` double NOT NULL,
  `estado` text COLLATE utf8_spanish_ci NOT NULL,
  `fecha` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` bigint(20) NOT NULL,
  `nombre` text COLLATE utf8_spanish_ci NOT NULL,
  `apellidos` text COLLATE utf8_spanish_ci NOT NULL,
  `agencia` text COLLATE utf8_spanish_ci NOT NULL,
  `instagram` text COLLATE utf8_spanish_ci NOT NULL,
  `email` text COLLATE utf8_spanish_ci NOT NULL,
  `telefono` text COLLATE utf8_spanish_ci NOT NULL,
  `clave` text COLLATE utf8_spanish_ci NOT NULL,
  `perfiles` text COLLATE utf8_spanish_ci NOT NULL,
  `admin` tinyint(1) NOT NULL,
  `activo` tinyint(1) NOT NULL,
  `fechalogin` datetime NOT NULL,
  `iplogin` text COLLATE utf8_spanish_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `chat`
--
ALTER TABLE `chat`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `clientes_potenciales`
--
ALTER TABLE `clientes_potenciales`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `codigos`
--
ALTER TABLE `codigos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `envios`
--
ALTER TABLE `envios`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `facturas`
--
ALTER TABLE `facturas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `facturas_conceptos`
--
ALTER TABLE `facturas_conceptos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `ficheros`
--
ALTER TABLE `ficheros`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `muro`
--
ALTER TABLE `muro`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `perfiles`
--
ALTER TABLE `perfiles`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `plantillas`
--
ALTER TABLE `plantillas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `presupuestos`
--
ALTER TABLE `presupuestos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `presupuestos_conceptos`
--
ALTER TABLE `presupuestos_conceptos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `productos_old`
--
ALTER TABLE `productos_old`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `servicios`
--
ALTER TABLE `servicios`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `tarjetas`
--
ALTER TABLE `tarjetas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `transacciones`
--
ALTER TABLE `transacciones`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `transacciones_old`
--
ALTER TABLE `transacciones_old`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `chat`
--
ALTER TABLE `chat`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `clientes_potenciales`
--
ALTER TABLE `clientes_potenciales`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `codigos`
--
ALTER TABLE `codigos`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `envios`
--
ALTER TABLE `envios`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `facturas`
--
ALTER TABLE `facturas`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `facturas_conceptos`
--
ALTER TABLE `facturas_conceptos`
  MODIFY `id` bigint(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `ficheros`
--
ALTER TABLE `ficheros`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `logs`
--
ALTER TABLE `logs`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `muro`
--
ALTER TABLE `muro`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `perfiles`
--
ALTER TABLE `perfiles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `plantillas`
--
ALTER TABLE `plantillas`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `presupuestos`
--
ALTER TABLE `presupuestos`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `presupuestos_conceptos`
--
ALTER TABLE `presupuestos_conceptos`
  MODIFY `id` bigint(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `productos_old`
--
ALTER TABLE `productos_old`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `servicios`
--
ALTER TABLE `servicios`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tarjetas`
--
ALTER TABLE `tarjetas`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `transacciones`
--
ALTER TABLE `transacciones`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `transacciones_old`
--
ALTER TABLE `transacciones_old`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
