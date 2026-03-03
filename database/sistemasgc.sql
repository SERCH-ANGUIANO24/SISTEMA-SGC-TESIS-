-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 01-03-2026 a las 05:44:55
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
-- Base de datos: `sistemasgc`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `auditorias`
--

CREATE TABLE `auditorias` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nombre_auditoria` varchar(255) NOT NULL,
  `tipo_auditoria` enum('Interna','Externa') NOT NULL,
  `auditor_lider` varchar(255) NOT NULL,
  `fecha_auditoria` date NOT NULL,
  `anio` year(4) NOT NULL,
  `auditores` text DEFAULT NULL,
  `archivo_path` varchar(255) DEFAULT NULL,
  `archivo_nombre` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `auditorias`
--

INSERT INTO `auditorias` (`id`, `nombre_auditoria`, `tipo_auditoria`, `auditor_lider`, `fecha_auditoria`, `anio`, `auditores`, `archivo_path`, `archivo_nombre`, `created_at`, `updated_at`) VALUES
(3, 'Correcion', 'Interna', 'Jose', '2020-10-20', '2020', 'aranza', 'auditorias/1771956942_PRACTICA1.txt', 'PRACTICA 1.txt', '2026-02-24 18:15:42', '2026-02-24 18:16:50'),
(5, 'Ejecucución', 'Interna', 'tania', '2022-02-07', '2022', 'manuel', 'auditorias/1771959634_ParaMACSS.txt', 'Para MACSS.txt', '2026-02-24 19:00:34', '2026-02-24 19:00:34'),
(9, 'Anual', 'Externa', 'Griselda', '2023-02-28', '2023', 'manuel', 'auditorias/1772311742_Piloto_Mexico.xlsx', 'Piloto_Mexico.xlsx', '2026-02-28 20:49:02', '2026-02-28 20:49:02');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `competencias`
--

CREATE TABLE `competencias` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `tipo` varchar(255) NOT NULL,
  `archivo_nombre` varchar(255) DEFAULT NULL,
  `archivo_ruta` varchar(255) DEFAULT NULL,
  `archivo_original` varchar(255) DEFAULT NULL,
  `archivo_tamano` int(11) DEFAULT NULL,
  `archivo_extension` varchar(255) DEFAULT NULL,
  `responsable` varchar(255) DEFAULT NULL,
  `fecha_emision` date DEFAULT NULL,
  `fecha_vencimiento` date DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `estado` varchar(255) NOT NULL DEFAULT 'activo',
  `parent_id` bigint(20) UNSIGNED DEFAULT NULL,
  `color` varchar(255) NOT NULL DEFAULT '#800000',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `competencias`
--

INSERT INTO `competencias` (`id`, `nombre`, `tipo`, `archivo_nombre`, `archivo_ruta`, `archivo_original`, `archivo_tamano`, `archivo_extension`, `responsable`, `fecha_emision`, `fecha_vencimiento`, `descripcion`, `estado`, `parent_id`, `color`, `created_at`, `updated_at`) VALUES
(1, 'Griselda', 'carpeta', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'activo', NULL, '#80004a', '2026-02-28 20:55:04', '2026-02-28 20:55:04'),
(2, 'José', 'carpeta', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'activo', NULL, '#008015', '2026-02-28 20:55:18', '2026-02-28 20:55:18'),
(3, 'Presentacion Proyecto Negocios Formal Azul (1)', 'documento', '1772315306_69a362aa62021.pdf', 'competencias/1772315306_69a362aa62021.pdf', 'Presentacion Proyecto Negocios Formal Azul (1).pdf', 4258735, 'pdf', NULL, NULL, NULL, NULL, 'activo', 2, '#800000', '2026-02-28 21:48:26', '2026-02-28 21:48:26'),
(4, 'Piloto_Mexico', 'documento', '1772315318_69a362b6686b1.xlsx', 'competencias/1772315318_69a362b6686b1.xlsx', 'Piloto_Mexico.xlsx', 478356, 'xlsx', NULL, NULL, NULL, NULL, 'activo', 2, '#800000', '2026-02-28 21:48:38', '2026-02-28 21:48:38'),
(5, 'diabetes', 'documento', '1772315336_69a362c8e875a.csv', 'competencias/1772315336_69a362c8e875a.csv', 'diabetes.csv', 23873, 'csv', NULL, NULL, NULL, NULL, 'activo', 1, '#800000', '2026-02-28 21:48:56', '2026-02-28 21:48:56'),
(6, 'Doc1', 'documento', '1772315348_69a362d471b8e.docx', 'competencias/1772315348_69a362d471b8e.docx', 'Doc1.docx', 39316, 'docx', NULL, NULL, NULL, NULL, 'activo', 1, '#800000', '2026-02-28 21:49:08', '2026-02-28 21:49:08');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `documental_documents`
--

CREATE TABLE `documental_documents` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `mime_type` varchar(255) DEFAULT NULL,
  `size` bigint(20) UNSIGNED DEFAULT NULL,
  `extension` varchar(255) DEFAULT NULL,
  `folder_id` bigint(20) UNSIGNED DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `responsable` varchar(255) DEFAULT NULL,
  `proceso` varchar(255) DEFAULT NULL,
  `departamento` varchar(255) DEFAULT NULL,
  `estatus` enum('Pendiente','Valido','No Valido') DEFAULT 'Pendiente',
  `observaciones` text DEFAULT NULL,
  `fecha` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `documental_documents`
--

INSERT INTO `documental_documents` (`id`, `name`, `original_name`, `file_path`, `mime_type`, `size`, `extension`, `folder_id`, `user_id`, `responsable`, `proceso`, `departamento`, `estatus`, `observaciones`, `fecha`, `created_at`, `updated_at`) VALUES
(7, 'Piloto_Mexico', 'Piloto_Mexico.xlsx', 'documental/10/1772313867_69a35d0b2bfed.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 478356, 'xlsx', 8, 10, 'Super Administrador', 'TI', 'Sistemas Computacionales', 'No Valido', NULL, '2026-02-28', '2026-02-28 21:24:27', '2026-02-28 21:24:27'),
(9, 'Doc1', 'Doc1.docx', 'documental/10/1772314147_69a35e238b286.docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 39316, 'docx', 8, 10, 'Super Administrador', 'TI', 'Sistemas Computacionales', 'Valido', NULL, '2026-02-28', '2026-02-28 21:29:07', '2026-03-01 03:15:25'),
(10, 'PRACTICA 1', 'PRACTICA 1.txt', 'documental/10/1772314300_69a35ebc247fc.txt', 'text/plain', 3675, 'txt', 8, 10, 'Super Administrador', 'TI', 'Sistemas Computacionales', 'No Valido', 'Falta de Información', '2026-02-28', '2026-02-28 21:31:40', '2026-03-01 03:12:41'),
(11, 'Presentacion Proyecto Negocios Formal Azul (1)', 'Presentacion Proyecto Negocios Formal Azul (1).pdf', 'documental/10/1772314622_69a35ffed0060.pdf', 'application/pdf', 4258735, 'pdf', 8, 10, 'Super Administrador', 'TI', 'Sistemas Computacionales', 'Valido', NULL, '2026-02-28', '2026-02-28 21:37:02', '2026-03-01 02:58:42'),
(12, 'escape1', 'escape1.jpg', 'documental/10/1772314651_69a3601b61475.jpg', 'image/jpeg', 916825, 'jpg', 8, 10, 'Super Administrador', 'TI', 'Sistemas Computacionales', 'Valido', NULL, '2026-02-28', '2026-02-28 21:37:31', '2026-03-01 00:55:09'),
(16, 'Piloto_Mexico', 'Piloto_Mexico.xlsx', 'documental/9/1772336418_69a3b522d92c5.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 478356, 'xlsx', 8, 9, 'Arely Colín', 'Planeación', 'Rectoría', 'No Valido', NULL, '2026-02-28', '2026-03-01 03:40:18', '2026-03-01 03:40:18'),
(17, 'Presentacion Proyecto Negocios Formal Azul (1)', 'Presentacion Proyecto Negocios Formal Azul (1).pdf', 'documental/9/1772336509_69a3b57d68f07.pdf', 'application/pdf', 4258735, 'pdf', 8, 9, 'Arely Colín', 'Planeación', 'Rectoría', 'Valido', NULL, '2026-02-28', '2026-03-01 03:41:49', '2026-03-01 03:45:04'),
(18, 'Piloto_Mexico', 'Piloto_Mexico.xlsx', 'documental/9/1772337968_69a3bb3058a03.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 478356, 'xlsx', 9, 9, 'Arely Colín', 'Planeación', 'Rectoría', 'Pendiente', NULL, '2026-02-28', '2026-03-01 04:06:08', '2026-03-01 04:06:08'),
(19, 'Sistemas Operativos', 'Sistemas Operativos.txt', 'documental/9/1772337985_69a3bb416787e.txt', 'text/plain', 535, 'txt', 9, 9, 'Arely Colín', 'Planeación', 'Rectoría', 'Valido', NULL, '2026-02-28', '2026-03-01 04:06:25', '2026-03-01 04:07:39');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `documental_folders`
--

CREATE TABLE `documental_folders` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `color` varchar(255) NOT NULL DEFAULT '#800000',
  `parent_id` bigint(20) UNSIGNED DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `documental_folders`
--

INSERT INTO `documental_folders` (`id`, `name`, `color`, `parent_id`, `user_id`, `created_at`, `updated_at`) VALUES
(5, 'Planeación', '#00801a', NULL, 10, '2026-02-27 17:19:06', '2026-02-28 21:09:53'),
(8, 'Rectoría', '#000980', 5, 10, '2026-02-28 21:18:15', '2026-02-28 21:18:15'),
(9, 'AAAAA', '#00800f', 5, 10, '2026-02-28 21:23:53', '2026-02-28 21:23:53');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `documents`
--

CREATE TABLE `documents` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `mime_type` varchar(255) DEFAULT NULL,
  `size` bigint(20) UNSIGNED DEFAULT NULL,
  `folder_id` bigint(20) UNSIGNED DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `documents`
--

INSERT INTO `documents` (`id`, `name`, `original_name`, `file_path`, `mime_type`, `size`, `folder_id`, `user_id`, `created_at`, `updated_at`) VALUES
(24, 'Presentacion Proyecto Negocios Formal Azul', 'Presentacion Proyecto Negocios Formal Azul.pdf', 'anexos/10/4296bc83-aa5d-4f2b-b3a3-82ece699e5e8.pdf', 'application/pdf', 4258735, 14, 10, '2026-02-27 22:38:04', '2026-02-27 22:38:04'),
(26, 'Doc1-2', 'Doc1-2.docx', 'anexos/10/f1da0029-65dd-4e5f-a02f-6c1b0178d581.docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 39316, 14, 10, '2026-02-27 22:40:15', '2026-02-27 22:58:49'),
(28, 'codigos-sgc', 'codigos-sgc.docx', 'anexos/11/aa11212c-a0f2-4805-8995-f9f7e36adace.docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 229385, 15, 11, '2026-02-27 23:03:15', '2026-02-27 23:49:45'),
(30, 'escape1', 'escape1.jpg', 'anexos/10/1ec38a30-265b-4c51-b20f-56c92bcc21c1.jpg', 'image/jpeg', 916825, 15, 10, '2026-02-27 23:41:50', '2026-02-27 23:41:50'),
(31, 'Sistemas Operativos', 'Sistemas Operativos.txt', 'anexos/10/c50bb214-4c33-4ba9-bd15-efc981bb66b5.txt', 'text/plain', 535, 15, 10, '2026-02-27 23:42:08', '2026-02-27 23:42:08'),
(32, 'Presentacion Proyecto Negocios Formal Azul (1)', 'Presentacion Proyecto Negocios Formal Azul (1).pdf', 'anexos/10/ecf14a35-af54-4d43-bf1c-5f845307d1be.pdf', 'application/pdf', 4258735, 15, 10, '2026-02-27 23:42:19', '2026-02-27 23:42:19'),
(33, 'Doc1', 'Doc1.docx', 'anexos/10/a24de17c-18d6-4282-9b16-64f8fc8ebf87.docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 39316, 15, 10, '2026-02-27 23:42:29', '2026-02-27 23:42:29'),
(36, 'Captura de pantalla_1', 'Captura de pantalla_1.png', 'anexos/10/2a4bab37-b203-40c1-858a-700ab996d188.png', 'image/png', 22770, 14, 10, '2026-02-27 23:46:20', '2026-02-27 23:57:27'),
(37, 'Codigos', 'Codigos.txt', 'anexos/10/dfbfc6b6-eb72-4476-9455-b50ed4a9ebaa.txt', 'text/plain', 693, 14, 10, '2026-02-27 23:47:29', '2026-02-27 23:47:29'),
(38, 'diabetes', 'diabetes.csv', 'anexos/10/b844f89e-4655-4c15-9f10-296edfa90ef4.csv', 'text/csv', 23873, 14, 10, '2026-02-28 18:56:50', '2026-02-28 18:56:50'),
(39, 'Proyecto', 'Proyecto.pdf', 'anexos/10/02d8c810-5dd7-427a-aeaa-bb30cbcebfc1.pdf', 'application/pdf', 510967, 14, 10, '2026-02-28 21:39:28', '2026-02-28 21:39:28');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `folders`
--

CREATE TABLE `folders` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `color` varchar(255) NOT NULL DEFAULT '#808080',
  `parent_id` bigint(20) UNSIGNED DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `folders`
--

INSERT INTO `folders` (`id`, `name`, `color`, `parent_id`, `user_id`, `created_at`, `updated_at`) VALUES
(14, 'Procedimientos', '#002080', NULL, 10, '2026-02-27 22:37:46', '2026-02-28 18:45:50'),
(15, 'Reglamentos', '#800000', NULL, 10, '2026-02-27 23:11:45', '2026-02-28 18:46:09');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `formatos`
--

CREATE TABLE `formatos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `proceso` varchar(255) NOT NULL,
  `departamento` varchar(255) NOT NULL,
  `clave_formato` varchar(255) NOT NULL,
  `codigo_procedimiento` varchar(255) NOT NULL,
  `version_procedimiento` varchar(255) NOT NULL,
  `nombre_archivo` varchar(255) NOT NULL,
  `ruta_archivo` varchar(255) NOT NULL,
  `extension_archivo` varchar(255) DEFAULT NULL,
  `tamanio_archivo` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `formatos`
