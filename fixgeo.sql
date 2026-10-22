-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3307
-- Tiempo de generación: 12-10-2026 a las 21:26:59
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
-- Base de datos: `marketplace_servicios_lp_V2`
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
(1, NULL, 'LOGIN_FAILED', 'usuarios', NULL, NULL, '{\"correo_intentado\":\"admin@geopro.com\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', '2026-10-02 15:43:48'),
(2, 1, 'REGISTRO_CLIENTE', 'usuarios', 1, NULL, '{\"zona\":\"Sopocachi\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', '2026-10-02 15:45:01'),
(3, 1, 'LOGIN_SUCCESS', 'usuarios', 1, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', '2026-10-02 15:45:09'),
(4, 1, 'LOGOUT', 'usuarios', 1, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', '2026-10-02 16:05:00'),
(5, 2, 'LOGIN_SUCCESS', 'usuarios', 2, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', '2026-10-02 16:05:07'),
(6, 2, 'CREAR_CATEGORIA', 'categorias', NULL, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', '2026-10-02 16:09:19'),
(7, 2, 'TOGGLE_CATEGORIA', 'categorias', 5, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', '2026-10-02 16:09:27'),
(8, 2, 'TOGGLE_CATEGORIA', 'categorias', 5, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', '2026-10-02 16:09:30'),
(9, 2, 'ACTUALIZAR_PLAN_2', 'planes_suscripcion', 2, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', '2026-10-02 16:23:18'),
(10, 2, 'ACTUALIZAR_PLAN_2', 'planes_suscripcion', 2, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', '2026-10-02 16:23:25'),
(11, 2, 'LOGOUT', 'usuarios', 2, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', '2026-10-02 17:23:47'),
(12, 3, 'REGISTRO_PRESTADOR', 'profesionales', 1, NULL, '{\"tipo_prestador\":\"TECNICO_PROFESIONAL\",\"macrodistrito\":\"SOPOCACHI\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', '2026-10-02 17:26:34'),
(13, 2, 'LOGIN_SUCCESS', 'usuarios', 2, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', '2026-10-02 17:26:42'),
(14, 2, 'LOGOUT', 'usuarios', 2, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', '2026-10-02 17:52:16'),
(15, 2, 'LOGIN_SUCCESS', 'usuarios', 2, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', '2026-10-02 17:52:26'),
(16, 2, 'APROBAR_PROFESIONAL', 'profesionales', 1, NULL, '[]', '::1', 'Mozilla/5.0 (Linux; Android 16; Pixel 10) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Mobile Safari/537.36', '2026-10-02 18:47:54'),
(17, 2, 'LOGOUT', 'usuarios', 2, NULL, '[]', '::1', 'Mozilla/5.0 (Linux; Android 16; Pixel 10) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Mobile Safari/537.36', '2026-10-02 19:23:40'),
(18, 3, 'LOGIN_SUCCESS', 'usuarios', 3, NULL, '[]', '::1', 'Mozilla/5.0 (Linux; Android 16; Pixel 10) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Mobile Safari/537.36', '2026-10-02 19:23:50'),
(19, 3, 'LOGOUT', 'usuarios', 3, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', '2026-10-03 01:10:31'),
(20, 3, 'LOGIN_SUCCESS', 'usuarios', 3, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', '2026-10-03 01:10:41'),
(21, 1, 'LOGIN_SUCCESS', 'usuarios', 1, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', '2026-10-04 17:05:59'),
(22, 1, 'LOGOUT', 'usuarios', 1, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', '2026-10-04 19:51:25'),
(23, 1, 'LOGIN_SUCCESS', 'usuarios', 1, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', '2026-10-04 20:04:17'),
(24, 1, 'LOGOUT', 'usuarios', 1, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', '2026-10-04 20:26:45'),
(25, NULL, 'LOGIN_FAILED', 'usuarios', NULL, NULL, '{\"correo_intentado\":\"profesional1@gmail.com\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', '2026-10-05 13:19:59'),
(26, 6, 'LOGIN_SUCCESS', 'usuarios', 6, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', '2026-10-05 13:20:16'),
(27, 6, 'LOGIN_SUCCESS', 'usuarios', 6, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', '2026-10-05 13:20:47'),
(28, NULL, 'LOGIN_FAILED', 'usuarios', NULL, NULL, '{\"correo_intentado\":\"profecional@gmail.com\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', '2026-10-05 13:23:13'),
(29, 2, 'LOGIN_SUCCESS', 'usuarios', 2, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', '2026-10-05 14:41:29'),
(30, 2, 'APROBAR_PROFESIONAL', 'profesionales', 2, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', '2026-10-05 14:41:43'),
(31, 3, 'LOGIN_SUCCESS', 'usuarios', 3, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-08 10:33:06'),
(32, NULL, 'LOGIN_FAILED', 'usuarios', NULL, NULL, '{\"correo_intentado\":\"cliente@gmail.com\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', '2026-10-08 10:34:00'),
(33, 1, 'LOGIN_SUCCESS', 'usuarios', 1, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', '2026-10-08 10:34:08'),
(34, NULL, 'CAMBIO_ESTADO_ACEPTADA', 'solicitudes_servicio', 1, '{\"estado_anterior\": \"PENDIENTE\"}', '{\"estado_nuevo\": \"ACEPTADA\", \"codigo\": \"GEO-E7ECBD9C\"}', '127.0.0.1', NULL, '2026-10-08 11:23:15'),
(35, NULL, 'CAMBIO_ESTADO_EN_CAMINO', 'solicitudes_servicio', 1, '{\"estado_anterior\": \"ACEPTADA\"}', '{\"estado_nuevo\": \"EN_CAMINO\", \"codigo\": \"GEO-E7ECBD9C\"}', '127.0.0.1', NULL, '2026-10-08 11:23:28'),
(36, NULL, 'CAMBIO_ESTADO_EN_PROCESO', 'solicitudes_servicio', 1, '{\"estado_anterior\": \"EN_CAMINO\"}', '{\"estado_nuevo\": \"EN_PROCESO\", \"codigo\": \"GEO-E7ECBD9C\"}', '127.0.0.1', NULL, '2026-10-08 11:53:14'),
(37, 3, 'CAMBIO_ESTADO_EN_PROCESO', 'solicitudes_servicio', 1, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-08 11:53:14'),
(38, NULL, 'CAMBIO_ESTADO_FINALIZADA', 'solicitudes_servicio', 1, '{\"estado_anterior\": \"EN_PROCESO\"}', '{\"estado_nuevo\": \"FINALIZADA\", \"codigo\": \"GEO-E7ECBD9C\"}', '127.0.0.1', NULL, '2026-10-08 11:53:17'),
(39, 11, 'REGISTRO_PROFESIONAL_APP', 'profesionales', 3, NULL, '{\"tipo_prestador\":\"OFICIO_EMPIRICO\",\"estado_validacion\":\"PENDIENTE\",\"origen\":\"app_movil\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', '2026-10-08 21:35:15'),
(40, NULL, 'CAMBIO_ESTADO_ACEPTADA', 'solicitudes_servicio', 5, '{\"estado_anterior\": \"PENDIENTE\"}', '{\"estado_nuevo\": \"ACEPTADA\", \"codigo\": \"GEO-6AA74A59\"}', '127.0.0.1', NULL, '2026-10-08 21:40:16'),
(41, NULL, 'CAMBIO_ESTADO_EN_CAMINO', 'solicitudes_servicio', 5, '{\"estado_anterior\": \"ACEPTADA\"}', '{\"estado_nuevo\": \"EN_CAMINO\", \"codigo\": \"GEO-6AA74A59\"}', '127.0.0.1', NULL, '2026-10-08 21:40:32'),
(42, NULL, 'CAMBIO_ESTADO_EN_PROCESO', 'solicitudes_servicio', 5, '{\"estado_anterior\": \"EN_CAMINO\"}', '{\"estado_nuevo\": \"EN_PROCESO\", \"codigo\": \"GEO-6AA74A59\"}', '127.0.0.1', NULL, '2026-10-08 21:43:17'),
(43, 3, 'CAMBIO_ESTADO_EN_PROCESO', 'solicitudes_servicio', 5, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-08 21:43:17'),
(44, NULL, 'CAMBIO_ESTADO_FINALIZADA', 'solicitudes_servicio', 5, '{\"estado_anterior\": \"EN_PROCESO\"}', '{\"estado_nuevo\": \"FINALIZADA\", \"codigo\": \"GEO-6AA74A59\"}', '127.0.0.1', NULL, '2026-10-08 21:43:20'),
(45, 3, 'LOGOUT', 'usuarios', 3, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-09 01:00:25'),
(46, 2, 'LOGIN_SUCCESS', 'usuarios', 2, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-09 01:00:32'),
(47, 2, 'CREAR_CATEGORIA', 'categorias', NULL, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-09 01:00:46'),
(48, 2, 'APROBAR_PROFESIONAL', 'profesionales', 3, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-09 01:01:45'),
(49, 12, 'REGISTRO_PROFESIONAL_APP', 'profesionales', 4, NULL, '{\"tipo_prestador\":\"TECNICO_PROFESIONAL\",\"estado_validacion\":\"PENDIENTE\",\"origen\":\"app_movil\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', '2026-10-09 01:04:51'),
(50, 2, 'RECHAZAR_PROFESIONAL', 'profesionales', 4, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-09 01:05:15'),
(51, 2, 'LOGOUT', 'usuarios', 2, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-09 01:06:09'),
(52, 3, 'LOGIN_SUCCESS', 'usuarios', 3, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-09 01:06:18'),
(53, NULL, 'CAMBIO_ESTADO_ACEPTADA', 'solicitudes_servicio', 6, '{\"estado_anterior\": \"PENDIENTE\"}', '{\"estado_nuevo\": \"ACEPTADA\", \"codigo\": \"GEO-6A774F44\"}', '127.0.0.1', NULL, '2026-10-09 01:07:25'),
(54, NULL, 'CAMBIO_ESTADO_EN_CAMINO', 'solicitudes_servicio', 6, '{\"estado_anterior\": \"ACEPTADA\"}', '{\"estado_nuevo\": \"EN_CAMINO\", \"codigo\": \"GEO-6A774F44\"}', '127.0.0.1', NULL, '2026-10-09 01:07:40'),
(55, NULL, 'CAMBIO_ESTADO_EN_PROCESO', 'solicitudes_servicio', 6, '{\"estado_anterior\": \"EN_CAMINO\"}', '{\"estado_nuevo\": \"EN_PROCESO\", \"codigo\": \"GEO-6A774F44\"}', '127.0.0.1', NULL, '2026-10-09 01:07:52'),
(56, 3, 'CAMBIO_ESTADO_EN_PROCESO', 'solicitudes_servicio', 6, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-09 01:07:52'),
(57, NULL, 'CAMBIO_ESTADO_FINALIZADA', 'solicitudes_servicio', 6, '{\"estado_anterior\": \"EN_PROCESO\"}', '{\"estado_nuevo\": \"FINALIZADA\", \"codigo\": \"GEO-6A774F44\"}', '127.0.0.1', NULL, '2026-10-09 01:08:04'),
(58, NULL, 'CAMBIO_ESTADO_ACEPTADA', 'solicitudes_servicio', 3, '{\"estado_anterior\": \"PENDIENTE\"}', '{\"estado_nuevo\": \"ACEPTADA\", \"codigo\": \"GEO-9D0A9358\"}', '127.0.0.1', NULL, '2026-10-09 01:59:24'),
(59, NULL, 'CAMBIO_ESTADO_EN_CAMINO', 'solicitudes_servicio', 3, '{\"estado_anterior\": \"ACEPTADA\"}', '{\"estado_nuevo\": \"EN_CAMINO\", \"codigo\": \"GEO-9D0A9358\"}', '127.0.0.1', NULL, '2026-10-09 01:59:41'),
(60, NULL, 'CAMBIO_ESTADO_EN_PROCESO', 'solicitudes_servicio', 3, '{\"estado_anterior\": \"EN_CAMINO\"}', '{\"estado_nuevo\": \"EN_PROCESO\", \"codigo\": \"GEO-9D0A9358\"}', '127.0.0.1', NULL, '2026-10-09 02:01:34'),
(61, NULL, 'CAMBIO_ESTADO_FINALIZADA', 'solicitudes_servicio', 3, '{\"estado_anterior\": \"EN_PROCESO\"}', '{\"estado_nuevo\": \"FINALIZADA\", \"codigo\": \"GEO-9D0A9358\"}', '127.0.0.1', NULL, '2026-10-09 02:01:46'),
(62, 3, 'COMPRA_TOKENS_APP', 'transacciones_suscripcion', 1, NULL, '{\"tokens\":20,\"monto\":20,\"comprobante\":\"GP-261011-9B90F142\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', '2026-10-10 22:48:41'),
(63, NULL, 'LOGIN_FAILED', 'usuarios', NULL, NULL, '{\"correo_intentado\":\"profecional@gmail.com\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-10 22:49:21'),
(64, 3, 'LOGIN_SUCCESS', 'usuarios', 3, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-10 22:49:32'),
(65, 3, 'COMPRA_PLAN_APP', 'transacciones_suscripcion', 1, NULL, '{\"id_plan\":2,\"monto\":30,\"comprobante\":\"GP-261011-CD623F29\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', '2026-10-10 22:50:14'),
(66, 3, 'LOGOUT', 'usuarios', 3, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-10 22:51:13'),
(67, 1, 'LOGIN_SUCCESS', 'usuarios', 1, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-10 22:51:23'),
(68, 3, 'COMPRA_PLAN_APP', 'transacciones_suscripcion', 1, NULL, '{\"id_plan\":3,\"monto\":69,\"comprobante\":\"GP-261011-2B944040\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', '2026-10-10 22:51:42'),
(69, NULL, 'CAMBIO_ESTADO_ACEPTADA', 'solicitudes_servicio', 7, '{\"estado_anterior\": \"PENDIENTE\"}', '{\"estado_nuevo\": \"ACEPTADA\", \"codigo\": \"GEO-630E66F8\"}', '127.0.0.1', NULL, '2026-10-10 22:53:35'),
(70, NULL, 'CAMBIO_ESTADO_EN_CAMINO', 'solicitudes_servicio', 7, '{\"estado_anterior\": \"ACEPTADA\"}', '{\"estado_nuevo\": \"EN_CAMINO\", \"codigo\": \"GEO-630E66F8\"}', '127.0.0.1', NULL, '2026-10-10 22:54:11'),
(71, NULL, 'CAMBIO_ESTADO_EN_PROCESO', 'solicitudes_servicio', 7, '{\"estado_anterior\": \"EN_CAMINO\"}', '{\"estado_nuevo\": \"EN_PROCESO\", \"codigo\": \"GEO-630E66F8\"}', '127.0.0.1', NULL, '2026-10-10 22:55:52'),
(72, NULL, 'CAMBIO_ESTADO_FINALIZADA', 'solicitudes_servicio', 7, '{\"estado_anterior\": \"EN_PROCESO\"}', '{\"estado_nuevo\": \"FINALIZADA\", \"codigo\": \"GEO-630E66F8\"}', '127.0.0.1', NULL, '2026-10-10 22:56:19'),
(73, 1, 'LOGOUT', 'usuarios', 1, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-11 05:47:48'),
(74, 3, 'LOGIN_SUCCESS', 'usuarios', 3, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-11 05:47:56'),
(75, NULL, 'CAMBIO_ESTADO_CANCELADA', 'solicitudes_servicio', 8, '{\"estado_anterior\": \"PENDIENTE\"}', '{\"estado_nuevo\": \"CANCELADA\", \"codigo\": \"GEO-B0F1D865\"}', '127.0.0.1', NULL, '2026-10-11 05:49:34'),
(76, NULL, 'CAMBIO_ESTADO_ACEPTADA', 'solicitudes_servicio', 9, '{\"estado_anterior\": \"PENDIENTE\"}', '{\"estado_nuevo\": \"ACEPTADA\", \"codigo\": \"GEO-B3889855\"}', '127.0.0.1', NULL, '2026-10-11 05:50:43'),
(77, NULL, 'CAMBIO_ESTADO_EN_CAMINO', 'solicitudes_servicio', 9, '{\"estado_anterior\": \"ACEPTADA\"}', '{\"estado_nuevo\": \"EN_CAMINO\", \"codigo\": \"GEO-B3889855\"}', '127.0.0.1', NULL, '2026-10-11 05:50:48'),
(78, NULL, 'CAMBIO_ESTADO_EN_PROCESO', 'solicitudes_servicio', 9, '{\"estado_anterior\": \"EN_CAMINO\"}', '{\"estado_nuevo\": \"EN_PROCESO\", \"codigo\": \"GEO-B3889855\"}', '127.0.0.1', NULL, '2026-10-11 05:51:30'),
(79, 3, 'CAMBIO_ESTADO_EN_PROCESO', 'solicitudes_servicio', 9, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-11 05:51:31'),
(80, NULL, 'CAMBIO_ESTADO_FINALIZADA', 'solicitudes_servicio', 9, '{\"estado_anterior\": \"EN_PROCESO\"}', '{\"estado_nuevo\": \"FINALIZADA\", \"codigo\": \"GEO-B3889855\"}', '127.0.0.1', NULL, '2026-10-11 05:51:43'),
(81, NULL, 'LOGIN_FAILED', 'usuarios', NULL, NULL, '{\"correo_intentado\":\"profecional@gmail.com\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-12 00:51:53'),
(82, 3, 'LOGIN_SUCCESS', 'usuarios', 3, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-12 00:52:03'),
(83, NULL, 'CAMBIO_ESTADO_CANCELADA', 'solicitudes_servicio', 4, '{\"estado_anterior\": \"PENDIENTE\"}', '{\"estado_nuevo\": \"CANCELADA\", \"codigo\": \"GEO-D93E2E6B\"}', '127.0.0.1', NULL, '2026-10-12 00:52:40');

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
(1, 6, 3, 4, 3, NULL, '2026-10-09 02:00:10'),
(2, 5, 3, 5, 2, NULL, '2026-10-09 02:00:18'),
(3, 1, 5, 5, 5, NULL, '2026-10-09 02:00:23'),
(4, 3, 4, 5, 4, 'bueno', '2026-10-09 02:02:02'),
(5, 7, 3, 3, 3, NULL, '2026-10-11 05:47:02'),
(6, 9, 3, 5, 5, NULL, '2026-10-11 05:51:54');

--
-- Disparadores `calificaciones`
--
DELIMITER $$
CREATE TRIGGER `trg_notif_calificacion` AFTER INSERT ON `calificaciones` FOR EACH ROW BEGIN
    DECLARE v_user_prof INT DEFAULT NULL;

    SELECT p.id_usuario INTO v_user_prof
      FROM solicitudes_servicio s
      INNER JOIN profesionales p ON p.id_profesional = s.id_profesional
     WHERE s.id_solicitud = NEW.id_solicitud
     LIMIT 1;

    IF v_user_prof IS NOT NULL THEN
        INSERT INTO notificaciones (id_usuario, tipo, mensaje, url_destino)
        VALUES (v_user_prof, 'NUEVA_CALIFICACION',
                CONCAT('Recibiste una calificación de ', NEW.puntuacion_general, ' estrella(s).'),
                'profesional/historial');
    END IF;
END
$$
DELIMITER ;

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
(1, 'Electricidad Residencial', 'electricidad', 'Instalaciones, cableado, cortos y tableros eléctricos.', 'TECNICO', 'fa-bolt', 1, '2026-10-02 15:17:06'),
(2, 'Electrónica y Electrodomésticos', 'electronica', 'Reparación de TVs, refrigeradores y microondas.', 'TECNICO', 'fa-tv', 1, '2026-10-02 15:17:06'),
(3, 'Plomería y Gasfitería', 'plomeria', 'Fugas de agua, desagües e instalaciones sanitarias.', 'AMBOS', 'fa-faucet-drip', 1, '2026-10-02 15:17:06'),
(4, 'Cuidado del Hogar y Niñeras', 'cuidado-nineras', 'Atención de niños, personas mayores y apoyo doméstico.', 'EMPIRICO_OFICIO', 'fa-baby-carriage', 1, '2026-10-02 15:17:06'),
(5, 'Albañilería y Obras Menores', 'albanileria', 'Construcción, refacciones de muros y cerámica.', 'EMPIRICO_OFICIO', 'fa-trowel-bricks', 1, '2026-10-02 15:17:06'),
(6, 'pintura de casas', 'pintura-de-casas', NULL, 'AMBOS', 'fa-solid fa-paintbrush', 1, '2026-10-02 16:09:19'),
(7, 'mecanica', 'mecanica', NULL, 'AMBOS', 'fa-solid fa-wrench', 1, '2026-10-09 01:00:46');

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
(1, 1, 'Av. mariscal santa cruz,', 'La Paz', -16.50705500, -68.21228700),
(2, 3, 'rio seco', 'La Paz', -16.50691800, -68.21504900);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes_favoritos`
--

CREATE TABLE `clientes_favoritos` (
  `id_favorito` int(11) NOT NULL,
  `id_cliente` int(11) NOT NULL,
  `id_profesional` int(11) NOT NULL,
  `fecha_agregado` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `clientes_favoritos`
--

INSERT INTO `clientes_favoritos` (`id_favorito`, `id_cliente`, `id_profesional`, `fecha_agregado`) VALUES
(1, 1, 1, '2026-10-12 00:52:58');

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
(1, 1, 'TITULO_TECNICO', 'uploads/documentos/DOC_1_c447e956d3662fda.jpeg', 'APROBADO', NULL, NULL, '2026-10-02 17:26:34', '2026-10-02 18:47:54'),
(2, 1, 'CI_ANVERSO', 'uploads/documentos/DOC_1_a4ee8f1aac102a4f.jpg', 'APROBADO', NULL, NULL, '2026-10-02 17:26:34', '2026-10-02 18:47:54'),
(3, 1, 'CI_REVERSO', 'uploads/documentos/DOC_1_f72ced2ee89c5a92.jpg', 'APROBADO', NULL, NULL, '2026-10-02 17:26:34', '2026-10-02 18:47:54'),
(4, 3, 'CI_ANVERSO', 'uploads/documentos/DOC_3_58eb85082f535b60.jpg', 'APROBADO', NULL, NULL, '2026-10-08 21:35:15', '2026-10-09 01:01:45'),
(5, 3, 'CI_REVERSO', 'uploads/documentos/DOC_3_5ca2ea2b074cdc2e.webp', 'APROBADO', NULL, NULL, '2026-10-08 21:35:15', '2026-10-09 01:01:45'),
(6, 4, 'CI_ANVERSO', 'uploads/documentos/DOC_4_678e0a501ae3f68f.jpg', 'RECHAZADO', NULL, NULL, '2026-10-09 01:04:51', '2026-10-09 01:05:15'),
(7, 4, 'CI_REVERSO', 'uploads/documentos/DOC_4_666a6d7d30099f01.webp', 'RECHAZADO', NULL, NULL, '2026-10-09 01:04:51', '2026-10-09 01:05:15'),
(8, 4, 'TITULO_TECNICO', 'uploads/documentos/DOC_4_7198431c8d2c3555.webp', 'RECHAZADO', NULL, NULL, '2026-10-09 01:04:51', '2026-10-09 01:05:15');

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
(1, 7, 3, 'TEXTO', 'hola', NULL, 0, '2026-10-10 22:53:47'),
(2, 7, 3, 'TEXTO', 'geo:-16.506777,-68.215470', NULL, 0, '2026-10-10 22:53:51'),
(3, 9, 1, 'IMAGEN', NULL, 'uploads/chat/CHAT_9_8f4d0ab9c6fa28bc.webp', 0, '2026-10-11 05:51:13');

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
(1, 2, 'NUEVO_PROFESIONAL', 'Nuevo profesional pendiente de validar: Jose App Mamani Machaca.', 'admin/profesionales', 0, '2026-10-08 21:35:15'),
(2, 3, 'NUEVA_SOLICITUD', 'Nueva solicitud de servicio en tu zona (GEO-6AA74A59).', 'profesional/solicitudes', 1, '2026-10-08 21:38:15'),
(3, 2, 'NUEVO_PROFESIONAL', 'Nuevo profesional pendiente de validar: Freddy App.', 'admin/profesionales', 0, '2026-10-09 01:04:51'),
(4, 3, 'NUEVA_SOLICITUD', 'Nueva solicitud de servicio en tu zona (GEO-6A774F44).', 'profesional/solicitudes', 1, '2026-10-09 01:07:12'),
(5, 1, 'SOLICITUD_ACEPTADA', 'Un profesional aceptó tu solicitud GEO-9D0A9358.', 'cliente/solicitud/3', 1, '2026-10-09 01:59:24'),
(6, 1, 'PROFESIONAL_EN_CAMINO', 'Tu profesional va en camino. Sigue su ubicación en el mapa.', 'cliente/solicitud/3', 1, '2026-10-09 01:59:41'),
(7, 3, 'NUEVA_CALIFICACION', 'Recibiste una calificación de 3 estrella(s).', 'profesional/historial', 1, '2026-10-09 02:00:10'),
(8, 3, 'NUEVA_CALIFICACION', 'Recibiste una calificación de 3 estrella(s).', 'profesional/historial', 1, '2026-10-09 02:00:18'),
(9, 3, 'NUEVA_CALIFICACION', 'Recibiste una calificación de 5 estrella(s).', 'profesional/historial', 1, '2026-10-09 02:00:23'),
(10, 1, 'SERVICIO_EN_PROCESO', 'Tu profesional llegó y comenzó el servicio.', 'cliente/solicitud/3', 1, '2026-10-09 02:01:34'),
(11, 1, 'SERVICIO_FINALIZADO', 'Tu servicio finalizó. Califica a tu profesional.', 'cliente/solicitud/3', 1, '2026-10-09 02:01:46'),
(12, 3, 'NUEVA_CALIFICACION', 'Recibiste una calificación de 4 estrella(s).', 'profesional/historial', 1, '2026-10-09 02:02:02'),
(13, 3, 'PAGO_CONFIRMADO', 'Compra confirmada: +20 tokens (comprobante GP-261011-9B90F142).', 'profesional/comprar_tokens', 0, '2026-10-10 22:48:41'),
(14, 3, 'PAGO_CONFIRMADO', 'Plan activado hasta el 2026-12-07 (comprobante GP-261011-CD623F29).', 'profesional/comprar_tokens', 0, '2026-10-10 22:50:14'),
(15, 3, 'PAGO_CONFIRMADO', 'Plan activado hasta el 2027-01-06 (comprobante GP-261011-2B944040).', 'profesional/comprar_tokens', 1, '2026-10-10 22:51:42'),
(16, 3, 'NUEVA_SOLICITUD', 'Un cliente te envió una solicitud (GEO-630E66F8).', 'profesional/solicitudes?id=7', 1, '2026-10-10 22:53:21'),
(17, 1, 'SOLICITUD_ACEPTADA', 'Un profesional aceptó tu solicitud GEO-630E66F8.', 'cliente/solicitud/7', 1, '2026-10-10 22:53:35'),
(18, 1, 'NUEVO_MENSAJE', 'Tienes mensajes nuevos de juan.', 'chat/7', 1, '2026-10-10 22:53:47'),
(19, 1, 'PROFESIONAL_EN_CAMINO', 'Tu profesional va en camino. Sigue su ubicación en el mapa.', 'cliente/solicitud/7', 1, '2026-10-10 22:54:11'),
(20, 1, 'SERVICIO_EN_PROCESO', 'Tu profesional llegó y comenzó el servicio.', 'cliente/solicitud/7', 1, '2026-10-10 22:55:52'),
(21, 1, 'SERVICIO_FINALIZADO', 'Tu servicio finalizó. Califica a tu profesional.', 'cliente/solicitud/7', 1, '2026-10-10 22:56:19'),
(22, 3, 'NUEVA_CALIFICACION', 'Recibiste una calificación de 3 estrella(s).', 'profesional/historial', 0, '2026-10-11 05:47:02'),
(23, 3, 'NUEVA_SOLICITUD', 'Nueva solicitud de servicio en tu zona (GEO-B0F1D865).', 'profesional/solicitudes?id=8', 1, '2026-10-11 05:48:35'),
(24, 3, 'NUEVA_SOLICITUD', 'Un cliente te envió una solicitud (GEO-B3889855).', 'profesional/solicitudes?id=9', 1, '2026-10-11 05:50:35'),
(25, 1, 'SOLICITUD_ACEPTADA', 'Un profesional aceptó tu solicitud GEO-B3889855.', 'cliente/solicitud/9', 0, '2026-10-11 05:50:43'),
(26, 1, 'PROFESIONAL_EN_CAMINO', 'Tu profesional va en camino. Sigue su ubicación en el mapa.', 'cliente/solicitud/9', 0, '2026-10-11 05:50:48'),
(27, 3, 'NUEVO_MENSAJE', 'Tienes mensajes nuevos de jose.', 'chat/9', 0, '2026-10-11 05:51:13'),
(28, 1, 'SERVICIO_EN_PROCESO', 'Tu profesional llegó y comenzó el servicio.', 'cliente/solicitud/9', 0, '2026-10-11 05:51:30'),
(29, 1, 'SERVICIO_FINALIZADO', 'Tu servicio finalizó. Califica a tu profesional.', 'cliente/solicitud/9', 0, '2026-10-11 05:51:43'),
(30, 3, 'NUEVA_CALIFICACION', 'Recibiste una calificación de 3 estrella(s).', 'profesional/historial', 0, '2026-10-11 05:51:54');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `planes_suscripcion`
--

CREATE TABLE `planes_suscripcion` (
  `id_plan` int(11) NOT NULL,
  `nombre_plan` varchar(50) NOT NULL,
  `precio` decimal(10,2) NOT NULL DEFAULT 0.00,
  `tokens_otorgados` int(11) NOT NULL DEFAULT 5,
  `posicionamiento_destacado` tinyint(1) NOT NULL DEFAULT 0,
  `descripcion` text DEFAULT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `planes_suscripcion`
--

INSERT INTO `planes_suscripcion` (`id_plan`, `nombre_plan`, `precio`, `tokens_otorgados`, `posicionamiento_destacado`, `descripcion`, `estado`) VALUES
(1, 'GRATUITO_TOKENS', 0.00, 5, 0, 'Asignación mensual de 5 tokens gratuitos. Pago por recarga si se agotan.', 1),
(2, 'BASICO_MENSUAL', 30.00, 50, 0, '40 tokens mensuales, insignia de verificación y posicionamiento estándar en La Paz.', 1),
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
(1, 3, 2, 3, 'TECNICO_PROFESIONAL', 'CI', '12576550', 5, 'soy un especialista en elctronica y electrodomesticos con experieencia de mas de 5 años', 'SOPOCACHI', 'rio seco', 70, '2027-01-06', 0.00, 'APROBADO', 'DISPONIBLE', -16.50653769, -68.21566848, '2026-10-11 05:51:29', '2026-10-02 17:26:34'),
(2, 10, 6, 1, 'OFICIO_EMPIRICO', 'CI', '125765550', 4, 'pinto casas', 'CENTRO', 'centro', 5, NULL, 0.00, 'APROBADO', 'NO_DISPONIBLE', NULL, NULL, '2026-10-05 14:41:43', '2026-10-05 14:41:16'),
(3, 11, 3, 1, 'OFICIO_EMPIRICO', 'CI', '12345678', 5, 'instalo plomeria y gasfiteria', 'EL_ALTO', 'centro', 5, NULL, 50.00, 'APROBADO', 'NO_DISPONIBLE', NULL, NULL, '2026-10-09 01:01:45', '2026-10-08 21:35:15'),
(4, 12, 7, 1, 'TECNICO_PROFESIONAL', 'CI', '12576555', 5, 'soy mecanico empirico y tengo licenciatura', 'EL_ALTO', 'rio seco', 5, NULL, 0.00, 'RECHAZADO', 'NO_DISPONIBLE', NULL, NULL, '2026-10-09 01:05:15', '2026-10-09 01:04:51');

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
(1, 'SUPER_ADMIN', 'Acceso total y configuración de auditoría', 1, '2026-10-02 15:17:06'),
(2, 'ADMIN', 'Gestión operativa, validación documental y reportes', 1, '2026-10-02 15:17:06'),
(3, 'PROFESIONAL', 'Técnicos calificados y trabajadores de oficios', 1, '2026-10-02 15:17:06'),
(4, 'CLIENTE', 'Usuarios demandantes de servicios', 1, '2026-10-02 15:17:06');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `solicitudes_servicio`
--

CREATE TABLE `solicitudes_servicio` (
  `id_solicitud` int(11) NOT NULL,
  `codigo_seguimiento` varchar(20) NOT NULL,
  `id_cliente` int(11) NOT NULL,
  `id_categoria` int(11) DEFAULT NULL,
  `id_profesional` int(11) DEFAULT NULL,
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

INSERT INTO `solicitudes_servicio` (`id_solicitud`, `codigo_seguimiento`, `id_cliente`, `id_categoria`, `id_profesional`, `descripcion_problema`, `direccion_servicio`, `macrodistrito`, `zona`, `latitud_destino`, `longitud_destino`, `estado_servicio`, `precio_acordado`, `tiempo_estimado_llegada_min`, `motivo_cancelacion`, `fecha_solicitud`, `fecha_inicio_atencion`, `fecha_finalizacion`) VALUES
(1, 'GEO-E7ECBD9C', 1, NULL, 1, 'se frego mi luz', 'Av. mariscal santa cruz,', 'CENTRO', 'La Paz', -16.50000000, -68.15000000, 'FINALIZADA', NULL, 30, NULL, '2026-10-08 10:35:21', '2026-10-08 11:53:14', NULL),
(2, 'GEO-89546DB3', 1, 5, NULL, 'se quemo mi luz||', 'trio seco', 'EL_ALTO', '25 de julio', -16.50705841, -68.21526480, 'PENDIENTE', NULL, NULL, NULL, '2026-10-08 11:51:39', NULL, NULL),
(3, 'GEO-9D0A9358', 1, 2, 1, 'se quemo mi luz', 'rio seco', 'PERIFERICA', '25 de julio', -16.50681937, -68.21552963, 'FINALIZADA', 30.00, 15, NULL, '2026-10-08 11:53:00', '2026-10-09 02:01:34', '2026-10-09 02:01:46'),
(4, 'GEO-D93E2E6B', 1, 2, NULL, 'se quemo luz', 'calle rio seco', 'EL_ALTO', '25 de julio', -16.50680071, -68.21545412, 'CANCELADA', NULL, NULL, 'Cancelada por el cliente', '2026-10-08 16:24:22', NULL, '2026-10-12 00:52:40'),
(5, 'GEO-6AA74A59', 1, 2, 1, 'ayuda aaaaaaaaaaaaaaa puerba 2 de app', 'rio seco cerca al multicine', 'EL_ALTO', 'en casa', -16.50688300, -68.21546900, 'FINALIZADA', NULL, 15, NULL, '2026-10-08 21:38:15', '2026-10-08 21:43:17', NULL),
(6, 'GEO-6A774F44', 1, 2, 1, 'se que mi luz', 'rio seco', 'EL_ALTO', 'rio seco', -16.50950191, -68.15234843, 'FINALIZADA', NULL, 30, NULL, '2026-10-09 01:07:12', '2026-10-09 01:07:52', NULL),
(7, 'GEO-630E66F8', 1, NULL, 1, '[ASIGNACIÓN I.A. CASCADA] - nesesito un especialista', 'Av. mariscal santa cruz,', 'CENTRO', 'La Paz', -16.50000000, -68.15000000, 'FINALIZADA', 10.00, 30, NULL, '2026-10-10 22:53:21', '2026-10-10 22:55:52', '2026-10-10 22:56:19'),
(8, 'GEO-B0F1D865', 1, 2, NULL, 'holaaaaaaaaaa', 'asasasas', 'COTAHUMA', 'asdasda', -16.50688300, -68.21546900, 'CANCELADA', NULL, NULL, 'Cancelada por el cliente', '2026-10-11 05:48:35', NULL, '2026-10-11 05:49:34'),
(9, 'GEO-B3889855', 1, 2, 1, 'ssasdasdas', 'asdasdasd', 'CENTRO', 'asdasdas', -16.50688300, -68.21546900, 'FINALIZADA', NULL, 12, NULL, '2026-10-11 05:50:35', '2026-10-11 05:51:30', NULL);

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
CREATE TRIGGER `trg_notif_estado_solicitud` AFTER UPDATE ON `solicitudes_servicio` FOR EACH ROW BEGIN
    DECLARE v_user_cliente INT DEFAULT NULL;
    DECLARE v_user_prof INT DEFAULT NULL;
    DECLARE v_url VARCHAR(255);

    IF OLD.estado_servicio <> NEW.estado_servicio THEN

        SELECT id_usuario INTO v_user_cliente
          FROM clientes WHERE id_cliente = NEW.id_cliente LIMIT 1;
        SET v_url = CONCAT('cliente/solicitud/', NEW.id_solicitud);

        IF v_user_cliente IS NOT NULL THEN
            IF NEW.estado_servicio = 'ACEPTADA' THEN
                INSERT INTO notificaciones (id_usuario, tipo, mensaje, url_destino)
                VALUES (v_user_cliente, 'SOLICITUD_ACEPTADA',
                        CONCAT('Un profesional aceptó tu solicitud ', NEW.codigo_seguimiento, '.'), v_url);
            ELSEIF NEW.estado_servicio = 'EN_CAMINO' THEN
                INSERT INTO notificaciones (id_usuario, tipo, mensaje, url_destino)
                VALUES (v_user_cliente, 'PROFESIONAL_EN_CAMINO',
                        'Tu profesional va en camino. Sigue su ubicación en el mapa.', v_url);
            ELSEIF NEW.estado_servicio = 'EN_PROCESO' THEN
                INSERT INTO notificaciones (id_usuario, tipo, mensaje, url_destino)
                VALUES (v_user_cliente, 'SERVICIO_EN_PROCESO',
                        'Tu profesional llegó y comenzó el servicio.', v_url);
            ELSEIF NEW.estado_servicio = 'FINALIZADA' THEN
                INSERT INTO notificaciones (id_usuario, tipo, mensaje, url_destino)
                VALUES (v_user_cliente, 'SERVICIO_FINALIZADO',
                        'Tu servicio finalizó. Califica a tu profesional.', v_url);
            END IF;
        END IF;

        -- Cancelaciones: se avisa a quien corresponde según quién canceló
        IF NEW.estado_servicio = 'CANCELADA' THEN
            IF NEW.motivo_cancelacion LIKE 'Rechazada por el profesional%' THEN
                IF v_user_cliente IS NOT NULL THEN
                    INSERT INTO notificaciones (id_usuario, tipo, mensaje, url_destino)
                    VALUES (v_user_cliente, 'SOLICITUD_RECHAZADA',
                            CONCAT('El profesional no pudo atender tu solicitud ', NEW.codigo_seguimiento,
                                   '. Puedes elegir otro o enviarla a toda la red.'), v_url);
                END IF;
            ELSEIF NEW.motivo_cancelacion LIKE 'Cancelada por el profesional%' THEN
                IF v_user_cliente IS NOT NULL THEN
                    INSERT INTO notificaciones (id_usuario, tipo, mensaje, url_destino)
                    VALUES (v_user_cliente, 'TRABAJO_CANCELADO',
                            CONCAT('Tu profesional canceló el servicio ', NEW.codigo_seguimiento,
                                   '. Puedes pedir uno nuevo.'), v_url);
                END IF;
            ELSEIF NEW.id_profesional IS NOT NULL THEN
                SELECT id_usuario INTO v_user_prof
                  FROM profesionales WHERE id_profesional = NEW.id_profesional LIMIT 1;
                IF v_user_prof IS NOT NULL THEN
                    INSERT INTO notificaciones (id_usuario, tipo, mensaje, url_destino)
                    VALUES (v_user_prof, 'SOLICITUD_CANCELADA',
                            CONCAT('El cliente canceló la solicitud ', NEW.codigo_seguimiento, '.'),
                            'profesional/historial');
                END IF;
            END IF;
        END IF;

        -- Ya fue tomada o cancelada: las alertas "nueva solicitud" quedan leídas para todos
        IF NEW.estado_servicio IN ('ACEPTADA', 'CANCELADA') THEN
            UPDATE notificaciones
               SET leida = 1
             WHERE tipo = 'NUEVA_SOLICITUD'
               AND url_destino = CONCAT('profesional/solicitudes?id=', NEW.id_solicitud);
        END IF;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_notif_nueva_solicitud` AFTER INSERT ON `solicitudes_servicio` FOR EACH ROW BEGIN
    DECLARE v_user_cliente INT DEFAULT NULL;
    DECLARE v_user_prof INT DEFAULT NULL;

    SELECT id_usuario INTO v_user_cliente
      FROM clientes WHERE id_cliente = NEW.id_cliente LIMIT 1;

    IF NEW.id_profesional IS NOT NULL THEN
        -- Solicitud directa a un profesional elegido
        SELECT id_usuario INTO v_user_prof
          FROM profesionales WHERE id_profesional = NEW.id_profesional LIMIT 1;

        IF v_user_prof IS NOT NULL THEN
            INSERT INTO notificaciones (id_usuario, tipo, mensaje, url_destino)
            VALUES (v_user_prof, 'NUEVA_SOLICITUD',
                    CONCAT('Un cliente te envió una solicitud (', NEW.codigo_seguimiento, ').'),
                    CONCAT('profesional/solicitudes?id=', NEW.id_solicitud));
        END IF;

    ELSEIF NEW.id_categoria IS NOT NULL THEN
        -- Solicitud abierta: técnicos disponibles del rubro,
        -- del mismo macrodistrito O a menos de 10 km del punto del servicio
        INSERT INTO notificaciones (id_usuario, tipo, mensaje, url_destino)
        SELECT p.id_usuario, 'NUEVA_SOLICITUD',
               CONCAT('Nueva solicitud de servicio en tu zona (', NEW.codigo_seguimiento, ').'),
               CONCAT('profesional/solicitudes?id=', NEW.id_solicitud)
          FROM profesionales p
          INNER JOIN usuarios u ON u.id_usuario = p.id_usuario
         WHERE p.id_categoria = NEW.id_categoria
           AND p.estado_validacion = 'APROBADO'
           AND p.estado_disponibilidad = 'DISPONIBLE'
           AND u.estado = 'ACTIVO'
           AND (v_user_cliente IS NULL OR p.id_usuario <> v_user_cliente)
           AND (
                p.macrodistrito_base = NEW.macrodistrito
                OR (
                    p.latitud_actual IS NOT NULL
                    AND p.longitud_actual IS NOT NULL
                    AND (6371 * ACOS(LEAST(1,
                          COS(RADIANS(NEW.latitud_destino)) * COS(RADIANS(p.latitud_actual))
                          * COS(RADIANS(p.longitud_actual) - RADIANS(NEW.longitud_destino))
                          + SIN(RADIANS(NEW.latitud_destino)) * SIN(RADIANS(p.latitud_actual))
                        ))) <= 10
                )
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
(1, 3, 1, -16.50957891, -68.15285918, 0.00, '2026-10-09 01:59:44'),
(2, 3, 1, -16.50957891, -68.15285918, 0.00, '2026-10-09 01:59:47'),
(3, 3, 1, -16.50957891, -68.15285918, 0.00, '2026-10-09 01:59:52'),
(4, 3, 1, -16.50969230, -68.15254776, 0.00, '2026-10-09 01:59:59'),
(5, 3, 1, -16.50969230, -68.15254776, 0.00, '2026-10-09 02:00:02'),
(6, 3, 1, -16.50969230, -68.15254776, 0.00, '2026-10-09 02:00:07'),
(7, 3, 1, -16.50936486, -68.15264737, 0.00, '2026-10-09 02:00:14'),
(8, 3, 1, -16.50936486, -68.15264737, 0.00, '2026-10-09 02:00:17'),
(9, 3, 1, -16.50936486, -68.15264737, 0.00, '2026-10-09 02:00:22'),
(10, 3, 1, -16.52000000, -68.14000000, 0.00, '2026-10-09 02:00:27'),
(11, 3, 1, -16.50958349, -68.15268256, 0.00, '2026-10-09 02:00:34'),
(12, 3, 1, -16.50958349, -68.15268256, 0.00, '2026-10-09 02:00:37'),
(13, 3, 1, -16.50958349, -68.15268256, 0.00, '2026-10-09 02:00:42'),
(14, 3, 1, -16.50941043, -68.15275598, 0.00, '2026-10-09 02:00:49'),
(15, 3, 1, -16.50941043, -68.15275598, 0.00, '2026-10-09 02:00:51'),
(16, 3, 1, -16.50941043, -68.15275598, 0.00, '2026-10-09 02:00:57'),
(17, 3, 1, -16.50957950, -68.15228320, 0.00, '2026-10-09 02:01:04'),
(18, 3, 1, -16.50957950, -68.15228320, 0.00, '2026-10-09 02:01:07'),
(19, 3, 1, -16.50957950, -68.15228320, 0.00, '2026-10-09 02:01:12'),
(20, 3, 1, -16.50939600, -68.15276892, 0.00, '2026-10-09 02:01:19'),
(21, 3, 1, -16.50939600, -68.15276892, 0.00, '2026-10-09 02:01:21'),
(22, 3, 1, -16.50939600, -68.15276892, 0.00, '2026-10-09 02:01:27'),
(23, 3, 1, -16.50935154, -68.15267817, 0.00, '2026-10-09 02:01:34'),
(24, 7, 1, -16.50686897, -68.21562307, 0.00, '2026-10-10 22:54:12'),
(25, 7, 1, -16.50686897, -68.21562307, 0.00, '2026-10-10 22:54:17'),
(26, 7, 1, -16.50686897, -68.21562307, 0.00, '2026-10-10 22:54:22'),
(27, 7, 1, -16.50668518, -68.21553643, 0.00, '2026-10-10 22:54:29'),
(28, 7, 1, -16.50668518, -68.21553643, 0.00, '2026-10-10 22:54:32'),
(29, 7, 1, -16.50668518, -68.21553643, 0.00, '2026-10-10 22:54:37'),
(30, 7, 1, -16.50655970, -68.21560771, 0.00, '2026-10-10 22:54:44'),
(31, 7, 1, -16.50655970, -68.21560771, 0.00, '2026-10-10 22:54:47'),
(32, 7, 1, -16.50655970, -68.21560771, 0.00, '2026-10-10 22:54:51'),
(33, 7, 1, -16.50656490, -68.21562415, 0.00, '2026-10-10 22:54:59'),
(34, 7, 1, -16.50656490, -68.21562415, 0.00, '2026-10-10 22:55:02'),
(35, 7, 1, -16.50656490, -68.21562415, 0.00, '2026-10-10 22:55:07'),
(36, 7, 1, -16.50661293, -68.21559165, 0.00, '2026-10-10 22:55:14'),
(37, 7, 1, -16.50661293, -68.21559165, 0.00, '2026-10-10 22:55:17'),
(38, 7, 1, -16.50661293, -68.21559165, 0.00, '2026-10-10 22:55:21'),
(39, 7, 1, -16.50688825, -68.21540602, 0.00, '2026-10-10 22:55:28'),
(40, 7, 1, -16.50688825, -68.21540602, 0.00, '2026-10-10 22:55:32'),
(41, 7, 1, -16.50686897, -68.21562307, 0.00, '2026-10-10 22:55:37'),
(42, 7, 1, -16.50686897, -68.21562307, 0.00, '2026-10-10 22:55:42'),
(43, 7, 1, -16.50683593, -68.21540237, 0.00, '2026-10-10 22:55:48'),
(44, 7, 1, -16.50683593, -68.21540237, 0.00, '2026-10-10 22:55:52');

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
(1, 1, NULL, 'PAQUETE_TOKENS', 20.00, 'QR_SIMPLE_BOLIVIA', 'GP-261011-9B90F142', 'CONFIRMADO', '2026-10-10 22:48:41'),
(2, 1, 2, 'MEMBRESIA_MENSUAL', 30.00, 'QR_SIMPLE_BOLIVIA', 'GP-261011-CD623F29', 'CONFIRMADO', '2026-10-10 22:50:14'),
(3, 1, 3, 'MEMBRESIA_MENSUAL', 69.00, 'QR_SIMPLE_BOLIVIA', 'GP-261011-2B944040', 'CONFIRMADO', '2026-10-10 22:51:42');

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
  `api_token` varchar(255) DEFAULT NULL,
  `api_token_expira` datetime DEFAULT NULL,
  `foto_perfil` varchar(255) DEFAULT NULL,
  `estado` enum('ACTIVO','INACTIVO','BLOQUEADO') NOT NULL DEFAULT 'ACTIVO',
  `token_recuperacion` varchar(255) DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `id_rol`, `nombre`, `apellido`, `correo`, `celular`, `password`, `api_token`, `api_token_expira`, `foto_perfil`, `estado`, `token_recuperacion`, `fecha_registro`) VALUES
(1, 4, 'jose', 'mamani machaca', 'cliente@gmail.com', '70123456', '$2y$10$a0Ffr0YDDzB3S0MAnbFQxef8ElcY0Wgi0nIt0YYvWxq3wilBQube2', '2a588ffb598f7fd82ec1a9279166e4a2e3f0e95d6b628587c9c59e9c6f9c48e0', '2026-11-10 20:52:33', NULL, 'ACTIVO', NULL, '2026-10-02 15:45:01'),
(2, 1, 'Admin', 'Geopro', 'admin@geopro.com', '70000000', '$2y$10$Nk4LKJ4dtTxMUV5/8cwz9OtfL/8nSqoH32Lo/myqPg/FKu.OaFhLG', 'bf733fbc831156f731649cda00d6467e0e68ecbe3d700afce829135bc168c553', '2026-11-10 07:53:35', NULL, 'ACTIVO', NULL, '2026-10-02 16:04:55'),
(3, 3, 'juan', 'quispe poma', 'profecional@gmail.com', '70000001', '$2y$10$NNrhyVcAlVzxiln.E6N7SuRmKNSAfe/P8YCguumCiJg9rqXHvBv2G', '0197b410bc8b34d8eb4a29075936c573b0df61e3e27e5a777f82e62786c503ff', '2026-11-10 07:53:35', NULL, 'ACTIVO', NULL, '2026-10-02 17:26:34'),
(6, 3, 'juan jose', 'mamani machaca', 'profesional@gmail.com', '', '$2y$10$D.Pf6Hb/FxObv5a/eBh2uuAGpm5.0eCoha1M0Qjr4txZcMEwHiOEG', '3181ead40fa36c4e5f7ea9d573f1698661576712b21ab137377450db2f9fad0b', '2026-11-10 07:53:35', NULL, 'ACTIVO', NULL, '2026-10-05 05:23:45'),
(10, 3, 'juan jose', 'amani machaca', 'profesional1@gail.com', '67073166', '$2y$10$aixOW647UYMP3BL9mFXgg.R6d0DubT0rLiHazoGWqTWecqyEAa826', NULL, NULL, NULL, 'ACTIVO', NULL, '2026-10-05 14:41:16'),
(11, 3, 'Jose App', 'Mamani Machaca', 'cliente1@gmail.com', '67073167', '$2y$10$RBdn3tTRP4/45uV5RmQrFeMK/j/0TrjrmCiFJsalt61BNoIqbtqby', '3ae5c856eae2d869dffa0e244303232150f1d9af5766f5d803b3e376424917d4', '2026-11-10 07:53:35', NULL, 'ACTIVO', NULL, '2026-10-08 21:35:15'),
(12, 3, 'Freddy', 'App', 'profesional2@gmail.com', '62552865', '$2y$10$PhxZ28rKQcurC2pts0ijbuoeGSGvgpSTjyHt6WT4Pc6RCKtLWM3Ty', NULL, NULL, NULL, 'ACTIVO', NULL, '2026-10-09 01:04:51');

--
-- Disparadores `usuarios`
--
DELIMITER $$
CREATE TRIGGER `trg_usuarios_token_expira` BEFORE UPDATE ON `usuarios` FOR EACH ROW BEGIN
    IF NOT (NEW.api_token <=> OLD.api_token) THEN
        IF NEW.api_token IS NULL THEN
            SET NEW.api_token_expira = NULL;
        ELSE
            SET NEW.api_token_expira = DATE_ADD(NOW(), INTERVAL 30 DAY);
        END IF;
    END IF;
END
$$
DELIMITER ;

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

CREATE ALGORITHM=UNDEFINED  SQL SECURITY DEFINER VIEW `vw_auditoria_detallada`  AS SELECT `a`.`id_log` AS `id_log`, `a`.`fecha_evento` AS `fecha_evento`, coalesce(concat(`u`.`nombre`,' ',`u`.`apellido`),'SISTEMA_AUTO') AS `responsable`, `r`.`nombre_rol` AS `nombre_rol`, `a`.`accion` AS `accion`, `a`.`tabla_afectada` AS `tabla_afectada`, `a`.`registro_id` AS `registro_id`, `a`.`valores_anteriores` AS `valores_anteriores`, `a`.`valores_nuevos` AS `valores_nuevos`, `a`.`ip_origen` AS `ip_origen` FROM ((`auditoria_logs` `a` left join `usuarios` `u` on(`a`.`id_usuario` = `u`.`id_usuario`)) left join `roles` `r` on(`u`.`id_rol` = `r`.`id_rol`)) ORDER BY `a`.`fecha_evento` DESC ;

-- --------------------------------------------------------

--
-- Estructura para la vista `vw_metricas_profesionales`
--
DROP TABLE IF EXISTS `vw_metricas_profesionales`;

CREATE ALGORITHM=UNDEFINED  SQL SECURITY DEFINER VIEW `vw_metricas_profesionales`  AS SELECT `p`.`id_profesional` AS `id_profesional`, concat(`u`.`nombre`,' ',`u`.`apellido`) AS `nombre_completo`, `u`.`correo` AS `correo`, `u`.`celular` AS `celular`, `c`.`nombre_categoria` AS `nombre_categoria`, `p`.`tipo_prestador` AS `tipo_prestador`, `p`.`macrodistrito_base` AS `macrodistrito_base`, `p`.`estado_validacion` AS `estado_validacion`, `p`.`estado_disponibilidad` AS `estado_disponibilidad`, `pl`.`nombre_plan` AS `nombre_plan`, count(`s`.`id_solicitud`) AS `total_servicios_atendidos`, coalesce(round(avg(`cal`.`puntuacion_general`),2),5.00) AS `promedio_estrellas`, coalesce(round(avg(`cal`.`puntualidad`),2),5.00) AS `promedio_puntualidad`, coalesce(round(avg(`cal`.`calidad_trabajo`),2),5.00) AS `promedio_calidad` FROM (((((`profesionales` `p` join `usuarios` `u` on(`p`.`id_usuario` = `u`.`id_usuario`)) join `categorias` `c` on(`p`.`id_categoria` = `c`.`id_categoria`)) join `planes_suscripcion` `pl` on(`p`.`id_plan` = `pl`.`id_plan`)) left join `solicitudes_servicio` `s` on(`p`.`id_profesional` = `s`.`id_profesional` and `s`.`estado_servicio` = 'FINALIZADA')) left join `calificaciones` `cal` on(`s`.`id_solicitud` = `cal`.`id_solicitud`)) GROUP BY `p`.`id_profesional`, `u`.`nombre`, `u`.`apellido`, `u`.`correo`, `u`.`celular`, `c`.`nombre_categoria`, `p`.`tipo_prestador`, `p`.`macrodistrito_base`, `p`.`estado_validacion`, `p`.`estado_disponibilidad`, `pl`.`nombre_plan` ;

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
-- Indices de la tabla `clientes_favoritos`
--
ALTER TABLE `clientes_favoritos`
  ADD PRIMARY KEY (`id_favorito`),
  ADD UNIQUE KEY `id_cliente` (`id_cliente`,`id_profesional`),
  ADD KEY `fk_fav_profesional` (`id_profesional`);

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
  MODIFY `id_log` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=84;

--
-- AUTO_INCREMENT de la tabla `calificaciones`
--
ALTER TABLE `calificaciones`
  MODIFY `id_calificacion` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id_categoria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id_cliente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `clientes_favoritos`
--
ALTER TABLE `clientes_favoritos`
  MODIFY `id_favorito` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `documentos_profesional`
--
ALTER TABLE `documentos_profesional`
  MODIFY `id_documento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `mensajes`
--
ALTER TABLE `mensajes`
  MODIFY `id_mensaje` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  MODIFY `id_notificacion` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT de la tabla `planes_suscripcion`
--
ALTER TABLE `planes_suscripcion`
  MODIFY `id_plan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `profesionales`
--
ALTER TABLE `profesionales`
  MODIFY `id_profesional` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id_rol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `solicitudes_servicio`
--
ALTER TABLE `solicitudes_servicio`
  MODIFY `id_solicitud` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `tracking_solicitud_gps`
--
ALTER TABLE `tracking_solicitud_gps`
  MODIFY `id_tracking` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT de la tabla `transacciones_suscripcion`
--
ALTER TABLE `transacciones_suscripcion`
  MODIFY `id_transaccion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

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
-- Filtros para la tabla `clientes_favoritos`
--
ALTER TABLE `clientes_favoritos`
  ADD CONSTRAINT `fk_fav_cliente` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id_cliente`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_fav_profesional` FOREIGN KEY (`id_profesional`) REFERENCES `profesionales` (`id_profesional`) ON DELETE CASCADE;

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
CREATE  EVENT `evt_purgar_mensajes_antiguos` ON SCHEDULE EVERY 1 DAY STARTS '2026-10-02 11:17:06' ON COMPLETION NOT PRESERVE ENABLE DO BEGIN
    -- Elimina mensajes de solicitudes finalizadas o canceladas hace más de 15 días
    DELETE m FROM mensajes m
    INNER JOIN solicitudes_servicio s ON m.id_solicitud = s.id_solicitud
    WHERE s.estado_servicio IN ('FINALIZADA', 'CANCELADA')
      AND s.fecha_finalizacion < DATE_SUB(NOW(), INTERVAL 15 DAY);
END$$

CREATE  EVENT `evt_caducar_membresias` ON SCHEDULE EVERY 1 DAY STARTS '2026-10-02 11:42:14' ON COMPLETION NOT PRESERVE ENABLE DO BEGIN
    -- Regresa al plan gratuito (1) a los que se les venció.
    -- OJO: No les quitamos los tokens (tokens_disponibles), solo pierden la prioridad del plan.
    UPDATE profesionales 
    SET id_plan = 1, fin_suscripcion = NULL 
    WHERE fin_suscripcion < CURDATE() AND id_plan != 1;
END$$

DELIMITER ;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
