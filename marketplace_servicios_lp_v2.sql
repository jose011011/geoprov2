-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3307
-- Tiempo de generación: 05-09-2026 a las 00:19:11
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
-- Base de datos: `marketplace_servicios_lp_v2`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `auditoria_logs`
--

CREATE TABLE `auditoria_logs` (
  `id_log` bigint(20) NOT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `accion` varchar(100) NOT NULL,
  `tabla_afectada` varchar(50) NOT NULL,
  `registro_id` int(11) DEFAULT NULL,
  `valores_anteriores` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`valores_anteriores`)),
  `valores_nuevos` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`valores_nuevos`)),
  `ip_origen` varchar(45) NOT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `fecha_evento` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `auditoria_logs`
--

INSERT INTO `auditoria_logs` (`id_log`, `id_usuario`, `accion`, `tabla_afectada`, `registro_id`, `valores_anteriores`, `valores_nuevos`, `ip_origen`, `user_agent`, `fecha_evento`) VALUES
(1, 1, 'REGISTRO_CLIENTE', 'usuarios', 1, NULL, '{\"zona\":\"El Alto\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-30 18:51:48'),
(2, 1, 'LOGIN_SUCCESS', 'usuarios', 1, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-30 18:52:56'),
(3, 1, 'LOGOUT', 'usuarios', 1, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-30 19:02:44'),
(4, 3, 'REGISTRO_CLIENTE', 'usuarios', 3, NULL, '{\"zona\":\"El Alto\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-30 19:06:19'),
(5, 3, 'LOGIN_SUCCESS', 'usuarios', 3, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-30 19:06:34'),
(6, 3, 'LOGOUT', 'usuarios', 3, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-30 19:06:37'),
(7, 3, 'LOGIN_SUCCESS', 'usuarios', 3, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-30 19:07:11'),
(8, 3, 'LOGOUT', 'usuarios', 3, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-30 19:09:26'),
(9, 1, 'LOGIN_SUCCESS', 'usuarios', 1, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-30 19:09:34'),
(10, 1, 'LOGOUT', 'usuarios', 1, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-30 19:43:01'),
(11, 4, 'REGISTRO_PRESTADOR', 'profesionales', 1, NULL, '{\"tipo_prestador\":\"TECNICO_PROFESIONAL\",\"macrodistrito\":\"EL_ALTO\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-30 19:45:43'),
(12, 3, 'LOGIN_SUCCESS', 'usuarios', 3, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-30 19:46:00'),
(13, 3, 'LOGOUT', 'usuarios', 3, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-30 19:46:40'),
(14, 4, 'LOGIN_SUCCESS', 'usuarios', 4, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-30 19:46:49'),
(15, 4, 'LOGOUT', 'usuarios', 4, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-30 19:55:06'),
(16, 1, 'LOGIN_SUCCESS', 'usuarios', 1, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-30 19:55:31'),
(17, 1, 'LOGOUT', 'usuarios', 1, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-30 19:56:55'),
(18, 3, 'LOGIN_SUCCESS', 'usuarios', 3, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-30 20:04:24'),
(19, 3, 'REVISION_DOCUMENTO_APROBADO', 'documentos_profesional', 1, NULL, '{\"id_profesional\":1}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-30 20:06:09'),
(20, 3, 'REVISION_DOCUMENTO_APROBADO', 'documentos_profesional', 2, NULL, '{\"id_profesional\":1}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-30 20:06:14'),
(21, 3, 'REVISION_DOCUMENTO_APROBADO', 'documentos_profesional', 3, NULL, '{\"id_profesional\":1}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-30 20:06:17'),
(22, 3, 'REVISION_DOCUMENTO_RECHAZADO', 'documentos_profesional', 3, NULL, '{\"id_profesional\":1}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-30 20:06:19'),
(23, 3, 'REVISION_DOCUMENTO_APROBADO', 'documentos_profesional', 3, NULL, '{\"id_profesional\":1}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-30 20:06:21'),
(24, 3, 'LOGOUT', 'usuarios', 3, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-30 20:07:04'),
(25, 6, 'REGISTRO_PRESTADOR', 'profesionales', 2, NULL, '{\"tipo_prestador\":\"OFICIO_EMPIRICO\",\"macrodistrito\":\"EL_ALTO\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-30 20:12:28'),
(26, 3, 'LOGIN_SUCCESS', 'usuarios', 3, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-30 20:12:36'),
(27, 3, 'REVISION_DOCUMENTO_APROBADO', 'documentos_profesional', 4, NULL, '{\"id_profesional\":2}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-30 20:12:45'),
(28, 3, 'REVISION_DOCUMENTO_APROBADO', 'documentos_profesional', 5, NULL, '{\"id_profesional\":2}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-30 20:12:47'),
(29, 3, 'REVISION_DOCUMENTO_APROBADO', 'documentos_profesional', 6, NULL, '{\"id_profesional\":2}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-30 20:12:49'),
(30, 3, 'APROBAR_PROFESIONAL', 'profesionales', 2, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-30 20:12:54'),
(31, 3, 'LOGOUT', 'usuarios', 3, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-30 20:12:58'),
(32, 6, 'LOGIN_SUCCESS', 'usuarios', 6, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-30 20:13:08'),
(33, 6, 'LOGOUT', 'usuarios', 6, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-30 20:29:33'),
(34, 1, 'LOGIN_SUCCESS', 'usuarios', 1, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-30 20:45:40'),
(35, 1, 'CREAR_SOLICITUD', 'solicitudes_servicio', 1, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-30 20:49:51'),
(36, 1, 'LOGOUT', 'usuarios', 1, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-30 20:50:13'),
(37, 4, 'LOGIN_SUCCESS', 'usuarios', 4, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-30 20:50:22'),
(38, NULL, 'CAMBIO_ESTADO_ACEPTADA', 'solicitudes_servicio', 1, '{\"estado_anterior\": \"PENDIENTE\"}', '{\"estado_nuevo\": \"ACEPTADA\", \"codigo\": \"GEO-98A063F5\"}', '127.0.0.1', NULL, '2026-08-30 21:11:19'),
(39, 4, 'CAMBIO_ESTADO_ACEPTADA', 'solicitudes_servicio', 1, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-30 21:11:19'),
(40, NULL, 'CAMBIO_ESTADO_EN_CAMINO', 'solicitudes_servicio', 1, '{\"estado_anterior\": \"ACEPTADA\"}', '{\"estado_nuevo\": \"EN_CAMINO\", \"codigo\": \"GEO-98A063F5\"}', '127.0.0.1', NULL, '2026-08-30 21:12:48'),
(41, 4, 'CAMBIO_ESTADO_EN_CAMINO', 'solicitudes_servicio', 1, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-30 21:12:48'),
(42, NULL, 'CAMBIO_ESTADO_EN_PROCESO', 'solicitudes_servicio', 1, '{\"estado_anterior\": \"EN_CAMINO\"}', '{\"estado_nuevo\": \"EN_PROCESO\", \"codigo\": \"GEO-98A063F5\"}', '127.0.0.1', NULL, '2026-08-30 21:12:50'),
(43, 4, 'CAMBIO_ESTADO_EN_PROCESO', 'solicitudes_servicio', 1, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-30 21:12:50'),
(44, NULL, 'CAMBIO_ESTADO_FINALIZADA', 'solicitudes_servicio', 1, '{\"estado_anterior\": \"EN_PROCESO\"}', '{\"estado_nuevo\": \"FINALIZADA\", \"codigo\": \"GEO-98A063F5\"}', '127.0.0.1', NULL, '2026-08-30 21:12:51'),
(45, 4, 'CAMBIO_ESTADO_FINALIZADA', 'solicitudes_servicio', 1, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-30 21:12:51'),
(46, 1, 'LOGIN_SUCCESS', 'usuarios', 1, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-31 13:27:12'),
(47, 1, 'CREAR_SOLICITUD', 'solicitudes_servicio', 2, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-31 13:28:05'),
(48, 1, 'LOGOUT', 'usuarios', 1, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-31 13:28:36'),
(49, 4, 'LOGIN_SUCCESS', 'usuarios', 4, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-31 13:28:47'),
(50, NULL, 'CAMBIO_ESTADO_ACEPTADA', 'solicitudes_servicio', 2, '{\"estado_anterior\": \"PENDIENTE\"}', '{\"estado_nuevo\": \"ACEPTADA\", \"codigo\": \"GEO-BEBE76A8\"}', '127.0.0.1', NULL, '2026-08-31 13:32:29'),
(51, 4, 'CAMBIO_ESTADO_ACEPTADA', 'solicitudes_servicio', 2, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-31 13:32:29'),
(52, NULL, 'CAMBIO_ESTADO_EN_CAMINO', 'solicitudes_servicio', 2, '{\"estado_anterior\": \"ACEPTADA\"}', '{\"estado_nuevo\": \"EN_CAMINO\", \"codigo\": \"GEO-BEBE76A8\"}', '127.0.0.1', NULL, '2026-08-31 13:32:38'),
(53, 4, 'CAMBIO_ESTADO_EN_CAMINO', 'solicitudes_servicio', 2, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-31 13:32:38'),
(54, 4, 'LOGOUT', 'usuarios', 4, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-31 13:32:49'),
(55, 1, 'LOGIN_SUCCESS', 'usuarios', 1, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-31 13:32:59'),
(56, 4, 'LOGIN_SUCCESS', 'usuarios', 4, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-01 03:58:18'),
(57, NULL, 'CAMBIO_ESTADO_EN_PROCESO', 'solicitudes_servicio', 2, '{\"estado_anterior\": \"EN_CAMINO\"}', '{\"estado_nuevo\": \"EN_PROCESO\", \"codigo\": \"GEO-BEBE76A8\"}', '127.0.0.1', NULL, '2026-09-01 03:58:32'),
(58, 4, 'CAMBIO_ESTADO_EN_PROCESO', 'solicitudes_servicio', 2, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-01 03:58:32'),
(59, NULL, 'CAMBIO_ESTADO_FINALIZADA', 'solicitudes_servicio', 2, '{\"estado_anterior\": \"EN_PROCESO\"}', '{\"estado_nuevo\": \"FINALIZADA\", \"codigo\": \"GEO-BEBE76A8\"}', '127.0.0.1', NULL, '2026-09-01 03:58:37'),
(60, 4, 'CAMBIO_ESTADO_FINALIZADA', 'solicitudes_servicio', 2, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-01 03:58:37'),
(61, 4, 'CREAR_SOLICITUD', 'solicitudes_servicio', 3, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-01 03:58:55'),
(62, NULL, 'CAMBIO_ESTADO_ACEPTADA', 'solicitudes_servicio', 3, '{\"estado_anterior\": \"PENDIENTE\"}', '{\"estado_nuevo\": \"ACEPTADA\", \"codigo\": \"GEO-F2F8479D\"}', '127.0.0.1', NULL, '2026-09-01 03:59:13'),
(63, 4, 'CAMBIO_ESTADO_ACEPTADA', 'solicitudes_servicio', 3, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-01 03:59:13'),
(64, NULL, 'CAMBIO_ESTADO_EN_CAMINO', 'solicitudes_servicio', 3, '{\"estado_anterior\": \"ACEPTADA\"}', '{\"estado_nuevo\": \"EN_CAMINO\", \"codigo\": \"GEO-F2F8479D\"}', '127.0.0.1', NULL, '2026-09-01 04:04:56'),
(65, 4, 'CAMBIO_ESTADO_EN_CAMINO', 'solicitudes_servicio', 3, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-01 04:04:56'),
(66, NULL, 'CAMBIO_ESTADO_EN_PROCESO', 'solicitudes_servicio', 3, '{\"estado_anterior\": \"EN_CAMINO\"}', '{\"estado_nuevo\": \"EN_PROCESO\", \"codigo\": \"GEO-F2F8479D\"}', '127.0.0.1', NULL, '2026-09-01 04:29:46'),
(67, 4, 'CAMBIO_ESTADO_EN_PROCESO', 'solicitudes_servicio', 3, NULL, '[]', '::1', 'Mozilla/5.0 (Linux; Android 15; Pixel 9) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '2026-09-01 04:29:46'),
(68, NULL, 'CAMBIO_ESTADO_FINALIZADA', 'solicitudes_servicio', 3, '{\"estado_anterior\": \"EN_PROCESO\"}', '{\"estado_nuevo\": \"FINALIZADA\", \"codigo\": \"GEO-F2F8479D\"}', '127.0.0.1', NULL, '2026-09-01 04:29:49'),
(69, 4, 'CAMBIO_ESTADO_FINALIZADA', 'solicitudes_servicio', 3, NULL, '[]', '::1', 'Mozilla/5.0 (Linux; Android 15; Pixel 9) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '2026-09-01 04:29:49'),
(70, 4, 'CREAR_SOLICITUD', 'solicitudes_servicio', 4, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-01 04:52:03'),
(71, NULL, 'CAMBIO_ESTADO_ACEPTADA', 'solicitudes_servicio', 4, '{\"estado_anterior\": \"PENDIENTE\"}', '{\"estado_nuevo\": \"ACEPTADA\", \"codigo\": \"GEO-588143F0\"}', '127.0.0.1', NULL, '2026-09-01 04:52:30'),
(72, 4, 'CAMBIO_ESTADO_ACEPTADA', 'solicitudes_servicio', 4, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-01 04:52:30'),
(73, NULL, 'CAMBIO_ESTADO_EN_CAMINO', 'solicitudes_servicio', 4, '{\"estado_anterior\": \"ACEPTADA\"}', '{\"estado_nuevo\": \"EN_CAMINO\", \"codigo\": \"GEO-588143F0\"}', '127.0.0.1', NULL, '2026-09-01 04:52:41'),
(74, 4, 'CAMBIO_ESTADO_EN_CAMINO', 'solicitudes_servicio', 4, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-01 04:52:41'),
(75, 4, 'LOGOUT', 'usuarios', 4, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-01 05:07:41'),
(76, NULL, 'LOGIN_FAILED', 'usuarios', NULL, NULL, '{\"correo_intentado\":\"admin@gmail.com\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-01 05:08:09'),
(77, 3, 'LOGIN_SUCCESS', 'usuarios', 3, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-01 05:09:21'),
(78, 3, 'CONFIRMAR_PAGO', 'transacciones_suscripcion', 1, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-01 05:09:28'),
(79, 3, 'LOGOUT', 'usuarios', 3, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-01 05:09:54'),
(80, 4, 'LOGIN_SUCCESS', 'usuarios', 4, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-01 05:10:06'),
(81, 4, 'LOGOUT', 'usuarios', 4, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-01 05:36:21'),
(82, 3, 'LOGIN_SUCCESS', 'usuarios', 3, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-01 05:36:29'),
(83, 3, 'LOGOUT', 'usuarios', 3, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-01 05:52:56'),
(84, 4, 'LOGIN_SUCCESS', 'usuarios', 4, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-01 05:53:09'),
(85, 3, 'LOGIN_SUCCESS', 'usuarios', 3, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-02 03:49:02'),
(86, 3, 'TOGGLE_CATEGORIA', 'categorias', 5, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-02 03:49:10'),
(87, 3, 'TOGGLE_CATEGORIA', 'categorias', 5, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-02 03:49:11'),
(88, 3, 'LOGOUT', 'usuarios', 3, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-02 04:37:20'),
(89, 1, 'LOGIN_SUCCESS', 'usuarios', 1, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-02 04:37:34'),
(90, 1, 'LOGOUT', 'usuarios', 1, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-02 04:38:05'),
(91, 3, 'LOGIN_SUCCESS', 'usuarios', 3, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-02 04:38:16'),
(92, 3, 'LOGOUT', 'usuarios', 3, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-02 04:39:38'),
(93, 4, 'LOGIN_SUCCESS', 'usuarios', 4, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-02 04:39:52'),
(94, NULL, 'CAMBIO_ESTADO_EN_PROCESO', 'solicitudes_servicio', 4, '{\"estado_anterior\": \"EN_CAMINO\"}', '{\"estado_nuevo\": \"EN_PROCESO\", \"codigo\": \"GEO-588143F0\"}', '127.0.0.1', NULL, '2026-09-02 05:27:13'),
(95, 4, 'CAMBIO_ESTADO_EN_PROCESO', 'solicitudes_servicio', 4, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-02 05:27:13'),
(96, NULL, 'CAMBIO_ESTADO_FINALIZADA', 'solicitudes_servicio', 4, '{\"estado_anterior\": \"EN_PROCESO\"}', '{\"estado_nuevo\": \"FINALIZADA\", \"codigo\": \"GEO-588143F0\"}', '127.0.0.1', NULL, '2026-09-02 05:27:16'),
(97, 4, 'CAMBIO_ESTADO_FINALIZADA', 'solicitudes_servicio', 4, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-02 05:27:16'),
(98, 4, 'LOGOUT', 'usuarios', 4, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-02 06:01:19'),
(99, 3, 'LOGIN_SUCCESS', 'usuarios', 3, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-02 06:01:47'),
(100, 3, 'LOGOUT', 'usuarios', 3, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-02 06:02:41'),
(101, 4, 'LOGIN_SUCCESS', 'usuarios', 4, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-02 06:02:56'),
(102, 4, 'CREAR_SOLICITUD', 'solicitudes_servicio', 5, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-02 06:04:42'),
(103, 4, 'CREAR_SOLICITUD', 'solicitudes_servicio', 6, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-02 06:05:04'),
(104, 4, 'LOGOUT', 'usuarios', 4, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-02 06:05:15'),
(105, 3, 'LOGIN_SUCCESS', 'usuarios', 3, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-02 06:05:24'),
(106, 3, 'REVISION_DOCUMENTO_APROBADO', 'documentos_profesional', 1, NULL, '{\"id_profesional\":1}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-02 06:05:32'),
(107, 3, 'REVISION_DOCUMENTO_APROBADO', 'documentos_profesional', 2, NULL, '{\"id_profesional\":1}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-02 06:05:34'),
(108, 3, 'REVISION_DOCUMENTO_APROBADO', 'documentos_profesional', 3, NULL, '{\"id_profesional\":1}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-02 06:05:36'),
(109, 3, 'LOGOUT', 'usuarios', 3, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-02 06:05:52'),
(110, 4, 'LOGIN_SUCCESS', 'usuarios', 4, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-02 06:06:05'),
(111, NULL, 'CAMBIO_ESTADO_ACEPTADA', 'solicitudes_servicio', 5, '{\"estado_anterior\": \"PENDIENTE\"}', '{\"estado_nuevo\": \"ACEPTADA\", \"codigo\": \"GEO-16AD1BB5\"}', '127.0.0.1', NULL, '2026-09-02 06:06:13'),
(112, 4, 'CAMBIO_ESTADO_ACEPTADA', 'solicitudes_servicio', 5, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-02 06:06:13'),
(113, NULL, 'CAMBIO_ESTADO_EN_CAMINO', 'solicitudes_servicio', 5, '{\"estado_anterior\": \"ACEPTADA\"}', '{\"estado_nuevo\": \"EN_CAMINO\", \"codigo\": \"GEO-16AD1BB5\"}', '127.0.0.1', NULL, '2026-09-02 06:06:25'),
(114, 4, 'CAMBIO_ESTADO_EN_CAMINO', 'solicitudes_servicio', 5, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-02 06:06:25'),
(115, NULL, 'CAMBIO_ESTADO_ACEPTADA', 'solicitudes_servicio', 6, '{\"estado_anterior\": \"PENDIENTE\"}', '{\"estado_nuevo\": \"ACEPTADA\", \"codigo\": \"GEO-5231017D\"}', '127.0.0.1', NULL, '2026-09-02 06:20:16'),
(116, 4, 'CAMBIO_ESTADO_ACEPTADA', 'solicitudes_servicio', 6, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-02 06:20:16'),
(117, NULL, 'CAMBIO_ESTADO_EN_CAMINO', 'solicitudes_servicio', 6, '{\"estado_anterior\": \"ACEPTADA\"}', '{\"estado_nuevo\": \"EN_CAMINO\", \"codigo\": \"GEO-5231017D\"}', '127.0.0.1', NULL, '2026-09-02 06:20:50'),
(118, 4, 'CAMBIO_ESTADO_EN_CAMINO', 'solicitudes_servicio', 6, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-02 06:20:50'),
(119, 4, 'LOGIN_SUCCESS', 'usuarios', 4, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-02 17:43:43'),
(120, 4, 'LOGOUT', 'usuarios', 4, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-02 17:59:26'),
(121, 1, 'LOGIN_SUCCESS', 'usuarios', 1, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-02 17:59:35'),
(122, 1, 'CREAR_SOLICITUD', 'solicitudes_servicio', 7, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-02 18:00:29'),
(123, 1, 'CREAR_SOLICITUD', 'solicitudes_servicio', 8, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-02 18:00:47'),
(124, 1, 'CREAR_SOLICITUD', 'solicitudes_servicio', 9, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-02 18:03:02'),
(125, 1, 'CREAR_SOLICITUD', 'solicitudes_servicio', 10, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-02 18:03:17'),
(126, 1, 'CREAR_SOLICITUD', 'solicitudes_servicio', 11, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-02 18:04:11'),
(127, 1, 'LOGOUT', 'usuarios', 1, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-02 18:05:30'),
(128, 3, 'LOGIN_SUCCESS', 'usuarios', 3, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-02 18:05:51'),
(129, 3, 'LOGOUT', 'usuarios', 3, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-02 18:09:04'),
(130, 1, 'LOGIN_SUCCESS', 'usuarios', 1, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-02 18:09:46'),
(131, 1, 'CREAR_SOLICITUD', 'solicitudes_servicio', 12, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-02 18:10:13'),
(132, 1, 'CREAR_SOLICITUD', 'solicitudes_servicio', 13, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-02 18:13:10'),
(133, 4, 'LOGIN_SUCCESS', 'usuarios', 4, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-02 18:14:03'),
(134, NULL, 'CAMBIO_ESTADO_ACEPTADA', 'solicitudes_servicio', 13, '{\"estado_anterior\": \"PENDIENTE\"}', '{\"estado_nuevo\": \"ACEPTADA\", \"codigo\": \"GEO-7AA61692\"}', '127.0.0.1', NULL, '2026-09-02 18:14:14'),
(135, 4, 'CAMBIO_ESTADO_ACEPTADA', 'solicitudes_servicio', 13, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-02 18:14:14'),
(136, NULL, 'CAMBIO_ESTADO_EN_CAMINO', 'solicitudes_servicio', 13, '{\"estado_anterior\": \"ACEPTADA\"}', '{\"estado_nuevo\": \"EN_CAMINO\", \"codigo\": \"GEO-7AA61692\"}', '127.0.0.1', NULL, '2026-09-02 18:14:32'),
(137, 4, 'CAMBIO_ESTADO_EN_CAMINO', 'solicitudes_servicio', 13, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-02 18:14:32'),
(138, 1, 'CREAR_SOLICITUD', 'solicitudes_servicio', 14, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-02 19:05:26'),
(139, NULL, 'CAMBIO_ESTADO_EN_PROCESO', 'solicitudes_servicio', 13, '{\"estado_anterior\": \"EN_CAMINO\"}', '{\"estado_nuevo\": \"EN_PROCESO\", \"codigo\": \"GEO-7AA61692\"}', '127.0.0.1', NULL, '2026-09-02 19:28:21'),
(140, 4, 'CAMBIO_ESTADO_EN_PROCESO', 'solicitudes_servicio', 13, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-02 19:28:21'),
(141, NULL, 'CAMBIO_ESTADO_ACEPTADA', 'solicitudes_servicio', 14, '{\"estado_anterior\": \"PENDIENTE\"}', '{\"estado_nuevo\": \"ACEPTADA\", \"codigo\": \"GEO-747F967C\"}', '127.0.0.1', NULL, '2026-09-02 19:30:34'),
(142, 4, 'CAMBIO_ESTADO_ACEPTADA', 'solicitudes_servicio', 14, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-02 19:30:34'),
(143, NULL, 'CAMBIO_ESTADO_EN_CAMINO', 'solicitudes_servicio', 14, '{\"estado_anterior\": \"ACEPTADA\"}', '{\"estado_nuevo\": \"EN_CAMINO\", \"codigo\": \"GEO-747F967C\"}', '127.0.0.1', NULL, '2026-09-02 19:30:42'),
(144, 4, 'CAMBIO_ESTADO_EN_CAMINO', 'solicitudes_servicio', 14, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-02 19:30:42'),
(145, NULL, 'CAMBIO_ESTADO_EN_PROCESO', 'solicitudes_servicio', 5, '{\"estado_anterior\": \"EN_CAMINO\"}', '{\"estado_nuevo\": \"EN_PROCESO\", \"codigo\": \"GEO-16AD1BB5\"}', '127.0.0.1', NULL, '2026-09-02 19:42:45'),
(146, 4, 'CAMBIO_ESTADO_EN_PROCESO', 'solicitudes_servicio', 5, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-02 19:42:45'),
(147, NULL, 'CAMBIO_ESTADO_EN_PROCESO', 'solicitudes_servicio', 14, '{\"estado_anterior\": \"EN_CAMINO\"}', '{\"estado_nuevo\": \"EN_PROCESO\", \"codigo\": \"GEO-747F967C\"}', '127.0.0.1', NULL, '2026-09-02 19:42:49'),
(148, 4, 'CAMBIO_ESTADO_EN_PROCESO', 'solicitudes_servicio', 14, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-02 19:42:49'),
(149, 1, 'CREAR_SOLICITUD', 'solicitudes_servicio', 15, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-02 19:52:45'),
(150, NULL, 'CAMBIO_ESTADO_FINALIZADA', 'solicitudes_servicio', 13, '{\"estado_anterior\": \"EN_PROCESO\"}', '{\"estado_nuevo\": \"FINALIZADA\", \"codigo\": \"GEO-7AA61692\"}', '127.0.0.1', NULL, '2026-09-02 19:55:53'),
(151, 4, 'CAMBIO_ESTADO_FINALIZADA', 'solicitudes_servicio', 13, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-02 19:55:53'),
(152, NULL, 'CAMBIO_ESTADO_FINALIZADA', 'solicitudes_servicio', 14, '{\"estado_anterior\": \"EN_PROCESO\"}', '{\"estado_nuevo\": \"FINALIZADA\", \"codigo\": \"GEO-747F967C\"}', '127.0.0.1', NULL, '2026-09-02 20:17:42'),
(153, 4, 'CAMBIO_ESTADO_FINALIZADA', 'solicitudes_servicio', 14, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-02 20:17:42'),
(154, 1, 'LOGIN_SUCCESS', 'usuarios', 1, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-04 00:17:52'),
(155, 4, 'LOGIN_SUCCESS', 'usuarios', 4, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-04 00:18:40'),
(156, 3, 'LOGIN_SUCCESS', 'usuarios', 3, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-04 00:19:03'),
(157, 4, 'LOGOUT', 'usuarios', 4, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-04 00:29:50'),
(158, 6, 'LOGIN_SUCCESS', 'usuarios', 6, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-04 00:30:08'),
(159, 6, 'LOGOUT', 'usuarios', 6, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-04 00:31:46'),
(160, NULL, 'LOGIN_FAILED', 'usuarios', NULL, NULL, '{\"correo_intentado\":\"profecional@gmail.com\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-04 00:31:57'),
(161, 4, 'LOGIN_SUCCESS', 'usuarios', 4, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-04 00:32:07'),
(162, 1, 'CREAR_SOLICITUD', 'solicitudes_servicio', 16, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-04 00:34:59'),
(163, NULL, 'CAMBIO_ESTADO_ACEPTADA', 'solicitudes_servicio', 16, '{\"estado_anterior\": \"PENDIENTE\"}', '{\"estado_nuevo\": \"ACEPTADA\", \"codigo\": \"GEO-ECA534E2\"}', '127.0.0.1', NULL, '2026-09-04 00:36:26'),
(164, 4, 'CAMBIO_ESTADO_ACEPTADA', 'solicitudes_servicio', 16, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-04 00:36:26'),
(165, NULL, 'CAMBIO_ESTADO_EN_CAMINO', 'solicitudes_servicio', 16, '{\"estado_anterior\": \"ACEPTADA\"}', '{\"estado_nuevo\": \"EN_CAMINO\", \"codigo\": \"GEO-ECA534E2\"}', '127.0.0.1', NULL, '2026-09-04 00:36:55'),
(166, 4, 'CAMBIO_ESTADO_EN_CAMINO', 'solicitudes_servicio', 16, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-04 00:36:55'),
(167, NULL, 'CAMBIO_ESTADO_EN_PROCESO', 'solicitudes_servicio', 16, '{\"estado_anterior\": \"EN_CAMINO\"}', '{\"estado_nuevo\": \"EN_PROCESO\", \"codigo\": \"GEO-ECA534E2\"}', '127.0.0.1', NULL, '2026-09-04 00:37:48'),
(168, 4, 'CAMBIO_ESTADO_EN_PROCESO', 'solicitudes_servicio', 16, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-04 00:37:48'),
(169, NULL, 'CAMBIO_ESTADO_FINALIZADA', 'solicitudes_servicio', 16, '{\"estado_anterior\": \"EN_PROCESO\"}', '{\"estado_nuevo\": \"FINALIZADA\", \"codigo\": \"GEO-ECA534E2\"}', '127.0.0.1', NULL, '2026-09-04 00:39:00'),
(170, 4, 'CAMBIO_ESTADO_FINALIZADA', 'solicitudes_servicio', 16, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-04 00:39:00'),
(171, 1, 'CREAR_SOLICITUD', 'solicitudes_servicio', 17, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-04 00:55:14'),
(172, NULL, 'CAMBIO_ESTADO_ACEPTADA', 'solicitudes_servicio', 17, '{\"estado_anterior\": \"PENDIENTE\"}', '{\"estado_nuevo\": \"ACEPTADA\", \"codigo\": \"GEO-F53DFC39\"}', '127.0.0.1', NULL, '2026-09-04 00:55:23'),
(173, 4, 'CAMBIO_ESTADO_ACEPTADA', 'solicitudes_servicio', 17, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-04 00:55:23'),
(174, NULL, 'CAMBIO_ESTADO_EN_CAMINO', 'solicitudes_servicio', 17, '{\"estado_anterior\": \"ACEPTADA\"}', '{\"estado_nuevo\": \"EN_CAMINO\", \"codigo\": \"GEO-F53DFC39\"}', '127.0.0.1', NULL, '2026-09-04 00:56:31'),
(175, 4, 'CAMBIO_ESTADO_EN_CAMINO', 'solicitudes_servicio', 17, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-04 00:56:31'),
(176, 1, 'CREAR_SOLICITUD', 'solicitudes_servicio', 18, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-04 01:00:50'),
(177, NULL, 'CAMBIO_ESTADO_ACEPTADA', 'solicitudes_servicio', 18, '{\"estado_anterior\": \"PENDIENTE\"}', '{\"estado_nuevo\": \"ACEPTADA\", \"codigo\": \"GEO-AF6A80F9\"}', '127.0.0.1', NULL, '2026-09-04 01:00:58'),
(178, 4, 'CAMBIO_ESTADO_ACEPTADA', 'solicitudes_servicio', 18, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-04 01:00:58'),
(179, 3, 'LOGIN_SUCCESS', 'usuarios', 3, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-04 21:16:31'),
(180, 3, 'LOGOUT', 'usuarios', 3, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-04 21:17:10'),
(181, 3, 'LOGIN_SUCCESS', 'usuarios', 3, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-04 21:54:42'),
(182, 3, 'LOGOUT', 'usuarios', 3, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-04 22:03:45'),
(183, 1, 'LOGIN_SUCCESS', 'usuarios', 1, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-04 22:03:51');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `calificaciones`
--

CREATE TABLE `calificaciones` (
  `id_calificacion` int(11) NOT NULL,
  `id_solicitud` int(11) NOT NULL,
  `puntuacion_general` int(11) NOT NULL,
  `puntualidad` int(11) NOT NULL DEFAULT 5,
  `calidad_trabajo` int(11) NOT NULL DEFAULT 5,
  `comentario` text DEFAULT NULL,
  `fecha_calificacion` timestamp NOT NULL DEFAULT current_timestamp()
) ;

--
-- Volcado de datos para la tabla `calificaciones`
--

INSERT INTO `calificaciones` (`id_calificacion`, `id_solicitud`, `puntuacion_general`, `puntualidad`, `calidad_trabajo`, `comentario`, `fecha_calificacion`) VALUES
(1, 3, 2, 5, 4, NULL, '2026-09-01 04:30:04'),
(2, 16, 2, 3, 5, NULL, '2026-09-04 00:40:51');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

CREATE TABLE `categorias` (
  `id_categoria` int(11) NOT NULL,
  `nombre_categoria` varchar(100) NOT NULL,
  `slug` varchar(120) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `tipo_clasificacion` enum('TECNICO','EMPIRICO_OFICIO','AMBOS') NOT NULL DEFAULT 'AMBOS',
  `icono_fa` varchar(50) DEFAULT 'fa-solid fa-wrench',
  `estado` tinyint(1) NOT NULL DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `categorias`
--

INSERT INTO `categorias` (`id_categoria`, `nombre_categoria`, `slug`, `descripcion`, `tipo_clasificacion`, `icono_fa`, `estado`, `fecha_creacion`) VALUES
(1, 'Electricidad Residencial', 'electricidad', 'Instalaciones, cableado, cortos y tableros eléctricos.', 'TECNICO', 'fa-bolt', 1, '2026-08-30 16:55:17'),
(2, 'Electrónica y Electrodomésticos', 'electronica', 'Reparación de TVs, refrigeradores y microondas.', 'TECNICO', 'fa-tv', 1, '2026-08-30 16:55:17'),
(3, 'Plomería y Gasfitería', 'plomeria', 'Fugas de agua, desagües e instalaciones sanitarias.', 'AMBOS', 'fa-faucet-drip', 1, '2026-08-30 16:55:17'),
(4, 'Cuidado del Hogar y Niñeras', 'cuidado-nineras', 'Atención de niños, personas mayores y apoyo doméstico.', 'EMPIRICO_OFICIO', 'fa-baby-carriage', 1, '2026-08-30 16:55:17'),
(5, 'Albañilería y Obras Menores', 'albanileria', 'Construcción, refacciones de muros y cerámica.', 'EMPIRICO_OFICIO', 'fa-trowel-bricks', 1, '2026-08-30 16:55:17');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `id_cliente` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `direccion_referencia` varchar(255) NOT NULL,
  `zona` varchar(100) NOT NULL,
  `latitud_predeterminada` decimal(10,8) DEFAULT NULL,
  `longitud_predeterminada` decimal(11,8) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `clientes`
--

INSERT INTO `clientes` (`id_cliente`, `id_usuario`, `direccion_referencia`, `zona`, `latitud_predeterminada`, `longitud_predeterminada`) VALUES
(1, 1, 'Rios seco/av.santa fe', 'El Alto', -16.50000000, -68.15000000),
(3, 4, 'rio seco', 'EL_ALTO', -16.50000000, -68.15000000),
(4, 6, 'rio seco', 'EL_ALTO', -16.50000000, -68.15000000);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `documentos_profesional`
--

CREATE TABLE `documentos_profesional` (
  `id_documento` int(11) NOT NULL,
  `id_profesional` int(11) NOT NULL,
  `tipo_documento_archivo` enum('CI_ANVERSO','CI_REVERSO','TITULO_TECNICO','CERTIFICADO_ANTECEDENTES','REFERENCIA_LABORAL','OTRO') NOT NULL DEFAULT 'CI_ANVERSO',
  `archivo_url` varchar(255) NOT NULL,
  `estado_revision` enum('PENDIENTE','APROBADO','RECHAZADO') NOT NULL DEFAULT 'PENDIENTE',
  `observacion` varchar(255) DEFAULT NULL,
  `revisado_por` int(11) DEFAULT NULL,
  `fecha_subida` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_revision` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `documentos_profesional`
--

INSERT INTO `documentos_profesional` (`id_documento`, `id_profesional`, `tipo_documento_archivo`, `archivo_url`, `estado_revision`, `observacion`, `revisado_por`, `fecha_subida`, `fecha_revision`) VALUES
(1, 1, 'TITULO_TECNICO', 'uploads/documentos/DOC_1_238de49c94b066e2.png', 'APROBADO', NULL, 3, '2026-08-30 19:45:43', '2026-09-02 06:05:32'),
(2, 1, 'CI_ANVERSO', 'uploads/documentos/DOC_1_bc0bf88160bfdb6f.png', 'APROBADO', NULL, 3, '2026-08-30 19:45:43', '2026-09-02 06:05:34'),
(3, 1, 'CI_REVERSO', 'uploads/documentos/DOC_1_379ea809bf20d760.png', 'APROBADO', NULL, 3, '2026-08-30 19:45:43', '2026-09-02 06:05:36'),
(4, 2, 'CI_ANVERSO', 'uploads/documentos/DOC_2_4ad304578d171c21.png', 'APROBADO', NULL, 3, '2026-08-30 20:12:28', '2026-08-30 20:12:45'),
(5, 2, 'CI_REVERSO', 'uploads/documentos/DOC_2_91a1e66d78390142.png', 'APROBADO', NULL, 3, '2026-08-30 20:12:28', '2026-08-30 20:12:47'),
(6, 2, 'CERTIFICADO_ANTECEDENTES', 'uploads/documentos/DOC_2_fae50078778255a4.png', 'APROBADO', NULL, 3, '2026-08-30 20:12:28', '2026-08-30 20:12:49');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mensajes`
--

CREATE TABLE `mensajes` (
  `id_mensaje` bigint(20) NOT NULL,
  `id_solicitud` int(11) NOT NULL,
  `id_remitente` int(11) NOT NULL,
  `tipo_mensaje` enum('TEXTO','IMAGEN','SISTEMA') NOT NULL DEFAULT 'TEXTO',
  `mensaje` text DEFAULT NULL,
  `archivo_adjunto` varchar(255) DEFAULT NULL,
  `leido` tinyint(1) NOT NULL DEFAULT 0,
  `fecha_envio` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `mensajes`
--

INSERT INTO `mensajes` (`id_mensaje`, `id_solicitud`, `id_remitente`, `tipo_mensaje`, `mensaje`, `archivo_adjunto`, `leido`, `fecha_envio`) VALUES
(1, 2, 1, 'TEXTO', 'hola', NULL, 0, '2026-08-31 13:28:18'),
(2, 2, 1, 'TEXTO', 'jefe', NULL, 0, '2026-08-31 13:28:24'),
(3, 2, 4, 'TEXTO', 'hollaaaaa', NULL, 0, '2026-08-31 13:29:07'),
(4, 2, 4, 'IMAGEN', NULL, 'uploads/chat/CHAT_2_268d02f683991a2a.png', 0, '2026-08-31 13:29:17'),
(5, 3, 4, 'TEXTO', 'holaaaaaaaaaaaaaaaaa', NULL, 0, '2026-09-01 03:59:02'),
(6, 6, 4, 'TEXTO', 'hollaaaaaaaaaaa', NULL, 0, '2026-09-02 17:54:45'),
(7, 14, 1, 'IMAGEN', NULL, 'uploads/chat/CHAT_14_c051285b823a1393.webp', 0, '2026-09-02 19:11:50'),
(8, 14, 4, 'TEXTO', 'cual es su problema ?', NULL, 0, '2026-09-02 19:29:11'),
(9, 14, 1, 'TEXTO', 'no tengo luz se queo mi foco', NULL, 0, '2026-09-02 19:29:26'),
(10, 14, 4, 'TEXTO', 'enserio ?', NULL, 0, '2026-09-02 19:29:33'),
(11, 14, 1, 'TEXTO', 'claro pues si no por que te nesesitaria !!!!!!', NULL, 0, '2026-09-02 19:29:45'),
(12, 14, 4, 'IMAGEN', NULL, 'uploads/chat/CHAT_14_5c74c9aabd0d882d.jpg', 0, '2026-09-02 19:30:03'),
(13, 14, 4, 'TEXTO', 'estoy aqui', NULL, 0, '2026-09-02 19:30:11'),
(14, 14, 4, 'TEXTO', 'en 30 minutos llego', NULL, 0, '2026-09-02 19:30:18'),
(15, 14, 1, 'TEXTO', 'posi', NULL, 0, '2026-09-02 19:30:22'),
(16, 15, 4, 'TEXTO', 'que paso', NULL, 0, '2026-09-02 19:53:07'),
(17, 15, 1, 'TEXTO', 'se que mo mi foco', NULL, 0, '2026-09-02 19:53:14'),
(18, 16, 1, 'TEXTO', 'hola', NULL, 0, '2026-09-04 00:35:29'),
(19, 16, 4, 'TEXTO', 'que problema tienes', NULL, 0, '2026-09-04 00:35:38'),
(20, 16, 1, 'IMAGEN', NULL, 'uploads/chat/CHAT_16_b310542df5080e50.jpg', 0, '2026-09-04 00:35:47'),
(21, 16, 1, 'TEXTO', 'este es mi problema', NULL, 0, '2026-09-04 00:35:52');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificaciones`
--

CREATE TABLE `notificaciones` (
  `id_notificacion` bigint(20) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `tipo` varchar(50) NOT NULL,
  `mensaje` varchar(255) NOT NULL,
  `url_destino` varchar(255) DEFAULT NULL,
  `leida` tinyint(1) NOT NULL DEFAULT 0,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `notificaciones`
--

INSERT INTO `notificaciones` (`id_notificacion`, `id_usuario`, `tipo`, `mensaje`, `url_destino`, `leida`, `fecha_creacion`) VALUES
(1, 4, 'NUEVA_SOLICITUD', 'Tienes una nueva solicitud de servicio.', 'http://localhost:8080/GEO_PRO_V2/public/profesional/solicitudes', 1, '2026-09-02 18:13:10'),
(2, 4, 'NUEVA_SOLICITUD', 'Tienes una nueva solicitud de servicio.', 'http://localhost:8080/GEO_PRO_V2/public/profesional/solicitudes', 1, '2026-09-02 19:05:26'),
(3, 4, 'NUEVA_SOLICITUD', 'Tienes una nueva solicitud de servicio.', 'http://localhost:8080/GEO_PRO_V2/public/profesional/solicitudes', 1, '2026-09-02 19:52:46'),
(4, 4, 'NUEVA_SOLICITUD', 'Tienes una nueva solicitud de servicio.', 'http://localhost:8080/GEO_PRO_V2/public/profesional/solicitudes', 1, '2026-09-04 00:34:59'),
(5, 4, 'NUEVA_SOLICITUD', 'Tienes una nueva solicitud de servicio.', 'http://localhost:8080/GEO_PRO_V2/public/profesional/solicitudes', 0, '2026-09-04 00:55:14'),
(6, 4, 'NUEVA_SOLICITUD', 'Tienes una nueva solicitud de servicio.', 'http://localhost:8080/GEO_PRO_V2/public/profesional/solicitudes', 0, '2026-09-04 01:00:50');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `planes_suscripcion`
--

CREATE TABLE `planes_suscripcion` (
  `id_plan` int(11) NOT NULL,
  `nombre_plan` varchar(50) NOT NULL,
  `precio_mensual` decimal(10,2) NOT NULL DEFAULT 0.00,
  `tokens_mensuales` int(11) NOT NULL DEFAULT 5,
  `posicionamiento_destacado` tinyint(1) NOT NULL DEFAULT 0,
  `descripcion` text DEFAULT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `planes_suscripcion`
--

INSERT INTO `planes_suscripcion` (`id_plan`, `nombre_plan`, `precio_mensual`, `tokens_mensuales`, `posicionamiento_destacado`, `descripcion`, `estado`) VALUES
(1, 'GRATUITO_TOKENS', 0.00, 5, 0, 'Asignación mensual de 5 tokens gratuitos. Pago por recarga si se agotan.', 1),
(2, 'BASICO_MENSUAL', 29.00, 40, 0, '40 tokens mensuales, insignia de verificación y posicionamiento estándar en La Paz.', 1),
(3, 'PREMIUM_DESTACADO', 69.00, 999, 1, 'Propuestas ilimitadas, prioridad de radio geográfico e insignia destacada.', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `profesionales`
--

CREATE TABLE `profesionales` (
  `id_profesional` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_categoria` int(11) NOT NULL,
  `id_plan` int(11) NOT NULL DEFAULT 1,
  `tipo_prestador` enum('TECNICO_PROFESIONAL','OFICIO_EMPIRICO') NOT NULL DEFAULT 'TECNICO_PROFESIONAL',
  `tipo_documento_identidad` enum('CI','NIT','EXTRANJERO') NOT NULL DEFAULT 'CI',
  `numero_documento` varchar(30) NOT NULL,
  `experiencia_anios` int(11) NOT NULL DEFAULT 0,
  `descripcion_servicio` text NOT NULL,
  `macrodistrito_base` enum('ZONA_SUR','SOPOCACHI','CENTRO','MIRAFLORES','SAN_PEDRO','COTAHUMA','PERIFERICA','EL_ALTO') NOT NULL,
  `zona_especifica` varchar(150) NOT NULL,
  `tokens_disponibles` int(11) NOT NULL DEFAULT 5,
  `fin_suscripcion` date DEFAULT NULL,
  `tarifa_base` decimal(10,2) DEFAULT 0.00,
  `estado_validacion` enum('PENDIENTE','APROBADO','RECHAZADO') NOT NULL DEFAULT 'PENDIENTE',
  `estado_disponibilidad` enum('DISPONIBLE','OCUPADO','NO_DISPONIBLE') NOT NULL DEFAULT 'NO_DISPONIBLE',
  `latitud_actual` decimal(10,8) DEFAULT NULL,
  `longitud_actual` decimal(11,8) DEFAULT NULL,
  `ultima_conexion` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `profesionales`
--

INSERT INTO `profesionales` (`id_profesional`, `id_usuario`, `id_categoria`, `id_plan`, `tipo_prestador`, `tipo_documento_identidad`, `numero_documento`, `experiencia_anios`, `descripcion_servicio`, `macrodistrito_base`, `zona_especifica`, `tokens_disponibles`, `fin_suscripcion`, `tarifa_base`, `estado_validacion`, `estado_disponibilidad`, `latitud_actual`, `longitud_actual`, `ultima_conexion`, `fecha_registro`) VALUES
(1, 4, 1, 1, 'TECNICO_PROFESIONAL', 'CI', '12576550', 0, 'estos son mis certificados que tengo en la intitucion del =x', 'EL_ALTO', 'rio seco', 10, NULL, 0.00, 'APROBADO', 'DISPONIBLE', -16.50962653, -68.15261558, '2026-09-04 01:00:58', '2026-08-30 19:45:43'),
(2, 6, 5, 1, 'OFICIO_EMPIRICO', 'CI', '1257655', 4, 'tengo experiencia en obra bruta en pisos casas', 'EL_ALTO', 'rio seco', 5, NULL, 0.00, 'APROBADO', 'DISPONIBLE', -16.50000000, -68.15000000, '2026-09-04 00:30:14', '2026-08-30 20:12:28');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id_rol` int(11) NOT NULL,
  `nombre_rol` varchar(50) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id_rol`, `nombre_rol`, `descripcion`, `estado`, `fecha_creacion`) VALUES
(1, 'SUPER_ADMIN', 'Acceso total y configuración de auditoría', 1, '2026-08-30 16:55:17'),
(2, 'ADMIN', 'Gestión operativa, validación documental y reportes', 1, '2026-08-30 16:55:17'),
(3, 'PROFESIONAL', 'Técnicos calificados y trabajadores de oficios', 1, '2026-08-30 16:55:17'),
(4, 'CLIENTE', 'Usuarios demandantes de servicios', 1, '2026-08-30 16:55:17');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `solicitudes_servicio`
--

CREATE TABLE `solicitudes_servicio` (
  `id_solicitud` int(11) NOT NULL,
  `codigo_seguimiento` varchar(20) NOT NULL,
  `id_cliente` int(11) NOT NULL,
  `id_profesional` int(11) NOT NULL,
  `descripcion_problema` text NOT NULL,
  `direccion_servicio` varchar(255) NOT NULL,
  `macrodistrito` enum('ZONA_SUR','SOPOCACHI','CENTRO','MIRAFLORES','SAN_PEDRO','COTAHUMA','PERIFERICA','EL_ALTO') NOT NULL,
  `zona` varchar(100) NOT NULL,
  `latitud_destino` decimal(10,8) NOT NULL,
  `longitud_destino` decimal(11,8) NOT NULL,
  `estado_servicio` enum('PENDIENTE','ACEPTADA','EN_CAMINO','EN_PROCESO','FINALIZADA','CANCELADA') NOT NULL DEFAULT 'PENDIENTE',
  `precio_acordado` decimal(10,2) DEFAULT NULL,
  `tiempo_estimado_llegada_min` int(11) DEFAULT NULL,
  `motivo_cancelacion` varchar(255) DEFAULT NULL,
  `fecha_solicitud` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_inicio_atencion` timestamp NULL DEFAULT NULL,
  `fecha_finalizacion` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `solicitudes_servicio`
--

INSERT INTO `solicitudes_servicio` (`id_solicitud`, `codigo_seguimiento`, `id_cliente`, `id_profesional`, `descripcion_problema`, `direccion_servicio`, `macrodistrito`, `zona`, `latitud_destino`, `longitud_destino`, `estado_servicio`, `precio_acordado`, `tiempo_estimado_llegada_min`, `motivo_cancelacion`, `fecha_solicitud`, `fecha_inicio_atencion`, `fecha_finalizacion`) VALUES
(1, 'GEO-98A063F5', 1, 1, 'se quemo mi foco', 'Rios seco/av.santa fe', 'EL_ALTO', 'El Alto', -16.50000000, -68.15000000, 'FINALIZADA', NULL, NULL, NULL, '2026-08-30 20:49:51', '2026-08-30 21:12:50', '2026-08-30 21:12:51'),
(2, 'GEO-BEBE76A8', 1, 1, 'se quemo mi foco', 'Rios seco/av.santa fe', 'EL_ALTO', 'El Alto', -16.50000000, -68.15000000, 'FINALIZADA', NULL, NULL, NULL, '2026-08-31 13:28:05', '2026-09-01 03:58:32', '2026-09-01 03:58:37'),
(3, 'GEO-F2F8479D', 3, 1, 'asdasdasdasdasd', 'rio seco', 'EL_ALTO', 'EL_ALTO', -16.50000000, -68.15000000, 'FINALIZADA', NULL, NULL, NULL, '2026-09-01 03:58:55', '2026-09-01 04:29:46', '2026-09-01 04:29:49'),
(4, 'GEO-588143F0', 3, 1, 'asddddddddddddddddddddddddddddd', 'rio seco', 'EL_ALTO', 'EL_ALTO', -16.50000000, -68.15000000, 'FINALIZADA', NULL, NULL, NULL, '2026-09-01 04:52:03', '2026-09-02 05:27:13', '2026-09-02 05:27:16'),
(5, 'GEO-16AD1BB5', 3, 1, 'dgdfgdfgdfgdfgdfgdfgdfg', 'rio seco', 'EL_ALTO', 'EL_ALTO', -16.50000000, -68.15000000, 'EN_PROCESO', NULL, NULL, NULL, '2026-09-02 06:04:42', '2026-09-02 19:42:45', NULL),
(6, 'GEO-5231017D', 3, 1, 'pruebaaaaaaaaaaa', 'rio seco', 'EL_ALTO', 'EL_ALTO', -16.50000000, -68.15000000, 'EN_CAMINO', NULL, NULL, NULL, '2026-09-02 06:05:04', NULL, NULL),
(7, 'GEO-3B74D131', 1, 1, 'ayudaaaa!!!!!!!!!!!!!!!!!!', 'Rios seco/av.santa fe', 'EL_ALTO', 'rio seco av. costanera', -16.50000000, -68.15000000, 'PENDIENTE', NULL, NULL, NULL, '2026-09-02 18:00:29', NULL, NULL),
(8, 'GEO-349BE65A', 1, 1, 'asdadadasdadasdad', 'Rios seco/av.santa fe', 'EL_ALTO', 'El Alto', -16.50000000, -68.15000000, 'PENDIENTE', NULL, NULL, NULL, '2026-09-02 18:00:47', NULL, NULL),
(9, 'GEO-3BB70B0F', 1, 1, 'asdadadasdadasdad', 'Rios seco/av.santa fe', 'EL_ALTO', 'El Alto', -16.50000000, -68.15000000, 'PENDIENTE', NULL, NULL, NULL, '2026-09-02 18:03:02', NULL, NULL),
(10, 'GEO-52E70E45', 1, 1, 'asdadadasdadasdad', 'Rios seco/av.santa fe', 'EL_ALTO', 'El Alto', -16.50000000, -68.15000000, 'PENDIENTE', NULL, NULL, NULL, '2026-09-02 18:03:17', NULL, NULL),
(11, 'GEO-9116E21F', 1, 1, 'ayudaaaaaaa', 'Rios seco/av.santa fe', 'EL_ALTO', 'El Alto', -16.49888768, -68.21033478, 'PENDIENTE', NULL, NULL, NULL, '2026-09-02 18:04:11', NULL, NULL),
(12, 'GEO-27E7DA0B', 1, 1, 'ayudaaaaaaaaaaaaa', 'Rios seco/av.santa fe', 'EL_ALTO', 'El Alto', -16.50699373, -68.20625782, 'PENDIENTE', NULL, NULL, NULL, '2026-09-02 18:10:13', NULL, NULL),
(13, 'GEO-7AA61692', 1, 1, 'ayudaaaaaaaaaaaaa', 'Rios seco/av.santa fe', 'EL_ALTO', 'El Alto', -16.50699373, -68.20625782, 'FINALIZADA', 20.00, NULL, NULL, '2026-09-02 18:13:10', '2026-09-02 19:28:21', '2026-09-02 19:55:53'),
(14, 'GEO-747F967C', 1, 1, 'tengno otro problema de mi luz', 'Rios seco/av.santa fe', 'EL_ALTO', 'El Alto', -16.50575933, -68.21057081, 'FINALIZADA', 99999999.99, 30, NULL, '2026-09-02 19:05:26', '2026-09-02 19:42:49', '2026-09-02 20:17:42'),
(15, 'GEO-0DE35214', 1, 1, 'se quemo mi foco', 'Rios seco/av.santa fe', 'EL_ALTO', 'El Alto', -16.50753378, -68.21277559, 'PENDIENTE', NULL, NULL, NULL, '2026-09-02 19:52:45', NULL, NULL),
(16, 'GEO-ECA534E2', 1, 1, 'asdddddddddddddasdasdasd', 'Rios seco/av.santa fe', 'EL_ALTO', 'El Alto', -16.50971662, -68.15245326, 'FINALIZADA', 15.00, 20, NULL, '2026-09-04 00:34:59', '2026-09-04 00:37:48', '2026-09-04 00:39:00'),
(17, 'GEO-F53DFC39', 1, 1, 'aSDSFDGFASFDSF', 'Rios seco/av.santa fe', 'EL_ALTO', 'El Alto', -16.50962496, -68.15242714, 'EN_CAMINO', NULL, 15, NULL, '2026-09-04 00:55:14', NULL, NULL),
(18, 'GEO-AF6A80F9', 1, 1, 'aSDSFDGFASFDSF', 'Rios seco/av.santa fe', 'EL_ALTO', 'El Alto', -16.50964103, -68.15265929, 'ACEPTADA', NULL, 15, NULL, '2026-09-04 01:00:50', NULL, NULL);

--
-- Disparadores `solicitudes_servicio`
--
DELIMITER $$
CREATE TRIGGER `trg_audit_solicitud_estado` AFTER UPDATE ON `solicitudes_servicio` FOR EACH ROW BEGIN
    IF OLD.estado_servicio <> NEW.estado_servicio THEN
        INSERT INTO auditoria_logs (
            id_usuario,
            accion,
            tabla_afectada,
            registro_id,
            valores_anteriores,
            valores_nuevos,
            ip_origen
        ) VALUES (
            NULL,
            CONCAT('CAMBIO_ESTADO_', NEW.estado_servicio),
            'solicitudes_servicio',
            NEW.id_solicitud,
            JSON_OBJECT('estado_anterior', OLD.estado_servicio),
            JSON_OBJECT('estado_nuevo', NEW.estado_servicio, 'codigo', NEW.codigo_seguimiento),
            '127.0.0.1'
        );
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_validar_profesional_solicitud` BEFORE INSERT ON `solicitudes_servicio` FOR EACH ROW BEGIN
    DECLARE v_estado_val ENUM('PENDIENTE', 'APROBADO', 'RECHAZADO');
    DECLARE v_disponibilidad ENUM('DISPONIBLE', 'OCUPADO', 'NO_DISPONIBLE');
    DECLARE v_fin_suscripcion DATE;
    DECLARE v_id_plan INT;

    SELECT estado_validacion, estado_disponibilidad, fin_suscripcion, id_plan
    INTO v_estado_val, v_disponibilidad, v_fin_suscripcion, v_id_plan
    FROM profesionales
    WHERE id_profesional = NEW.id_profesional;

    IF v_estado_val != 'APROBADO' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Operación denegada: El prestador de servicio no ha sido validado documentalmente.';
    END IF;

    IF v_disponibilidad != 'DISPONIBLE' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Operación denegada: El prestador de servicio no se encuentra en estado DISPONIBLE.';
    END IF;

    -- El plan gratuito (id_plan = 1) no vence. Los planes pagos requieren fin_suscripcion vigente.
    IF v_id_plan != 1 THEN
        IF v_fin_suscripcion IS NULL OR v_fin_suscripcion < CURDATE() THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Operación denegada: La membresía del prestador de servicio se encuentra vencida.';
        END IF;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tracking_solicitud_gps`
--

CREATE TABLE `tracking_solicitud_gps` (
  `id_tracking` bigint(20) NOT NULL,
  `id_solicitud` int(11) NOT NULL,
  `id_profesional` int(11) NOT NULL,
  `latitud` decimal(10,8) NOT NULL,
  `longitud` decimal(11,8) NOT NULL,
  `velocidad_kmh` decimal(5,2) DEFAULT 0.00,
  `timestamp_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tracking_solicitud_gps`
--

INSERT INTO `tracking_solicitud_gps` (`id_tracking`, `id_solicitud`, `id_profesional`, `latitud`, `longitud`, `velocidad_kmh`, `timestamp_registro`) VALUES
(1, 2, 1, -16.50634343, -68.21578064, 0.00, '2026-08-31 13:32:42'),
(2, 2, 1, -16.50634343, -68.21578064, 0.00, '2026-08-31 13:32:45'),
(3, 2, 1, -16.50629889, -68.21579457, 0.00, '2026-09-01 03:58:27'),
(4, 2, 1, -16.50629889, -68.21579457, 0.00, '2026-09-01 03:58:27'),
(5, 3, 1, -16.50634343, -68.21578064, 0.00, '2026-09-01 04:04:59'),
(6, 3, 1, -16.50634343, -68.21578064, 0.00, '2026-09-01 04:05:02'),
(7, 3, 1, -16.50634343, -68.21578064, 0.00, '2026-09-01 04:05:08'),
(8, 3, 1, -16.50629889, -68.21579457, 0.00, '2026-09-01 04:05:14'),
(9, 3, 1, -16.50629889, -68.21579457, 0.00, '2026-09-01 04:05:20'),
(10, 3, 1, -16.50634343, -68.21578064, 0.00, '2026-09-01 04:05:26'),
(11, 3, 1, -16.50634343, -68.21578064, 0.00, '2026-09-01 04:05:32'),
(12, 3, 1, -16.50629889, -68.21579457, 0.00, '2026-09-01 04:05:38'),
(13, 3, 1, -16.50629889, -68.21579457, 0.00, '2026-09-01 04:05:44'),
(14, 3, 1, -16.50630710, -68.21578650, 0.00, '2026-09-01 04:05:50'),
(15, 3, 1, -16.50630710, -68.21578650, 0.00, '2026-09-01 04:05:56'),
(16, 3, 1, -16.50634343, -68.21578064, 0.00, '2026-09-01 04:06:02'),
(17, 3, 1, -16.50634343, -68.21578064, 0.00, '2026-09-01 04:06:08'),
(18, 3, 1, -16.50634343, -68.21578064, 0.00, '2026-09-01 04:06:14'),
(19, 3, 1, -16.50634343, -68.21578064, 0.00, '2026-09-01 04:06:20'),
(20, 3, 1, -16.50634343, -68.21578064, 0.00, '2026-09-01 04:06:29'),
(21, 3, 1, -16.50634343, -68.21578064, 0.00, '2026-09-01 04:11:14'),
(22, 3, 1, -16.50634343, -68.21578064, 0.00, '2026-09-01 04:11:20'),
(23, 3, 1, -16.50629889, -68.21579457, 0.00, '2026-09-01 04:11:26'),
(24, 3, 1, -16.50629889, -68.21579457, 0.00, '2026-09-01 04:11:32'),
(25, 3, 1, -16.50634343, -68.21578064, 0.00, '2026-09-01 04:11:38'),
(26, 3, 1, -16.50634343, -68.21578064, 0.00, '2026-09-01 04:11:44'),
(27, 3, 1, -16.50634343, -68.21578064, 0.00, '2026-09-01 04:11:52'),
(28, 3, 1, -16.50634343, -68.21578064, 0.00, '2026-09-01 04:11:56'),
(29, 3, 1, -16.50630710, -68.21578650, 0.00, '2026-09-01 04:12:02'),
(30, 3, 1, -16.50630710, -68.21578650, 0.00, '2026-09-01 04:12:08'),
(31, 3, 1, -16.50688200, -68.21546900, 0.00, '2026-09-01 04:12:15'),
(32, 3, 1, -16.50630710, -68.21578650, 0.00, '2026-09-01 04:12:23'),
(33, 3, 1, -16.50630710, -68.21578650, 0.00, '2026-09-01 04:12:26'),
(34, 3, 1, -16.50630710, -68.21578650, 0.00, '2026-09-01 04:12:32'),
(35, 3, 1, -16.50630710, -68.21578650, 0.00, '2026-09-01 04:12:41'),
(36, 3, 1, -16.50630710, -68.21578650, 0.00, '2026-09-01 04:12:44'),
(37, 3, 1, -16.50630710, -68.21578650, 0.00, '2026-09-01 04:12:50'),
(38, 3, 1, -16.50688200, -68.21546900, 0.00, '2026-09-01 04:12:56'),
(39, 3, 1, -16.50629889, -68.21579457, 0.00, '2026-09-01 04:13:04'),
(40, 3, 1, -16.50629889, -68.21579457, 0.00, '2026-09-01 04:13:08'),
(41, 3, 1, -16.50634343, -68.21578064, 0.00, '2026-09-01 04:13:15'),
(42, 3, 1, -16.50634343, -68.21578064, 0.00, '2026-09-01 04:13:20'),
(43, 3, 1, -16.50634343, -68.21578064, 0.00, '2026-09-01 04:13:26'),
(44, 3, 1, -16.50634343, -68.21578064, 0.00, '2026-09-01 04:13:32'),
(45, 3, 1, -16.50634343, -68.21578064, 0.00, '2026-09-01 04:13:40'),
(46, 3, 1, -16.50634343, -68.21578064, 0.00, '2026-09-01 04:13:44'),
(47, 3, 1, -16.50629889, -68.21579457, 0.00, '2026-09-01 04:13:51'),
(48, 3, 1, -16.50629889, -68.21579457, 0.00, '2026-09-01 04:13:56'),
(49, 3, 1, -16.50634343, -68.21578064, 0.00, '2026-09-01 04:14:02'),
(50, 3, 1, -16.50634343, -68.21578064, 0.00, '2026-09-01 04:14:08'),
(51, 3, 1, -16.50634343, -68.21578064, 0.00, '2026-09-01 04:14:14'),
(52, 3, 1, -16.50634343, -68.21578064, 0.00, '2026-09-01 04:14:20'),
(53, 3, 1, -16.50634343, -68.21578064, 0.00, '2026-09-01 04:14:26'),
(54, 3, 1, -16.50634343, -68.21578064, 0.00, '2026-09-01 04:14:32'),
(55, 3, 1, -16.50634343, -68.21578064, 0.00, '2026-09-01 04:14:41'),
(56, 3, 1, -16.50634343, -68.21578064, 0.00, '2026-09-01 04:14:44'),
(57, 3, 1, -16.50634343, -68.21578064, 0.00, '2026-09-01 04:14:50'),
(58, 3, 1, -16.50634343, -68.21578064, 0.00, '2026-09-01 04:14:57'),
(59, 3, 1, -16.50634343, -68.21578064, 0.00, '2026-09-01 04:15:02'),
(60, 3, 1, -16.50634343, -68.21578064, 0.00, '2026-09-01 04:15:08'),
(61, 3, 1, -16.50634343, -68.21578064, 0.00, '2026-09-01 04:15:14'),
(62, 3, 1, -16.50634343, -68.21578064, 0.00, '2026-09-01 04:15:20'),
(63, 3, 1, -16.50634343, -68.21578064, 0.00, '2026-09-01 04:15:26'),
(64, 3, 1, -16.50634343, -68.21578064, 0.00, '2026-09-01 04:15:32'),
(65, 3, 1, -16.50634343, -68.21578064, 0.00, '2026-09-01 04:15:38'),
(66, 3, 1, -16.50634343, -68.21578064, 0.00, '2026-09-01 04:15:47'),
(67, 3, 1, -16.50634343, -68.21578064, 0.00, '2026-09-01 04:15:50'),
(68, 3, 1, -16.50634343, -68.21578064, 0.00, '2026-09-01 04:15:56'),
(69, 3, 1, -16.50634343, -68.21578064, 0.00, '2026-09-01 04:16:05'),
(70, 3, 1, -16.50634343, -68.21578064, 0.00, '2026-09-01 04:16:08'),
(71, 3, 1, -16.50634343, -68.21578064, 0.00, '2026-09-01 04:16:14'),
(72, 3, 1, -16.50634343, -68.21578064, 0.00, '2026-09-01 04:16:23'),
(73, 3, 1, -16.50634343, -68.21578064, 0.00, '2026-09-01 04:16:26'),
(74, 3, 1, -16.50634343, -68.21578064, 0.00, '2026-09-01 04:16:32'),
(75, 3, 1, -16.50634343, -68.21578064, 0.00, '2026-09-01 04:16:38'),
(76, 3, 1, -16.50634343, -68.21578064, 0.00, '2026-09-01 04:16:44'),
(77, 3, 1, -16.50634343, -68.21578064, 0.00, '2026-09-01 04:16:50'),
(78, 3, 1, -16.50634343, -68.21578064, 0.00, '2026-09-01 04:16:56'),
(79, 3, 1, -16.50634343, -68.21578064, 0.00, '2026-09-01 04:17:02'),
(80, 3, 1, -16.50634343, -68.21578064, 0.00, '2026-09-01 04:17:08'),
(81, 3, 1, -16.50634343, -68.21578064, 0.00, '2026-09-01 04:17:14'),
(82, 3, 1, -16.50634343, -68.21578064, 0.00, '2026-09-01 04:17:20'),
(83, 3, 1, -16.50634343, -68.21578064, 0.00, '2026-09-01 04:17:26'),
(84, 3, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 04:18:26'),
(85, 3, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 04:19:09'),
(86, 3, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 04:19:11'),
(87, 3, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 04:19:17'),
(88, 3, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 04:19:23'),
(89, 3, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 04:19:29'),
(90, 3, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 04:29:44'),
(91, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 04:52:41'),
(92, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 04:52:44'),
(93, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 04:52:45'),
(94, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 04:52:45'),
(95, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 04:52:45'),
(96, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 04:52:45'),
(97, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 04:52:46'),
(98, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 04:52:46'),
(99, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 04:52:46'),
(100, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 04:52:46'),
(101, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 04:52:46'),
(102, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 04:52:47'),
(103, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 04:52:47'),
(104, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 04:52:47'),
(105, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 04:52:47'),
(106, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 04:52:47'),
(107, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 04:52:48'),
(108, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 04:52:48'),
(109, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 04:52:48'),
(110, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 04:52:48'),
(111, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 04:52:54'),
(112, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 04:53:00'),
(113, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 04:53:05'),
(114, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 04:53:11'),
(115, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 04:53:17'),
(116, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 04:53:23'),
(117, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 04:53:29'),
(118, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 04:53:35'),
(119, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 04:53:41'),
(120, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 04:53:47'),
(121, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 04:53:53'),
(122, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 04:53:59'),
(123, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 04:54:05'),
(124, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 04:54:11'),
(125, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 04:54:17'),
(126, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 04:54:23'),
(127, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 04:54:29'),
(128, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 04:54:35'),
(129, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 04:54:41'),
(130, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 04:54:47'),
(131, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 04:54:53'),
(132, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 04:54:59'),
(133, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 04:55:05'),
(134, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 04:55:11'),
(135, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 04:55:17'),
(136, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 04:55:23'),
(137, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 04:55:29'),
(138, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 04:55:35'),
(139, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 04:55:41'),
(140, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 04:55:47'),
(141, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 04:55:53'),
(142, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 04:55:59'),
(143, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 04:56:05'),
(144, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 04:56:11'),
(145, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 04:56:17'),
(146, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 04:56:23'),
(147, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 04:56:29'),
(148, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 04:56:35'),
(149, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 04:56:41'),
(150, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 04:56:47'),
(151, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 04:56:53'),
(152, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 04:56:59'),
(153, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 04:57:05'),
(154, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 04:57:11'),
(155, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 04:57:17'),
(156, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 04:57:19'),
(157, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 04:57:25'),
(158, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 04:57:31'),
(159, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 04:57:37'),
(160, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 04:57:43'),
(161, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 04:57:49'),
(162, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 04:57:55'),
(163, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 04:58:01'),
(164, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 04:58:07'),
(165, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 04:58:13'),
(166, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 04:58:19'),
(167, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 04:58:25'),
(168, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 04:58:31'),
(169, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 04:58:37'),
(170, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 04:58:43'),
(171, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 04:58:49'),
(172, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 04:58:55'),
(173, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 04:59:01'),
(174, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 04:59:07'),
(175, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 04:59:13'),
(176, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 04:59:19'),
(177, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 04:59:25'),
(178, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 04:59:31'),
(179, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 04:59:37'),
(180, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 04:59:43'),
(181, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 04:59:49'),
(182, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 04:59:55'),
(183, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:00:01'),
(184, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:00:07'),
(185, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:00:13'),
(186, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:00:19'),
(187, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:00:25'),
(188, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:00:31'),
(189, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:00:37'),
(190, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:00:43'),
(191, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:00:49'),
(192, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:00:55'),
(193, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:01:01'),
(194, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:01:07'),
(195, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:01:13'),
(196, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:01:19'),
(197, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:01:25'),
(198, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:01:31'),
(199, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:01:37'),
(200, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:01:43'),
(201, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:01:49'),
(202, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:01:55'),
(203, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:02:01'),
(204, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:02:07'),
(205, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:02:13'),
(206, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:02:19'),
(207, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:02:25'),
(208, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:02:31'),
(209, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:02:37'),
(210, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:02:43'),
(211, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:02:49'),
(212, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:02:55'),
(213, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:03:01'),
(214, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:03:07'),
(215, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:03:13'),
(216, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:03:19'),
(217, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:03:25'),
(218, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:03:31'),
(219, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:03:37'),
(220, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:03:43'),
(221, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:03:49'),
(222, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:03:55'),
(223, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:04:01'),
(224, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:04:07'),
(225, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:04:13'),
(226, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:04:19'),
(227, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:04:25'),
(228, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:04:31'),
(229, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:04:37'),
(230, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:04:43'),
(231, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:04:49'),
(232, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:04:55'),
(233, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:05:01'),
(234, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:05:07'),
(235, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:05:13'),
(236, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:05:19'),
(237, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:05:25'),
(238, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:05:31'),
(239, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:05:37'),
(240, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:05:43'),
(241, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:05:49'),
(242, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:05:55'),
(243, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:06:01'),
(244, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:06:07'),
(245, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:06:13'),
(246, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:06:19'),
(247, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:06:25'),
(248, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:06:31'),
(249, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:06:37'),
(250, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:06:40'),
(251, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:06:42'),
(252, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:13:14'),
(253, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:13:20'),
(254, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:13:26'),
(255, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:13:32'),
(256, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:13:38'),
(257, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:13:44'),
(258, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:13:50'),
(259, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:13:56'),
(260, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:14:02'),
(261, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:14:08'),
(262, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:14:14'),
(263, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:14:20'),
(264, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:14:26'),
(265, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:14:32'),
(266, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:14:38'),
(267, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:14:44'),
(268, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:14:50'),
(269, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:14:56'),
(270, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:15:02'),
(271, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:15:08'),
(272, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:15:14'),
(273, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:15:20'),
(274, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:15:26'),
(275, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:15:32'),
(276, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:15:38'),
(277, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:15:44'),
(278, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:15:50'),
(279, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:15:56'),
(280, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:16:02'),
(281, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:16:08'),
(282, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:16:14'),
(283, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:16:20'),
(284, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:16:26'),
(285, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:16:32'),
(286, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:16:38'),
(287, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:16:44'),
(288, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:16:50'),
(289, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:16:56'),
(290, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:17:02'),
(291, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:17:08'),
(292, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:17:14'),
(293, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:17:20'),
(294, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:17:26'),
(295, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:17:32'),
(296, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:17:38'),
(297, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:17:44'),
(298, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:17:50'),
(299, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:17:56'),
(300, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:18:02'),
(301, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:18:08'),
(302, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:18:14'),
(303, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:18:20'),
(304, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:18:26'),
(305, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:18:32'),
(306, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:18:38'),
(307, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:18:44'),
(308, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:18:50'),
(309, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:18:56'),
(310, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:19:02'),
(311, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:19:08'),
(312, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:19:14'),
(313, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:19:20'),
(314, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:19:26'),
(315, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:19:32'),
(316, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:19:38'),
(317, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:19:44'),
(318, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:19:50'),
(319, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:19:56'),
(320, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:20:02'),
(321, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:20:08'),
(322, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:20:14'),
(323, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:20:20'),
(324, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:20:26'),
(325, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:20:32'),
(326, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:20:38'),
(327, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:20:44'),
(328, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:20:50'),
(329, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:20:56'),
(330, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:21:02'),
(331, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:21:08'),
(332, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:21:14'),
(333, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:21:20'),
(334, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:21:26'),
(335, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:21:32'),
(336, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:21:38'),
(337, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:21:44'),
(338, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:21:50'),
(339, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:21:56'),
(340, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:22:02'),
(341, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:22:08'),
(342, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:22:14'),
(343, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:22:20'),
(344, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:22:26'),
(345, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:22:32'),
(346, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:22:38'),
(347, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:53:16'),
(348, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:53:22'),
(349, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:53:26'),
(350, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:53:31'),
(351, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:53:37'),
(352, 4, 1, -16.50000000, -68.15000000, 0.00, '2026-09-01 05:53:43'),
(353, 4, 1, -16.50629889, -68.21579457, 0.00, '2026-09-02 05:27:13'),
(354, 5, 1, -16.50634343, -68.21578064, 0.00, '2026-09-02 06:06:28'),
(355, 5, 1, -16.50634343, -68.21578064, 0.00, '2026-09-02 06:06:31'),
(356, 5, 1, -16.50634343, -68.21578064, 0.00, '2026-09-02 06:06:37'),
(357, 5, 1, -16.50634343, -68.21578064, 0.00, '2026-09-02 06:06:43'),
(358, 5, 1, -16.50634343, -68.21578064, 0.00, '2026-09-02 06:06:49'),
(359, 5, 1, -16.50629889, -68.21579457, 0.00, '2026-09-02 06:06:55'),
(360, 5, 1, -16.50629889, -68.21579457, 0.00, '2026-09-02 06:07:01'),
(361, 5, 1, -16.50634343, -68.21578064, 0.00, '2026-09-02 06:07:07'),
(362, 5, 1, -16.50634343, -68.21578064, 0.00, '2026-09-02 06:07:13'),
(363, 5, 1, -16.50634343, -68.21578064, 0.00, '2026-09-02 06:07:19'),
(364, 5, 1, -16.50634343, -68.21578064, 0.00, '2026-09-02 06:07:25'),
(365, 5, 1, -16.50634343, -68.21578064, 0.00, '2026-09-02 06:07:32'),
(366, 5, 1, -16.50634343, -68.21578064, 0.00, '2026-09-02 06:07:37'),
(367, 5, 1, -16.50634343, -68.21578064, 0.00, '2026-09-02 06:07:46'),
(368, 5, 1, -16.50634343, -68.21578064, 0.00, '2026-09-02 06:07:49'),
(369, 5, 1, -16.50634343, -68.21578064, 0.00, '2026-09-02 06:07:55'),
(370, 5, 1, -16.50634343, -68.21578064, 0.00, '2026-09-02 06:08:01'),
(371, 5, 1, -16.50634343, -68.21578064, 0.00, '2026-09-02 06:08:07'),
(372, 5, 1, -16.50634343, -68.21578064, 0.00, '2026-09-02 06:08:13'),
(373, 5, 1, -16.50634343, -68.21578064, 0.00, '2026-09-02 06:08:19'),
(374, 5, 1, -16.50629889, -68.21579457, 0.00, '2026-09-02 06:08:25'),
(375, 5, 1, -16.50629889, -68.21579457, 0.00, '2026-09-02 06:08:31'),
(376, 5, 1, -16.50634343, -68.21578064, 0.00, '2026-09-02 06:08:40'),
(377, 5, 1, -16.50634343, -68.21578064, 0.00, '2026-09-02 06:08:43'),
(378, 5, 1, -16.50634343, -68.21578064, 0.00, '2026-09-02 06:08:49'),
(379, 5, 1, -16.50634343, -68.21578064, 0.00, '2026-09-02 06:08:56'),
(380, 5, 1, -16.50634343, -68.21578064, 0.00, '2026-09-02 06:09:01'),
(381, 5, 1, -16.50634343, -68.21578064, 0.00, '2026-09-02 06:09:07'),
(382, 5, 1, -16.50634343, -68.21578064, 0.00, '2026-09-02 06:09:13'),
(383, 5, 1, -16.50634343, -68.21578064, 0.00, '2026-09-02 06:09:22'),
(384, 5, 1, -16.50634343, -68.21578064, 0.00, '2026-09-02 06:09:25'),
(385, 5, 1, -16.50634343, -68.21578064, 0.00, '2026-09-02 06:09:31'),
(386, 5, 1, -16.50634343, -68.21578064, 0.00, '2026-09-02 06:09:38'),
(387, 5, 1, -16.50634343, -68.21578064, 0.00, '2026-09-02 06:09:43'),
(388, 5, 1, -16.50634343, -68.21578064, 0.00, '2026-09-02 06:09:49'),
(389, 5, 1, -16.50634343, -68.21578064, 0.00, '2026-09-02 06:09:55'),
(390, 5, 1, -16.50634343, -68.21578064, 0.00, '2026-09-02 06:10:01'),
(391, 5, 1, -16.50634343, -68.21578064, 0.00, '2026-09-02 06:10:07'),
(392, 5, 1, -16.50634343, -68.21578064, 0.00, '2026-09-02 06:10:13'),
(393, 5, 1, -16.50634343, -68.21578064, 0.00, '2026-09-02 06:10:19'),
(394, 5, 1, -16.50634343, -68.21578064, 0.00, '2026-09-02 06:10:25'),
(395, 5, 1, -16.50634343, -68.21578064, 0.00, '2026-09-02 06:10:31'),
(396, 5, 1, -16.50634343, -68.21578064, 0.00, '2026-09-02 06:10:37'),
(397, 5, 1, -16.50634343, -68.21578064, 0.00, '2026-09-02 06:10:43'),
(398, 5, 1, -16.50634343, -68.21578064, 0.00, '2026-09-02 06:10:52'),
(399, 5, 1, -16.50634343, -68.21578064, 0.00, '2026-09-02 06:10:55'),
(400, 5, 1, -16.50634343, -68.21578064, 0.00, '2026-09-02 06:11:01'),
(401, 5, 1, -16.50634343, -68.21578064, 0.00, '2026-09-02 06:11:09'),
(402, 5, 1, -16.50634343, -68.21578064, 0.00, '2026-09-02 06:11:13'),
(403, 5, 1, -16.50634343, -68.21578064, 0.00, '2026-09-02 06:11:22'),
(404, 5, 1, -16.50634343, -68.21578064, 0.00, '2026-09-02 06:11:25'),
(405, 5, 1, -16.50634343, -68.21578064, 0.00, '2026-09-02 06:11:31'),
(406, 5, 1, -16.50634343, -68.21578064, 0.00, '2026-09-02 06:11:37'),
(407, 5, 1, -16.50634343, -68.21578064, 0.00, '2026-09-02 06:11:43'),
(408, 5, 1, -16.50629889, -68.21579457, 0.00, '2026-09-02 06:11:49'),
(409, 5, 1, -16.50629889, -68.21579457, 0.00, '2026-09-02 06:11:55'),
(410, 5, 1, -16.50634343, -68.21578064, 0.00, '2026-09-02 06:12:02'),
(411, 5, 1, -16.50634343, -68.21578064, 0.00, '2026-09-02 06:12:07'),
(412, 5, 1, -16.50629889, -68.21579457, 0.00, '2026-09-02 06:12:16'),
(413, 5, 1, -16.50629889, -68.21579457, 0.00, '2026-09-02 06:12:19'),
(414, 5, 1, -16.50629889, -68.21579457, 0.00, '2026-09-02 06:12:25'),
(415, 5, 1, -16.50634343, -68.21578064, 0.00, '2026-09-02 06:12:31'),
(416, 5, 1, -16.50634343, -68.21578064, 0.00, '2026-09-02 06:12:37'),
(417, 5, 1, -16.50688200, -68.21546900, 0.00, '2026-09-02 06:12:43'),
(418, 5, 1, -16.50629889, -68.21579457, 0.00, '2026-09-02 06:12:51'),
(419, 5, 1, -16.50629889, -68.21579457, 0.00, '2026-09-02 06:12:55'),
(420, 5, 1, -16.50629889, -68.21579457, 0.00, '2026-09-02 06:13:01'),
(421, 5, 1, -16.50688200, -68.21546900, 0.00, '2026-09-02 06:13:10'),
(422, 5, 1, -16.50688200, -68.21546900, 0.00, '2026-09-02 06:13:13'),
(423, 5, 1, -16.50634343, -68.21578064, 0.00, '2026-09-02 06:13:19'),
(424, 5, 1, -16.50634343, -68.21578064, 0.00, '2026-09-02 06:13:25'),
(425, 5, 1, -16.50634343, -68.21578064, 0.00, '2026-09-02 06:13:31'),
(426, 5, 1, -16.50634343, -68.21578064, 0.00, '2026-09-02 06:13:37'),
(427, 5, 1, -16.50634343, -68.21578064, 0.00, '2026-09-02 06:13:43'),
(428, 5, 1, -16.50634343, -68.21578064, 0.00, '2026-09-02 06:13:49'),
(429, 5, 1, -16.50634343, -68.21578064, 0.00, '2026-09-02 06:13:55'),
(430, 5, 1, -16.50634343, -68.21578064, 0.00, '2026-09-02 06:14:01'),
(431, 5, 1, -16.50629889, -68.21579457, 0.00, '2026-09-02 06:14:10'),
(432, 5, 1, -16.50629889, -68.21579457, 0.00, '2026-09-02 06:14:13'),
(433, 5, 1, -16.50629889, -68.21579457, 0.00, '2026-09-02 06:14:19'),
(434, 5, 1, -16.50629889, -68.21579457, 0.00, '2026-09-02 06:14:27'),
(435, 5, 1, -16.50629889, -68.21579457, 0.00, '2026-09-02 06:14:31'),
(436, 5, 1, -16.50629889, -68.21579457, 0.00, '2026-09-02 06:14:37'),
(437, 5, 1, -16.50634343, -68.21578064, 0.00, '2026-09-02 06:14:43'),
(438, 5, 1, -16.50634343, -68.21578064, 0.00, '2026-09-02 06:14:49'),
(439, 5, 1, -16.50688200, -68.21546900, 0.00, '2026-09-02 06:14:57'),
(440, 5, 1, -16.50630710, -68.21578650, 0.00, '2026-09-02 06:15:04'),
(441, 5, 1, -16.50630710, -68.21578650, 0.00, '2026-09-02 06:15:07'),
(442, 5, 1, -16.50630710, -68.21578650, 0.00, '2026-09-02 06:15:13'),
(443, 5, 1, -16.50634343, -68.21578064, 0.00, '2026-09-02 06:15:21'),
(444, 5, 1, -16.50634343, -68.21578064, 0.00, '2026-09-02 06:15:25'),
(445, 5, 1, -16.50634343, -68.21578064, 0.00, '2026-09-02 06:15:31'),
(446, 5, 1, -16.50630710, -68.21578650, 0.00, '2026-09-02 06:15:37'),
(447, 5, 1, -16.50630710, -68.21578650, 0.00, '2026-09-02 06:15:43'),
(448, 5, 1, -16.50688200, -68.21546900, 0.00, '2026-09-02 06:15:49'),
(449, 5, 1, -16.50634343, -68.21578064, 0.00, '2026-09-02 06:15:58'),
(450, 5, 1, -16.50634343, -68.21578064, 0.00, '2026-09-02 06:16:01'),
(451, 5, 1, -16.50634343, -68.21578064, 0.00, '2026-09-02 06:16:07'),
(452, 5, 1, -16.50629889, -68.21579457, 0.00, '2026-09-02 06:16:13'),
(453, 5, 1, -16.50629889, -68.21579457, 0.00, '2026-09-02 06:16:19'),
(454, 5, 1, -16.50644839, -68.21569233, 0.00, '2026-09-02 06:16:27'),
(455, 5, 1, -16.50644839, -68.21569233, 0.00, '2026-09-02 06:16:31'),
(456, 5, 1, -16.50644839, -68.21569233, 0.00, '2026-09-02 06:16:37'),
(457, 5, 1, -16.50634343, -68.21578064, 0.00, '2026-09-02 06:16:46'),
(458, 5, 1, -16.50634343, -68.21578064, 0.00, '2026-09-02 06:16:49'),
(459, 5, 1, -16.50634343, -68.21578064, 0.00, '2026-09-02 06:16:55'),
(460, 5, 1, -16.50688200, -68.21546900, 0.00, '2026-09-02 06:17:03'),
(461, 5, 1, -16.50634343, -68.21578064, 0.00, '2026-09-02 06:17:07'),
(462, 5, 1, -16.50634343, -68.21578064, 0.00, '2026-09-02 06:17:13'),
(463, 5, 1, -16.50688200, -68.21546900, 0.00, '2026-09-02 06:17:19'),
(464, 5, 1, -16.50630710, -68.21578650, 0.00, '2026-09-02 06:17:28'),
(465, 5, 1, -16.50630710, -68.21578650, 0.00, '2026-09-02 06:17:31'),
(466, 5, 1, -16.50630710, -68.21578650, 0.00, '2026-09-02 06:17:37'),
(467, 5, 1, -16.50630710, -68.21578650, 0.00, '2026-09-02 06:17:45'),
(468, 5, 1, -16.50630710, -68.21578650, 0.00, '2026-09-02 06:17:49'),
(469, 5, 1, -16.50634343, -68.21578064, 0.00, '2026-09-02 06:17:56'),
(470, 5, 1, -16.50634343, -68.21578064, 0.00, '2026-09-02 06:18:01'),
(471, 5, 1, -16.50629889, -68.21579457, 0.00, '2026-09-02 06:18:07'),
(472, 5, 1, -16.50629889, -68.21579457, 0.00, '2026-09-02 06:18:13'),
(473, 5, 1, -16.50630710, -68.21578650, 0.00, '2026-09-02 06:18:19'),
(474, 5, 1, -16.50630710, -68.21578650, 0.00, '2026-09-02 06:18:25'),
(475, 5, 1, -16.50630710, -68.21578650, 0.00, '2026-09-02 06:18:34'),
(476, 5, 1, -16.50630710, -68.21578650, 0.00, '2026-09-02 06:18:37'),
(477, 5, 1, -16.50630710, -68.21578650, 0.00, '2026-09-02 06:18:43'),
(478, 5, 1, -16.50688200, -68.21546900, 0.00, '2026-09-02 06:18:49'),
(479, 5, 1, -16.50634343, -68.21578064, 0.00, '2026-09-02 06:20:11'),
(480, 5, 1, -16.50634343, -68.21578064, 0.00, '2026-09-02 06:20:14'),
(481, 5, 1, -16.50634343, -68.21578064, 0.00, '2026-09-02 06:20:16'),
(482, 5, 1, -16.50629889, -68.21579457, 0.00, '2026-09-02 06:20:48'),
(483, 5, 1, -16.50629889, -68.21579457, 0.00, '2026-09-02 06:20:51'),
(484, 6, 1, -16.50629889, -68.21579457, 0.00, '2026-09-02 06:20:51'),
(485, 13, 1, -16.49008300, -68.20688800, 0.00, '2026-09-02 19:02:01'),
(486, 13, 1, -16.49008300, -68.20688800, 0.00, '2026-09-02 19:03:56'),
(487, 13, 1, -16.50634343, -68.21578064, 0.00, '2026-09-02 19:04:00'),
(488, 13, 1, -16.50634343, -68.21578064, 0.00, '2026-09-02 19:04:17'),
(489, 13, 1, -16.50634343, -68.21578064, 0.00, '2026-09-02 19:28:17'),
(490, 14, 1, -16.50634343, -68.21578064, 0.00, '2026-09-02 19:30:52'),
(491, 5, 1, -16.50634343, -68.21578064, 0.00, '2026-09-02 19:42:40'),
(492, 14, 1, -16.50634343, -68.21578064, 0.00, '2026-09-02 19:42:47'),
(493, 16, 1, -16.50969778, -68.15243490, 0.00, '2026-09-04 00:37:23'),
(494, 16, 1, -16.50967890, -68.15277109, 0.00, '2026-09-04 00:37:36'),
(495, 16, 1, -16.50968431, -68.15247273, 0.00, '2026-09-04 00:37:48'),
(496, 17, 1, -16.50971286, -68.15256534, 0.00, '2026-09-04 00:56:49'),
(497, 17, 1, -16.50963928, -68.15261301, 0.00, '2026-09-04 00:57:00'),
(498, 17, 1, -16.50968476, -68.15246916, 0.00, '2026-09-04 00:57:11'),
(499, 17, 1, -16.50953945, -68.15251015, 0.00, '2026-09-04 00:57:22'),
(500, 17, 1, -16.50963654, -68.15240599, 0.00, '2026-09-04 00:57:32'),
(501, 17, 1, -16.50972370, -68.15240333, 0.00, '2026-09-04 00:57:42'),
(502, 17, 1, -16.50959635, -68.15242656, 0.00, '2026-09-04 00:57:53'),
(503, 17, 1, -16.50956641, -68.15248260, 0.00, '2026-09-04 00:58:03'),
(504, 17, 1, -16.50965997, -68.15256886, 0.00, '2026-09-04 00:58:14'),
(505, 17, 1, -16.50968961, -68.15246707, 0.00, '2026-09-04 00:58:26'),
(506, 17, 1, -16.50969726, -68.15257780, 0.00, '2026-09-04 00:58:36'),
(507, 17, 1, -16.50968153, -68.15256257, 0.00, '2026-09-04 00:58:46'),
(508, 17, 1, -16.50965322, -68.15235616, 0.00, '2026-09-04 01:00:11'),
(509, 17, 1, -16.50962653, -68.15261558, 0.00, '2026-09-04 01:00:22');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `transacciones_suscripcion`
--

CREATE TABLE `transacciones_suscripcion` (
  `id_transaccion` int(11) NOT NULL,
  `id_profesional` int(11) NOT NULL,
  `id_plan` int(11) DEFAULT NULL,
  `tipo_transaccion` enum('MEMBRESIA_MENSUAL','PAQUETE_TOKENS') NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `metodo_pago` varchar(50) DEFAULT 'QR_SIMPLE_BOLIVIA',
  `codigo_comprobante` varchar(100) NOT NULL,
  `estado_pago` enum('PENDIENTE','CONFIRMADO','FALLIDO') DEFAULT 'CONFIRMADO',
  `fecha_pago` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `transacciones_suscripcion`
--

INSERT INTO `transacciones_suscripcion` (`id_transaccion`, `id_profesional`, `id_plan`, `tipo_transaccion`, `monto`, `metodo_pago`, `codigo_comprobante`, `estado_pago`, `fecha_pago`) VALUES
(1, 1, NULL, 'PAQUETE_TOKENS', 10.00, 'QR_SIMPLE_BOLIVIA', 'ASFASFASADASD12313', 'CONFIRMADO', '2026-09-01 05:07:34');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `id_rol` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `correo` varchar(120) NOT NULL,
  `celular` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `foto_perfil` varchar(255) DEFAULT NULL,
  `estado` enum('ACTIVO','INACTIVO','BLOQUEADO') NOT NULL DEFAULT 'ACTIVO',
  `token_recuperacion` varchar(255) DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `id_rol`, `nombre`, `apellido`, `correo`, `celular`, `password`, `foto_perfil`, `estado`, `token_recuperacion`, `fecha_registro`) VALUES
(1, 4, 'cliente', 'mamani machaca', 'cliente@gmail.com', '62552865', '$2y$10$6TRsOAC0LpoNd9CSQpV.G.bpZfbGAqaPT.ndCGk0FulmIoFpgG/XG', NULL, 'ACTIVO', NULL, '2026-08-30 18:51:48'),
(3, 1, 'super', 'admin Mamani', 'admin@geopro.com', '70000000', '$2y$10$EWILLTXX9j62AoMpG4Ap1.RaUdztvRMlbj9R3nJQaO0McYEGn1Ex6', NULL, 'ACTIVO', NULL, '2026-08-30 19:06:19'),
(4, 3, 'profecional', 'profecional profecional', 'profecional@gmail.com', '70000001', '$2y$10$stVDZg/wN8wQtC9RHq9C6ODS/M3scdBQC1YsMyOULJqyQMUeGT99q', NULL, 'ACTIVO', NULL, '2026-08-30 19:45:43'),
(6, 3, 'Empirico', 'Empirico Empirico', 'empirico@gmail.com', '70000009', '$2y$10$JPGeZenU56T2F.7pzUhgVOIajPtbxd9UmGqKapbDR32y9tV3IyMle', NULL, 'ACTIVO', NULL, '2026-08-30 20:12:28');

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vw_auditoria_detallada`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vw_auditoria_detallada` (
`id_log` bigint(20)
,`fecha_evento` timestamp
,`responsable` varchar(201)
,`nombre_rol` varchar(50)
,`accion` varchar(100)
,`tabla_afectada` varchar(50)
,`registro_id` int(11)
,`valores_anteriores` longtext
,`valores_nuevos` longtext
,`ip_origen` varchar(45)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vw_metricas_profesionales`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vw_metricas_profesionales` (
`id_profesional` int(11)
,`nombre_completo` varchar(201)
,`correo` varchar(120)
,`celular` varchar(20)
,`nombre_categoria` varchar(100)
,`tipo_prestador` enum('TECNICO_PROFESIONAL','OFICIO_EMPIRICO')
,`macrodistrito_base` enum('ZONA_SUR','SOPOCACHI','CENTRO','MIRAFLORES','SAN_PEDRO','COTAHUMA','PERIFERICA','EL_ALTO')
,`estado_validacion` enum('PENDIENTE','APROBADO','RECHAZADO')
,`estado_disponibilidad` enum('DISPONIBLE','OCUPADO','NO_DISPONIBLE')
,`nombre_plan` varchar(50)
,`total_servicios_atendidos` bigint(21)
,`promedio_estrellas` decimal(13,2)
,`promedio_puntualidad` decimal(13,2)
,`promedio_calidad` decimal(13,2)
);

-- --------------------------------------------------------

--
-- Estructura para la vista `vw_auditoria_detallada`
--
DROP TABLE IF EXISTS `vw_auditoria_detallada`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_auditoria_detallada`  AS SELECT `a`.`id_log` AS `id_log`, `a`.`fecha_evento` AS `fecha_evento`, coalesce(concat(`u`.`nombre`,' ',`u`.`apellido`),'SISTEMA_AUTO') AS `responsable`, `r`.`nombre_rol` AS `nombre_rol`, `a`.`accion` AS `accion`, `a`.`tabla_afectada` AS `tabla_afectada`, `a`.`registro_id` AS `registro_id`, `a`.`valores_anteriores` AS `valores_anteriores`, `a`.`valores_nuevos` AS `valores_nuevos`, `a`.`ip_origen` AS `ip_origen` FROM ((`auditoria_logs` `a` left join `usuarios` `u` on(`a`.`id_usuario` = `u`.`id_usuario`)) left join `roles` `r` on(`u`.`id_rol` = `r`.`id_rol`)) ORDER BY `a`.`fecha_evento` DESC ;

-- --------------------------------------------------------

--
-- Estructura para la vista `vw_metricas_profesionales`
--
DROP TABLE IF EXISTS `vw_metricas_profesionales`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_metricas_profesionales`  AS SELECT `p`.`id_profesional` AS `id_profesional`, concat(`u`.`nombre`,' ',`u`.`apellido`) AS `nombre_completo`, `u`.`correo` AS `correo`, `u`.`celular` AS `celular`, `c`.`nombre_categoria` AS `nombre_categoria`, `p`.`tipo_prestador` AS `tipo_prestador`, `p`.`macrodistrito_base` AS `macrodistrito_base`, `p`.`estado_validacion` AS `estado_validacion`, `p`.`estado_disponibilidad` AS `estado_disponibilidad`, `pl`.`nombre_plan` AS `nombre_plan`, count(`s`.`id_solicitud`) AS `total_servicios_atendidos`, coalesce(round(avg(`cal`.`puntuacion_general`),2),5.00) AS `promedio_estrellas`, coalesce(round(avg(`cal`.`puntualidad`),2),5.00) AS `promedio_puntualidad`, coalesce(round(avg(`cal`.`calidad_trabajo`),2),5.00) AS `promedio_calidad` FROM (((((`profesionales` `p` join `usuarios` `u` on(`p`.`id_usuario` = `u`.`id_usuario`)) join `categorias` `c` on(`p`.`id_categoria` = `c`.`id_categoria`)) join `planes_suscripcion` `pl` on(`p`.`id_plan` = `pl`.`id_plan`)) left join `solicitudes_servicio` `s` on(`p`.`id_profesional` = `s`.`id_profesional` and `s`.`estado_servicio` = 'FINALIZADA')) left join `calificaciones` `cal` on(`s`.`id_solicitud` = `cal`.`id_solicitud`)) GROUP BY `p`.`id_profesional`, `u`.`nombre`, `u`.`apellido`, `u`.`correo`, `u`.`celular`, `c`.`nombre_categoria`, `p`.`tipo_prestador`, `p`.`macrodistrito_base`, `p`.`estado_validacion`, `p`.`estado_disponibilidad`, `pl`.`nombre_plan` ;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `auditoria_logs`
--
ALTER TABLE `auditoria_logs`
  ADD PRIMARY KEY (`id_log`),
  ADD KEY `fk_auditoria_usuario` (`id_usuario`),
  ADD KEY `idx_auditoria_fecha` (`fecha_evento`);

--
-- Indices de la tabla `calificaciones`
--
ALTER TABLE `calificaciones`
  ADD PRIMARY KEY (`id_calificacion`),
  ADD UNIQUE KEY `id_solicitud` (`id_solicitud`);

--
-- Indices de la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id_categoria`),
  ADD UNIQUE KEY `nombre_categoria` (`nombre_categoria`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id_cliente`),
  ADD UNIQUE KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `documentos_profesional`
--
ALTER TABLE `documentos_profesional`
  ADD PRIMARY KEY (`id_documento`),
  ADD KEY `fk_doc_profesional` (`id_profesional`),
  ADD KEY `fk_doc_revisor` (`revisado_por`);

--
-- Indices de la tabla `mensajes`
--
ALTER TABLE `mensajes`
  ADD PRIMARY KEY (`id_mensaje`),
  ADD KEY `fk_mensaje_solicitud` (`id_solicitud`),
  ADD KEY `fk_mensaje_remitente` (`id_remitente`);

--
-- Indices de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD PRIMARY KEY (`id_notificacion`),
  ADD KEY `idx_notif_usuario_leida` (`id_usuario`,`leida`);

--
-- Indices de la tabla `planes_suscripcion`
--
ALTER TABLE `planes_suscripcion`
  ADD PRIMARY KEY (`id_plan`),
  ADD UNIQUE KEY `nombre_plan` (`nombre_plan`);

--
-- Indices de la tabla `profesionales`
--
ALTER TABLE `profesionales`
  ADD PRIMARY KEY (`id_profesional`),
  ADD UNIQUE KEY `id_usuario` (`id_usuario`),
  ADD UNIQUE KEY `numero_documento` (`numero_documento`),
  ADD KEY `fk_profesional_categoria` (`id_categoria`),
  ADD KEY `fk_profesional_plan` (`id_plan`),
  ADD KEY `idx_prof_disponibilidad` (`estado_disponibilidad`,`estado_validacion`),
  ADD KEY `idx_prof_distrito` (`macrodistrito_base`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id_rol`),
  ADD UNIQUE KEY `nombre_rol` (`nombre_rol`);

--
-- Indices de la tabla `solicitudes_servicio`
--
ALTER TABLE `solicitudes_servicio`
  ADD PRIMARY KEY (`id_solicitud`),
  ADD UNIQUE KEY `codigo_seguimiento` (`codigo_seguimiento`),
  ADD KEY `fk_solicitud_cliente` (`id_cliente`),
  ADD KEY `fk_solicitud_profesional` (`id_profesional`),
  ADD KEY `idx_solicitud_estado` (`estado_servicio`);

--
-- Indices de la tabla `tracking_solicitud_gps`
--
ALTER TABLE `tracking_solicitud_gps`
  ADD PRIMARY KEY (`id_tracking`),
  ADD KEY `fk_tracking_profesional` (`id_profesional`),
  ADD KEY `idx_tracking_reciente` (`id_solicitud`,`timestamp_registro`);

--
-- Indices de la tabla `transacciones_suscripcion`
--
ALTER TABLE `transacciones_suscripcion`
  ADD PRIMARY KEY (`id_transaccion`),
  ADD UNIQUE KEY `codigo_comprobante` (`codigo_comprobante`),
  ADD KEY `fk_transaccion_profesional` (`id_profesional`),
  ADD KEY `fk_transaccion_plan` (`id_plan`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `correo` (`correo`),
  ADD UNIQUE KEY `celular` (`celular`),
  ADD KEY `fk_usuario_rol` (`id_rol`),
  ADD KEY `idx_usuarios_correo` (`correo`),
  ADD KEY `idx_usuarios_celular` (`celular`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `auditoria_logs`
--
ALTER TABLE `auditoria_logs`
  MODIFY `id_log` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=184;

--
-- AUTO_INCREMENT de la tabla `calificaciones`
--
ALTER TABLE `calificaciones`
  MODIFY `id_calificacion` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id_categoria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id_cliente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `documentos_profesional`
--
ALTER TABLE `documentos_profesional`
  MODIFY `id_documento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `mensajes`
--
ALTER TABLE `mensajes`
  MODIFY `id_mensaje` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  MODIFY `id_notificacion` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `planes_suscripcion`
--
ALTER TABLE `planes_suscripcion`
  MODIFY `id_plan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `profesionales`
--
ALTER TABLE `profesionales`
  MODIFY `id_profesional` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id_rol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `solicitudes_servicio`
--
ALTER TABLE `solicitudes_servicio`
  MODIFY `id_solicitud` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de la tabla `tracking_solicitud_gps`
--
ALTER TABLE `tracking_solicitud_gps`
  MODIFY `id_tracking` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=510;

--
-- AUTO_INCREMENT de la tabla `transacciones_suscripcion`
--
ALTER TABLE `transacciones_suscripcion`
  MODIFY `id_transaccion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `auditoria_logs`
--
ALTER TABLE `auditoria_logs`
  ADD CONSTRAINT `fk_auditoria_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE SET NULL;

--
-- Filtros para la tabla `calificaciones`
--
ALTER TABLE `calificaciones`
  ADD CONSTRAINT `fk_calificacion_solicitud` FOREIGN KEY (`id_solicitud`) REFERENCES `solicitudes_servicio` (`id_solicitud`) ON DELETE CASCADE;

--
-- Filtros para la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD CONSTRAINT `fk_cliente_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `documentos_profesional`
--
ALTER TABLE `documentos_profesional`
  ADD CONSTRAINT `fk_doc_profesional` FOREIGN KEY (`id_profesional`) REFERENCES `profesionales` (`id_profesional`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_doc_revisor` FOREIGN KEY (`revisado_por`) REFERENCES `usuarios` (`id_usuario`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `mensajes`
--
ALTER TABLE `mensajes`
  ADD CONSTRAINT `fk_mensaje_remitente` FOREIGN KEY (`id_remitente`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_mensaje_solicitud` FOREIGN KEY (`id_solicitud`) REFERENCES `solicitudes_servicio` (`id_solicitud`) ON DELETE CASCADE;

--
-- Filtros para la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD CONSTRAINT `fk_notif_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `profesionales`
--
ALTER TABLE `profesionales`
  ADD CONSTRAINT `fk_profesional_categoria` FOREIGN KEY (`id_categoria`) REFERENCES `categorias` (`id_categoria`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_profesional_plan` FOREIGN KEY (`id_plan`) REFERENCES `planes_suscripcion` (`id_plan`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_profesional_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `solicitudes_servicio`
--
ALTER TABLE `solicitudes_servicio`
  ADD CONSTRAINT `fk_solicitud_cliente` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id_cliente`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_solicitud_profesional` FOREIGN KEY (`id_profesional`) REFERENCES `profesionales` (`id_profesional`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `tracking_solicitud_gps`
--
ALTER TABLE `tracking_solicitud_gps`
  ADD CONSTRAINT `fk_tracking_profesional` FOREIGN KEY (`id_profesional`) REFERENCES `profesionales` (`id_profesional`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_tracking_solicitud` FOREIGN KEY (`id_solicitud`) REFERENCES `solicitudes_servicio` (`id_solicitud`) ON DELETE CASCADE;

--
-- Filtros para la tabla `transacciones_suscripcion`
--
ALTER TABLE `transacciones_suscripcion`
  ADD CONSTRAINT `fk_transaccion_plan` FOREIGN KEY (`id_plan`) REFERENCES `planes_suscripcion` (`id_plan`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_transaccion_profesional` FOREIGN KEY (`id_profesional`) REFERENCES `profesionales` (`id_profesional`) ON DELETE CASCADE;

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `fk_usuario_rol` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`) ON UPDATE CASCADE;

DELIMITER $$
--
-- Eventos
--
CREATE DEFINER=`root`@`localhost` EVENT `evt_purgar_mensajes_antiguos` ON SCHEDULE EVERY 1 DAY STARTS '2026-08-30 12:54:41' ON COMPLETION NOT PRESERVE ENABLE DO BEGIN
    -- Elimina mensajes de solicitudes finalizadas o canceladas hace más de 15 días
    DELETE m FROM mensajes m
    INNER JOIN solicitudes_servicio s ON m.id_solicitud = s.id_solicitud
    WHERE s.estado_servicio IN ('FINALIZADA', 'CANCELADA')
      AND s.fecha_finalizacion < DATE_SUB(NOW(), INTERVAL 15 DAY);
END$$

DELIMITER ;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