--

INSERT INTO `formatos` (`id`, `proceso`, `departamento`, `clave_formato`, `codigo_procedimiento`, `version_procedimiento`, `nombre_archivo`, `ruta_archivo`, `extension_archivo`, `tamanio_archivo`, `created_at`, `updated_at`) VALUES
(1, 'PLANEACION', 'DIRECCIÓN ACADÉMICA', 'FDA-01', '265484489494', 'V1', 'Presentacion Proyecto Negocios Formal Azul.pdf', 'formatos/0be38900-8b6c-4dfa-a5b5-c1e6fe26fa86.pdf', 'PDF', 4258735, '2026-02-24 16:57:52', '2026-02-24 16:57:52'),
(2, 'TITULACION', 'SERVICIOS ESCOLARES', 'FSE-22', '4489456498789', 'V4', 'Practica_Android_Kotlin_Empresarial_2_Diciembre.docx', 'formatos/fee81ff3-67c4-48c6-896d-cf77a0e535d2.docx', 'DOCX', 33756, '2026-02-24 16:59:26', '2026-02-24 16:59:26'),
(4, 'PLANEACION', 'DIRECCIÓN ACADÉMICA', 'FSE-01', '57778758758785', 'v5', 'Formato Harvard 3 (2) (1).pdf', 'formatos/d1acb23a-25eb-4c8b-8569-77a20a94219a.pdf', 'PDF', 124159, '2026-02-26 17:09:31', '2026-02-26 17:09:31'),
(5, 'PLANEACION', 'DIRECCIÓN ACADÉMICA', 'FDA-5', '549849871615', 'V4', 'escape1.jpg', 'formatos/acc5bff2-cb25-4099-8032-0a9d1b124f15.jpg', 'JPG', 916825, '2026-02-28 22:00:30', '2026-02-28 22:00:30');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `formatos_documents`
--

CREATE TABLE `formatos_documents` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `mime_type` varchar(255) DEFAULT NULL,
  `size` bigint(20) UNSIGNED DEFAULT NULL,
  `extension` varchar(255) DEFAULT NULL,
  `folder_id` bigint(20) UNSIGNED DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `departamento` varchar(255) DEFAULT NULL,
  `tipo_documento` varchar(255) DEFAULT NULL,
  `fecha_documento` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `formatos_folders`
--

CREATE TABLE `formatos_folders` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `color` varchar(255) NOT NULL DEFAULT '#16a34a',
  `parent_id` bigint(20) UNSIGNED DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `gestion_documents`
--

CREATE TABLE `gestion_documents` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `mime_type` varchar(255) NOT NULL,
  `size` bigint(20) UNSIGNED NOT NULL,
  `folder_id` bigint(20) UNSIGNED DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `responsable` varchar(255) DEFAULT NULL,
  `proceso` varchar(255) DEFAULT NULL,
  `departamento` varchar(255) DEFAULT NULL,
  `fecha` date DEFAULT NULL,
  `estatus` enum('valido','no_valido') NOT NULL DEFAULT 'no_valido',
  `observaciones` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `gestion_folders`
--

CREATE TABLE `gestion_folders` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `color` varchar(255) NOT NULL DEFAULT '#800000',
  `parent_id` bigint(20) UNSIGNED DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `informes`
--

CREATE TABLE `informes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nombre_informe` varchar(255) NOT NULL,
  `tipo_auditoria` enum('Interna','Externa') NOT NULL,
  `auditor_lider` varchar(255) NOT NULL,
  `fecha_informe` date NOT NULL,
  `fecha_auditoria` date NOT NULL,
  `archivo_path` varchar(255) NOT NULL,
  `archivo_nombre` varchar(255) NOT NULL,
  `auditoria_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `matrices_documents`
--

CREATE TABLE `matrices_documents` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `mime_type` varchar(255) DEFAULT NULL,
  `size` bigint(20) UNSIGNED DEFAULT NULL,
  `extension` varchar(255) DEFAULT NULL,
  `folder_id` bigint(20) UNSIGNED DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `tipo_documento` varchar(255) DEFAULT NULL,
  `fecha_documento` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `matrices_documents`
--

INSERT INTO `matrices_documents` (`id`, `name`, `original_name`, `file_path`, `mime_type`, `size`, `extension`, `folder_id`, `user_id`, `tipo_documento`, `fecha_documento`, `created_at`, `updated_at`) VALUES
(6, 'Piloto_Mexico', 'Piloto_Mexico.xlsx', 'matrices/10/1772304611_69a338e37720a.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 478356, 'xlsx', 2, 10, 'Excel', '2026-02-28', '2026-02-28 18:50:11', '2026-02-28 18:50:11'),
(7, 'Sistemas Operativos', 'Sistemas Operativos.txt', 'matrices/10/1772304626_69a338f26e21f.txt', 'text/plain', 535, 'txt', 2, 10, 'Documento', '2026-02-28', '2026-02-28 18:50:26', '2026-02-28 18:50:26'),
(8, 'Presentacion Proyecto Negocios Formal Azul (1)', 'Presentacion Proyecto Negocios Formal Azul (1).pdf', 'matrices/10/1772304639_69a338ff5c978.pdf', 'application/pdf', 4258735, 'pdf', 2, 10, 'PDF', '2026-02-28', '2026-02-28 18:50:39', '2026-02-28 18:50:39'),
(14, 'diabetes2', 'diabetes.csv', 'matrices/10/1772310472_69a34fc890c6b.csv', 'text/csv', 23873, 'csv', 2, 10, 'CSV', '2026-02-28', '2026-02-28 20:27:52', '2026-02-28 20:29:02'),
(15, 'Proyecto', 'Proyecto.pdf', 'matrices/11/1772315170_69a36222b5b86.pdf', 'application/pdf', 510967, 'pdf', 2, 11, 'PDF', '2026-02-28', '2026-02-28 21:46:10', '2026-02-28 21:46:10'),
(16, 'escape1', 'escape1.jpg', 'matrices/11/1772315190_69a36236a31cb.jpg', 'image/jpeg', 916825, 'jpg', 2, 11, 'Imagen', '2026-02-28', '2026-02-28 21:46:30', '2026-02-28 21:46:30');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `matrices_folders`
--

CREATE TABLE `matrices_folders` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `color` varchar(255) NOT NULL DEFAULT '#800000',
  `parent_id` bigint(20) UNSIGNED DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `matrices_folders`
--

INSERT INTO `matrices_folders` (`id`, `name`, `color`, `parent_id`, `user_id`, `created_at`, `updated_at`) VALUES
(2, 'Matrices', '#6a8000', NULL, 10, '2026-02-27 17:18:30', '2026-02-28 20:30:12');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2025_08_14_170933_add_two_factor_columns_to_users_table', 1),
(5, '2026_02_10_170735_add_proceso_departamento_to_users_table', 2),
(6, '2026_02_11_193349_create_folders_table', 3),
(7, '2026_02_11_193655_create_documents_table', 4),
(9, '2026_02_12_133530_create_gestion_folders_table', 5),
(10, '2026_02_12_133531_create_gestion_documents_table', 5),
(17, '2026_02_10_165506_add_proceso_departamento_to_users_table', 6),
(18, '2026_02_13_124619_create_documental_folders_table', 6),
(19, '2026_02_13_124652_create_documental_documents_table', 6),
(20, '2026_02_13_132537_create_matrices_folders_table', 6),
(21, '2026_02_13_132634_create_matrices_documents_table', 6),
(22, '2026_02_16_131307_create_formatos_folders_table', 6),
(23, '2026_02_16_131419_create_formatos_documents_table', 6),
(24, '2026_02_18_210925_create_formatos_table', 7),
(25, '2026_02_22_190133_add_is_active_to_users', 7),
(26, '2026_02_24_093043_create_auditorias_table', 7),
(27, '2026_02_24_093137_create_informes_table', 7),
(28, '2026_02_24_093234_create_solicitudes_mejora_table', 7),
(36, '2026_02_27_091934_add_role_to_users_table', 8),
(37, '2026_02_27_094229_create_competencias_table', 9);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('DkTbsKws2eZYT7VDgHezSP8LfFB5J3lKc2WstAYr', 10, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiaVhSYmdMM2d5VFlYODFFbUNOZlQxUnI5RGZaTmM2U2FQQktSY2MxaCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9kb2N1bWVudGFsL3Zlci1hcmNoaXZvLzEwIjtzOjU6InJvdXRlIjtzOjIyOiJkb2N1bWVudGFsLnZlci5hcmNoaXZvIjt9czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTA7fQ==', 1772339272);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `solicitudes_mejora`
--

CREATE TABLE `solicitudes_mejora` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nombre_solicitud` varchar(255) NOT NULL,
  `tipo_auditoria` enum('Interna','Externa') NOT NULL,
  `folio_solicitud` varchar(255) NOT NULL,
  `responsable_accion` varchar(255) NOT NULL,
  `fecha_solicitud` date NOT NULL,
  `fecha_aplicacion` date NOT NULL,
  `actividades_verificacion` text NOT NULL,
  `fecha_verificacion` date NOT NULL,
  `estatus` enum('Cerrado','En Proceso') NOT NULL,
  `archivo_path` varchar(255) NOT NULL,
  `archivo_nombre` varchar(255) NOT NULL,
  `auditoria_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `role` varchar(255) NOT NULL DEFAULT 'user',
  `proceso` varchar(255) DEFAULT NULL,
  `departamento` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `two_factor_secret` text DEFAULT NULL,
  `two_factor_recovery_codes` text DEFAULT NULL,
  `two_factor_confirmed_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `role`, `proceso`, `departamento`, `is_active`, `email_verified_at`, `password`, `two_factor_secret`, `two_factor_recovery_codes`, `two_factor_confirmed_at`, `remember_token`, `created_at`, `updated_at`) VALUES
(9, 'Arely Colín', 'colin3@gmail.com', 'user', 'Planeación', 'Rectoría', 1, NULL, '$2y$12$V4mJ5RYEULPkT2COkjuj5u5S3geazSEUngX7T03gnoLqzmxokKJF2', NULL, NULL, NULL, NULL, '2026-02-18 19:51:48', '2026-02-18 19:51:48'),
(10, 'Super Administrador', 'superadmin@uptex.edu.mx', 'superadmin', 'TI', 'Sistemas Computacionales', 1, NULL, '$2y$12$tD4lhSLJVboN2YHYRka0deUHY4.h5yVKN0HQ.He6SDM8pddZKJfy6', NULL, NULL, NULL, NULL, '2026-02-25 16:55:03', '2026-02-27 22:23:35'),
(11, 'Administrador', 'admin@uptex.edu.mx', 'admin', 'TI', 'Sistemas Computacionales', 1, NULL, '$2y$12$cw9gRfKDnj61/UelwoNSeu/.RSoPAKgFK9ZhC16n3DAzqiss9m8lq', NULL, NULL, NULL, NULL, '2026-02-25 16:55:03', '2026-02-27 22:23:36'),
(12, 'Sarai Jhovana Llanos Soto', 'sarai@uptex.edu.mx', 'user', 'Vinculación', 'Vinculación', 1, NULL, '$2y$12$M3SjpH2gCncd7FRbs83DXOOz02a38zXyrIfR3q7ANTgFlbmehlXOe', NULL, NULL, NULL, NULL, '2026-03-01 04:13:46', '2026-03-01 04:13:46');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `auditorias`
--
ALTER TABLE `auditorias`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indices de la tabla `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indices de la tabla `competencias`
--
ALTER TABLE `competencias`
  ADD PRIMARY KEY (`id`),
  ADD KEY `competencias_parent_id_foreign` (`parent_id`),
  ADD KEY `competencias_tipo_parent_id_index` (`tipo`,`parent_id`);

--
-- Indices de la tabla `documental_documents`
--
ALTER TABLE `documental_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `documental_documents_folder_id_foreign` (`folder_id`),
  ADD KEY `documental_documents_user_id_foreign` (`user_id`);

--
-- Indices de la tabla `documental_folders`
--
ALTER TABLE `documental_folders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `documental_folders_parent_id_foreign` (`parent_id`),
  ADD KEY `documental_folders_user_id_foreign` (`user_id`);

--
-- Indices de la tabla `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `documents_folder_id_foreign` (`folder_id`),
  ADD KEY `documents_user_id_foreign` (`user_id`);

--
-- Indices de la tabla `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indices de la tabla `folders`
--
ALTER TABLE `folders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `folders_parent_id_foreign` (`parent_id`),
  ADD KEY `folders_user_id_foreign` (`user_id`);

--
-- Indices de la tabla `formatos`
--
ALTER TABLE `formatos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `formatos_documents`
--
ALTER TABLE `formatos_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `formatos_documents_folder_id_foreign` (`folder_id`),
  ADD KEY `formatos_documents_user_id_foreign` (`user_id`);

--
-- Indices de la tabla `formatos_folders`
--
ALTER TABLE `formatos_folders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `formatos_folders_parent_id_foreign` (`parent_id`),
  ADD KEY `formatos_folders_user_id_foreign` (`user_id`);

--
-- Indices de la tabla `gestion_documents`
--
ALTER TABLE `gestion_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `gestion_documents_folder_id_foreign` (`folder_id`),
  ADD KEY `gestion_documents_user_id_foreign` (`user_id`);

--
-- Indices de la tabla `gestion_folders`
--
ALTER TABLE `gestion_folders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `gestion_folders_parent_id_foreign` (`parent_id`),
  ADD KEY `gestion_folders_user_id_foreign` (`user_id`);

--
-- Indices de la tabla `informes`
--
ALTER TABLE `informes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `informes_auditoria_id_foreign` (`auditoria_id`);

--
-- Indices de la tabla `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indices de la tabla `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `matrices_documents`
--
ALTER TABLE `matrices_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `matrices_documents_folder_id_foreign` (`folder_id`),
  ADD KEY `matrices_documents_user_id_foreign` (`user_id`);

--
-- Indices de la tabla `matrices_folders`
--
ALTER TABLE `matrices_folders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `matrices_folders_parent_id_foreign` (`parent_id`),
  ADD KEY `matrices_folders_user_id_foreign` (`user_id`);

--
-- Indices de la tabla `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indices de la tabla `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indices de la tabla `solicitudes_mejora`
--
ALTER TABLE `solicitudes_mejora`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `solicitudes_mejora_folio_solicitud_unique` (`folio_solicitud`),
  ADD KEY `solicitudes_mejora_auditoria_id_foreign` (`auditoria_id`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `auditorias`
--
ALTER TABLE `auditorias`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `competencias`
--
ALTER TABLE `competencias`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `documental_documents`
--
ALTER TABLE `documental_documents`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT de la tabla `documental_folders`
--
ALTER TABLE `documental_folders`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `documents`
--
ALTER TABLE `documents`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT de la tabla `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `folders`
--
ALTER TABLE `folders`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT de la tabla `formatos`
--
ALTER TABLE `formatos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `formatos_documents`
--
ALTER TABLE `formatos_documents`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `formatos_folders`
--
ALTER TABLE `formatos_folders`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `gestion_documents`
--
ALTER TABLE `gestion_documents`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `gestion_folders`
--
ALTER TABLE `gestion_folders`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `informes`
--
ALTER TABLE `informes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `matrices_documents`
--
ALTER TABLE `matrices_documents`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de la tabla `matrices_folders`
--
ALTER TABLE `matrices_folders`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT de la tabla `solicitudes_mejora`
--
ALTER TABLE `solicitudes_mejora`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `competencias`
--
ALTER TABLE `competencias`
  ADD CONSTRAINT `competencias_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `competencias` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `documental_documents`
--
ALTER TABLE `documental_documents`
  ADD CONSTRAINT `documental_documents_folder_id_foreign` FOREIGN KEY (`folder_id`) REFERENCES `documental_folders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `documental_documents_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `documental_folders`
--
ALTER TABLE `documental_folders`
  ADD CONSTRAINT `documental_folders_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `documental_folders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `documental_folders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `documents`
--
ALTER TABLE `documents`
  ADD CONSTRAINT `documents_folder_id_foreign` FOREIGN KEY (`folder_id`) REFERENCES `folders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `documents_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `folders`
--
ALTER TABLE `folders`
  ADD CONSTRAINT `folders_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `folders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `folders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `formatos_documents`
--
ALTER TABLE `formatos_documents`
  ADD CONSTRAINT `formatos_documents_folder_id_foreign` FOREIGN KEY (`folder_id`) REFERENCES `formatos_folders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `formatos_documents_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `formatos_folders`
--
ALTER TABLE `formatos_folders`
  ADD CONSTRAINT `formatos_folders_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `formatos_folders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `formatos_folders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `gestion_documents`
--
ALTER TABLE `gestion_documents`
  ADD CONSTRAINT `gestion_documents_folder_id_foreign` FOREIGN KEY (`folder_id`) REFERENCES `gestion_folders` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `gestion_documents_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `gestion_folders`
--
ALTER TABLE `gestion_folders`
  ADD CONSTRAINT `gestion_folders_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `gestion_folders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `gestion_folders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `informes`
--
ALTER TABLE `informes`
  ADD CONSTRAINT `informes_auditoria_id_foreign` FOREIGN KEY (`auditoria_id`) REFERENCES `auditorias` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `matrices_documents`
--
ALTER TABLE `matrices_documents`
  ADD CONSTRAINT `matrices_documents_folder_id_foreign` FOREIGN KEY (`folder_id`) REFERENCES `matrices_folders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `matrices_documents_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `matrices_folders`
--
ALTER TABLE `matrices_folders`
  ADD CONSTRAINT `matrices_folders_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `matrices_folders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `matrices_folders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `solicitudes_mejora`
--
ALTER TABLE `solicitudes_mejora`
  ADD CONSTRAINT `solicitudes_mejora_auditoria_id_foreign` FOREIGN KEY (`auditoria_id`) REFERENCES `auditorias` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
