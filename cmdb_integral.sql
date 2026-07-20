-- phpMyAdmin SQL Dump
-- version 5.2.3deb1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jul 20, 2026 at 04:11 PM
-- Server version: 8.4.10-0ubuntu0.26.04.1
-- PHP Version: 8.5.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `cmdb_integral`
--

-- --------------------------------------------------------

--
-- Table structure for table `accesos_portal_colaborador`
--

CREATE TABLE `accesos_portal_colaborador` (
  `id` bigint UNSIGNED NOT NULL,
  `usuario_id` int UNSIGNED NOT NULL,
  `ip` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `accessed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `accesos_portal_colaborador`
--

INSERT INTO `accesos_portal_colaborador` (`id`, `usuario_id`, `ip`, `accessed_at`) VALUES
(1, 3, '127.0.0.1', '2026-07-18 11:14:50'),
(2, 3, '127.0.0.1', '2026-07-18 11:15:37'),
(3, 3, '127.0.0.1', '2026-07-18 11:15:38'),
(4, 3, '127.0.0.1', '2026-07-18 11:16:10'),
(5, 3, '127.0.0.1', '2026-07-18 11:18:05'),
(6, 3, '127.0.0.1', '2026-07-20 09:36:54'),
(7, 3, '127.0.0.1', '2026-07-20 09:36:54'),
(8, 3, '127.0.0.1', '2026-07-20 14:06:39'),
(9, 3, '127.0.0.1', '2026-07-20 14:18:29'),
(10, 3, '127.0.0.1', '2026-07-20 14:23:01'),
(11, 3, '127.0.0.1', '2026-07-20 14:23:16'),
(12, 3, '127.0.0.1', '2026-07-20 15:04:02'),
(13, 3, '127.0.0.1', '2026-07-20 15:16:12'),
(14, 3, '127.0.0.1', '2026-07-20 15:24:40'),
(15, 3, '127.0.0.1', '2026-07-20 15:26:31'),
(16, 3, '127.0.0.1', '2026-07-20 15:27:30'),
(17, 3, '127.0.0.1', '2026-07-20 15:30:33'),
(18, 3, '127.0.0.1', '2026-07-20 15:32:37'),
(19, 3, '127.0.0.1', '2026-07-20 15:32:44');

-- --------------------------------------------------------

--
-- Table structure for table `api_tokens`
--

CREATE TABLE `api_tokens` (
  `id` int UNSIGNED NOT NULL,
  `usuario_id` int UNSIGNED NOT NULL,
  `token_hash` char(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` datetime NOT NULL,
  `revoked_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `api_tokens`
--

INSERT INTO `api_tokens` (`id`, `usuario_id`, `token_hash`, `expires_at`, `revoked_at`, `created_at`) VALUES
(1, 1, '11aebe7ca769dad7b64e1094009bb41657022988b34f30f2a05b88bce0a207fc', '2026-07-20 21:44:58', NULL, '2026-07-20 13:44:58'),
(2, 1, '49e33df6f3316de501e5fb70d988a8381b2e12603a4367bc27654beaaf8a4bda', '2026-07-20 16:45:07', NULL, '2026-07-20 13:45:07'),
(3, 1, '1c1fffb48cee58de2e75073b2285a8a0f91ac974519f8279081d4085182dc8cf', '2026-07-20 16:46:13', NULL, '2026-07-20 13:46:13'),
(4, 1, '80f87ee45c9686bce99ef57f97ff5b4c8f09a5305e14dda7bdb5b4b157dcb7e4', '2026-07-20 16:52:00', NULL, '2026-07-20 13:52:00'),
(5, 1, 'a8151d05d18b638dfd9b91c943a724f6be46558f7cdf8a778d8bb21ad62d1e9b', '2026-07-20 17:06:39', NULL, '2026-07-20 14:06:39');

-- --------------------------------------------------------

--
-- Table structure for table `asignaciones`
--

CREATE TABLE `asignaciones` (
  `id` int UNSIGNED NOT NULL,
  `inventario_id` int UNSIGNED NOT NULL,
  `colaborador_id` int UNSIGNED NOT NULL,
  `usuario_asignador_id` int UNSIGNED DEFAULT NULL,
  `audit_id` bigint UNSIGNED DEFAULT NULL,
  `firma_id` bigint UNSIGNED DEFAULT NULL,
  `fecha_asignacion` date NOT NULL,
  `fecha_devolucion` date DEFAULT NULL,
  `ip_asignada` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `observaciones` text COLLATE utf8mb4_unicode_ci,
  `estado` enum('ACTIVA','DEVUELTA') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ACTIVA',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `asignaciones`
--

INSERT INTO `asignaciones` (`id`, `inventario_id`, `colaborador_id`, `usuario_asignador_id`, `audit_id`, `firma_id`, `fecha_asignacion`, `fecha_devolucion`, `ip_asignada`, `observaciones`, `estado`, `created_at`) VALUES
(1, 1, 1, NULL, NULL, NULL, '2024-03-01', NULL, '192.168.10.25', 'Laptop con cargador y maletín.', 'ACTIVA', '2026-07-17 14:44:43'),
(2, 6, 2, NULL, NULL, NULL, '2024-06-01', NULL, '192.168.10.45', 'Laptop con cargador USB-C y mouse inalámbrico.', 'ACTIVA', '2026-07-17 14:44:43'),
(3, 8, 3, NULL, NULL, NULL, '2023-11-10', NULL, NULL, 'Monitor instalado en puesto de Recursos Humanos.', 'ACTIVA', '2026-07-17 14:44:43'),
(4, 11, 4, NULL, NULL, NULL, '2024-10-10', NULL, '10.20.5.34', 'Teléfono IP con extensión 2204.', 'ACTIVA', '2026-07-17 14:44:43'),
(11, 33, 24, 1, 65, NULL, '2026-07-20', NULL, '10.10.10.10', 'Asignacion fixture para pruebas Postman.', 'ACTIVA', '2026-07-20 12:47:15'),
(12, 14, 10, 1, 72, NULL, '2026-07-20', NULL, '234.443.43', 'Hola', 'ACTIVA', '2026-07-20 13:21:48');

-- --------------------------------------------------------

--
-- Table structure for table `bitacora`
--

CREATE TABLE `bitacora` (
  `id` bigint UNSIGNED NOT NULL,
  `usuario_id` int UNSIGNED DEFAULT NULL,
  `modulo` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `accion` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `entidad` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `entidad_id` bigint UNSIGNED DEFAULT NULL,
  `descripcion` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_agent` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nivel` enum('INFO','ADVERTENCIA','ERROR') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'INFO',
  `resultado` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'OK',
  `motivo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `datos_anteriores_json` longtext COLLATE utf8mb4_unicode_ci,
  `datos_posteriores_json` longtext COLLATE utf8mb4_unicode_ci,
  `correlation_id` char(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `previous_hash` char(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `record_hash` char(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `firma_id` bigint UNSIGNED DEFAULT NULL,
  `fingerprint` char(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payload_version` smallint UNSIGNED NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bitacora`
--

INSERT INTO `bitacora` (`id`, `usuario_id`, `modulo`, `accion`, `entidad`, `entidad_id`, `descripcion`, `ip`, `user_agent`, `nivel`, `resultado`, `motivo`, `datos_anteriores_json`, `datos_posteriores_json`, `correlation_id`, `previous_hash`, `record_hash`, `firma_id`, `fingerprint`, `payload_version`, `created_at`) VALUES
(1, 1, 'SISTEMA', 'SEMILLA', NULL, NULL, 'Base de datos CMDB creada con datos de ejemplo.', '127.0.0.1', NULL, 'INFO', 'OK', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-07-17 14:44:44'),
(2, 1, 'SISTEMA', 'SEMILLA_REALISTA', NULL, NULL, 'Datos realistas adicionales cargados para demostración.', '127.0.0.1', NULL, 'INFO', 'OK', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-07-17 14:44:44'),
(3, 1, 'AUTENTICACION', 'LOGIN', 'usuarios', 1, 'Inicio de sesión exitoso.', '127.0.0.1', 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:152.0) Gecko/20100101 Firefox/152.0', 'INFO', 'OK', NULL, '[]', '[]', '83a7887cd7dcf4b306ea61487282200b', NULL, '0e8a7b6191921ca575d86ac4410a5b9eb514c034ad1911eebf63f62ace642a82', NULL, NULL, 1, '2026-07-17 15:10:43'),
(4, 1, 'AUTENTICACION', 'LOGIN', 'usuarios', 1, 'Inicio de sesión exitoso.', '127.0.0.1', 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:152.0) Gecko/20100101 Firefox/152.0', 'INFO', 'OK', NULL, '[]', '[]', '5ec2be051c0d0eccfbcdaacf05a901a3', '0e8a7b6191921ca575d86ac4410a5b9eb514c034ad1911eebf63f62ace642a82', 'f9a892d48da2c748938c52f251a2863d61f20e7fc5117049c26e8274a8c3cbc9', NULL, NULL, 1, '2026-07-17 17:21:37'),
(5, 1, 'AUTENTICACION', 'LOGOUT', 'usuarios', 1, 'Cierre de sesión.', '127.0.0.1', 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:152.0) Gecko/20100101 Firefox/152.0', 'INFO', 'OK', NULL, '[]', '[]', 'c21a5a83896126ab6e2162f06957baa2', 'f9a892d48da2c748938c52f251a2863d61f20e7fc5117049c26e8274a8c3cbc9', '307e51b6b41c02c755287b5ebfa63fd1fb32de5d3670cd727b99dbe7f1b52b6d', NULL, NULL, 1, '2026-07-17 17:22:04'),
(6, 1, 'AUTENTICACION', 'LOGIN', 'usuarios', 1, 'Inicio de sesión exitoso.', '127.0.0.1', 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:152.0) Gecko/20100101 Firefox/152.0', 'INFO', 'OK', NULL, '[]', '[]', 'c055b45d65b669649d0efe64ff31d7f0', '307e51b6b41c02c755287b5ebfa63fd1fb32de5d3670cd727b99dbe7f1b52b6d', '3cb9077b511eac09326f6001296e2584b3e2710b245f6d7294d04bf7c854c8c2', NULL, NULL, 1, '2026-07-17 17:22:11'),
(7, 1, 'AUTENTICACION', 'LOGOUT', 'usuarios', 1, 'Cierre de sesión.', '127.0.0.1', 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:152.0) Gecko/20100101 Firefox/152.0', 'INFO', 'OK', NULL, '[]', '[]', '3fc3b2dc38f7a1ae99230e31f84b1e47', '3cb9077b511eac09326f6001296e2584b3e2710b245f6d7294d04bf7c854c8c2', '12bdca461832628e1334bfc17dd92d69605020c1d3503ee663fb8ebda30ae5bf', NULL, NULL, 1, '2026-07-17 17:22:14'),
(8, 1, 'AUTENTICACION', 'LOGIN', 'usuarios', 1, 'Inicio de sesión exitoso.', '127.0.0.1', 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:152.0) Gecko/20100101 Firefox/152.0', 'INFO', 'OK', NULL, '[]', '[]', 'cee7c608fa8845d8666b085d37cce859', '12bdca461832628e1334bfc17dd92d69605020c1d3503ee663fb8ebda30ae5bf', 'ed9789897ca74fff59bad5f618a67156851712dcfd4c137fbfd256a8b62bf2f7', NULL, NULL, 1, '2026-07-17 17:22:35'),
(9, 1, 'AUTENTICACION', 'LOGOUT', 'usuarios', 1, 'Cierre de sesión.', '127.0.0.1', 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:152.0) Gecko/20100101 Firefox/152.0', 'INFO', 'OK', NULL, '[]', '[]', '61e2dd022fcad4abcdfbcb6de54e015d', 'ed9789897ca74fff59bad5f618a67156851712dcfd4c137fbfd256a8b62bf2f7', '953f2dbb42a5c2cb7dd68b51506d9ee7efe89f9a446a7a9b1a99e245eceb5980', NULL, NULL, 1, '2026-07-17 17:22:37'),
(10, 1, 'AUTENTICACION', 'RECUPERACION_SOLICITADA', 'usuarios', 1, 'Solicitud de recuperación de contraseña.', '127.0.0.1', 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:152.0) Gecko/20100101 Firefox/152.0', 'INFO', 'OK', NULL, '[]', '{\"token\":\"[REDACTED]\"}', '4462325ccbce4c53317b845e87a528a7', '953f2dbb42a5c2cb7dd68b51506d9ee7efe89f9a446a7a9b1a99e245eceb5980', 'fce791fd6cbb5962e419f11722a4417d37fa96f3a43b38f1cacff543b7877ff9', NULL, NULL, 1, '2026-07-17 17:22:44'),
(11, 1, 'AUTENTICACION', 'LOGIN', 'usuarios', 1, 'Inicio de sesión exitoso.', '127.0.0.1', 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:152.0) Gecko/20100101 Firefox/152.0', 'INFO', 'OK', NULL, '[]', '[]', '597ac5707a4810823b769f2ec37ddab0', 'fce791fd6cbb5962e419f11722a4417d37fa96f3a43b38f1cacff543b7877ff9', 'e68b41f8b0dcf126e4f200ec9fb20427f01667f20dd5b0be27fadaf94f7fc62d', NULL, NULL, 1, '2026-07-17 17:23:55'),
(12, 1, 'CATEGORIAS', 'ACTUALIZAR', 'categorias', 2, 'Categoría #2 actualizada.', '127.0.0.1', 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:152.0) Gecko/20100101 Firefox/152.0', 'INFO', 'OK', NULL, '{\"id\":2,\"nombre\":\"Software\",\"tipo\":\"SOFTWARE\",\"descripcion\":\"Aplicaciones y sistemas operativos.\",\"activo\":1,\"created_at\":\"2026-07-17 09:44:43\",\"updated_at\":\"2026-07-17 09:44:43\"}', '{\"nombre\":\"Software\",\"tipo\":\"SOFTWARE\",\"descripcion\":\"Aplicaciones y sistemas operativos..\",\"activo\":1}', 'a4bc7495243d47de6f6572463e1a4f78', 'e68b41f8b0dcf126e4f200ec9fb20427f01667f20dd5b0be27fadaf94f7fc62d', '263ae9ed0b2cd3d8d1dd36428e8f127d548a118301f6ed74e6fb7e0acb994ce9', NULL, NULL, 1, '2026-07-17 17:29:12'),
(13, 1, 'CATEGORIAS', 'CREAR', 'categorias', 12, 'Categoría #12 creada.', '127.0.0.1', 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:152.0) Gecko/20100101 Firefox/152.0', 'INFO', 'OK', NULL, '[]', '{\"nombre\":\"dgarah\",\"tipo\":\"HARDWARE\",\"descripcion\":\"dsahartja\",\"activo\":1}', '03825378499c107f0733d268c028714d', '263ae9ed0b2cd3d8d1dd36428e8f127d548a118301f6ed74e6fb7e0acb994ce9', 'dbd2601c0a4f3a8a12fbfa1350dcf718173946818e005b856a9f01afba29a6f9', NULL, NULL, 1, '2026-07-17 17:29:22'),
(14, 1, 'CATEGORIAS', 'ACTUALIZAR', 'categorias', 12, 'Categoría #12 actualizada.', '127.0.0.1', 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:152.0) Gecko/20100101 Firefox/152.0', 'INFO', 'OK', NULL, '{\"id\":12,\"nombre\":\"dgarah\",\"tipo\":\"HARDWARE\",\"descripcion\":\"dsahartja\",\"activo\":1,\"created_at\":\"2026-07-17 12:29:22\",\"updated_at\":\"2026-07-17 12:29:22\"}', '{\"nombre\":\"mArIaM HERNÁNDEZ\",\"tipo\":\"HARDWARE\",\"descripcion\":\"dsahartja\",\"activo\":1}', '48de38bb818ae301f8ca9593c3a61ab3', 'dbd2601c0a4f3a8a12fbfa1350dcf718173946818e005b856a9f01afba29a6f9', 'f998b463ac69dbb3918acbbab181b4d96bcd1d4b83ca9f5b999e6ae446f492f2', NULL, NULL, 1, '2026-07-17 17:29:36'),
(15, 1, 'CATEGORIAS', 'ACTUALIZAR', 'categorias', 12, 'Categoría #12 actualizada.', '127.0.0.1', 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:152.0) Gecko/20100101 Firefox/152.0', 'INFO', 'OK', NULL, '{\"id\":12,\"nombre\":\"mArIaM HERNÁNDEZ\",\"tipo\":\"HARDWARE\",\"descripcion\":\"dsahartja\",\"activo\":1,\"created_at\":\"2026-07-17 12:29:22\",\"updated_at\":\"2026-07-17 12:29:36\"}', '{\"nombre\":\"FFfmaldrmg gv \",\"tipo\":\"HARDWARE\",\"descripcion\":\"dsahartja\",\"activo\":1}', 'ddf1fedd3e082e9bb36f733594ef3da9', 'f998b463ac69dbb3918acbbab181b4d96bcd1d4b83ca9f5b999e6ae446f492f2', '6bb0cb0ae2fdea41e6d576fb671d38788eefc025313aa7f36070e33c4eca9d27', NULL, NULL, 1, '2026-07-17 17:29:57'),
(16, 1, 'CATEGORIAS', 'ACTUALIZAR', 'categorias', 12, 'Categoría #12 actualizada.', '127.0.0.1', 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:152.0) Gecko/20100101 Firefox/152.0', 'INFO', 'OK', NULL, '{\"id\":12,\"nombre\":\"FFfmaldrmg gv \",\"tipo\":\"HARDWARE\",\"descripcion\":\"dsahartja\",\"activo\":1,\"created_at\":\"2026-07-17 12:29:22\",\"updated_at\":\"2026-07-17 12:29:57\"}', '{\"nombre\":\"sfarha\",\"tipo\":\"HARDWARE\",\"descripcion\":\"dsahartja\",\"activo\":1}', '88d8acb04bffb22cef4d78cd1399dc8e', '6bb0cb0ae2fdea41e6d576fb671d38788eefc025313aa7f36070e33c4eca9d27', '507d225ebee739c70003026641d1a9d6ff58a2f91a038e2bd07ec75ec2016455', NULL, NULL, 1, '2026-07-17 17:30:12'),
(17, 1, 'CATEGORIAS', 'BAJA_LOGICA', 'categorias', 12, 'Categoría #12 dada de baja.', '127.0.0.1', 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:152.0) Gecko/20100101 Firefox/152.0', 'INFO', 'OK', NULL, '{\"id\":12,\"nombre\":\"sfarha\",\"tipo\":\"HARDWARE\",\"descripcion\":\"dsahartja\",\"activo\":1,\"created_at\":\"2026-07-17 12:29:22\",\"updated_at\":\"2026-07-17 12:30:12\"}', '{\"activo\":0}', '4d5f750809ababbcadcd58bb98956ef7', '507d225ebee739c70003026641d1a9d6ff58a2f91a038e2bd07ec75ec2016455', '7d0b81f209b7568c184d1eaf7c5315d21b3bf212e8bd5aa13ec671e80538ca98', NULL, NULL, 1, '2026-07-17 17:30:17'),
(18, 1, 'ASIGNACIONES', 'SOLICITAR_DEVOLUCION', 'asignaciones', 4, 'Solicitud de devolución para asignación #4.', '127.0.0.1', 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:152.0) Gecko/20100101 Firefox/152.0', 'INFO', 'OK', 'Devolución de activo', '[]', '{\"motivo\":\"Devolución de activo\",\"estado_fisico\":null,\"observaciones\":\"\",\"evidencia\":\"\"}', '9bc68e814e8d2f6c4e7d38b45e361b01', '7d0b81f209b7568c184d1eaf7c5315d21b3bf212e8bd5aa13ec671e80538ca98', 'b6818c4e5a0855909b4835e9efbef3b2ba4a2fb86f13f8c0b5aa79f45d40e0ae', NULL, NULL, 1, '2026-07-17 17:33:33'),
(19, 1, 'ASIGNACIONES', 'RECIBIR_DEVOLUCION', 'devoluciones', 1, 'Recepción física de devolución #1.', '127.0.0.1', 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:152.0) Gecko/20100101 Firefox/152.0', 'INFO', 'OK', NULL, '[]', '{\"estado_fisico\":\"INCOMPLETO\",\"evidencia\":\"\",\"accesorios_recibidos\":\"\",\"observacion_recepcion\":\"falta factura\"}', '907d81bdf95e84e3db83d5c40a0ddc71', 'b6818c4e5a0855909b4835e9efbef3b2ba4a2fb86f13f8c0b5aa79f45d40e0ae', '9e49b72a52c79c9545ca4835c3db0055fc92a6b45cf207389a3f1133a9781314', NULL, NULL, 1, '2026-07-17 17:34:12'),
(20, 1, 'AUTENTICACION', 'LOGIN', 'usuarios', 1, 'Inicio de sesión exitoso.', '127.0.0.1', 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:152.0) Gecko/20100101 Firefox/152.0', 'INFO', 'OK', NULL, '[]', '[]', 'fe56831658755a274426a8f75994a1b8', '9e49b72a52c79c9545ca4835c3db0055fc92a6b45cf207389a3f1133a9781314', 'a8335e59a6e47a566f5b391a4aaac17a238ded827c7ed810dffe902854e199bc', NULL, NULL, 1, '2026-07-18 11:08:37'),
(21, 1, 'REPORTES', 'EXPORTAR_CATEGORIAS', NULL, NULL, 'CMDB - Reporte por categoría. Filtros: Sin filtros', '127.0.0.1', 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:152.0) Gecko/20100101 Firefox/152.0', 'INFO', 'OK', NULL, '[]', '[]', '965d22f70eb48b1f36dddee06ff9e76b', 'a8335e59a6e47a566f5b391a4aaac17a238ded827c7ed810dffe902854e199bc', '98cd911cd81eafa6a4a8f77a948a66fc7d6b1c57464253cd2ce0cd11f9ed670c', NULL, NULL, 1, '2026-07-18 11:08:46'),
(22, 1, 'LICENCIAS', 'LIBERAR_CUPO', 'licencia_asignaciones', 1, 'Cupo de licencia #1 liberado.', '127.0.0.1', 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:152.0) Gecko/20100101 Firefox/152.0', 'INFO', 'OK', NULL, '[]', '{\"estado\":\"LIBERADA\",\"inventario_id\":13}', '0423d8b724ee5139fb56f55c05036795', '98cd911cd81eafa6a4a8f77a948a66fc7d6b1c57464253cd2ce0cd11f9ed670c', 'c7f246df10b89b4d141c5ee8d28fd3103f6c90a0dd4b0744aaa140781a80781a', NULL, NULL, 1, '2026-07-18 11:09:38'),
(23, 1, 'AUTENTICACION', 'LOGOUT', 'usuarios', 1, 'Cierre de sesión.', '127.0.0.1', 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:152.0) Gecko/20100101 Firefox/152.0', 'INFO', 'OK', NULL, '[]', '[]', '0e663e2465cb1ae9d065be82d7673a20', 'c7f246df10b89b4d141c5ee8d28fd3103f6c90a0dd4b0744aaa140781a80781a', '1b41ef5c9f2368849570745ecb27ee26a685499f2f58401ff233e9a13421229b', NULL, NULL, 1, '2026-07-18 11:14:11'),
(24, 3, 'AUTENTICACION', 'LOGIN', 'usuarios', 3, 'Inicio de sesión exitoso.', '127.0.0.1', 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:152.0) Gecko/20100101 Firefox/152.0', 'INFO', 'OK', NULL, '[]', '[]', '3d51688662bf2fa2c3ab0db7c61c4037', '1b41ef5c9f2368849570745ecb27ee26a685499f2f58401ff233e9a13421229b', 'd13b01a7b840efb5d25b5915096ebcd543b23bd5c1719d3b0ae8554dc661f473', NULL, NULL, 1, '2026-07-18 11:14:50'),
(25, 3, 'PORTAL', 'SOLICITAR_DEVOLUCION', 'asignaciones', 1, 'Solicitud de devolución para asignación #1.', '127.0.0.1', 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:152.0) Gecko/20100101 Firefox/152.0', 'INFO', 'OK', NULL, '[]', '{\"motivo\":\"Defectuoso de fabrica\",\"evidencia\":\"\"}', 'c3a9970af06c8feaff4cd607ee1968b0', 'd13b01a7b840efb5d25b5915096ebcd543b23bd5c1719d3b0ae8554dc661f473', '4f97a010f28862baebd7e79320247ef0d7cd0597b02773a48a0307d72467b428', NULL, NULL, 1, '2026-07-18 11:16:10'),
(26, 3, 'NECESIDADES', 'CREAR_PORTAL', 'necesidades', 7, 'Solicitud #7 creada desde Portal del Colaborador.', '127.0.0.1', 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:152.0) Gecko/20100101 Firefox/152.0', 'INFO', 'OK', NULL, '[]', '{\"colaborador_id\":1,\"categoria_id\":1,\"tipo_necesidad\":\"EQUIPO\",\"descripcion\":\"New equipment for the team\",\"justificacion\":\"The old one is in repair.\",\"prioridad\":\"MEDIA\",\"costo_estimado\":1000,\"costo_unitario_estimado\":1000,\"cantidad\":1,\"anio_objetivo\":2026}', '174209e8865fc2444ee0c2af01222233', '4f97a010f28862baebd7e79320247ef0d7cd0597b02773a48a0307d72467b428', '08488cab55dc5279b196e54563a62a99df0263324d30f75eaf93639fa6154aa0', NULL, NULL, 1, '2026-07-18 11:18:05'),
(27, 3, 'AUTENTICACION', 'LOGOUT', 'usuarios', 3, 'Cierre de sesión.', '127.0.0.1', 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:152.0) Gecko/20100101 Firefox/152.0', 'INFO', 'OK', NULL, '[]', '[]', '64c523bbce6074e9aeedc5936185713e', '08488cab55dc5279b196e54563a62a99df0263324d30f75eaf93639fa6154aa0', '91d1d46afd68ba2ead09bd1dfb0f066a840f819fcd0d28c3cbcf2db26a6c7f16', NULL, NULL, 1, '2026-07-18 11:18:17'),
(28, 2, 'AUTENTICACION', 'LOGIN', 'usuarios', 2, 'Inicio de sesión exitoso.', '127.0.0.1', 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:152.0) Gecko/20100101 Firefox/152.0', 'INFO', 'OK', NULL, '[]', '[]', 'd44fc4c1a7594545e71ce0760da6860d', '91d1d46afd68ba2ead09bd1dfb0f066a840f819fcd0d28c3cbcf2db26a6c7f16', '93e3e5e92baf9f1bbd154f5d2d04bb035de71c241f4f081559e07d961e7aceec', NULL, NULL, 1, '2026-07-18 11:18:44'),
(29, 2, 'PRESUPUESTO', 'EXPORTAR', 'presupuestos', 1, 'Presupuesto #1 exportado a Excel.', '127.0.0.1', 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:152.0) Gecko/20100101 Firefox/152.0', 'INFO', 'EXPORTADO', NULL, '[]', '[]', '59c227b44bdc999c850ac9c6656aff92', '93e3e5e92baf9f1bbd154f5d2d04bb035de71c241f4f081559e07d961e7aceec', '1c2ba6d1021ef4438b2cc7a6b1b4815d0c1a3ce5d702ae6e74e586592975109c', NULL, NULL, 1, '2026-07-18 11:21:11'),
(30, 2, 'AUTENTICACION', 'LOGOUT', 'usuarios', 2, 'Cierre de sesión.', '127.0.0.1', 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:152.0) Gecko/20100101 Firefox/152.0', 'INFO', 'OK', NULL, '[]', '[]', '2663260e50f181476346cece15c51817', '1c2ba6d1021ef4438b2cc7a6b1b4815d0c1a3ce5d702ae6e74e586592975109c', 'b18b122b077554dece67f24a3df119e5e4cc4de84c14de976ada68228893cc44', NULL, NULL, 1, '2026-07-18 11:22:51'),
(31, 1, 'AUTENTICACION', 'LOGIN', 'usuarios', 1, 'Inicio de sesión exitoso.', '127.0.0.1', 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:152.0) Gecko/20100101 Firefox/152.0', 'INFO', 'OK', NULL, '[]', '[]', '7c65ebf45b77f284a60a6d999323f420', 'b18b122b077554dece67f24a3df119e5e4cc4de84c14de976ada68228893cc44', '1a6a487545765d18abd26759969b8aa92dc5e705835e91170c62d2a433b8f05f', NULL, NULL, 1, '2026-07-18 11:23:06'),
(32, 1, 'NECESIDADES', 'ACTUALIZAR_ESTADO', 'necesidades', 7, 'Solicitud #7 cambió a APROBADA.', '127.0.0.1', 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:152.0) Gecko/20100101 Firefox/152.0', 'INFO', 'APROBADA', '', '{\"id\":7,\"colaborador_id\":1,\"categoria_id\":1,\"tipo_necesidad\":\"EQUIPO\",\"descripcion\":\"New equipment for the team\",\"justificacion\":\"The old one is in repair.\",\"prioridad\":\"MEDIA\",\"costo_estimado\":\"1000.00\",\"costo_unitario_estimado\":\"1000.00\",\"cantidad\":1,\"anio_objetivo\":2026,\"estado\":\"EN_ESPERA\",\"comentario_resolucion\":null,\"usuario_procesador_id\":null,\"respuesta_administrativa\":null,\"fecha_procesamiento\":null,\"audit_id\":null,\"firma_id\":null,\"created_at\":\"2026-07-18 06:18:05\",\"updated_at\":\"2026-07-18 06:18:05\"}', '{\"estado\":\"APROBADA\",\"respuesta_administrativa\":\"\",\"usuario_procesador_id\":1}', '009ee222a661614d1297210c16dbf1dc', '1a6a487545765d18abd26759969b8aa92dc5e705835e91170c62d2a433b8f05f', '0ce80ae7771a617dac40f70aa648039de14dd4250f2f6ddb0b2ffc5166cb629f', NULL, NULL, 1, '2026-07-18 11:23:21'),
(33, 1, 'NECESIDADES', 'ACTUALIZAR_ESTADO', 'necesidades', 7, 'Solicitud #7 cambió a APROBADA.', '127.0.0.1', 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:152.0) Gecko/20100101 Firefox/152.0', 'INFO', 'APROBADA', 'Nuevo equipo', '{\"id\":7,\"colaborador_id\":1,\"categoria_id\":1,\"tipo_necesidad\":\"EQUIPO\",\"descripcion\":\"New equipment for the team\",\"justificacion\":\"The old one is in repair.\",\"prioridad\":\"MEDIA\",\"costo_estimado\":\"1000.00\",\"costo_unitario_estimado\":\"1000.00\",\"cantidad\":1,\"anio_objetivo\":2026,\"estado\":\"EN_ESPERA\",\"comentario_resolucion\":null,\"usuario_procesador_id\":null,\"respuesta_administrativa\":null,\"fecha_procesamiento\":null,\"audit_id\":null,\"firma_id\":null,\"created_at\":\"2026-07-18 06:18:05\",\"updated_at\":\"2026-07-18 06:18:05\"}', '{\"estado\":\"APROBADA\",\"respuesta_administrativa\":\"Nuevo equipo\",\"usuario_procesador_id\":1}', 'f6e04d82145cc2c0f268ed44f3c152c9', '0ce80ae7771a617dac40f70aa648039de14dd4250f2f6ddb0b2ffc5166cb629f', '944b86d37a4f131dc3e9bb5ca8adaa66d89fb2047c3044d174e1b6dc23abb518', NULL, NULL, 1, '2026-07-18 11:23:54'),
(34, 1, 'COLABORADORES', 'ACTUALIZAR', 'colaboradores', 4, 'Colaborador #4 actualizado.', '127.0.0.1', 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:152.0) Gecko/20100101 Firefox/152.0', 'INFO', 'OK', NULL, '{\"id\":4,\"nombres\":\"Ana\",\"apellidos\":\"Rodríguez\",\"identificacion\":\"8-999-1004\",\"departamento\":\"Comunicaciones\",\"ubicacion\":\"Edificio 102 - Oficina 8\",\"direccion\":\"Campus Central\",\"telefono\":\"6000-1004\",\"email\":\"ana.rodriguez@cmdb.local\",\"foto\":null,\"activo\":1,\"created_at\":\"2026-07-17 09:44:43\",\"updated_at\":\"2026-07-17 09:44:43\"}', '{\"nombres\":\"Ana\",\"apellidos\":\"Rodríguez\",\"identificacion\":\"8-999-1004\",\"departamento\":\"Comunicaciones\",\"ubicacion\":\"Edificio 102 - Oficina 8\",\"direccion\":\"Campus Central\",\"telefono\":\"6000-1004\",\"email\":\"ana.rodriguez@cmdb.local\",\"activo\":1,\"foto\":\"uploads/collaborators/634f1f5af6e060f842d9ad52.jpg\"}', '0637d348271bf06e363978dc4f8ca0e3', '944b86d37a4f131dc3e9bb5ca8adaa66d89fb2047c3044d174e1b6dc23abb518', '8a1be8cedab082b1c7296495ea830d6c7b3f0e55c25d685c739f21621b1c795f', NULL, NULL, 1, '2026-07-18 11:37:53'),
(35, 1, 'COLABORADORES', 'ACTUALIZAR', 'colaboradores', 2, 'Colaborador #2 actualizado.', '127.0.0.1', 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:152.0) Gecko/20100101 Firefox/152.0', 'INFO', 'OK', NULL, '{\"id\":2,\"nombres\":\"Carlos\",\"apellidos\":\"Gómez\",\"identificacion\":\"8-999-1002\",\"departamento\":\"Finanzas\",\"ubicacion\":\"Edificio 201 - Oficina 5\",\"direccion\":\"Campus Central\",\"telefono\":\"6000-1002\",\"email\":\"carlos.gomez@cmdb.local\",\"foto\":null,\"activo\":1,\"created_at\":\"2026-07-17 09:44:43\",\"updated_at\":\"2026-07-17 09:44:43\"}', '{\"nombres\":\"Carlos\",\"apellidos\":\"Gómez\",\"identificacion\":\"8-999-1002\",\"departamento\":\"Finanzas\",\"ubicacion\":\"Edificio 201 - Oficina 5\",\"direccion\":\"Campus Central\",\"telefono\":\"6000-1002\",\"email\":\"carlos.gomez@cmdb.local\",\"activo\":1,\"foto\":\"uploads/collaborators/a0128d6173e517bdc2374839.jpg\"}', 'c7f3496433c852b206682a7131f5212a', '8a1be8cedab082b1c7296495ea830d6c7b3f0e55c25d685c739f21621b1c795f', '2c4b2a3a5914d8d9578b39597849a1c631e9c87c1975f42ba3be5aadb07ae192', NULL, NULL, 1, '2026-07-18 11:38:04'),
(36, 1, 'AUTENTICACION', 'LOGIN', 'usuarios', 1, 'Inicio de sesión exitoso.', '127.0.0.1', 'curl/8.18.0', 'INFO', 'OK', NULL, '[]', '[]', '22432da1dc7307b3ad70db57eef7c690', '2c4b2a3a5914d8d9578b39597849a1c631e9c87c1975f42ba3be5aadb07ae192', 'dfab48e6583cf468c0a9c235e9ef04e4a809ad3ffbb8bfa72b11c7e371aa9d05', NULL, NULL, 1, '2026-07-20 08:44:25'),
(37, 1, 'AUTENTICACION', 'LOGIN', 'usuarios', 1, 'Inicio de sesión exitoso.', '127.0.0.1', 'curl/8.18.0', 'INFO', 'OK', NULL, '[]', '[]', 'a2cf127df3ac58b6d5581b4a2867e9b9', 'dfab48e6583cf468c0a9c235e9ef04e4a809ad3ffbb8bfa72b11c7e371aa9d05', '4eea303cee1ac4f242a2ae36ff99d332b189e9f4f55260763b0768199e53453c', NULL, NULL, 1, '2026-07-20 09:36:14'),
(38, 1, 'AUTENTICACION', 'LOGIN', 'usuarios', 1, 'Inicio de sesión exitoso.', '127.0.0.1', 'curl/8.18.0', 'INFO', 'OK', NULL, '[]', '[]', '579416deb9bfa7c98aa26ff38bc5f58d', '4eea303cee1ac4f242a2ae36ff99d332b189e9f4f55260763b0768199e53453c', '31922caf226f1ccc3dc6a2a0a3a0825c7365edffa509dc6222ae8e82c3ab02b8', NULL, NULL, 1, '2026-07-20 09:36:36'),
(39, 1, 'AUTENTICACION', 'LOGIN', 'usuarios', 1, 'Inicio de sesión exitoso.', '127.0.0.1', 'curl/8.18.0', 'INFO', 'OK', NULL, '[]', '[]', '7ad22bdeeb3d6c76c69832e1b850432d', '31922caf226f1ccc3dc6a2a0a3a0825c7365edffa509dc6222ae8e82c3ab02b8', 'b835831119d1e1813938219aedd495474457c313133eecea5d5c46963d46c161', NULL, NULL, 1, '2026-07-20 09:36:53'),
(40, 2, 'AUTENTICACION', 'LOGIN', 'usuarios', 2, 'Inicio de sesión exitoso.', '127.0.0.1', 'curl/8.18.0', 'INFO', 'OK', NULL, '[]', '[]', '7c6ac3b2a93bc7e907a795b9e466287a', 'b835831119d1e1813938219aedd495474457c313133eecea5d5c46963d46c161', 'ef8a84dfea60d2b79cae0af71e8023fa41370a1cbbde4662a5d20e189ce5ec3b', NULL, NULL, 1, '2026-07-20 09:36:53'),
(41, 3, 'AUTENTICACION', 'LOGIN', 'usuarios', 3, 'Inicio de sesión exitoso.', '127.0.0.1', 'curl/8.18.0', 'INFO', 'OK', NULL, '[]', '[]', '94a93e589a59445b86d89802eefdd0a1', 'ef8a84dfea60d2b79cae0af71e8023fa41370a1cbbde4662a5d20e189ce5ec3b', '06993330fdfaf32b210121811f9c3984a00e905cb4578f0daf422f822af3bf4a', NULL, NULL, 1, '2026-07-20 09:36:54'),
(42, 1, 'AUTENTICACION', 'LOGIN', 'usuarios', 1, 'Inicio de sesión exitoso.', '127.0.0.1', 'curl/8.18.0', 'INFO', 'OK', NULL, '[]', '[]', '3a4fb1aa2067629a8b305fa2042b3502', '06993330fdfaf32b210121811f9c3984a00e905cb4578f0daf422f822af3bf4a', 'd327099741c6eca279143bede9f77dcf09f6d4cbd7b8c4fc4951bd7bf92453a0', NULL, NULL, 1, '2026-07-20 09:37:07'),
(43, 1, 'REPORTES', 'EXPORTAR_ASIGNACIONES', NULL, NULL, 'CMDB - Reporte de responsables de equipos. Filtros: Sin filtros', '127.0.0.1', 'curl/8.18.0', 'INFO', 'OK', NULL, '[]', '[]', 'c6389183d51df055adc600c7b28f00a7', 'd327099741c6eca279143bede9f77dcf09f6d4cbd7b8c4fc4951bd7bf92453a0', '3e3b156d5bb9f4c29dd0f6451f048e10ac8aa872549ee6d56a21121b091029b9', NULL, NULL, 1, '2026-07-20 09:37:07'),
(44, 1, 'REPORTES', 'EXPORTAR_HISTORIAL_ESTADOS', NULL, NULL, 'CMDB - Reporte de historial de estados. Filtros: Sin filtros', '127.0.0.1', 'curl/8.18.0', 'INFO', 'OK', NULL, '[]', '[]', '79c22a6253774098f6f4c770f30caf34', '3e3b156d5bb9f4c29dd0f6451f048e10ac8aa872549ee6d56a21121b091029b9', '30cd04317d1c569f01825aab118de5c8ff4f76675c136ea849a9c3619f66fb43', NULL, NULL, 1, '2026-07-20 09:37:07'),
(45, 1, 'REPORTES', 'EXPORTAR_SOLICITUDES', NULL, NULL, 'CMDB - Reporte de solicitudes. Filtros: Sin filtros', '127.0.0.1', 'curl/8.18.0', 'INFO', 'OK', NULL, '[]', '[]', '256200d7a9c75514195d17098bb05dd9', '30cd04317d1c569f01825aab118de5c8ff4f76675c136ea849a9c3619f66fb43', '76e64347e2370b5f3819a1f81679e64ab0a87d42be4ecef57225bdd02862f4eb', NULL, NULL, 1, '2026-07-20 09:37:07'),
(46, 1, 'REPORTES', 'EXPORTAR_DEVOLUCIONES', NULL, NULL, 'CMDB - Reporte de devoluciones. Filtros: Sin filtros', '127.0.0.1', 'curl/8.18.0', 'INFO', 'OK', NULL, '[]', '[]', '274e0a4d7a5a69e3835c05aadc1a38c2', '76e64347e2370b5f3819a1f81679e64ab0a87d42be4ecef57225bdd02862f4eb', '5c05c9906205f6d29d37df732158c269f59afcda1e6ec88a2e102f728610689e', NULL, NULL, 1, '2026-07-20 09:37:07'),
(47, 1, 'REPORTES', 'EXPORTAR_REVISIONES', NULL, NULL, 'CMDB - Reporte de revisiones técnicas. Filtros: Sin filtros', '127.0.0.1', 'curl/8.18.0', 'INFO', 'OK', NULL, '[]', '[]', 'f395f004cfb6ac6890dc532ef4d5a71d', '5c05c9906205f6d29d37df732158c269f59afcda1e6ec88a2e102f728610689e', 'f375564fde13a8094f37e0e98bdb6d2ea71507c4477cf22a39f743e237adb2c2', NULL, NULL, 1, '2026-07-20 09:37:07'),
(48, 1, 'AUTENTICACION', 'LOGIN', 'usuarios', 1, 'Inicio de sesión exitoso.', '127.0.0.1', 'curl/8.18.0', 'INFO', 'OK', NULL, '[]', '[]', '15c993fc5001b0102acfcd215b52bbc2', 'f375564fde13a8094f37e0e98bdb6d2ea71507c4477cf22a39f743e237adb2c2', '2be8a6e0439ec2970cf2346b813714db3e2e9dea007e99f520d7a4c0b3978f5f', NULL, NULL, 1, '2026-07-20 09:37:32'),
(49, 1, 'REPORTES', 'EXPORTAR_INVENTARIO', NULL, NULL, 'CMDB - Reporte de inventario. Filtros: Sin filtros', '127.0.0.1', 'curl/8.18.0', 'INFO', 'OK', NULL, '[]', '[]', '7af2fd33aebe3608be41118fe524f3ec', '2be8a6e0439ec2970cf2346b813714db3e2e9dea007e99f520d7a4c0b3978f5f', '5b2497bfa04c9d7d6b08c2462e770e13a5534a3e2c7090527ae15bddc5a1dfd3', NULL, NULL, 1, '2026-07-20 09:37:32'),
(50, 1, 'REPORTES', 'EXPORTAR_ASIGNACIONES', NULL, NULL, 'CMDB - Reporte de responsables de equipos. Filtros: Sin filtros', '127.0.0.1', 'curl/8.18.0', 'INFO', 'OK', NULL, '[]', '[]', '968eca01a06a062a01c2c6ff8ceefa58', '5b2497bfa04c9d7d6b08c2462e770e13a5534a3e2c7090527ae15bddc5a1dfd3', 'ab44f4ff199823a57d673bb70af2125bfdf1ed9137952aac22b275815120b11a', NULL, NULL, 1, '2026-07-20 09:37:32'),
(51, 1, 'REPORTES', 'EXPORTAR_DISPONIBLES', NULL, NULL, 'CMDB - Reporte de activos disponibles. Filtros: Sin filtros', '127.0.0.1', 'curl/8.18.0', 'INFO', 'OK', NULL, '[]', '[]', '822ac6ab38c963fc3cacad9da90513c1', 'ab44f4ff199823a57d673bb70af2125bfdf1ed9137952aac22b275815120b11a', 'a5a3b8adb25cfef98af8d960ced5f16bcb3fbe56c13538af6777964fb411b251', NULL, NULL, 1, '2026-07-20 09:37:32'),
(52, 1, 'REPORTES', 'EXPORTAR_CATEGORIAS', NULL, NULL, 'CMDB - Reporte por categoría. Filtros: Sin filtros', '127.0.0.1', 'curl/8.18.0', 'INFO', 'OK', NULL, '[]', '[]', '1f611b0d445a25f32d3bd323637fce2c', 'a5a3b8adb25cfef98af8d960ced5f16bcb3fbe56c13538af6777964fb411b251', '3020c6df7b8445c40bfae1bed1973db0639dad0d27dec5f889e7028db24c19ec', NULL, NULL, 1, '2026-07-20 09:37:32'),
(53, 1, 'REPORTES', 'EXPORTAR_ASIGNADOS_CATEGORIA', NULL, NULL, 'CMDB - Reporte de asignados por categoría. Filtros: Sin filtros', '127.0.0.1', 'curl/8.18.0', 'INFO', 'OK', NULL, '[]', '[]', 'e1a21441826f9c037ae0f932e33d7e19', '3020c6df7b8445c40bfae1bed1973db0639dad0d27dec5f889e7028db24c19ec', 'efe68171347fc7c0717a8685fa7b5a07704b3433a8471f59d1730532f7777059', NULL, NULL, 1, '2026-07-20 09:37:32'),
(54, 1, 'REPORTES', 'EXPORTAR_REPARACION', NULL, NULL, 'CMDB - Reporte de reparación. Filtros: Sin filtros', '127.0.0.1', 'curl/8.18.0', 'INFO', 'OK', NULL, '[]', '[]', 'e0613228e1ae2c5263a937d6f0210f60', 'efe68171347fc7c0717a8685fa7b5a07704b3433a8471f59d1730532f7777059', 'e6876fc7ea88a8c204defeddedcaa02e1c71f441d6b9abca71c4e38d6fe71449', NULL, NULL, 1, '2026-07-20 09:37:32'),
(55, 1, 'REPORTES', 'EXPORTAR_DONACIONES', NULL, NULL, 'CMDB - Reporte de donaciones. Filtros: Sin filtros', '127.0.0.1', 'curl/8.18.0', 'INFO', 'OK', NULL, '[]', '[]', 'b922bc3d246bebd4a4c15dc3a2031e82', 'e6876fc7ea88a8c204defeddedcaa02e1c71f441d6b9abca71c4e38d6fe71449', 'e8f7a04a8085d5d9736a4f5ac3a3f8c95de482ac2776f20ba9e413f479bd7b97', NULL, NULL, 1, '2026-07-20 09:37:33'),
(56, 1, 'REPORTES', 'EXPORTAR_DESCARTES', NULL, NULL, 'CMDB - Reporte de descartes. Filtros: Sin filtros', '127.0.0.1', 'curl/8.18.0', 'INFO', 'OK', NULL, '[]', '[]', 'c38881484d7db9e021effb9dcea81f22', 'e8f7a04a8085d5d9736a4f5ac3a3f8c95de482ac2776f20ba9e413f479bd7b97', 'd1acc86bee55091ab4a6d1144511d85a4685d03812c8a72fd0ae0f4bf913e87e', NULL, NULL, 1, '2026-07-20 09:37:33'),
(57, 1, 'REPORTES', 'EXPORTAR_LICENCIAS', NULL, NULL, 'CMDB - Reporte de licencias. Filtros: Sin filtros', '127.0.0.1', 'curl/8.18.0', 'INFO', 'OK', NULL, '[]', '[]', '4df9259129a6adbf5c76bac991dc4b06', 'd1acc86bee55091ab4a6d1144511d85a4685d03812c8a72fd0ae0f4bf913e87e', '556ccf1fe6dcbc6fdd06aa1d3c59f0e6313d61e8931342e65e2133241844147e', NULL, NULL, 1, '2026-07-20 09:37:33'),
(58, 1, 'REPORTES', 'EXPORTAR_CUPOS_LICENCIAS', NULL, NULL, 'CMDB - Reporte de cupos de licencias. Filtros: Sin filtros', '127.0.0.1', 'curl/8.18.0', 'INFO', 'OK', NULL, '[]', '[]', '4891ccf5011be7215e2f5367512841a6', '556ccf1fe6dcbc6fdd06aa1d3c59f0e6313d61e8931342e65e2133241844147e', '04b4a023e446333a4eeed1886cb4be761791d95c7b66259199cd7b04c2964f47', NULL, NULL, 1, '2026-07-20 09:37:33'),
(59, 1, 'REPORTES', 'EXPORTAR_VENCIMIENTOS', NULL, NULL, 'CMDB - Reporte de vencimientos. Filtros: Sin filtros', '127.0.0.1', 'curl/8.18.0', 'INFO', 'OK', NULL, '[]', '[]', '613c9c969b1c06edafb9e4d2e8e88ede', '04b4a023e446333a4eeed1886cb4be761791d95c7b66259199cd7b04c2964f47', '3fdce74427cd298156e935b705e647b85658a19477b96f35e2a3e1eec5039028', NULL, NULL, 1, '2026-07-20 09:37:33'),
(60, 1, 'REPORTES', 'EXPORTAR_DEPRECIACION', NULL, NULL, 'CMDB - Reporte de depreciación. Filtros: Sin filtros', '127.0.0.1', 'curl/8.18.0', 'INFO', 'OK', NULL, '[]', '[]', '544145fdd4518ae69dd584ab6e0a96f7', '3fdce74427cd298156e935b705e647b85658a19477b96f35e2a3e1eec5039028', 'f84635242e163e1683dc08ce5d0e41c141347f3b9f5152f2b2afafb3935177fe', NULL, NULL, 1, '2026-07-20 09:37:33'),
(61, 1, 'REPORTES', 'EXPORTAR_HISTORIAL_ESTADOS', NULL, NULL, 'CMDB - Reporte de historial de estados. Filtros: Sin filtros', '127.0.0.1', 'curl/8.18.0', 'INFO', 'OK', NULL, '[]', '[]', '8b1103d11419817ab3cdf623c3ddf1c6', 'f84635242e163e1683dc08ce5d0e41c141347f3b9f5152f2b2afafb3935177fe', '91b25e90435cfbb4b457f66cdc0a820636ba6b7b5def9c91d9c58332f31f5edd', NULL, NULL, 1, '2026-07-20 09:37:33'),
(62, 1, 'REPORTES', 'EXPORTAR_SOLICITUDES', NULL, NULL, 'CMDB - Reporte de solicitudes. Filtros: Sin filtros', '127.0.0.1', 'curl/8.18.0', 'INFO', 'OK', NULL, '[]', '[]', 'ed979949294a5344f573f2126eb0992c', '91b25e90435cfbb4b457f66cdc0a820636ba6b7b5def9c91d9c58332f31f5edd', '297ca55f95d49ad60f2046169719507b1cf218b53ebd01e2b8e001c25d6a0fe8', NULL, NULL, 1, '2026-07-20 09:37:33'),
(63, 1, 'REPORTES', 'EXPORTAR_DEVOLUCIONES', NULL, NULL, 'CMDB - Reporte de devoluciones. Filtros: Sin filtros', '127.0.0.1', 'curl/8.18.0', 'INFO', 'OK', NULL, '[]', '[]', 'f78941aa397c17a605e10d00baa928d5', '297ca55f95d49ad60f2046169719507b1cf218b53ebd01e2b8e001c25d6a0fe8', '9e0b735819a47e8134fa8565668e73dd96ea7caaf82920fca682df21a6c6f64d', NULL, NULL, 1, '2026-07-20 09:37:33'),
(64, 1, 'REPORTES', 'EXPORTAR_REVISIONES', NULL, NULL, 'CMDB - Reporte de revisiones técnicas. Filtros: Sin filtros', '127.0.0.1', 'curl/8.18.0', 'INFO', 'OK', NULL, '[]', '[]', 'a8b50c0bdfbd9284e1251e1674c97997', '9e0b735819a47e8134fa8565668e73dd96ea7caaf82920fca682df21a6c6f64d', '52206a966054c49a033cd5cfaafd52783e230574e5cbb3bf680546bce2f94161', NULL, NULL, 1, '2026-07-20 09:37:33'),
(65, 1, 'POSTMAN', 'FIXTURE', 'asignaciones', NULL, 'Asignacion fixture Postman creada.', 'CLI', 'CLI', 'INFO', 'OK', NULL, '[]', '[]', '3310d25a1c3f88f8cc46c4e3711f5b6d', '52206a966054c49a033cd5cfaafd52783e230574e5cbb3bf680546bce2f94161', 'b4edc58a98b8594016036433f6ffaebbd38978847bbdcc8419b98d14c5da1f08', NULL, NULL, 1, '2026-07-20 12:47:15'),
(66, NULL, 'QR', 'ACCESO_PUBLICO', 'inventario', 31, 'Consulta pública QR del activo #31.', '127.0.0.1', 'curl/8.18.0', 'INFO', 'OK', NULL, '[]', '{\"codigo_activo\":\"PM-LIC-POSTMAN\",\"publico_limitado\":true}', '5a87d1a5ba770b53988b7b5cfc76ed55', 'b4edc58a98b8594016036433f6ffaebbd38978847bbdcc8419b98d14c5da1f08', '8941ee6a76024f5de9a3b173a9ef11d17f7371e78811ce5bdee03dddc1d7146b', NULL, NULL, 1, '2026-07-20 12:50:29'),
(67, 1, 'AUTENTICACION', 'LOGIN', 'usuarios', 1, 'Inicio de sesión exitoso.', '127.0.0.1', 'CLI', 'INFO', 'OK', NULL, '[]', '[]', '5b230c63451cff3b5ce4c7794ace6aa3', '8941ee6a76024f5de9a3b173a9ef11d17f7371e78811ce5bdee03dddc1d7146b', '7e94b2884b07f8f73e3f29054e216a2aeb3f69119940081aeaba96fb487f5ae7', NULL, NULL, 1, '2026-07-20 13:07:54'),
(68, 1, 'API_CATEGORIAS', 'POST', 'categorias', 26, 'Categoria #26 creada desde API.', '127.0.0.1', 'CLI', 'INFO', 'OK', NULL, '[]', '{\"nombre\":\"API Smoke 1784552874\",\"tipo\":\"SOFTWARE\",\"descripcion\":\"Smoke API desde script local\",\"activo\":1}', '4b30cea431ab92fdcd8148945b3543f7', '7e94b2884b07f8f73e3f29054e216a2aeb3f69119940081aeaba96fb487f5ae7', '0b3348d87b1b1bba7142e8ba981f8b4a41c485bffafa1b61b878bd69903a74c6', NULL, NULL, 1, '2026-07-20 13:07:54'),
(69, 1, 'API_CATEGORIAS', 'PUT', 'categorias', 26, 'Categoria #26 actualizada desde API.', '127.0.0.1', 'CLI', 'INFO', 'OK', NULL, '{\"id\":26,\"nombre\":\"API Smoke 1784552874\",\"tipo\":\"SOFTWARE\",\"descripcion\":\"Smoke API desde script local\",\"activo\":1,\"created_at\":\"2026-07-20 08:07:54\",\"updated_at\":\"2026-07-20 08:07:54\"}', '{\"nombre\":\"API Smoke 1784552874 Actualizada\",\"tipo\":\"SOFTWARE\",\"descripcion\":\"Actualizada por PUT\",\"activo\":1}', 'ec85a1fc142d98391af8bdde99f322cc', '0b3348d87b1b1bba7142e8ba981f8b4a41c485bffafa1b61b878bd69903a74c6', 'a2658abfab63cfd88c494aa9b9a9dbfb20efeced114fe55357da028c4f212f0a', NULL, NULL, 1, '2026-07-20 13:07:54'),
(70, 1, 'API_CATEGORIAS', 'DELETE', 'categorias', 26, 'Categoria #26 dada de baja desde API.', '127.0.0.1', 'CLI', 'INFO', 'OK', NULL, '{\"id\":26,\"nombre\":\"API Smoke 1784552874 Actualizada\",\"tipo\":\"SOFTWARE\",\"descripcion\":\"Actualizada por PUT\",\"activo\":1,\"created_at\":\"2026-07-20 08:07:54\",\"updated_at\":\"2026-07-20 08:07:54\"}', '{\"activo\":0}', 'f59d2e2f9f80b9d75b60926dcec1483a', 'a2658abfab63cfd88c494aa9b9a9dbfb20efeced114fe55357da028c4f212f0a', 'd35c993819f58655a796da5d63356c1d9db0086c3dfc3438ea95a017fbc6d48d', NULL, NULL, 1, '2026-07-20 13:07:54'),
(71, 1, 'AUTENTICACION', 'LOGIN', 'usuarios', 1, 'Inicio de sesión exitoso.', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'INFO', 'OK', NULL, '[]', '[]', 'ed4739b14f5fd05bbe4bf936522c913f', 'd35c993819f58655a796da5d63356c1d9db0086c3dfc3438ea95a017fbc6d48d', '0ec03573d876c6dbaefe5114deced0fc97f99122a521c0fc58a59ee6a5e45fc8', NULL, NULL, 1, '2026-07-20 13:20:19'),
(72, 1, 'ASIGNACIONES', 'ASIGNAR', 'asignaciones', NULL, 'Solicitud de asignación para activo #14.', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'INFO', 'OK', NULL, '[]', '{\"inventario_id\":14,\"colaborador_id\":10,\"fecha_asignacion\":\"2026-07-20\",\"ip_asignada\":\"234.443.43\",\"observaciones\":\"Hola\"}', 'f5c3a711204f99a3a25f3f64749d3aa6', '0ec03573d876c6dbaefe5114deced0fc97f99122a521c0fc58a59ee6a5e45fc8', '805fba30273de4f1d604426deffa0a099914271023ff4dbf0b659c7c567dffdc', NULL, NULL, 1, '2026-07-20 13:21:48'),
(73, 1, 'AUTENTICACION', 'LOGOUT', 'usuarios', 1, 'Cierre de sesión.', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'INFO', 'OK', NULL, '[]', '[]', 'e5d6d0bcc699b9a2931588eff0e5a7dd', '805fba30273de4f1d604426deffa0a099914271023ff4dbf0b659c7c567dffdc', '10d896a503546d5cc6ebb5ae69a68b1c30423eb9536246efe77636704b8dba9c', NULL, NULL, 1, '2026-07-20 13:23:50'),
(74, 1, 'API_CATEGORIAS', 'POST', 'categorias', 27, 'Categoria #27 creada desde API.', '127.0.0.1', 'CLI', 'INFO', 'OK', NULL, '[]', '{\"nombre\":\"CRUD Basico 1784553870\",\"tipo\":\"SOFTWARE\",\"descripcion\":\"Creada desde prueba CRUD basica\",\"activo\":1}', 'a9e94c0fb8442b069ca717271aaed7eb', '10d896a503546d5cc6ebb5ae69a68b1c30423eb9536246efe77636704b8dba9c', 'f58bb70a93e17c1d1090515984c7843a0177970df55411a43ef260b2c9892831', NULL, NULL, 1, '2026-07-20 13:24:30'),
(75, 1, 'API_CATEGORIAS', 'PUT', 'categorias', 27, 'Categoria #27 actualizada desde API.', '127.0.0.1', 'CLI', 'INFO', 'OK', NULL, '{\"id\":27,\"nombre\":\"CRUD Basico 1784553870\",\"tipo\":\"SOFTWARE\",\"descripcion\":\"Creada desde prueba CRUD basica\",\"activo\":1,\"created_at\":\"2026-07-20 08:24:30\",\"updated_at\":\"2026-07-20 08:24:30\"}', '{\"nombre\":\"CRUD Basico 1784553870 Actualizada\",\"tipo\":\"SOFTWARE\",\"descripcion\":\"Actualizada desde prueba CRUD basica\",\"activo\":1}', 'ddbf4da39a21ab4a4df02cef2d78157a', 'f58bb70a93e17c1d1090515984c7843a0177970df55411a43ef260b2c9892831', 'af9979fae60eac7f37ea399587ef95fe093d29cbb8450f2c0bc1a05b3b3bd485', NULL, NULL, 1, '2026-07-20 13:24:30'),
(76, 1, 'API_CATEGORIAS', 'DELETE', 'categorias', 27, 'Categoria #27 dada de baja desde API.', '127.0.0.1', 'CLI', 'INFO', 'OK', NULL, '{\"id\":27,\"nombre\":\"CRUD Basico 1784553870 Actualizada\",\"tipo\":\"SOFTWARE\",\"descripcion\":\"Actualizada desde prueba CRUD basica\",\"activo\":1,\"created_at\":\"2026-07-20 08:24:30\",\"updated_at\":\"2026-07-20 08:24:30\"}', '{\"activo\":0}', '641dca13c65d70f7ba07e2a6b77589c9', 'af9979fae60eac7f37ea399587ef95fe093d29cbb8450f2c0bc1a05b3b3bd485', 'b2da9ee35e1c46c7e112693c2d7d28d2d1c8e811821fb10b3364c7c79aa970cc', NULL, NULL, 1, '2026-07-20 13:24:31'),
(77, 1, 'AUTENTICACION', 'LOGIN', 'usuarios', 1, 'Inicio de sesión exitoso.', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'INFO', 'OK', NULL, '[]', '[]', '6a6accbaf5063b4299fb8d28aec09eb4', 'b2da9ee35e1c46c7e112693c2d7d28d2d1c8e811821fb10b3364c7c79aa970cc', '53c29c028bd2fa22f939f051fd33112fda3062f47ada8e47044426911fa05e08', NULL, NULL, 1, '2026-07-20 13:42:31'),
(78, 1, 'API_CATEGORIAS', 'POST', 'categorias', 28, 'Categoria #28 creada desde API.', '127.0.0.1', 'CLI', 'INFO', 'OK', NULL, '[]', '{\"nombre\":\"Categoria Bearer 20260720134508\",\"tipo\":\"SOFTWARE\",\"descripcion\":\"Creada en prueba automatica Bearer\",\"activo\":1}', 'f8b929568018053d9a5bdbcb56756924', '53c29c028bd2fa22f939f051fd33112fda3062f47ada8e47044426911fa05e08', '22482484a817d7e894466b6b67856420433c80135a7582fd142bfd2ce9aac38c', NULL, NULL, 1, '2026-07-20 13:45:08'),
(79, 1, 'API_CATEGORIAS', 'PUT', 'categorias', 28, 'Categoria #28 actualizada desde API.', '127.0.0.1', 'CLI', 'INFO', 'OK', NULL, '{\"id\":28,\"nombre\":\"Categoria Bearer 20260720134508\",\"tipo\":\"SOFTWARE\",\"descripcion\":\"Creada en prueba automatica Bearer\",\"activo\":1,\"created_at\":\"2026-07-20 08:45:08\",\"updated_at\":\"2026-07-20 08:45:08\"}', '{\"nombre\":\"Categoria Bearer 20260720134508 Actualizada\",\"tipo\":\"SOFTWARE\",\"descripcion\":\"Actualizada en prueba automatica Bearer\",\"activo\":1}', '2b5556b48f3f47c2be30dcc44800dd4c', '22482484a817d7e894466b6b67856420433c80135a7582fd142bfd2ce9aac38c', '93398c92e13eb692eacb1563172677ea8d99654cf4b89df38789e2a0499e7e62', NULL, NULL, 1, '2026-07-20 13:45:08'),
(80, 1, 'API_CATEGORIAS', 'DELETE', 'categorias', 28, 'Categoria #28 dada de baja desde API.', '127.0.0.1', 'CLI', 'INFO', 'OK', NULL, '{\"id\":28,\"nombre\":\"Categoria Bearer 20260720134508 Actualizada\",\"tipo\":\"SOFTWARE\",\"descripcion\":\"Actualizada en prueba automatica Bearer\",\"activo\":1,\"created_at\":\"2026-07-20 08:45:08\",\"updated_at\":\"2026-07-20 08:45:08\"}', '{\"activo\":0}', '0b42f594017fc620983d310ef57dbb87', '93398c92e13eb692eacb1563172677ea8d99654cf4b89df38789e2a0499e7e62', '4674e950480eaf12aeaa5b808f25f18cd88272d2889f5ad29284ef2b99265f2a', NULL, NULL, 1, '2026-07-20 13:45:09'),
(81, 1, 'AUTENTICACION', 'LOGIN', 'usuarios', 1, 'Inicio de sesión exitoso.', '127.0.0.1', 'CLI', 'INFO', 'OK', NULL, '[]', '[]', 'e99f7c4695b9aa7bb75299eaee433876', '4674e950480eaf12aeaa5b808f25f18cd88272d2889f5ad29284ef2b99265f2a', 'a9854de417ab488cacf6e4afddc413a6e232f9ea8b7d1f4470c00b1d263cc943', NULL, NULL, 1, '2026-07-20 13:54:46'),
(82, 1, 'COLABORADORES', 'CREAR', 'colaboradores', 25, 'Colaborador #25 registrado.', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'INFO', 'OK', NULL, '[]', '{\"nombres\":\"Jorge\",\"apellidos\":\"Osorio\",\"identificacion\":\"3-434-544\",\"departamento\":\"Admisiones\",\"ubicacion\":\"Edificio 232\",\"direccion\":\"Buena vista\",\"telefono\":\"6545-5453\",\"email\":\"jorge@gmail.com\",\"activo\":1,\"foto\":null}', 'fd36cba9f80aa6657abc4354878b38f5', 'a9854de417ab488cacf6e4afddc413a6e232f9ea8b7d1f4470c00b1d263cc943', '34865caece2001bf70ee0eb7ebc36d7db74bb3724ebd6e00269ff945d7f95f25', NULL, NULL, 1, '2026-07-20 14:02:10'),
(83, 1, 'REPORTES', 'EXPORTAR_INVENTARIO', NULL, NULL, 'CMDB - Reporte de inventario. Filtros: Sin filtros', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'INFO', 'OK', NULL, '[]', '[]', '5d7fad205e63a9f86b2fa4bbe6eb7f95', '34865caece2001bf70ee0eb7ebc36d7db74bb3724ebd6e00269ff945d7f95f25', '66e4efbd2ffc40092004cd32a89367dffd1385516bb77b0aefa1759c46456572', NULL, NULL, 1, '2026-07-20 14:04:18'),
(84, 1, 'REPORTES', 'EXPORTAR_ASIGNACIONES', NULL, NULL, 'CMDB - Reporte de responsables de equipos. Filtros: Sin filtros', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'INFO', 'OK', NULL, '[]', '[]', '7469bc6cb40b7bb6d46ac7c383b927bb', '66e4efbd2ffc40092004cd32a89367dffd1385516bb77b0aefa1759c46456572', 'fd6011ca47db8fde92de3d2a2acb4d9b2fb0bc085fd589e91ca0db66e948341e', NULL, NULL, 1, '2026-07-20 14:04:22'),
(85, 1, 'REPORTES', 'EXPORTAR_DISPONIBLES', NULL, NULL, 'CMDB - Reporte de activos disponibles. Filtros: Sin filtros', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'INFO', 'OK', NULL, '[]', '[]', '0061b82f9e00d41aa96a693da3836bba', 'fd6011ca47db8fde92de3d2a2acb4d9b2fb0bc085fd589e91ca0db66e948341e', '020c3422b994d1626008efa52e609288150a02a1795e4d303150f4cf8205d35f', NULL, NULL, 1, '2026-07-20 14:04:24'),
(86, NULL, 'QR', 'ACCESO_PUBLICO', 'inventario', 31, 'Consulta pública QR del activo #31.', '127.0.0.1', 'CLI', 'INFO', 'OK', NULL, '[]', '{\"codigo_activo\":\"PM-LIC-POSTMAN\",\"publico_limitado\":true}', 'e64ec8520692b655473982ec03bfc7cc', '020c3422b994d1626008efa52e609288150a02a1795e4d303150f4cf8205d35f', '70d4d2c4076970075336834ab58b036f624bcb936e77278508756d03c6c20da4', NULL, NULL, 1, '2026-07-20 14:06:35'),
(87, 1, 'AUTENTICACION', 'LOGIN', 'usuarios', 1, 'Inicio de sesión exitoso.', '127.0.0.1', 'CLI', 'INFO', 'OK', NULL, '[]', '[]', '7348aec28a11551cf2d259b26fcf536a', '70d4d2c4076970075336834ab58b036f624bcb936e77278508756d03c6c20da4', '2f1827ed612de8e0d98dcd93fdde1b25258d758765b49eafe05db144dd60ec5a', NULL, NULL, 1, '2026-07-20 14:06:36'),
(88, 1, 'COLABORADORES', 'CREAR', 'colaboradores', 30, 'Colaborador #30 registrado.', '127.0.0.1', 'CLI', 'INFO', 'OK', NULL, '[]', '{\"nombres\":\"Smoke\",\"apellidos\":\"Colaborador\",\"identificacion\":\"HTTP_SMOKE_20260720140636_981c71\",\"departamento\":\"Tecnología\",\"ubicacion\":\"Lab HTTP\",\"direccion\":\"Temporal\",\"telefono\":\"6000-9090\",\"email\":\"http_smoke_20260720140636_981c71@cmdb.local\",\"activo\":1,\"foto\":null}', '8b4ca5519f3139c7d93e3b9493f11d87', '2f1827ed612de8e0d98dcd93fdde1b25258d758765b49eafe05db144dd60ec5a', 'e78bcc09258cd7027cde974470853aef27e529de305edc938cbfed0b46d09ea3', NULL, NULL, 1, '2026-07-20 14:06:37'),
(89, 1, 'REPORTES', 'EXPORTAR_INVENTARIO', NULL, NULL, 'CMDB - Reporte de inventario. Filtros: Sin filtros', '127.0.0.1', 'CLI', 'INFO', 'OK', NULL, '[]', '[]', '69652e28b911cc6c2d7703864fedf834', 'e78bcc09258cd7027cde974470853aef27e529de305edc938cbfed0b46d09ea3', '9b3cb8dad82ff11601b38a6f2640d78a58727e3ec78891e1adb467e8a941d4d6', NULL, NULL, 1, '2026-07-20 14:06:37'),
(90, 1, 'REPORTES', 'EXPORTAR_ASIGNACIONES', NULL, NULL, 'CMDB - Reporte de responsables de equipos. Filtros: Sin filtros', '127.0.0.1', 'CLI', 'INFO', 'OK', NULL, '[]', '[]', '3de0a297a8fe078605a918b0f77e0402', '9b3cb8dad82ff11601b38a6f2640d78a58727e3ec78891e1adb467e8a941d4d6', '44927212bae4d69912c9be232f63f80d6b4d5680362748a096f1a31fbd2e4231', NULL, NULL, 1, '2026-07-20 14:06:37'),
(91, 1, 'REPORTES', 'EXPORTAR_DISPONIBLES', NULL, NULL, 'CMDB - Reporte de activos disponibles. Filtros: Sin filtros', '127.0.0.1', 'CLI', 'INFO', 'OK', NULL, '[]', '[]', '3682530fcaac90019b51667919361e27', '44927212bae4d69912c9be232f63f80d6b4d5680362748a096f1a31fbd2e4231', '0c24d7a2b2e05735f1f363db9f1556d7aadc905f149731be8b7b32d87784e323', NULL, NULL, 1, '2026-07-20 14:06:37'),
(92, 1, 'REPORTES', 'EXPORTAR_CATEGORIAS', NULL, NULL, 'CMDB - Reporte por categoría. Filtros: Sin filtros', '127.0.0.1', 'CLI', 'INFO', 'OK', NULL, '[]', '[]', '426d523b1928122b69999995454ac549', '0c24d7a2b2e05735f1f363db9f1556d7aadc905f149731be8b7b32d87784e323', '10a1464b7a8e3a098add91cab8213c50531477e63e8778f2d7f5a6979beb0180', NULL, NULL, 1, '2026-07-20 14:06:37'),
(93, 1, 'REPORTES', 'EXPORTAR_ASIGNADOS_CATEGORIA', NULL, NULL, 'CMDB - Reporte de asignados por categoría. Filtros: Sin filtros', '127.0.0.1', 'CLI', 'INFO', 'OK', NULL, '[]', '[]', 'bc607671a81799f3dafb0be826f19788', '10a1464b7a8e3a098add91cab8213c50531477e63e8778f2d7f5a6979beb0180', '79ff079f14cabe39e7c7dee3b2a296c891942b59773bde3c0c087c405d5e3262', NULL, NULL, 1, '2026-07-20 14:06:37'),
(94, 1, 'REPORTES', 'EXPORTAR_REPARACION', NULL, NULL, 'CMDB - Reporte de reparación. Filtros: Sin filtros', '127.0.0.1', 'CLI', 'INFO', 'OK', NULL, '[]', '[]', 'a988a00fbd84bbdfb35f862330099018', '79ff079f14cabe39e7c7dee3b2a296c891942b59773bde3c0c087c405d5e3262', '28215fa5ec4b3c394007a2df21be80481e85ececda319fb95c18eb128f689510', NULL, NULL, 1, '2026-07-20 14:06:38'),
(95, 1, 'REPORTES', 'EXPORTAR_DONACIONES', NULL, NULL, 'CMDB - Reporte de donaciones. Filtros: Sin filtros', '127.0.0.1', 'CLI', 'INFO', 'OK', NULL, '[]', '[]', '25f1ff90e938e91687440252f4f33aa6', '28215fa5ec4b3c394007a2df21be80481e85ececda319fb95c18eb128f689510', '0eaed240ff7ba7de35980c979f278474012a1c120784365ca89c1fbf59f8e9e0', NULL, NULL, 1, '2026-07-20 14:06:38'),
(96, 1, 'REPORTES', 'EXPORTAR_DESCARTES', NULL, NULL, 'CMDB - Reporte de descartes. Filtros: Sin filtros', '127.0.0.1', 'CLI', 'INFO', 'OK', NULL, '[]', '[]', '5242c0c27c1847424de8f1b64cf92df1', '0eaed240ff7ba7de35980c979f278474012a1c120784365ca89c1fbf59f8e9e0', '25d00fd24787955b84a8e7954934754859bfa02cc2ec2b6155af8b972a2ffb15', NULL, NULL, 1, '2026-07-20 14:06:38'),
(97, 1, 'REPORTES', 'EXPORTAR_LICENCIAS', NULL, NULL, 'CMDB - Reporte de licencias. Filtros: Sin filtros', '127.0.0.1', 'CLI', 'INFO', 'OK', NULL, '[]', '[]', '386a83a8d107b6f9a4d96a455a8836b3', '25d00fd24787955b84a8e7954934754859bfa02cc2ec2b6155af8b972a2ffb15', '35208ce1f698835a9e2bc51a81ae9718dceca5e884bd1f5a36c7d5cb5fcad4f8', NULL, NULL, 1, '2026-07-20 14:06:38'),
(98, 1, 'REPORTES', 'EXPORTAR_CUPOS_LICENCIAS', NULL, NULL, 'CMDB - Reporte de cupos de licencias. Filtros: Sin filtros', '127.0.0.1', 'CLI', 'INFO', 'OK', NULL, '[]', '[]', '045c37a1e247aa340e95e2d738c4f490', '35208ce1f698835a9e2bc51a81ae9718dceca5e884bd1f5a36c7d5cb5fcad4f8', '1115163390dff7751abe4e38a174d8c0c3ac3ce03cb549d4885dd99fe79fec52', NULL, NULL, 1, '2026-07-20 14:06:38'),
(99, 1, 'REPORTES', 'EXPORTAR_VENCIMIENTOS', NULL, NULL, 'CMDB - Reporte de vencimientos. Filtros: Sin filtros', '127.0.0.1', 'CLI', 'INFO', 'OK', NULL, '[]', '[]', 'd0137a56f15112d2edce59494f39c944', '1115163390dff7751abe4e38a174d8c0c3ac3ce03cb549d4885dd99fe79fec52', '1991a64582aa41c5e494d3a0f1b3d826743f86eade20cc0f98052fafbb43fab9', NULL, NULL, 1, '2026-07-20 14:06:38'),
(100, 1, 'REPORTES', 'EXPORTAR_DEPRECIACION', NULL, NULL, 'CMDB - Reporte de depreciación. Filtros: Sin filtros', '127.0.0.1', 'CLI', 'INFO', 'OK', NULL, '[]', '[]', 'b996f21b96458c9500c2cbe08253c37c', '1991a64582aa41c5e494d3a0f1b3d826743f86eade20cc0f98052fafbb43fab9', '707f27b9803b7b56a7d8c9729c9f14c768ca865a6fe35caf166c75225cc4c340', NULL, NULL, 1, '2026-07-20 14:06:38'),
(101, 1, 'REPORTES', 'EXPORTAR_SOLICITUDES', NULL, NULL, 'CMDB - Reporte de solicitudes. Filtros: Sin filtros', '127.0.0.1', 'CLI', 'INFO', 'OK', NULL, '[]', '[]', 'c50f2308430b0eb0b0ab37d86624611e', '707f27b9803b7b56a7d8c9729c9f14c768ca865a6fe35caf166c75225cc4c340', 'c68c6987bf98b26a9efec7b00238b1072bea97410e7d76b4275076f9679dbd25', NULL, NULL, 1, '2026-07-20 14:06:38'),
(102, 1, 'REPORTES', 'EXPORTAR_DEVOLUCIONES', NULL, NULL, 'CMDB - Reporte de devoluciones. Filtros: Sin filtros', '127.0.0.1', 'CLI', 'INFO', 'OK', NULL, '[]', '[]', '0795078473203030732c38a57ebdfa1c', 'c68c6987bf98b26a9efec7b00238b1072bea97410e7d76b4275076f9679dbd25', '6c7ba57dfb5b37b4f6a7d575b2302391eb1655f6220bb6d10b6d6fb355598d6c', NULL, NULL, 1, '2026-07-20 14:06:38'),
(103, 1, 'REPORTES', 'EXPORTAR_REVISIONES', NULL, NULL, 'CMDB - Reporte de revisiones técnicas. Filtros: Sin filtros', '127.0.0.1', 'CLI', 'INFO', 'OK', NULL, '[]', '[]', '9f136b50827f6b822dab55799de75780', '6c7ba57dfb5b37b4f6a7d575b2302391eb1655f6220bb6d10b6d6fb355598d6c', '86007419c28e867f0eeb64bf94f5d87094e7fd539cb36ee6a54ed46e365730c5', NULL, NULL, 1, '2026-07-20 14:06:38'),
(104, 1, 'REPORTES', 'EXPORTAR_HISTORIAL_ESTADOS', NULL, NULL, 'CMDB - Reporte de historial de estados. Filtros: Sin filtros', '127.0.0.1', 'CLI', 'INFO', 'OK', NULL, '[]', '[]', '40b31b17de2b2d60bb4182716277aee7', '86007419c28e867f0eeb64bf94f5d87094e7fd539cb36ee6a54ed46e365730c5', 'a50359a94e9b759845eb669a32df8fa64c67bd8afd67b7107df6e9fd00e35e61', NULL, NULL, 1, '2026-07-20 14:06:38'),
(105, 1, 'PRESUPUESTO', 'EXPORTAR', 'presupuestos', 1, 'Presupuesto #1 exportado a Excel.', '127.0.0.1', 'CLI', 'INFO', 'EXPORTADO', NULL, '[]', '[]', '3081dcebff8ceb8ea8e0cff15165388a', 'a50359a94e9b759845eb669a32df8fa64c67bd8afd67b7107df6e9fd00e35e61', '2177e55e2c519e9a036adc43c94d984ebb7f3b0f781f93b7160db52d8645708a', NULL, NULL, 1, '2026-07-20 14:06:38'),
(106, 1, 'API_CATEGORIAS', 'POST', 'categorias', 33, 'Categoria #33 creada desde API.', '127.0.0.1', 'CLI', 'INFO', 'OK', NULL, '[]', '{\"nombre\":\"HTTP_SMOKE_20260720140636_981c71_Categoria\",\"tipo\":\"SOFTWARE\",\"descripcion\":\"Categoria temporal HTTP smoke\",\"activo\":1}', '7e8a5046118265108279178803e31585', '2177e55e2c519e9a036adc43c94d984ebb7f3b0f781f93b7160db52d8645708a', 'fcb91fd0b66f45781c1daa670cb8dfa921e9c84ebb56afb2fb1ea48d7f0fbd09', NULL, NULL, 1, '2026-07-20 14:06:39');
INSERT INTO `bitacora` (`id`, `usuario_id`, `modulo`, `accion`, `entidad`, `entidad_id`, `descripcion`, `ip`, `user_agent`, `nivel`, `resultado`, `motivo`, `datos_anteriores_json`, `datos_posteriores_json`, `correlation_id`, `previous_hash`, `record_hash`, `firma_id`, `fingerprint`, `payload_version`, `created_at`) VALUES
(107, 1, 'API_CATEGORIAS', 'PUT', 'categorias', 33, 'Categoria #33 actualizada desde API.', '127.0.0.1', 'CLI', 'INFO', 'OK', NULL, '{\"id\":33,\"nombre\":\"HTTP_SMOKE_20260720140636_981c71_Categoria\",\"tipo\":\"SOFTWARE\",\"descripcion\":\"Categoria temporal HTTP smoke\",\"activo\":1,\"created_at\":\"2026-07-20 09:06:39\",\"updated_at\":\"2026-07-20 09:06:39\"}', '{\"nombre\":\"HTTP_SMOKE_20260720140636_981c71_Categoria_Actualizada\",\"tipo\":\"SOFTWARE\",\"descripcion\":\"Categoria temporal HTTP smoke actualizada\",\"activo\":1}', 'b1234e2beb3d79241967922270bd5f7a', 'fcb91fd0b66f45781c1daa670cb8dfa921e9c84ebb56afb2fb1ea48d7f0fbd09', 'f0a60eda95c1753762cc56b4ba43c90dcb6b85c182bc9dafbedc177477f3be83', NULL, NULL, 1, '2026-07-20 14:06:39'),
(108, 1, 'API_CATEGORIAS', 'DELETE', 'categorias', 33, 'Categoria #33 dada de baja desde API.', '127.0.0.1', 'CLI', 'INFO', 'OK', NULL, '{\"id\":33,\"nombre\":\"HTTP_SMOKE_20260720140636_981c71_Categoria_Actualizada\",\"tipo\":\"SOFTWARE\",\"descripcion\":\"Categoria temporal HTTP smoke actualizada\",\"activo\":1,\"created_at\":\"2026-07-20 09:06:39\",\"updated_at\":\"2026-07-20 09:06:39\"}', '{\"activo\":0}', '5eb46b08d6262b26ad832f690757fe3a', 'f0a60eda95c1753762cc56b4ba43c90dcb6b85c182bc9dafbedc177477f3be83', '3ea92f6ec506e16d3b7c0f1e1a098a21bef0937c2c5d43e2ebd8f2d568ae6e27', NULL, NULL, 1, '2026-07-20 14:06:39'),
(109, 3, 'AUTENTICACION', 'LOGIN', 'usuarios', 3, 'Inicio de sesión exitoso.', '127.0.0.1', 'CLI', 'INFO', 'OK', NULL, '[]', '[]', '078c7ba2072131b14783be7e299f7a36', '3ea92f6ec506e16d3b7c0f1e1a098a21bef0937c2c5d43e2ebd8f2d568ae6e27', '7d5b0f022cd6ac8a00159c51e3ede5914d9fea92c1dcf5e3aabd5a09386261d7', NULL, NULL, 1, '2026-07-20 14:06:39'),
(110, 1, 'AUTENTICACION', 'LOGOUT', 'usuarios', 1, 'Cierre de sesión.', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'INFO', 'OK', NULL, '[]', '[]', '46064bc0ddfc9182e417cc453daaba48', '7d5b0f022cd6ac8a00159c51e3ede5914d9fea92c1dcf5e3aabd5a09386261d7', '1b5fab3a570516d587dac459628785a491fa74e931227e17c3d5c01564a0089b', NULL, NULL, 1, '2026-07-20 14:17:39'),
(111, 1, 'AUTENTICACION', 'LOGIN', 'usuarios', 1, 'Inicio de sesión exitoso.', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'INFO', 'OK', NULL, '[]', '[]', 'b3d6fc5861e5cb6d069450d76fdeaab2', '1b5fab3a570516d587dac459628785a491fa74e931227e17c3d5c01564a0089b', '2d1eec71a27f1fff9a251f712732fdcac6a92990526360034fed20b4acdf82ee', NULL, NULL, 1, '2026-07-20 14:17:49'),
(112, 1, 'AUTENTICACION', 'LOGOUT', 'usuarios', 1, 'Cierre de sesión.', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'INFO', 'OK', NULL, '[]', '[]', 'f7ae0360b7ac64deca4216a58b63564b', '2d1eec71a27f1fff9a251f712732fdcac6a92990526360034fed20b4acdf82ee', 'fe3cb89563a8e0de6c9dfd5c0925e208d87c529e0753e5c229fff9f8f21c49b8', NULL, NULL, 1, '2026-07-20 14:17:59'),
(113, 3, 'AUTENTICACION', 'LOGIN', 'usuarios', 3, 'Inicio de sesión exitoso.', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'INFO', 'OK', NULL, '[]', '[]', 'e5821058e131e2ee0e67b169220acb1b', 'fe3cb89563a8e0de6c9dfd5c0925e208d87c529e0753e5c229fff9f8f21c49b8', 'b12fa9bbf3b0036f324865148faa3ea76a93bcc29d792b99ab4324c5ce71e1a3', NULL, NULL, 1, '2026-07-20 14:18:29'),
(114, 3, 'AUTENTICACION', 'LOGOUT', 'usuarios', 3, 'Cierre de sesión.', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'INFO', 'OK', NULL, '[]', '[]', '2de45b7e0a60a336e23e9cef30c95ab3', 'b12fa9bbf3b0036f324865148faa3ea76a93bcc29d792b99ab4324c5ce71e1a3', '2a34f82fd4a5bfc21f0db1ed002b20b0844133fed87503339dcd147ce8cfa28c', NULL, NULL, 1, '2026-07-20 14:23:29'),
(115, 1, 'AUTENTICACION', 'LOGIN', 'usuarios', 1, 'Inicio de sesión exitoso.', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'INFO', 'OK', NULL, '[]', '[]', '064c09460ac54d7042bbd283db00547a', '2a34f82fd4a5bfc21f0db1ed002b20b0844133fed87503339dcd147ce8cfa28c', 'ab51a06348f0ecfa993a14ac37022090e37ec5b5c6783e920947f84262d217ad', NULL, NULL, 1, '2026-07-20 14:48:09'),
(116, 1, 'AUTENTICACION', 'LOGOUT', 'usuarios', 1, 'Cierre de sesión.', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'INFO', 'OK', NULL, '[]', '[]', '3b132c75a1f53cdc858108f41e067271', 'ab51a06348f0ecfa993a14ac37022090e37ec5b5c6783e920947f84262d217ad', '6fcb2e0b9b3bff4d8e441a7d9fe27541b6f1486685b9eb35e41c664d2fbeec2a', NULL, NULL, 1, '2026-07-20 14:51:35'),
(117, 1, 'AUTENTICACION', 'LOGIN', 'usuarios', 1, 'Inicio de sesión exitoso.', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'INFO', 'OK', NULL, '[]', '[]', '5f4f1ee07f4d55b13d8301f3fa491558', '6fcb2e0b9b3bff4d8e441a7d9fe27541b6f1486685b9eb35e41c664d2fbeec2a', '29a6cd71bccc087d04089d31e8b816ae255294aede434b2504f9eb221dd4821b', NULL, NULL, 1, '2026-07-20 14:59:13'),
(118, 1, 'NOTICIAS', 'CREAR', NULL, NULL, 'Noticia #6 creada.', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'INFO', 'OK', NULL, '[]', '[]', '86e24f70c2beb65a21f6db7b6ab01cf6', '29a6cd71bccc087d04089d31e8b816ae255294aede434b2504f9eb221dd4821b', '40fd22566f19bb88053357e6c13b4fa0feef10c309bf75f1d0fd664cead4a67c', NULL, NULL, 1, '2026-07-20 15:00:07'),
(119, 1, 'USUARIOS', 'CREAR', 'usuarios', 24, 'Usuario #24 creado.', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'INFO', 'OK', NULL, '[]', '{\"colaborador_id\":0,\"nombre_usuario\":\"Susy\",\"email\":\"susy@local.com\",\"password\":\"[REDACTED]\",\"rol\":\"OPERADOR\",\"activo\":1}', '48518d35c234fd98614ac8ee35c92ae7', '40fd22566f19bb88053357e6c13b4fa0feef10c309bf75f1d0fd664cead4a67c', '15970e46f5dae655740b9898c39694f40f49610b9a6ea93516ad8701403f85e4', NULL, NULL, 1, '2026-07-20 15:03:07'),
(120, 1, 'AUTENTICACION', 'LOGOUT', 'usuarios', 1, 'Cierre de sesión.', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'INFO', 'OK', NULL, '[]', '[]', 'e427c72c710c148f9f3bc28dc7951435', '15970e46f5dae655740b9898c39694f40f49610b9a6ea93516ad8701403f85e4', '5fd94117e0351d52a1e9523ad876b09ec7e978138394994e2783a23af3ea426a', NULL, NULL, 1, '2026-07-20 15:03:17'),
(121, 24, 'AUTENTICACION', 'LOGIN', 'usuarios', 24, 'Inicio de sesión exitoso.', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'INFO', 'OK', NULL, '[]', '[]', '3ba595ff563a9504a4d46f8719005e4e', '5fd94117e0351d52a1e9523ad876b09ec7e978138394994e2783a23af3ea426a', '346051bc48c1523180b387315a1081ea8d0a8a746be6d0860d69e846369059bb', NULL, NULL, 1, '2026-07-20 15:03:27'),
(122, 24, 'AUTENTICACION', 'LOGOUT', 'usuarios', 24, 'Cierre de sesión.', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'INFO', 'OK', NULL, '[]', '[]', 'da9afd20b58947b757e2d62b088040dd', '346051bc48c1523180b387315a1081ea8d0a8a746be6d0860d69e846369059bb', 'a33df4fdeb8d239c091aceebed57d9f3078e46abe223341f541385228202f8f6', NULL, NULL, 1, '2026-07-20 15:03:52'),
(123, 3, 'AUTENTICACION', 'LOGIN', 'usuarios', 3, 'Inicio de sesión exitoso.', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'INFO', 'OK', NULL, '[]', '[]', '762e5266f043cca3f07f83fed0673bcd', 'a33df4fdeb8d239c091aceebed57d9f3078e46abe223341f541385228202f8f6', '69cd3f7034541ed4fa4f7d47726a0989b8745c6c9ee390d272d584ff3d4d1672', NULL, NULL, 1, '2026-07-20 15:04:02'),
(124, 3, 'AUTENTICACION', 'LOGOUT', 'usuarios', 3, 'Cierre de sesión.', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'INFO', 'OK', NULL, '[]', '[]', 'd4ed7ff8a8a2c303ddc319996c281bf6', '69cd3f7034541ed4fa4f7d47726a0989b8745c6c9ee390d272d584ff3d4d1672', '067d649ff2b784f9009b9919cb7e58040abcabbfa894d5bbbe52e93cd7646a3a', NULL, NULL, 1, '2026-07-20 15:04:53'),
(125, 2, 'AUTENTICACION', 'LOGIN', 'usuarios', 2, 'Inicio de sesión exitoso.', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'INFO', 'OK', NULL, '[]', '[]', '814d85b09e7c07f76c4ed7c6b2606c68', '067d649ff2b784f9009b9919cb7e58040abcabbfa894d5bbbe52e93cd7646a3a', '3c28f56d0a31d43617e776a7884c966486e4245e4171eb4f13330553c58d1830', NULL, NULL, 1, '2026-07-20 15:05:01'),
(126, 2, 'AUTENTICACION', 'LOGOUT', 'usuarios', 2, 'Cierre de sesión.', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'INFO', 'OK', NULL, '[]', '[]', '5cf6b6095af26619eb2a979e6d0e72d6', '3c28f56d0a31d43617e776a7884c966486e4245e4171eb4f13330553c58d1830', '8ff24169964896497982f477deb3217ba8051bf9658fffd3281d4bd752f1c9db', NULL, NULL, 1, '2026-07-20 15:05:33'),
(127, 1, 'AUTENTICACION', 'LOGIN', 'usuarios', 1, 'Inicio de sesión exitoso.', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'INFO', 'OK', NULL, '[]', '[]', '3f69f19a3db0369b0263bebddcf03b89', '8ff24169964896497982f477deb3217ba8051bf9658fffd3281d4bd752f1c9db', '90c391ebe03be2520863ff3d89367fd96126c2721c8787b1f79bcc46d9eb800b', NULL, NULL, 1, '2026-07-20 15:05:36'),
(128, 1, 'INVENTARIO', 'CREAR', 'inventario', 38, 'Activo #38 registrado. Estado HMAC: pendiente de configuración.', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'INFO', 'OK', NULL, '[]', '{\"categoria_id\":4,\"codigo_activo\":\"ACT-0055\",\"nombre\":\"HP-Laptop\",\"tipo_activo\":\"SOFTWARE\",\"subcategoria\":\"Laptop\",\"marca\":\"HP\",\"modelo\":\"AAFEWD94352QE\",\"serie\":\"5663FGHRT\",\"costo\":1900,\"fecha_ingreso\":\"2026-07-20\",\"vida_util_meses\":36,\"estado\":\"DISPONIBLE\",\"es_licencia\":1,\"clave_licencia\":\"\",\"proveedor_licencia\":\"DFJW\",\"tipo_licencia\":\"SEFSFDS\",\"fecha_adquisicion_licencia\":\"2026-07-06\",\"url_licencia\":null,\"fecha_vencimiento_licencia\":\"2026-07-23\",\"observaciones_licencia\":\"FGSTR\",\"estado_licencia\":\"ACTIVA\",\"clave_licencia_cifrada\":\"\",\"clave_licencia_hash\":\"\",\"clave_licencia_algoritmo\":\"\",\"clave_licencia_migrada_at\":\"\",\"cantidad\":40,\"responsable_donacion\":null,\"fecha_donacion\":null,\"beneficiario_donacion\":null,\"evidencia_donacion\":null,\"observacion_donacion\":null,\"observacion_tecnica_descarte\":null,\"evaluador_descarte_id\":null,\"fecha_evaluacion_descarte\":null,\"evidencia_descarte\":null,\"notas\":\"STJSJYW\",\"activo\":1,\"imagen_principal\":\"uploads/equipment/b10ad8c0a255fe61ffd44f63.jpg\",\"thumbnail\":\"uploads/equipment/thumb_b10ad8c0a255fe61ffd44f63.jpg\",\"new_image_path\":\"uploads/equipment/e946fd62538d2d8de791fbfa.jpg\"}', 'beb12f03708fb693cb31bc8e4c834e20', '90c391ebe03be2520863ff3d89367fd96126c2721c8787b1f79bcc46d9eb800b', 'b5b0961a002fba75531e4b068942b958668b02ac0d7c03786afbeaccd105f700', NULL, NULL, 1, '2026-07-20 15:11:14'),
(129, 1, 'QR', 'GENERAR', 'inventario', 33, 'QR del activo #33 generado.', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'INFO', 'OK', NULL, '[]', '{\"qr_id\":6,\"token_hash\":\"[REDACTED]\"}', '856b794ac185cb2eb0e13963e3a0ee5f', 'b5b0961a002fba75531e4b068942b958668b02ac0d7c03786afbeaccd105f700', '80e6331b468efd30904908de0c1aa00e19e3628c88862f2e0dec922e9d238f41', NULL, NULL, 1, '2026-07-20 15:12:58'),
(130, 1, 'QR', 'DESCARGAR', 'inventario', 33, 'QR del activo #33 descargado.', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'INFO', 'OK', NULL, '[]', '[]', '6601bc8f0c8fad343dcd5e27b333dde1', '80e6331b468efd30904908de0c1aa00e19e3628c88862f2e0dec922e9d238f41', 'ba6813c0941aef0a4bbdc3b896364edb13e20278100a26015cc73109c9d4666d', NULL, NULL, 1, '2026-07-20 15:13:03'),
(131, 1, 'LICENCIAS', 'ASIGNAR_CUPO', 'inventario', 38, 'Cupo de licencia asignado desde activo #38.', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'INFO', 'OK', NULL, '[]', '{\"colaborador_id\":1,\"cantidad\":30,\"fecha_asignacion\":\"2026-07-20\"}', 'a05898f8badce25020c6eb5bafd472b8', 'ba6813c0941aef0a4bbdc3b896364edb13e20278100a26015cc73109c9d4666d', '6e688f4a2f12f93ff4084c9128354b0f45e797678b3c30657316785b89aba847', NULL, NULL, 1, '2026-07-20 15:14:22'),
(132, 1, 'INVENTARIO', 'CAMBIAR_ESTADO', 'inventario', 38, 'Activo #38 cambiado a EN_REPARACION.', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'INFO', 'OK', 'Cambio manual de estado', '{\"estado\":\"DISPONIBLE\",\"activo\":{\"id\":38,\"categoria_id\":4,\"codigo_activo\":\"ACT-0055\",\"nombre\":\"HP-Laptop\",\"tipo_activo\":\"SOFTWARE\",\"subcategoria\":\"Laptop\",\"marca\":\"HP\",\"modelo\":\"AAFEWD94352QE\",\"serie\":\"5663FGHRT\",\"costo\":\"1900.00\",\"fecha_ingreso\":\"2026-07-20\",\"vida_util_meses\":36,\"estado\":\"DISPONIBLE\",\"imagen_principal\":\"uploads/equipment/b10ad8c0a255fe61ffd44f63.jpg\",\"thumbnail\":\"uploads/equipment/thumb_b10ad8c0a255fe61ffd44f63.jpg\",\"es_licencia\":1,\"clave_licencia\":\"\",\"clave_licencia_cifrada\":\"\",\"clave_licencia_hash\":\"\",\"clave_licencia_algoritmo\":\"\",\"clave_licencia_migrada_at\":\"\",\"proveedor_licencia\":\"DFJW\",\"tipo_licencia\":\"SEFSFDS\",\"fecha_adquisicion_licencia\":\"2026-07-06\",\"url_licencia\":null,\"fecha_vencimiento_licencia\":\"2026-07-23\",\"estado_licencia\":\"ACTIVA\",\"observaciones_licencia\":\"FGSTR\",\"cantidad\":40,\"responsable_donacion\":null,\"beneficiario_donacion\":null,\"evidencia_donacion\":null,\"observacion_donacion\":null,\"fecha_donacion\":null,\"valor_donacion\":null,\"autorizador_donacion_id\":null,\"observacion_tecnica_descarte\":null,\"evaluador_descarte_id\":null,\"responsable_descarte_id\":null,\"motivo_descarte\":null,\"fecha_evaluacion_descarte\":null,\"evidencia_descarte\":null,\"notas\":\"STJSJYW\",\"firma_integridad\":\"\",\"activo\":1,\"created_at\":\"2026-07-20 10:11:14\",\"updated_at\":\"2026-07-20 10:11:14\",\"categoria_nombre\":\"Equipo de Cómputo\",\"integridad_valida\":false,\"imagenes\":[{\"id\":9,\"inventario_id\":38,\"ruta\":\"uploads/equipment/b10ad8c0a255fe61ffd44f63.jpg\",\"es_principal\":1,\"created_at\":\"2026-07-20 10:11:14\"},{\"id\":10,\"inventario_id\":38,\"ruta\":\"uploads/equipment/e946fd62538d2d8de791fbfa.jpg\",\"es_principal\":0,\"created_at\":\"2026-07-20 10:11:14\"}]}}', '{\"estado\":\"EN_REPARACION\",\"meta\":{\"motivo\":\"Cambio manual de estado\",\"observacion\":\"Error\",\"origen\":\"manual\",\"entidad_origen\":\"inventario\",\"entidad_id\":38}}', '288d888d681b3f8b2771aac50e0eb445', '6e688f4a2f12f93ff4084c9128354b0f45e797678b3c30657316785b89aba847', 'f701a8d64d34ba5abcf5547acd1492ccf732bfeb46e3d87eec9b06b3444181d9', NULL, NULL, 1, '2026-07-20 15:15:14'),
(133, 1, 'AUTENTICACION', 'LOGOUT', 'usuarios', 1, 'Cierre de sesión.', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'INFO', 'OK', NULL, '[]', '[]', 'bc2403583e2b7f1dbc9bf7691849004a', 'f701a8d64d34ba5abcf5547acd1492ccf732bfeb46e3d87eec9b06b3444181d9', '2e1ff5e422dba136a59f5205c3f9a5a345ebc0c1687360685880fb71ec4967df', NULL, NULL, 1, '2026-07-20 15:16:07'),
(134, 3, 'AUTENTICACION', 'LOGIN', 'usuarios', 3, 'Inicio de sesión exitoso.', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'INFO', 'OK', NULL, '[]', '[]', 'f923c79b6ef2b93d870fe3d037d57695', '2e1ff5e422dba136a59f5205c3f9a5a345ebc0c1687360685880fb71ec4967df', '256e79fe03e64b8bd557c60e04ddcb83f78642f598aec8ba0f05347b80b55198', NULL, NULL, 1, '2026-07-20 15:16:12'),
(135, 3, 'AUTENTICACION', 'LOGOUT', 'usuarios', 3, 'Cierre de sesión.', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'INFO', 'OK', NULL, '[]', '[]', '41c8d6d6fa4469dd31a6af486074ee8e', '256e79fe03e64b8bd557c60e04ddcb83f78642f598aec8ba0f05347b80b55198', 'e33e065dbc3c4d4606b9d75394eb1f7a69bc3c6f961aa90f113d82e889ca28af', NULL, NULL, 1, '2026-07-20 15:16:39'),
(136, 1, 'AUTENTICACION', 'LOGIN', 'usuarios', 1, 'Inicio de sesión exitoso.', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'INFO', 'OK', NULL, '[]', '[]', '5f2aa755ec45c6d8dd784993bc49cbb8', 'e33e065dbc3c4d4606b9d75394eb1f7a69bc3c6f961aa90f113d82e889ca28af', '134464956952b62c3b7fe1cef4f40eea86e64b549a83ea6fad3dc1ab5fe0df60', NULL, NULL, 1, '2026-07-20 15:16:45'),
(137, 1, 'CATEGORIAS', 'CREAR', 'categorias', 34, 'Categoría #34 creada.', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'INFO', 'OK', NULL, '[]', '{\"nombre\":\"Redes\",\"tipo\":\"SOFTWARE\",\"descripcion\":\"dasgerhdt\",\"activo\":1}', '46da95125e3adb52d49def340749a292', '134464956952b62c3b7fe1cef4f40eea86e64b549a83ea6fad3dc1ab5fe0df60', 'b5c983361ff71c51d7f8a2712056183b68c35410adeaf3c54649408629db48a0', NULL, NULL, 1, '2026-07-20 15:18:19'),
(138, 1, 'AUTENTICACION', 'LOGOUT', 'usuarios', 1, 'Cierre de sesión.', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'INFO', 'OK', NULL, '[]', '[]', 'e1402059b87818b97da1312a6882b34e', 'b5c983361ff71c51d7f8a2712056183b68c35410adeaf3c54649408629db48a0', 'aba9809722573c1de96886460544f7781b7fa20698a7c94223795a550886af7c', NULL, NULL, 1, '2026-07-20 15:20:26'),
(139, 2, 'AUTENTICACION', 'LOGIN', 'usuarios', 2, 'Inicio de sesión exitoso.', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'INFO', 'OK', NULL, '[]', '[]', '7efd7ced5115583cec39c8ba1a9825c9', 'aba9809722573c1de96886460544f7781b7fa20698a7c94223795a550886af7c', '0f19960da4031935b258103b7ba4263cdae8ec4cd72704e15075d6f3806be1f1', NULL, NULL, 1, '2026-07-20 15:20:31'),
(140, 2, 'AUTENTICACION', 'LOGOUT', 'usuarios', 2, 'Cierre de sesión.', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'INFO', 'OK', NULL, '[]', '[]', '8bf8d2c30e6e02c752f642ff27390c95', '0f19960da4031935b258103b7ba4263cdae8ec4cd72704e15075d6f3806be1f1', '4d32769d8f5473af21abcde27f2204f7fbd1a48dd071e3e50166ca0fd033ba7e', NULL, NULL, 1, '2026-07-20 15:20:46'),
(141, 1, 'AUTENTICACION', 'LOGIN', 'usuarios', 1, 'Inicio de sesión exitoso.', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'INFO', 'OK', NULL, '[]', '[]', '25e52c1e01b14233d9e066bbe251c890', '4d32769d8f5473af21abcde27f2204f7fbd1a48dd071e3e50166ca0fd033ba7e', '17e796aa0854e7c49bc163cc2d6dd173f58db23fc845313e75440b444ed43cbb', NULL, NULL, 1, '2026-07-20 15:20:53'),
(142, 1, 'AUTENTICACION', 'LOGOUT', 'usuarios', 1, 'Cierre de sesión.', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'INFO', 'OK', NULL, '[]', '[]', 'a52e218fa3fe56ca5e2842508675faa6', '17e796aa0854e7c49bc163cc2d6dd173f58db23fc845313e75440b444ed43cbb', '28c1e598e16fa03dafa9ccd533aff67ce38f9c056743f5d66d9dcfc4fab3d5af', NULL, NULL, 1, '2026-07-20 15:23:50'),
(143, 2, 'AUTENTICACION', 'LOGIN', 'usuarios', 2, 'Inicio de sesión exitoso.', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'INFO', 'OK', NULL, '[]', '[]', '523fd276492597d56030b34c9920577e', '28c1e598e16fa03dafa9ccd533aff67ce38f9c056743f5d66d9dcfc4fab3d5af', 'd45d6481515fdbc0fab7c7d94ead62de44a76a4d5344c4b5715e4043f4b90eaa', NULL, NULL, 1, '2026-07-20 15:23:56'),
(144, 2, 'AUTENTICACION', 'LOGOUT', 'usuarios', 2, 'Cierre de sesión.', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'INFO', 'OK', NULL, '[]', '[]', '7d27ce3d41a614a54b13e8e4d580ccd5', 'd45d6481515fdbc0fab7c7d94ead62de44a76a4d5344c4b5715e4043f4b90eaa', '3834cbb4c313d22fc073583ad18d91a65ad66333889d8da686e84a7cc5c3f433', NULL, NULL, 1, '2026-07-20 15:24:36'),
(145, 3, 'AUTENTICACION', 'LOGIN', 'usuarios', 3, 'Inicio de sesión exitoso.', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'INFO', 'OK', NULL, '[]', '[]', 'cc0177435493b083e6d4a7a66b75e8c6', '3834cbb4c313d22fc073583ad18d91a65ad66333889d8da686e84a7cc5c3f433', 'ddfe859145c30ec1cd6e5c0f400120873281a2f99a0d9cffb6fad507c48b864f', NULL, NULL, 1, '2026-07-20 15:24:40'),
(146, 3, 'AUTENTICACION', 'LOGOUT', 'usuarios', 3, 'Cierre de sesión.', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'INFO', 'OK', NULL, '[]', '[]', 'a0d86d665eaa4dc0876410c2ef09a536', 'ddfe859145c30ec1cd6e5c0f400120873281a2f99a0d9cffb6fad507c48b864f', '105cb5f91fa728c159d76b7ef8016bdd151694f4ddbd8db5a8b6446d7e588571', NULL, NULL, 1, '2026-07-20 15:25:10'),
(147, 24, 'AUTENTICACION', 'LOGIN', 'usuarios', 24, 'Inicio de sesión exitoso.', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'INFO', 'OK', NULL, '[]', '[]', '0ae6a76362dc4917859117742467d3cd', '105cb5f91fa728c159d76b7ef8016bdd151694f4ddbd8db5a8b6446d7e588571', 'dfc9b01fe94af3915828126dd969f57efb39a2f59de2af46af2ca6f8edfda880', NULL, NULL, 1, '2026-07-20 15:25:14'),
(148, 24, 'AUTENTICACION', 'LOGOUT', 'usuarios', 24, 'Cierre de sesión.', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'INFO', 'OK', NULL, '[]', '[]', '8b510345c0b76ccf7b89b6483f7a63c5', 'dfc9b01fe94af3915828126dd969f57efb39a2f59de2af46af2ca6f8edfda880', 'a0174e178a32b7af9ac12fdd6937316486a3a077864c6bd19da0653cebf696e4', NULL, NULL, 1, '2026-07-20 15:25:45'),
(149, 1, 'AUTENTICACION', 'LOGIN', 'usuarios', 1, 'Inicio de sesión exitoso.', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'INFO', 'OK', NULL, '[]', '[]', 'aa21e21336560dc4bf50459f43b4b9f4', 'a0174e178a32b7af9ac12fdd6937316486a3a077864c6bd19da0653cebf696e4', '55db42df044538d9e69b2c73af535620a11a8b0d730f90316fb33f84c2b7649b', NULL, NULL, 1, '2026-07-20 15:25:50'),
(150, 1, 'AUTENTICACION', 'LOGOUT', 'usuarios', 1, 'Cierre de sesión.', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'INFO', 'OK', NULL, '[]', '[]', '068c1b4808ee95fb6635d104dcf6b0e9', '55db42df044538d9e69b2c73af535620a11a8b0d730f90316fb33f84c2b7649b', '48fcd30c0893085b5af28bbd89ae9e2c85df5c3946502ba50e19a20fec57b331', NULL, NULL, 1, '2026-07-20 15:26:16'),
(151, 24, 'AUTENTICACION', 'LOGIN', 'usuarios', 24, 'Inicio de sesión exitoso.', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'INFO', 'OK', NULL, '[]', '[]', 'e68def2b805b88f2ca870a4f8f6487a7', '48fcd30c0893085b5af28bbd89ae9e2c85df5c3946502ba50e19a20fec57b331', 'da24edc906fe1ee22dbe7e696e7f2462a07e0eab61448c95b6eab657ff9489bd', NULL, NULL, 1, '2026-07-20 15:26:20'),
(152, 24, 'AUTENTICACION', 'LOGOUT', 'usuarios', 24, 'Cierre de sesión.', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'INFO', 'OK', NULL, '[]', '[]', '31a0322d311c4b39c7996dea2fa2972f', 'da24edc906fe1ee22dbe7e696e7f2462a07e0eab61448c95b6eab657ff9489bd', 'c35a08dcab3fdf02b182e91476b2f0ceff73350cfba2be440b63ca0d7ee72f66', NULL, NULL, 1, '2026-07-20 15:26:27'),
(153, 3, 'AUTENTICACION', 'LOGIN', 'usuarios', 3, 'Inicio de sesión exitoso.', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'INFO', 'OK', NULL, '[]', '[]', '2598e8e2d5b6fb3352baab0bdf467609', 'c35a08dcab3fdf02b182e91476b2f0ceff73350cfba2be440b63ca0d7ee72f66', 'ada0d602336718f9106071b84d8dbd09cb337bc97294b169897bc76180885cf2', NULL, NULL, 1, '2026-07-20 15:26:31'),
(154, 3, 'NECESIDADES', 'CREAR_PORTAL', 'necesidades', 9, 'Solicitud #9 creada desde Portal del Colaborador.', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'INFO', 'OK', NULL, '[]', '{\"colaborador_id\":1,\"categoria_id\":6,\"tipo_necesidad\":\"EQUIPO\",\"descripcion\":\"Lic para nueva PC\",\"justificacion\":\"PCgjrgw rt\",\"prioridad\":\"MEDIA\",\"costo_estimado\":900,\"costo_unitario_estimado\":90,\"cantidad\":10,\"anio_objetivo\":2026}', '7d7058ba41178057c60b357f12e98136', 'ada0d602336718f9106071b84d8dbd09cb337bc97294b169897bc76180885cf2', '1255c7f8dfce3e39f88f22b606315144aee7ed6c64efedd9eefe55d16663978a', NULL, NULL, 1, '2026-07-20 15:27:30'),
(155, 3, 'AUTENTICACION', 'LOGOUT', 'usuarios', 3, 'Cierre de sesión.', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'INFO', 'OK', NULL, '[]', '[]', 'e9bdb06396dd4b61be7b4697424880c0', '1255c7f8dfce3e39f88f22b606315144aee7ed6c64efedd9eefe55d16663978a', '4ac633044f3684c0c3c13a572c58ea7c2325e9ed031a38aaaf2e57646962930c', NULL, NULL, 1, '2026-07-20 15:27:54'),
(156, 1, 'AUTENTICACION', 'LOGIN', 'usuarios', 1, 'Inicio de sesión exitoso.', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'INFO', 'OK', NULL, '[]', '[]', '7e80a86458f262218c4642fcca96880d', '4ac633044f3684c0c3c13a572c58ea7c2325e9ed031a38aaaf2e57646962930c', 'ea1be32b63b779700419f00173cc580ad3e717ec311534bd6b42592f7b6f69b6', NULL, NULL, 1, '2026-07-20 15:27:58'),
(157, 1, 'AUTENTICACION', 'LOGOUT', 'usuarios', 1, 'Cierre de sesión.', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'INFO', 'OK', NULL, '[]', '[]', '08330bd02cdf40ca7666a88cff0a4240', 'ea1be32b63b779700419f00173cc580ad3e717ec311534bd6b42592f7b6f69b6', '31983671120515d305bf911e27797dd5cc94cc1f9389e4d25deffb66d3b166b7', NULL, NULL, 1, '2026-07-20 15:30:22'),
(158, 24, 'AUTENTICACION', 'LOGIN', 'usuarios', 24, 'Inicio de sesión exitoso.', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'INFO', 'OK', NULL, '[]', '[]', '799576332aaf22ae8a21ca82063e5e30', '31983671120515d305bf911e27797dd5cc94cc1f9389e4d25deffb66d3b166b7', '36c0fe10677c910c388ba6fd4abe7f9b1399885670ebb0544fad830a2a0459bd', NULL, NULL, 1, '2026-07-20 15:30:27'),
(159, 24, 'AUTENTICACION', 'LOGOUT', 'usuarios', 24, 'Cierre de sesión.', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'INFO', 'OK', NULL, '[]', '[]', 'ee34f2007fea4ca7dfde1755d66564c9', '36c0fe10677c910c388ba6fd4abe7f9b1399885670ebb0544fad830a2a0459bd', '53ab521e865e3ee660920efd54e9fa96587a2ac4b072652ccbcd9bbdd313e95e', NULL, NULL, 1, '2026-07-20 15:30:29'),
(160, 3, 'AUTENTICACION', 'LOGIN', 'usuarios', 3, 'Inicio de sesión exitoso.', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'INFO', 'OK', NULL, '[]', '[]', 'fb791c4a1bbe425a37add5797638f7ca', '53ab521e865e3ee660920efd54e9fa96587a2ac4b072652ccbcd9bbdd313e95e', '422e11855c752793d9a129804f4af9dcbc52a09e224d782b620a7e6dbffb61cb', NULL, NULL, 1, '2026-07-20 15:30:33'),
(161, 3, 'AUTENTICACION', 'LOGOUT', 'usuarios', 3, 'Cierre de sesión.', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'INFO', 'OK', NULL, '[]', '[]', '2830e4e8d7eb1cf7faf3c9b814cb6032', '422e11855c752793d9a129804f4af9dcbc52a09e224d782b620a7e6dbffb61cb', '0acfbb1215f34a98a4be5c66490e8293ebc7dc9639b1ba3ae9debc7a4f12ea82', NULL, NULL, 1, '2026-07-20 15:31:08'),
(162, 1, 'AUTENTICACION', 'LOGIN', 'usuarios', 1, 'Inicio de sesión exitoso.', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'INFO', 'OK', NULL, '[]', '[]', '8dca5e83b8520bd0755f336c4abdd551', '0acfbb1215f34a98a4be5c66490e8293ebc7dc9639b1ba3ae9debc7a4f12ea82', 'a221fcb95c9bc0cb9a76b6d1b4e7291cfc0d4be7e75df6002288d1cf62ecd56a', NULL, NULL, 1, '2026-07-20 15:31:11'),
(163, 1, 'ASIGNACIONES', 'RECIBIR_DEVOLUCION', 'devoluciones', 6, 'Recepción física de devolución #6.', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'INFO', 'OK', NULL, '[]', '{\"estado_fisico\":\"BUENO\",\"evidencia\":\"\",\"accesorios_recibidos\":\"Cable\",\"observacion_recepcion\":\"COndiciones buenas\"}', 'c14eef87f7a5ccb0d795c4b248079941', 'a221fcb95c9bc0cb9a76b6d1b4e7291cfc0d4be7e75df6002288d1cf62ecd56a', '025905b3a49032b3711a948d1368a8f414d89707b481cedc832734458eb80764', NULL, NULL, 1, '2026-07-20 15:32:28'),
(164, 1, 'AUTENTICACION', 'LOGOUT', 'usuarios', 1, 'Cierre de sesión.', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'INFO', 'OK', NULL, '[]', '[]', '6c6eee271feb965e1db5fa5b47336fc1', '025905b3a49032b3711a948d1368a8f414d89707b481cedc832734458eb80764', '356216096c40015a5a69b14c97f27f25456e8231c7e5fcec61e6007aa204c87f', NULL, NULL, 1, '2026-07-20 15:32:31'),
(165, 3, 'AUTENTICACION', 'LOGIN', 'usuarios', 3, 'Inicio de sesión exitoso.', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'INFO', 'OK', NULL, '[]', '[]', '3bb19e4a1f8d6672e4a87c4a814c9165', '356216096c40015a5a69b14c97f27f25456e8231c7e5fcec61e6007aa204c87f', 'e5e0f99257ce77a9fa3375a4809f22152a05214179f9d7a969a22fe9447bb583', NULL, NULL, 1, '2026-07-20 15:32:37'),
(166, 3, 'AUTENTICACION', 'LOGOUT', 'usuarios', 3, 'Cierre de sesión.', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'INFO', 'OK', NULL, '[]', '[]', '10d7185fd22382923bc157b163dd54ee', 'e5e0f99257ce77a9fa3375a4809f22152a05214179f9d7a969a22fe9447bb583', '692a565a5c6446436a542ad1ef3ee0f3c46a9b053ea2b23fac32a3b0b1ecf8c1', NULL, NULL, 1, '2026-07-20 15:32:46'),
(167, 1, 'AUTENTICACION', 'LOGIN', 'usuarios', 1, 'Inicio de sesión exitoso.', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'INFO', 'OK', NULL, '[]', '[]', '86aec762653b14ff92b178bcbedfa653', '692a565a5c6446436a542ad1ef3ee0f3c46a9b053ea2b23fac32a3b0b1ecf8c1', 'bf9011301509d07e4128c739058e5d4a42e6e0a01fb23823a6e7d75c4d2f4aa1', NULL, NULL, 1, '2026-07-20 15:32:50');

-- --------------------------------------------------------

--
-- Table structure for table `categorias`
--

CREATE TABLE `categorias` (
  `id` int UNSIGNED NOT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo` enum('HARDWARE','SOFTWARE') COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categorias`
--

INSERT INTO `categorias` (`id`, `nombre`, `tipo`, `descripcion`, `activo`, `created_at`, `updated_at`) VALUES
(1, 'Hardware', 'HARDWARE', 'Dispositivos físicos generales.', 1, '2026-07-17 14:44:43', '2026-07-17 14:44:43'),
(2, 'Software', 'SOFTWARE', 'Aplicaciones y sistemas operativos..', 1, '2026-07-17 14:44:43', '2026-07-17 17:29:12'),
(3, 'Equipo de Red', 'HARDWARE', 'Switches, routers, puntos de acceso y firewalls.', 1, '2026-07-17 14:44:43', '2026-07-17 14:44:43'),
(4, 'Equipo de Cómputo', 'HARDWARE', 'Laptop, desktop, monitor y periféricos.', 1, '2026-07-17 14:44:43', '2026-07-17 14:44:43'),
(5, 'Equipo de Telefonía', 'HARDWARE', 'Teléfonos IP, móviles y accesorios.', 1, '2026-07-17 14:44:43', '2026-07-17 14:44:43'),
(6, 'Licencias de Software', 'SOFTWARE', 'Licencias no asignadas y renovaciones.', 1, '2026-07-17 14:44:43', '2026-07-17 14:44:43'),
(7, 'Servidores', 'HARDWARE', 'Servidores físicos y equipos de centro de datos.', 1, '2026-07-17 14:44:43', '2026-07-17 14:44:43'),
(8, 'Periféricos', 'HARDWARE', 'Monitores, UPS, teclados, impresoras y accesorios.', 1, '2026-07-17 14:44:43', '2026-07-17 14:44:43'),
(9, 'Seguridad Informática', 'SOFTWARE', 'Herramientas de protección, antivirus y monitoreo.', 1, '2026-07-17 14:44:43', '2026-07-17 14:44:43'),
(10, 'Sistemas Operativos', 'SOFTWARE', 'Sistemas operativos de escritorio y servidor.', 1, '2026-07-17 14:44:43', '2026-07-17 14:44:43'),
(11, 'Herramientas de Diseño', 'SOFTWARE', 'Software de diseño, edición y producción multimedia.', 1, '2026-07-17 14:44:43', '2026-07-17 14:44:43'),
(12, 'sfarha', 'HARDWARE', 'dsahartja', 0, '2026-07-17 17:29:22', '2026-07-17 17:30:17'),
(25, 'PM Postman Software', 'SOFTWARE', 'Categoria de apoyo para pruebas Postman', 1, '2026-07-20 12:47:14', '2026-07-20 12:47:14'),
(26, 'API Smoke 1784552874 Actualizada', 'SOFTWARE', 'Actualizada por PUT', 0, '2026-07-20 13:07:54', '2026-07-20 13:07:54'),
(27, 'CRUD Basico 1784553870 Actualizada', 'SOFTWARE', 'Actualizada desde prueba CRUD basica', 0, '2026-07-20 13:24:30', '2026-07-20 13:24:31'),
(28, 'Categoria Bearer 20260720134508 Actualizada', 'SOFTWARE', 'Actualizada en prueba automatica Bearer', 0, '2026-07-20 13:45:08', '2026-07-20 13:45:09'),
(34, 'Redes', 'SOFTWARE', 'dasgerhdt', 1, '2026-07-20 15:18:19', '2026-07-20 15:18:19');

-- --------------------------------------------------------

--
-- Table structure for table `colaboradores`
--

CREATE TABLE `colaboradores` (
  `id` int UNSIGNED NOT NULL,
  `nombres` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `apellidos` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `identificacion` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `departamento` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ubicacion` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `direccion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefono` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `foto` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `colaboradores`
--

INSERT INTO `colaboradores` (`id`, `nombres`, `apellidos`, `identificacion`, `departamento`, `ubicacion`, `direccion`, `telefono`, `email`, `foto`, `activo`, `created_at`, `updated_at`) VALUES
(1, 'Sofía', 'Martínez', '8-999-1001', 'Tecnología', 'Edificio 303 - Oficina 12', 'Campus Central', '6000-1001', 'sofia.martinez@cmdb.local', NULL, 1, '2026-07-17 14:44:43', '2026-07-17 14:44:43'),
(2, 'Carlos', 'Gómez', '8-999-1002', 'Finanzas', 'Edificio 201 - Oficina 5', 'Campus Central', '6000-1002', 'carlos.gomez@cmdb.local', 'uploads/collaborators/a0128d6173e517bdc2374839.jpg', 1, '2026-07-17 14:44:43', '2026-07-18 11:38:04'),
(3, 'Laura', 'Vega', '8-999-1003', 'Recursos Humanos', 'Casa 257', 'Vía principal', '6000-1003', 'laura.vega@cmdb.local', NULL, 1, '2026-07-17 14:44:43', '2026-07-17 14:44:43'),
(4, 'Ana', 'Rodríguez', '8-999-1004', 'Comunicaciones', 'Edificio 102 - Oficina 8', 'Campus Central', '6000-1004', 'ana.rodriguez@cmdb.local', 'uploads/collaborators/634f1f5af6e060f842d9ad52.jpg', 1, '2026-07-17 14:44:43', '2026-07-18 11:37:53'),
(5, 'Miguel', 'Ríos', '8-999-1005', 'Infraestructura', 'Edificio 303 - Data Center', 'Campus Central', '6000-1005', 'miguel.rios@cmdb.local', NULL, 1, '2026-07-17 14:44:43', '2026-07-17 14:44:43'),
(6, 'Patricia', 'Castillo', '8-999-1006', 'Compras', 'Edificio 201 - Oficina 11', 'Campus Central', '6000-1006', 'patricia.castillo@cmdb.local', NULL, 1, '2026-07-17 14:44:43', '2026-07-17 14:44:43'),
(7, 'Roberto', 'Núñez', '8-999-1007', 'Mesa de Ayuda', 'Edificio 303 - Piso 1', 'Campus Central', '6000-1007', 'roberto.nunez@cmdb.local', NULL, 1, '2026-07-17 14:44:43', '2026-07-17 14:44:43'),
(8, 'Valeria', 'Torres', '8-999-1008', 'Dirección Académica', 'Edificio 101 - Dirección', 'Campus Central', '6000-1008', 'valeria.torres@cmdb.local', NULL, 1, '2026-07-17 14:44:43', '2026-07-17 14:44:43'),
(9, 'Javier', 'Chen', '8-999-1009', 'Laboratorios', 'Edificio 405 - Laboratorio 2', 'Campus Central', '6000-1009', 'javier.chen@cmdb.local', NULL, 1, '2026-07-17 14:44:43', '2026-07-17 14:44:43'),
(10, 'Daniela', 'Morales', '8-999-1010', 'Biblioteca', 'Biblioteca Central - Atención', 'Campus Central', '6000-1010', 'daniela.morales@cmdb.local', NULL, 1, '2026-07-17 14:44:43', '2026-07-17 14:44:43'),
(11, 'Fernando', 'Batista', '8-999-1011', 'Investigación', 'Edificio 501 - Oficina 4', 'Campus Central', '6000-1011', 'fernando.batista@cmdb.local', NULL, 1, '2026-07-17 14:44:43', '2026-07-17 14:44:43'),
(24, 'Postman', 'Colaborador', 'PM-20260720074715', 'QA', 'Laboratorio Postman', 'Datos de prueba', '6000-9999', 'postman.colaborador@cmdb.local', NULL, 1, '2026-07-20 12:47:15', '2026-07-20 12:47:15'),
(25, 'Jorge', 'Osorio', '3-434-544', 'Admisiones', 'Edificio 232', 'Buena vista', '6545-5453', 'jorge@gmail.com', NULL, 1, '2026-07-20 14:02:10', '2026-07-20 14:02:10');

-- --------------------------------------------------------

--
-- Table structure for table `departamentos`
--

CREATE TABLE `departamentos` (
  `id` int UNSIGNED NOT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `departamentos`
--

INSERT INTO `departamentos` (`id`, `nombre`, `activo`, `created_at`, `updated_at`) VALUES
(1, 'Admisiones', 1, '2026-07-20 13:54:14', '2026-07-20 13:54:14'),
(2, 'Biblioteca', 1, '2026-07-20 13:54:14', '2026-07-20 13:54:14'),
(3, 'Compras', 1, '2026-07-20 13:54:14', '2026-07-20 13:54:14'),
(4, 'Comunicaciones', 1, '2026-07-20 13:54:14', '2026-07-20 13:54:14'),
(5, 'Contabilidad', 1, '2026-07-20 13:54:14', '2026-07-20 13:54:14'),
(6, 'Dirección Académica', 1, '2026-07-20 13:54:14', '2026-07-20 13:54:14'),
(7, 'Extensión Universitaria', 1, '2026-07-20 13:54:14', '2026-07-20 13:54:14'),
(8, 'Finanzas', 1, '2026-07-20 13:54:14', '2026-07-20 13:54:14'),
(9, 'Infraestructura', 1, '2026-07-20 13:54:14', '2026-07-20 13:54:14'),
(10, 'Investigación', 1, '2026-07-20 13:54:14', '2026-07-20 13:54:14'),
(11, 'Laboratorios', 1, '2026-07-20 13:54:15', '2026-07-20 13:54:15'),
(12, 'Mesa de Ayuda', 1, '2026-07-20 13:54:15', '2026-07-20 13:54:15'),
(13, 'Recursos Humanos', 1, '2026-07-20 13:54:15', '2026-07-20 13:54:15'),
(14, 'Registro Académico', 1, '2026-07-20 13:54:15', '2026-07-20 13:54:15'),
(15, 'Seguridad Física', 1, '2026-07-20 13:54:15', '2026-07-20 13:54:15'),
(16, 'Soporte Técnico', 1, '2026-07-20 13:54:15', '2026-07-20 13:54:15'),
(17, 'Tecnología', 1, '2026-07-20 13:54:15', '2026-07-20 13:54:15'),
(18, 'QA', 1, '2026-07-20 13:54:15', '2026-07-20 13:54:15');

-- --------------------------------------------------------

--
-- Table structure for table `devoluciones`
--

CREATE TABLE `devoluciones` (
  `id` bigint UNSIGNED NOT NULL,
  `asignacion_id` int UNSIGNED NOT NULL,
  `inventario_id` int UNSIGNED NOT NULL,
  `solicitado_por` int UNSIGNED DEFAULT NULL,
  `recibido_por` int UNSIGNED DEFAULT NULL,
  `motivo` varchar(160) COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado_fisico` enum('BUENO','REGULAR','DANADO','INCOMPLETO') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `observaciones` text COLLATE utf8mb4_unicode_ci,
  `evidencia` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_recepcion` datetime DEFAULT NULL,
  `accesorios_recibidos` text COLLATE utf8mb4_unicode_ci,
  `observacion_recepcion` text COLLATE utf8mb4_unicode_ci,
  `firma_id` bigint UNSIGNED DEFAULT NULL,
  `estado` enum('PENDIENTE_REVISION','EN_REVISION','APROBADA','RECHAZADA') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'PENDIENTE_REVISION',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `devoluciones`
--

INSERT INTO `devoluciones` (`id`, `asignacion_id`, `inventario_id`, `solicitado_por`, `recibido_por`, `motivo`, `estado_fisico`, `observaciones`, `evidencia`, `fecha_recepcion`, `accesorios_recibidos`, `observacion_recepcion`, `firma_id`, `estado`, `created_at`, `updated_at`) VALUES
(1, 4, 11, 1, NULL, 'Devolución de activo', NULL, '', '', NULL, NULL, NULL, NULL, 'PENDIENTE_REVISION', '2026-07-17 17:33:33', '2026-07-17 17:33:33'),
(6, 1, 1, 3, 1, 'Defectuoso de fabrica', 'BUENO', '', '', '2026-07-20 10:32:28', 'Cable', 'COndiciones buenas', NULL, 'EN_REVISION', '2026-07-18 11:16:10', '2026-07-20 15:32:28');

-- --------------------------------------------------------

--
-- Table structure for table `firmas_digitales`
--

CREATE TABLE `firmas_digitales` (
  `id` bigint UNSIGNED NOT NULL,
  `llave_id` int UNSIGNED NOT NULL,
  `usuario_id` int UNSIGNED DEFAULT NULL,
  `modulo` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `accion` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `entidad` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `entidad_id` bigint UNSIGNED DEFAULT NULL,
  `payload_hash` char(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `firma` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `algoritmo` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'RSA-SHA256',
  `fingerprint` char(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payload_version` smallint UNSIGNED NOT NULL DEFAULT '1',
  `audit_id` bigint UNSIGNED DEFAULT NULL,
  `correlation_id` char(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payload_json` longtext COLLATE utf8mb4_unicode_ci,
  `resultado_inicial` enum('VALIDA','INVALIDA','LLAVE_REVOCADA','NO_VERIFICABLE','ERROR') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'NO_VERIFICABLE',
  `resultado_verificacion` enum('VALIDA','INVALIDA','LLAVE_REVOCADA','NO_VERIFICABLE','ERROR') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `verified_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `intentos_login`
--

CREATE TABLE `intentos_login` (
  `id` bigint UNSIGNED NOT NULL,
  `usuario_id` int UNSIGNED DEFAULT NULL,
  `identificador` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `exitoso` tinyint(1) NOT NULL,
  `motivo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `intentos_login`
--

INSERT INTO `intentos_login` (`id`, `usuario_id`, `identificador`, `ip`, `exitoso`, `motivo`, `created_at`) VALUES
(1, 1, 'admin@cmdb.local', '127.0.0.1', 1, 'Inicio de sesión correcto', '2026-07-17 15:10:43'),
(2, 1, 'admin@cmdb.local', '127.0.0.1', 1, 'Inicio de sesión correcto', '2026-07-17 17:21:37'),
(3, 1, 'admin@cmdb.local', '127.0.0.1', 1, 'Inicio de sesión correcto', '2026-07-17 17:22:11'),
(4, 1, 'admin@cmdb.local', '127.0.0.1', 1, 'Inicio de sesión correcto', '2026-07-17 17:22:35'),
(5, 1, 'admin@cmdb.local', '127.0.0.1', 1, 'Inicio de sesión correcto', '2026-07-17 17:23:55'),
(6, 1, 'admin@cmdb.local', '127.0.0.1', 1, 'Inicio de sesión correcto', '2026-07-18 11:08:37'),
(7, 3, 'sofia.martinez@cmdb.local', '127.0.0.1', 0, 'Contraseña inválida', '2026-07-18 11:14:37'),
(8, 3, 'sofia.martinez@cmdb.local', '127.0.0.1', 1, 'Inicio de sesión correcto', '2026-07-18 11:14:50'),
(9, 2, 'operador@cmdb.local', '127.0.0.1', 1, 'Inicio de sesión correcto', '2026-07-18 11:18:44'),
(10, 1, 'admin@cmdb.local', '127.0.0.1', 1, 'Inicio de sesión correcto', '2026-07-18 11:23:06'),
(11, 1, 'admin@cmdb.local', '127.0.0.1', 1, 'Inicio de sesión correcto', '2026-07-20 08:44:25'),
(12, 1, 'admin@cmdb.local', '127.0.0.1', 1, 'Inicio de sesión correcto', '2026-07-20 09:36:14'),
(13, 1, 'admin@cmdb.local', '127.0.0.1', 1, 'Inicio de sesión correcto', '2026-07-20 09:36:36'),
(14, 1, 'admin@cmdb.local', '127.0.0.1', 1, 'Inicio de sesión correcto', '2026-07-20 09:36:53'),
(15, 2, 'operador@cmdb.local', '127.0.0.1', 1, 'Inicio de sesión correcto', '2026-07-20 09:36:53'),
(16, 3, 'sofia.martinez@cmdb.local', '127.0.0.1', 1, 'Inicio de sesión correcto', '2026-07-20 09:36:54'),
(17, 1, 'admin@cmdb.local', '127.0.0.1', 1, 'Inicio de sesión correcto', '2026-07-20 09:37:07'),
(18, 1, 'admin@cmdb.local', '127.0.0.1', 1, 'Inicio de sesión correcto', '2026-07-20 09:37:32'),
(19, 1, 'admin@cmdb.local', '127.0.0.1', 1, 'Inicio de sesión correcto', '2026-07-20 13:07:54'),
(20, 1, 'admin@cmdb.local', '127.0.0.1', 1, 'Inicio de sesión correcto', '2026-07-20 13:20:19'),
(21, 1, 'admin@cmdb.local', '127.0.0.1', 1, 'api_login_correcto', '2026-07-20 13:24:30'),
(22, NULL, '{{admin_identifier}}', '127.0.0.1', 0, 'api_login_rechazado', '2026-07-20 13:26:22'),
(23, 1, 'admin@cmdb.local', '127.0.0.1', 1, 'api_login_correcto', '2026-07-20 13:27:27'),
(24, 1, 'admin@cmdb.local', '127.0.0.1', 1, 'Inicio de sesión correcto', '2026-07-20 13:42:31'),
(25, 1, 'admin@cmdb.local', '127.0.0.1', 1, 'api_login_correcto', '2026-07-20 13:45:07'),
(26, 1, 'admin@cmdb.local', '127.0.0.1', 1, 'api_login_correcto', '2026-07-20 13:46:13'),
(27, 1, 'admin@cmdb.local', '127.0.0.1', 1, 'api_login_correcto', '2026-07-20 13:52:00'),
(28, 1, 'admin@cmdb.local', '127.0.0.1', 1, 'Inicio de sesión correcto', '2026-07-20 13:54:45'),
(29, 1, 'admin@cmdb.local', '127.0.0.1', 1, 'Inicio de sesión correcto', '2026-07-20 14:06:36'),
(30, 1, 'admin@cmdb.local', '127.0.0.1', 1, 'api_login_correcto', '2026-07-20 14:06:39'),
(31, 3, 'sofia.martinez@cmdb.local', '127.0.0.1', 1, 'Inicio de sesión correcto', '2026-07-20 14:06:39'),
(32, 1, 'admin@cmdb.local', '127.0.0.1', 1, 'Inicio de sesión correcto', '2026-07-20 14:17:49'),
(33, 3, 'sofia.martinez@cmdb.local', '127.0.0.1', 1, 'Inicio de sesión correcto', '2026-07-20 14:18:28'),
(34, 1, 'admin@cmdb.local', '127.0.0.1', 1, 'Inicio de sesión correcto', '2026-07-20 14:48:09'),
(35, 1, 'admin@cmdb.local', '127.0.0.1', 1, 'Inicio de sesión correcto', '2026-07-20 14:59:13'),
(36, 24, 'susy@local.com', '127.0.0.1', 1, 'Inicio de sesión correcto', '2026-07-20 15:03:27'),
(37, 3, 'sofia.martinez@cmdb.local', '127.0.0.1', 1, 'Inicio de sesión correcto', '2026-07-20 15:04:02'),
(38, 2, 'operador@cmdb.local', '127.0.0.1', 1, 'Inicio de sesión correcto', '2026-07-20 15:05:00'),
(39, 1, 'admin@cmdb.local', '127.0.0.1', 1, 'Inicio de sesión correcto', '2026-07-20 15:05:36'),
(40, 3, 'sofia.martinez@cmdb.local', '127.0.0.1', 1, 'Inicio de sesión correcto', '2026-07-20 15:16:12'),
(41, 1, 'admin@cmdb.local', '127.0.0.1', 1, 'Inicio de sesión correcto', '2026-07-20 15:16:45'),
(42, 2, 'operador@cmdb.local', '127.0.0.1', 1, 'Inicio de sesión correcto', '2026-07-20 15:20:31'),
(43, 1, 'admin@cmdb.local', '127.0.0.1', 1, 'Inicio de sesión correcto', '2026-07-20 15:20:53'),
(44, 2, 'operador@cmdb.local', '127.0.0.1', 1, 'Inicio de sesión correcto', '2026-07-20 15:23:56'),
(45, 3, 'sofia.martinez@cmdb.local', '127.0.0.1', 1, 'Inicio de sesión correcto', '2026-07-20 15:24:40'),
(46, 24, 'susy@local.com', '127.0.0.1', 1, 'Inicio de sesión correcto', '2026-07-20 15:25:14'),
(47, 1, 'admin@cmdb.local', '127.0.0.1', 1, 'Inicio de sesión correcto', '2026-07-20 15:25:50'),
(48, 24, 'susy@local.com', '127.0.0.1', 1, 'Inicio de sesión correcto', '2026-07-20 15:26:20'),
(49, 3, 'sofia.martinez@cmdb.local', '127.0.0.1', 1, 'Inicio de sesión correcto', '2026-07-20 15:26:31'),
(50, 1, 'admin@cmdb.local', '127.0.0.1', 1, 'Inicio de sesión correcto', '2026-07-20 15:27:58'),
(51, 24, 'susy', '127.0.0.1', 1, 'Inicio de sesión correcto', '2026-07-20 15:30:27'),
(52, 3, 'sofia.martinez@cmdb.local', '127.0.0.1', 1, 'Inicio de sesión correcto', '2026-07-20 15:30:33'),
(53, 1, 'admin@cmdb.local', '127.0.0.1', 1, 'Inicio de sesión correcto', '2026-07-20 15:31:11'),
(54, 3, 'sofia.martinez@cmdb.local', '127.0.0.1', 1, 'Inicio de sesión correcto', '2026-07-20 15:32:37'),
(55, 1, 'admin@cmdb.local', '127.0.0.1', 1, 'Inicio de sesión correcto', '2026-07-20 15:32:50');

-- --------------------------------------------------------

--
-- Table structure for table `inventario`
--

CREATE TABLE `inventario` (
  `id` int UNSIGNED NOT NULL,
  `categoria_id` int UNSIGNED DEFAULT NULL,
  `codigo_activo` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo_activo` enum('HARDWARE','SOFTWARE') COLLATE utf8mb4_unicode_ci NOT NULL,
  `subcategoria` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `marca` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `modelo` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `serie` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `costo` decimal(12,2) NOT NULL DEFAULT '0.00',
  `fecha_ingreso` date NOT NULL,
  `vida_util_meses` smallint UNSIGNED NOT NULL DEFAULT '36',
  `estado` enum('DISPONIBLE','ASIGNADO','DEVOLUCION_REGISTRADA','REVISION_TECNICA','EN_REPARACION','DANADO','DESCARTE','DONADO','MANTENIMIENTO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'DISPONIBLE',
  `imagen_principal` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `thumbnail` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `es_licencia` tinyint(1) NOT NULL DEFAULT '0',
  `clave_licencia` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `clave_licencia_cifrada` longtext COLLATE utf8mb4_unicode_ci,
  `clave_licencia_hash` char(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `clave_licencia_algoritmo` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `clave_licencia_migrada_at` datetime DEFAULT NULL,
  `proveedor_licencia` varchar(160) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tipo_licencia` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_adquisicion_licencia` date DEFAULT NULL,
  `url_licencia` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_vencimiento_licencia` date DEFAULT NULL,
  `estado_licencia` enum('ACTIVA','INACTIVA','VENCIDA') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ACTIVA',
  `observaciones_licencia` text COLLATE utf8mb4_unicode_ci,
  `cantidad` int UNSIGNED NOT NULL DEFAULT '1',
  `responsable_donacion` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `beneficiario_donacion` varchar(160) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `evidencia_donacion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `observacion_donacion` text COLLATE utf8mb4_unicode_ci,
  `fecha_donacion` date DEFAULT NULL,
  `valor_donacion` decimal(12,2) DEFAULT NULL,
  `autorizador_donacion_id` int UNSIGNED DEFAULT NULL,
  `observacion_tecnica_descarte` text COLLATE utf8mb4_unicode_ci,
  `evaluador_descarte_id` int UNSIGNED DEFAULT NULL,
  `responsable_descarte_id` int UNSIGNED DEFAULT NULL,
  `motivo_descarte` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_evaluacion_descarte` date DEFAULT NULL,
  `evidencia_descarte` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notas` text COLLATE utf8mb4_unicode_ci,
  `firma_integridad` char(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `inventario`
--

INSERT INTO `inventario` (`id`, `categoria_id`, `codigo_activo`, `nombre`, `tipo_activo`, `subcategoria`, `marca`, `modelo`, `serie`, `costo`, `fecha_ingreso`, `vida_util_meses`, `estado`, `imagen_principal`, `thumbnail`, `es_licencia`, `clave_licencia`, `clave_licencia_cifrada`, `clave_licencia_hash`, `clave_licencia_algoritmo`, `clave_licencia_migrada_at`, `proveedor_licencia`, `tipo_licencia`, `fecha_adquisicion_licencia`, `url_licencia`, `fecha_vencimiento_licencia`, `estado_licencia`, `observaciones_licencia`, `cantidad`, `responsable_donacion`, `beneficiario_donacion`, `evidencia_donacion`, `observacion_donacion`, `fecha_donacion`, `valor_donacion`, `autorizador_donacion_id`, `observacion_tecnica_descarte`, `evaluador_descarte_id`, `responsable_descarte_id`, `motivo_descarte`, `fecha_evaluacion_descarte`, `evidencia_descarte`, `notas`, `firma_integridad`, `activo`, `created_at`, `updated_at`) VALUES
(1, 4, 'ACT-0001', 'Laptop de Desarrollo', 'HARDWARE', 'Laptop', 'Acer', 'TravelMate P2', 'LAP-ACER-001', 950.00, '2024-02-15', 36, 'REVISION_TECNICA', NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'ACTIVA', NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Equipo de desarrollo entregado a Tecnología.', '', 1, '2026-07-17 14:44:43', '2026-07-20 15:32:28'),
(2, 5, 'ACT-0002', 'Teléfono IP de Inventario', 'HARDWARE', 'Teléfono IP', 'Yealink', 'T31P', 'TEL-IP-001', 78.00, '2024-09-01', 48, 'DISPONIBLE', NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'ACTIVA', NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Disponible para nueva asignación.', 'e4f6886414f7902a74cb7248e52bb5ea81fb01586c0a1496382d7b88c718c37b', 1, '2026-07-17 14:44:43', '2026-07-17 14:44:43'),
(3, 6, 'LIC-0001', 'Microsoft 365 Business Standard', 'SOFTWARE', 'Licencia', 'Microsoft', 'M365', 'LIC-O365-001', 125.00, '2025-01-10', 12, 'DISPONIBLE', NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'ACTIVA', NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Licencia disponible sin asignar; clave operativa no incluida en datos semilla.', 'ae9348370b4d4ffa12627ace15cdb68fbcbd4332bd5194f07cb33cb9584eac1f', 1, '2026-07-17 14:44:43', '2026-07-17 14:44:43'),
(4, 2, 'SW-0001', 'Antivirus Corporativo', 'SOFTWARE', 'Seguridad', 'Bitdefender', 'GravityZone', 'SW-ANT-001', 420.00, '2023-10-20', 12, 'DISPONIBLE', NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'ACTIVA', NULL, 20, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Suscripción anual de antivirus.', '669e4a38db58bde25b0701f5b8a342761785b43565e5bce23afdbc02807beac8', 1, '2026-07-17 14:44:43', '2026-07-17 14:44:43'),
(5, 3, 'NET-0001', 'Switch de Acceso', 'HARDWARE', 'Switch', 'Cisco', 'CBS250', 'SWT-CISCO-001', 520.00, '2022-08-01', 60, 'MANTENIMIENTO', NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'ACTIVA', NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Equipo de red en mantenimiento preventivo.', 'd23152cc48bab2d0c638d8190acc2c165e42349511dad2b74b5e1e4d0f565865', 1, '2026-07-17 14:44:43', '2026-07-17 14:44:43'),
(6, 4, 'ACT-0003', 'Laptop Administrativa Lenovo ThinkPad T14', 'HARDWARE', 'Laptop', 'Lenovo', 'ThinkPad T14 Gen 3', 'LAP-LEN-T14-002', 1180.00, '2024-05-18', 36, 'ASIGNADO', NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'ACTIVA', NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Asignada a Finanzas para gestión presupuestaria y reportes.', 'cc7cf4567ccc9f8e036473f95d72f09addf553be3da4b9f6f5624ab884c800ab', 1, '2026-07-17 14:44:43', '2026-07-17 14:44:43'),
(7, 4, 'ACT-0004', 'Desktop HP EliteDesk 800', 'HARDWARE', 'Desktop', 'HP', 'EliteDesk 800 G6', 'DESK-HP-800-003', 740.00, '2025-02-12', 48, 'DISPONIBLE', NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'ACTIVA', NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Equipo preparado para nuevo puesto administrativo.', '870b944173188fc88cd45e36c61ff1f4d1c4711c45a491bc21e79fe7bd983fa1', 1, '2026-07-17 14:44:43', '2026-07-17 14:44:43'),
(8, 8, 'ACT-0005', 'Monitor Dell 24 pulgadas', 'HARDWARE', 'Monitor', 'Dell', 'P2422H', 'MON-DELL-2422-004', 210.00, '2023-11-03', 48, 'ASIGNADO', NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'ACTIVA', NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Monitor externo para puesto de Recursos Humanos.', 'ec61a3a884b24ff968f755bbae6f95599bce52ee125b898157802cd94709dfba', 1, '2026-07-17 14:44:43', '2026-07-17 14:44:43'),
(9, 3, 'NET-0002', 'Firewall perimetral Fortinet', 'HARDWARE', 'Firewall', 'Fortinet', 'FortiGate 60F', 'FW-FGT-60F-002', 1320.00, '2022-06-20', 60, 'MANTENIMIENTO', NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'ACTIVA', NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'En mantenimiento por actualización de firmware y revisión de reglas.', 'c88378ec600d5858aa6ca1cd0bc6c4d6d7e939e9afd3be81e2ce141b89dc84be', 1, '2026-07-17 14:44:43', '2026-07-17 14:44:43'),
(10, 3, 'NET-0003', 'Punto de acceso WiFi 6', 'HARDWARE', 'Access Point', 'Ubiquiti', 'UniFi U6 Lite', 'AP-UBQ-U6-003', 145.00, '2025-03-15', 48, 'DISPONIBLE', NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'ACTIVA', NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Disponible para ampliar cobertura en Biblioteca.', '27e3d57152a0ee29fca6c802fe663fe7aef64645287293c6c5d09027a002e0f0', 1, '2026-07-17 14:44:43', '2026-07-17 14:44:43'),
(11, 5, 'TEL-0003', 'Teléfono IP Cisco 8841', 'HARDWARE', 'Teléfono IP', 'Cisco', '8841', 'TEL-CISCO-8841-003', 165.00, '2024-10-05', 48, 'DEVOLUCION_REGISTRADA', NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'ACTIVA', NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Teléfono asignado a Comunicaciones.', '', 1, '2026-07-17 14:44:43', '2026-07-17 17:33:33'),
(12, 7, 'SRV-0001', 'Servidor Dell PowerEdge R450', 'HARDWARE', 'Servidor', 'Dell', 'PowerEdge R450', 'SRV-DELL-R450-001', 4850.00, '2021-09-10', 72, 'MANTENIMIENTO', NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'ACTIVA', NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Servidor de virtualización en ventana de mantenimiento preventivo.', 'dc051be1d32b1971bd3e9e11fc78cc66b862f510dc727799764e433e35c8172c', 1, '2026-07-17 14:44:43', '2026-07-17 14:44:43'),
(13, 11, 'LIC-0002', 'Adobe Creative Cloud Equipos', 'SOFTWARE', 'Licencia', 'Adobe', 'Creative Cloud Teams', 'LIC-ADOBE-CC-002', 720.00, '2025-01-02', 12, 'DISPONIBLE', NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, 'Adobe', NULL, NULL, 'https://adminconsole.adobe.com/', '2026-01-02', 'ACTIVA', 'Licencia anual compartida por Comunicaciones y Diseño.', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Control por cupos; clave operativa fuera de datos semilla.', '088e0702604e2319d319cf7ac24ad88c94535a32ec34aceb1784a2c86e582d7e', 1, '2026-07-17 14:44:43', '2026-07-17 14:44:43'),
(14, 2, 'LIC-0003', 'AutoCAD LT', 'SOFTWARE', 'Licencia', 'Autodesk', 'AutoCAD LT 2026', 'LIC-AUTOCAD-003', 390.00, '2025-04-01', 12, 'ASIGNADO', NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, 'Autodesk', NULL, NULL, 'https://manage.autodesk.com/', '2026-04-01', 'ACTIVA', 'Licencias para infraestructura y proyectos técnicos.', 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Asignar solo a personal técnico; clave operativa fuera de datos semilla.', '', 1, '2026-07-17 14:44:43', '2026-07-20 13:21:48'),
(15, 9, 'LIC-0004', 'ESET Endpoint Security', 'SOFTWARE', 'Licencia', 'ESET', 'Endpoint Security', 'LIC-ESET-004', 980.00, '2024-12-15', 12, 'DISPONIBLE', NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, 'ESET', NULL, NULL, 'https://eba.eset.com/', '2026-12-15', 'ACTIVA', 'Cupos para estaciones administrativas y laboratorios.', 40, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Renovación anual de seguridad endpoint; clave operativa fuera de datos semilla.', '4710a39f11becc9af669bfbad482be82b6e6fbdeb0e3c7bb0188739de27c8f95', 1, '2026-07-17 14:44:43', '2026-07-17 14:44:43'),
(16, 2, 'SW-0002', 'Jira Service Management', 'SOFTWARE', 'Mesa de ayuda', 'Atlassian', 'Cloud Standard', 'SW-JIRA-002', 540.00, '2025-06-01', 12, 'DISPONIBLE', NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, 'Atlassian', NULL, NULL, 'https://admin.atlassian.com/', '2026-06-01', 'ACTIVA', 'Licencia para gestión de incidentes de Mesa de Ayuda.', 10, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Uso proyectado para mesa de ayuda; clave operativa fuera de datos semilla.', 'c09d257092bab6609fe017a8e15937891dbf5647ed01ec08613826230819614d', 1, '2026-07-17 14:44:43', '2026-07-17 14:44:43'),
(17, 4, 'ACT-0006', 'Tablet Samsung Galaxy Tab A8', 'HARDWARE', 'Tablet', 'Samsung', 'Galaxy Tab A8', 'TAB-SAM-A8-006', 230.00, '2021-03-12', 36, 'DONADO', NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'ACTIVA', NULL, 1, 'Patricia Castillo', 'Programa de Alfabetización Digital', 'ACTA-DON-2026-004', 'Donación aprobada por comité de activos; equipo funcional con desgaste normal.', '2026-06-15', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Donado por renovación tecnológica.', '5550883ca0ea49a3c3720712209ab9718c2e7dab45358a7f6694c67c04224145', 0, '2026-07-17 14:44:43', '2026-07-17 14:44:43'),
(18, 8, 'ACT-0007', 'UPS APC Back-UPS 750', 'HARDWARE', 'UPS', 'APC', 'Back-UPS 750', 'UPS-APC-750-007', 115.00, '2020-08-20', 48, 'DESCARTE', NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'ACTIVA', NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Batería agotada, carcasa deteriorada y costo de reparación superior al reemplazo.', 1, NULL, NULL, '2026-05-20', 'INF-DESC-2026-002', 'Pendiente retiro por proveedor autorizado.', '75001e1b2c60e66f80b45eac3826f05b3d0dfb57fa686fad647c2d7a0b89a634', 1, '2026-07-17 14:44:43', '2026-07-17 14:44:43'),
(31, 25, 'PM-LIC-POSTMAN', 'Licencia Postman DB', 'SOFTWARE', 'Licencia', 'Postman', 'Fixture', 'PM-LIC-POSTMAN-SERIE', 150.00, '2026-07-20', 12, 'DISPONIBLE', NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, 'Proveedor Postman', 'Anual', '2026-07-20', 'https://example.com/postman-license', '2027-07-20', 'ACTIVA', 'Licencia de prueba sin clave sensible.', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Fixture Postman en base de datos.', '', 1, '2026-07-20 12:47:15', '2026-07-20 12:47:15'),
(32, 25, 'PM-SW-POSTMAN', 'Software Postman asignable', 'SOFTWARE', 'Software', 'Postman', 'Fixture', 'PM-SW-POSTMAN-SERIE', 80.00, '2026-07-20', 12, 'DISPONIBLE', NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'ACTIVA', NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Fixture Postman en base de datos.', '', 1, '2026-07-20 12:47:15', '2026-07-20 12:47:15'),
(33, 25, 'PM-FLOW-20260720074715', 'Software Postman flujo devolucion', 'SOFTWARE', 'Software', 'Postman', 'Fixture', 'PM-FLOW-20260720074715-SERIE', 80.00, '2026-07-20', 12, 'ASIGNADO', NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'ACTIVA', NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Fixture Postman en base de datos.', '', 1, '2026-07-20 12:47:15', '2026-07-20 12:47:15'),
(38, 4, 'ACT-0055', 'HP-Laptop', 'SOFTWARE', 'Laptop', 'HP', 'AAFEWD94352QE', '5663FGHRT', 1900.00, '2026-07-20', 36, 'EN_REPARACION', 'uploads/equipment/b10ad8c0a255fe61ffd44f63.jpg', 'uploads/equipment/thumb_b10ad8c0a255fe61ffd44f63.jpg', 1, NULL, NULL, NULL, NULL, NULL, 'DFJW', 'SEFSFDS', '2026-07-06', NULL, '2026-07-23', 'ACTIVA', 'FGSTR', 40, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'STJSJYW', '', 1, '2026-07-20 15:11:14', '2026-07-20 15:15:14');

-- --------------------------------------------------------

--
-- Table structure for table `inventario_estado_historial`
--

CREATE TABLE `inventario_estado_historial` (
  `id` bigint UNSIGNED NOT NULL,
  `inventario_id` int UNSIGNED NOT NULL,
  `usuario_id` int UNSIGNED DEFAULT NULL,
  `estado_anterior` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estado_nuevo` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `motivo` varchar(160) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `observacion` text COLLATE utf8mb4_unicode_ci,
  `firma_id` bigint UNSIGNED DEFAULT NULL,
  `entidad_origen` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `entidad_id` bigint UNSIGNED DEFAULT NULL,
  `audit_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `inventario_estado_historial`
--

INSERT INTO `inventario_estado_historial` (`id`, `inventario_id`, `usuario_id`, `estado_anterior`, `estado_nuevo`, `motivo`, `observacion`, `firma_id`, `entidad_origen`, `entidad_id`, `audit_id`, `created_at`) VALUES
(1, 6, 1, NULL, 'ASIGNADO', 'Carga semilla realista', 'Estado inicial registrado durante carga de datos demo.', NULL, NULL, NULL, NULL, '2026-07-17 14:44:43'),
(2, 7, 1, NULL, 'DISPONIBLE', 'Carga semilla realista', 'Estado inicial registrado durante carga de datos demo.', NULL, NULL, NULL, NULL, '2026-07-17 14:44:43'),
(3, 8, 1, NULL, 'ASIGNADO', 'Carga semilla realista', 'Estado inicial registrado durante carga de datos demo.', NULL, NULL, NULL, NULL, '2026-07-17 14:44:43'),
(4, 9, 1, NULL, 'MANTENIMIENTO', 'Carga semilla realista', 'Estado inicial registrado durante carga de datos demo.', NULL, NULL, NULL, NULL, '2026-07-17 14:44:43'),
(5, 10, 1, NULL, 'DISPONIBLE', 'Carga semilla realista', 'Estado inicial registrado durante carga de datos demo.', NULL, NULL, NULL, NULL, '2026-07-17 14:44:43'),
(6, 11, 1, NULL, 'ASIGNADO', 'Carga semilla realista', 'Estado inicial registrado durante carga de datos demo.', NULL, NULL, NULL, NULL, '2026-07-17 14:44:43'),
(7, 12, 1, NULL, 'MANTENIMIENTO', 'Carga semilla realista', 'Estado inicial registrado durante carga de datos demo.', NULL, NULL, NULL, NULL, '2026-07-17 14:44:43'),
(8, 13, 1, NULL, 'DISPONIBLE', 'Carga semilla realista', 'Estado inicial registrado durante carga de datos demo.', NULL, NULL, NULL, NULL, '2026-07-17 14:44:43'),
(9, 14, 1, NULL, 'DISPONIBLE', 'Carga semilla realista', 'Estado inicial registrado durante carga de datos demo.', NULL, NULL, NULL, NULL, '2026-07-17 14:44:43'),
(10, 15, 1, NULL, 'DISPONIBLE', 'Carga semilla realista', 'Estado inicial registrado durante carga de datos demo.', NULL, NULL, NULL, NULL, '2026-07-17 14:44:43'),
(11, 16, 1, NULL, 'DISPONIBLE', 'Carga semilla realista', 'Estado inicial registrado durante carga de datos demo.', NULL, NULL, NULL, NULL, '2026-07-17 14:44:43'),
(12, 17, 1, NULL, 'DONADO', 'Carga semilla realista', 'Estado inicial registrado durante carga de datos demo.', NULL, NULL, NULL, NULL, '2026-07-17 14:44:43'),
(13, 18, 1, NULL, 'DESCARTE', 'Carga semilla realista', 'Estado inicial registrado durante carga de datos demo.', NULL, NULL, NULL, NULL, '2026-07-17 14:44:43'),
(14, 11, 1, 'ASIGNADO', 'DEVOLUCION_REGISTRADA', 'Devolución de asignación', NULL, NULL, 'devoluciones', 1, 18, '2026-07-17 17:33:33'),
(33, 1, 3, 'ASIGNADO', 'DEVOLUCION_REGISTRADA', 'Devolución de asignación', NULL, NULL, 'devoluciones', 6, 25, '2026-07-18 11:16:10'),
(43, 31, NULL, NULL, 'DISPONIBLE', 'Registro inicial', 'Activo creado en inventario.', NULL, NULL, NULL, NULL, '2026-07-20 12:47:15'),
(44, 32, NULL, NULL, 'DISPONIBLE', 'Registro inicial', 'Activo creado en inventario.', NULL, NULL, NULL, NULL, '2026-07-20 12:47:15'),
(45, 33, NULL, NULL, 'DISPONIBLE', 'Registro inicial', 'Activo creado en inventario.', NULL, NULL, NULL, NULL, '2026-07-20 12:47:15'),
(46, 33, 1, 'DISPONIBLE', 'ASIGNADO', 'Asignación a colaborador', 'Activo asignado desde el módulo de asignaciones.', NULL, 'asignaciones', 11, 65, '2026-07-20 12:47:15'),
(47, 14, 1, 'DISPONIBLE', 'ASIGNADO', 'Asignación a colaborador', 'Activo asignado desde el módulo de asignaciones.', NULL, 'asignaciones', 12, 72, '2026-07-20 13:21:48'),
(57, 38, NULL, NULL, 'DISPONIBLE', 'Registro inicial', 'Activo creado en inventario.', NULL, NULL, NULL, NULL, '2026-07-20 15:11:14'),
(58, 38, 1, 'DISPONIBLE', 'EN_REPARACION', 'Cambio manual de estado', 'Error', NULL, 'inventario', 38, 132, '2026-07-20 15:15:14'),
(59, 1, 1, 'DEVOLUCION_REGISTRADA', 'REVISION_TECNICA', 'Recepción física de devolución', 'COndiciones buenas', NULL, 'devoluciones', 6, 163, '2026-07-20 15:32:28');

-- --------------------------------------------------------

--
-- Table structure for table `inventario_imagenes`
--

CREATE TABLE `inventario_imagenes` (
  `id` int UNSIGNED NOT NULL,
  `inventario_id` int UNSIGNED NOT NULL,
  `ruta` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `es_principal` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `inventario_imagenes`
--

INSERT INTO `inventario_imagenes` (`id`, `inventario_id`, `ruta`, `es_principal`, `created_at`) VALUES
(9, 38, 'uploads/equipment/b10ad8c0a255fe61ffd44f63.jpg', 1, '2026-07-20 15:11:14'),
(10, 38, 'uploads/equipment/e946fd62538d2d8de791fbfa.jpg', 0, '2026-07-20 15:11:14');

-- --------------------------------------------------------

--
-- Table structure for table `inventario_qr`
--

CREATE TABLE `inventario_qr` (
  `id` bigint UNSIGNED NOT NULL,
  `inventario_id` int UNSIGNED NOT NULL,
  `token` char(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token_hash` char(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payload_hash` char(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `estado` enum('ACTIVO','REVOCADO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ACTIVO',
  `created_by` int UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `revoked_at` datetime DEFAULT NULL,
  `revoked_by` int UNSIGNED DEFAULT NULL,
  `revoked_reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `regenerated_from_id` bigint UNSIGNED DEFAULT NULL,
  `last_accessed_at` datetime DEFAULT NULL,
  `access_count` int UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `inventario_qr`
--

INSERT INTO `inventario_qr` (`id`, `inventario_id`, `token`, `token_hash`, `payload_hash`, `activo`, `estado`, `created_by`, `created_at`, `revoked_at`, `revoked_by`, `revoked_reason`, `regenerated_from_id`, `last_accessed_at`, `access_count`) VALUES
(4, 31, 'bd5ddfef4531ccff35b320e731b7fe10a528a2932b46aa5dca6307e71565f053', '95145a00280ecdd8d5c53b553e0b608b2f6c27f33c4b058d413b0803c4270370', '807ffba34a5fb95df5148471faf86c7e5a26909467b7e59e073e07726e5fd54a', 1, 'ACTIVO', 1, '2026-07-20 12:47:15', NULL, NULL, NULL, NULL, '2026-07-20 09:06:35', 2),
(6, 33, 'f110992869fd271c454a79c0f49765d4da8dac0af9e2c089b758f8740a8800d3', 'bc80ec5601c6458671e88da0b08f40443a9cd378ab0455597c2317fef44b726a', 'a7fab76933d7396eb00cf16c52d905498ef70301348a14247dd7f8d9d74834a7', 1, 'ACTIVO', 1, '2026-07-20 15:12:58', NULL, NULL, NULL, NULL, NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `licencia_asignaciones`
--

CREATE TABLE `licencia_asignaciones` (
  `id` bigint UNSIGNED NOT NULL,
  `inventario_id` int UNSIGNED NOT NULL,
  `colaborador_id` int UNSIGNED NOT NULL,
  `usuario_id` int UNSIGNED DEFAULT NULL,
  `cantidad` int UNSIGNED NOT NULL DEFAULT '1',
  `fecha_asignacion` date NOT NULL,
  `fecha_fin` date DEFAULT NULL,
  `estado` enum('ACTIVA','LIBERADA','VENCIDA') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ACTIVA',
  `observaciones` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `licencia_asignaciones`
--

INSERT INTO `licencia_asignaciones` (`id`, `inventario_id`, `colaborador_id`, `usuario_id`, `cantidad`, `fecha_asignacion`, `fecha_fin`, `estado`, `observaciones`, `created_at`) VALUES
(1, 13, 4, 1, 1, '2025-01-10', '2026-07-18', 'LIBERADA', 'Suite completa para campañas institucionales.', '2026-07-17 14:44:43'),
(2, 13, 8, 1, 1, '2025-02-03', NULL, 'ACTIVA', 'Licencia para revisión de material gráfico.', '2026-07-17 14:44:43'),
(3, 14, 5, 1, 1, '2025-04-05', NULL, 'ACTIVA', 'Diseños de infraestructura y planos técnicos.', '2026-07-17 14:44:43'),
(4, 15, 9, 1, 12, '2025-01-20', NULL, 'ACTIVA', 'Cupos instalados en laboratorio 2.', '2026-07-17 14:44:43'),
(5, 16, 7, 1, 3, '2025-06-05', NULL, 'ACTIVA', 'Agentes de mesa de ayuda.', '2026-07-17 14:44:43'),
(6, 38, 1, 1, 30, '2026-07-20', NULL, 'ACTIVA', 'LAPTOP', '2026-07-20 15:14:22');

-- --------------------------------------------------------

--
-- Table structure for table `llaves_rsa`
--

CREATE TABLE `llaves_rsa` (
  `id` int UNSIGNED NOT NULL,
  `usuario_id` int UNSIGNED DEFAULT NULL,
  `nombre` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `public_key` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `private_key_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `key_store_reference` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `private_key_encrypted` tinyint(1) NOT NULL DEFAULT '1',
  `algoritmo` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'RSA-SHA256',
  `bits` smallint UNSIGNED NOT NULL DEFAULT '3072',
  `fingerprint` char(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` enum('ACTIVA','REVOCADA','REEMPLAZADA','ROTADA') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ACTIVA',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `revoked_at` datetime DEFAULT NULL,
  `revocation_reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `revoked_by` int UNSIGNED DEFAULT NULL,
  `replaced_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `necesidades`
--

CREATE TABLE `necesidades` (
  `id` int UNSIGNED NOT NULL,
  `colaborador_id` int UNSIGNED NOT NULL,
  `categoria_id` int UNSIGNED DEFAULT NULL,
  `tipo_necesidad` enum('EQUIPO','SOFTWARE','LICENCIA') COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `justificacion` text COLLATE utf8mb4_unicode_ci,
  `prioridad` enum('BAJA','MEDIA','ALTA') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'MEDIA',
  `costo_estimado` decimal(12,2) DEFAULT NULL,
  `costo_unitario_estimado` decimal(12,2) DEFAULT NULL,
  `cantidad` int UNSIGNED NOT NULL DEFAULT '1',
  `anio_objetivo` smallint UNSIGNED DEFAULT NULL,
  `estado` enum('EN_ESPERA','EN_TRAMITE','APROBADA','RECHAZADA','PENDIENTE','EN_REVISION','ATENDIDA','CANCELADA') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'EN_ESPERA',
  `comentario_resolucion` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `usuario_procesador_id` int UNSIGNED DEFAULT NULL,
  `respuesta_administrativa` text COLLATE utf8mb4_unicode_ci,
  `fecha_procesamiento` datetime DEFAULT NULL,
  `audit_id` bigint UNSIGNED DEFAULT NULL,
  `firma_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `necesidades`
--

INSERT INTO `necesidades` (`id`, `colaborador_id`, `categoria_id`, `tipo_necesidad`, `descripcion`, `justificacion`, `prioridad`, `costo_estimado`, `costo_unitario_estimado`, `cantidad`, `anio_objetivo`, `estado`, `comentario_resolucion`, `usuario_procesador_id`, `respuesta_administrativa`, `fecha_procesamiento`, `audit_id`, `firma_id`, `created_at`, `updated_at`) VALUES
(1, 2, 5, 'EQUIPO', 'Se requiere un teléfono IP para el nuevo puesto de Finanzas.', NULL, 'MEDIA', NULL, NULL, 1, NULL, 'PENDIENTE', NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-17 14:44:43', '2026-07-17 14:44:43'),
(2, 3, 6, 'LICENCIA', 'Licencia de software de ofimática para colaboradora nueva.', NULL, 'ALTA', NULL, NULL, 1, NULL, 'EN_REVISION', NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-17 14:44:43', '2026-07-17 14:44:43'),
(3, 10, 4, 'EQUIPO', 'Equipo de escritorio para estación de autopréstamo en biblioteca.', NULL, 'ALTA', 780.00, NULL, 1, NULL, 'PENDIENTE', NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-17 14:44:43', '2026-07-17 14:44:43'),
(4, 9, 3, 'EQUIPO', 'Punto de acceso adicional para mejorar cobertura del laboratorio 2.', NULL, 'MEDIA', 160.00, NULL, 1, NULL, 'EN_REVISION', 'Validar canalización y punto de red disponible.', NULL, NULL, NULL, NULL, NULL, '2026-07-17 14:44:43', '2026-07-17 14:44:43'),
(5, 4, 11, 'LICENCIA', 'Licencia adicional de diseño para apoyo temporal de campaña institucional.', NULL, 'MEDIA', 720.00, NULL, 1, NULL, 'PENDIENTE', NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-17 14:44:43', '2026-07-17 14:44:43'),
(6, 5, 9, 'SOFTWARE', 'Herramienta de monitoreo para alertas de disponibilidad de servicios críticos.', NULL, 'ALTA', 1250.00, NULL, 1, NULL, 'EN_REVISION', 'Comparar opciones cloud y on-premise.', NULL, NULL, NULL, NULL, NULL, '2026-07-17 14:44:43', '2026-07-17 14:44:43'),
(7, 1, 1, 'EQUIPO', 'New equipment for the team', 'The old one is in repair.', 'MEDIA', 1000.00, 1000.00, 1, 2026, 'APROBADA', 'Nuevo equipo', 1, 'Nuevo equipo', '2026-07-18 06:23:54', 33, NULL, '2026-07-18 11:18:05', '2026-07-18 11:23:54'),
(8, 24, 25, 'SOFTWARE', 'Solicitud fixture Postman en base de datos.', 'Evidencia de pruebas Postman.', 'MEDIA', 120.00, 120.00, 1, 2026, 'EN_ESPERA', NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-20 12:47:15', '2026-07-20 12:47:15'),
(9, 1, 6, 'EQUIPO', 'Lic para nueva PC', 'PCgjrgw rt', 'MEDIA', 900.00, 90.00, 10, 2026, 'EN_ESPERA', NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-20 15:27:30', '2026-07-20 15:27:30');

-- --------------------------------------------------------

--
-- Table structure for table `necesidades_historial`
--

CREATE TABLE `necesidades_historial` (
  `id` bigint UNSIGNED NOT NULL,
  `necesidad_id` int UNSIGNED NOT NULL,
  `usuario_id` int UNSIGNED DEFAULT NULL,
  `estado_anterior` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estado_nuevo` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `observacion` text COLLATE utf8mb4_unicode_ci,
  `respuesta_administrativa` text COLLATE utf8mb4_unicode_ci,
  `firma_id` bigint UNSIGNED DEFAULT NULL,
  `audit_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `necesidades_historial`
--

INSERT INTO `necesidades_historial` (`id`, `necesidad_id`, `usuario_id`, `estado_anterior`, `estado_nuevo`, `observacion`, `respuesta_administrativa`, `firma_id`, `audit_id`, `created_at`) VALUES
(1, 7, 1, 'EN_ESPERA', 'APROBADA', 'Nuevo equipo', 'Nuevo equipo', NULL, 33, '2026-07-18 11:23:54');

-- --------------------------------------------------------

--
-- Table structure for table `noticias`
--

CREATE TABLE `noticias` (
  `id` int UNSIGNED NOT NULL,
  `usuario_id` int UNSIGNED DEFAULT NULL,
  `titulo` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `resumen` varchar(300) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contenido` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `imagen` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `publicada` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `noticias`
--

INSERT INTO `noticias` (`id`, `usuario_id`, `titulo`, `resumen`, `contenido`, `imagen`, `publicada`, `created_at`, `updated_at`) VALUES
(1, 1, 'Por qué una CMDB evita pérdidas de activos', 'Una CMDB mantiene trazabilidad de responsables, estados y ubicación de los equipos.', 'Registrar cada activo, custodio y estado permite conocer qué equipo tiene cada colaborador y reduce el riesgo de pérdida, duplicidad o compras innecesarias.', NULL, 1, '2026-07-17 14:44:43', '2026-07-17 14:44:43'),
(2, 1, 'Importancia de renovar equipos cerca de depreciación', 'La depreciación permite planificar mantenimiento, renovación y presupuesto tecnológico.', 'El módulo de alertas identifica equipos cuya vida útil se acerca a su fecha límite para tomar decisiones preventivas y mantener la continuidad operativa.', NULL, 1, '2026-07-17 14:44:43', '2026-07-17 14:44:43'),
(3, 1, 'Buenas prácticas para la devolución de equipos', 'La revisión técnica evita reasignar equipos con fallas ocultas.', 'Antes de volver a marcar un equipo como disponible, registre motivo de devolución, estado físico y observación técnica. Esto mantiene trazabilidad y reduce incidentes posteriores.', NULL, 1, '2026-07-17 14:44:44', '2026-07-17 14:44:44'),
(4, 1, 'Control de licencias por cupos', 'Asignar cupos permite medir uso real y planificar renovaciones.', 'Las licencias con cantidad superior a uno deben administrarse por cupos asignados a colaboradores o áreas responsables. La CMDB ayuda a comparar cupos disponibles contra demanda.', NULL, 1, '2026-07-17 14:44:44', '2026-07-17 14:44:44'),
(5, 1, 'QR para activos críticos', 'El QR facilita identificar activos sin exponer información sensible.', 'Cada activo puede consultarse desde su detalle interno mediante QR. El código no debe contener claves de licencia ni datos confidenciales.', NULL, 1, '2026-07-17 14:44:44', '2026-07-17 14:44:44'),
(6, 1, 'Bitacora', 'dfaha', 'adfhart', 'uploads/equipment/79e2e54f1ef3153db758f47a.jpg', 1, '2026-07-20 15:00:07', '2026-07-20 15:00:07');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` bigint UNSIGNED NOT NULL,
  `usuario_id` int UNSIGNED NOT NULL,
  `token_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expira_at` datetime NOT NULL,
  `usado` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`id`, `usuario_id`, `token_hash`, `expira_at`, `usado`, `created_at`) VALUES
(1, 1, '$argon2id$v=19$m=131072,t=4,p=2$SGc2SkJZcHZuU1I0M2xRRQ$59QL9LeXB2PPlJF1K3dBWnMHhv/vaZWD1rnsG7eidmw', '2026-07-17 12:52:44', 0, '2026-07-17 17:22:44');

-- --------------------------------------------------------

--
-- Table structure for table `presupuestos`
--

CREATE TABLE `presupuestos` (
  `id` int UNSIGNED NOT NULL,
  `nombre` varchar(160) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo` enum('ANUAL','QUINQUENAL') COLLATE utf8mb4_unicode_ci NOT NULL,
  `anio_inicio` smallint UNSIGNED NOT NULL,
  `anio_fin` smallint UNSIGNED NOT NULL,
  `total_estimado` decimal(14,2) NOT NULL DEFAULT '0.00',
  `presupuesto_base` decimal(14,2) NOT NULL DEFAULT '0.00',
  `inflacion_anual` decimal(6,2) NOT NULL DEFAULT '0.00',
  `crecimiento_anual` decimal(6,2) NOT NULL DEFAULT '0.00',
  `total_quinquenal` decimal(14,2) NOT NULL DEFAULT '0.00',
  `registros_sin_costo` int UNSIGNED NOT NULL DEFAULT '0',
  `supuestos` text COLLATE utf8mb4_unicode_ci,
  `filtros_json` text COLLATE utf8mb4_unicode_ci,
  `estado` enum('BORRADOR','APROBADO','CERRADO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'BORRADOR',
  `created_by` int UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `presupuestos`
--

INSERT INTO `presupuestos` (`id`, `nombre`, `tipo`, `anio_inicio`, `anio_fin`, `total_estimado`, `presupuesto_base`, `inflacion_anual`, `crecimiento_anual`, `total_quinquenal`, `registros_sin_costo`, `supuestos`, `filtros_json`, `estado`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'Presupuesto tecnológico anual 2026', 'ANUAL', 2026, 2026, 2910.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, NULL, 'BORRADOR', 1, '2026-07-17 14:44:44', '2026-07-17 14:44:44'),
(2, 'Plan quinquenal de renovación tecnológica 2026-2030', 'QUINQUENAL', 2026, 2030, 21850.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, NULL, 'BORRADOR', 1, '2026-07-17 14:44:44', '2026-07-17 14:44:44');

-- --------------------------------------------------------

--
-- Table structure for table `presupuesto_detalles`
--

CREATE TABLE `presupuesto_detalles` (
  `id` bigint UNSIGNED NOT NULL,
  `presupuesto_id` int UNSIGNED NOT NULL,
  `categoria_id` int UNSIGNED DEFAULT NULL,
  `necesidad_id` int UNSIGNED DEFAULT NULL,
  `tipo_necesidad` enum('EQUIPO','SOFTWARE','LICENCIA','RENOVACION','MANTENIMIENTO') COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cantidad` int UNSIGNED NOT NULL DEFAULT '1',
  `costo_unitario` decimal(12,2) NOT NULL DEFAULT '0.00',
  `costo_base` decimal(12,2) DEFAULT NULL,
  `subtotal` decimal(14,2) NOT NULL DEFAULT '0.00',
  `anio` smallint UNSIGNED NOT NULL,
  `year_index` tinyint UNSIGNED NOT NULL DEFAULT '0',
  `factor_proyeccion` decimal(16,8) NOT NULL DEFAULT '1.00000000',
  `inflacion_anual` decimal(6,2) NOT NULL DEFAULT '0.00',
  `crecimiento_anual` decimal(6,2) NOT NULL DEFAULT '0.00',
  `tiene_costo` tinyint(1) NOT NULL DEFAULT '1',
  `motivo_sin_costo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `prioridad` enum('BAJA','MEDIA','ALTA') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estado_solicitud` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `presupuesto_detalles`
--

INSERT INTO `presupuesto_detalles` (`id`, `presupuesto_id`, `categoria_id`, `necesidad_id`, `tipo_necesidad`, `descripcion`, `cantidad`, `costo_unitario`, `costo_base`, `subtotal`, `anio`, `year_index`, `factor_proyeccion`, `inflacion_anual`, `crecimiento_anual`, `tiene_costo`, `motivo_sin_costo`, `prioridad`, `estado_solicitud`) VALUES
(1, 1, 4, NULL, 'EQUIPO', 'Reposición de equipos administrativos de alto uso.', 2, 780.00, NULL, 1560.00, 2026, 0, 1.00000000, 0.00, 0.00, 1, NULL, NULL, NULL),
(2, 1, 3, NULL, 'EQUIPO', 'Ampliación de cobertura inalámbrica en laboratorios.', 2, 160.00, NULL, 320.00, 2026, 0, 1.00000000, 0.00, 0.00, 1, NULL, NULL, NULL),
(3, 1, 9, NULL, 'SOFTWARE', 'Renovación y monitoreo de seguridad endpoint.', 1, 1030.00, NULL, 1030.00, 2026, 0, 1.00000000, 0.00, 0.00, 1, NULL, NULL, NULL),
(4, 2, 4, NULL, 'EQUIPO', 'Renovación progresiva de laptops y desktops.', 20, 850.00, NULL, 17000.00, 2027, 0, 1.00000000, 0.00, 0.00, 1, NULL, NULL, NULL),
(5, 2, 7, NULL, 'EQUIPO', 'Reserva para actualización de infraestructura de virtualización.', 1, 4850.00, NULL, 4850.00, 2028, 0, 1.00000000, 0.00, 0.00, 1, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `revisiones_tecnicas`
--

CREATE TABLE `revisiones_tecnicas` (
  `id` bigint UNSIGNED NOT NULL,
  `devolucion_id` bigint UNSIGNED NOT NULL,
  `inventario_id` int UNSIGNED NOT NULL,
  `tecnico_id` int UNSIGNED DEFAULT NULL,
  `resultado` enum('DISPONIBLE','EN_REPARACION','DANADO','DESCARTE','DONADO','MANTENIMIENTO') COLLATE utf8mb4_unicode_ci NOT NULL,
  `diagnostico` text COLLATE utf8mb4_unicode_ci,
  `opinion_tecnica` text COLLATE utf8mb4_unicode_ci,
  `recomendacion` text COLLATE utf8mb4_unicode_ci,
  `observacion_tecnica` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `evidencia` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `aprobador_id` int UNSIGNED DEFAULT NULL,
  `firma_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ubicaciones_historial`
--

CREATE TABLE `ubicaciones_historial` (
  `id` bigint UNSIGNED NOT NULL,
  `colaborador_id` int UNSIGNED NOT NULL,
  `ubicacion_anterior` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ubicacion_nueva` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tipo` enum('OFICINA','EDIFICIO','CASA','SEDE','DIRECCION','OTRO') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  `motivo` text COLLATE utf8mb4_unicode_ci,
  `usuario_id` int UNSIGNED DEFAULT NULL,
  `audit_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ubicaciones_historial`
--

INSERT INTO `ubicaciones_historial` (`id`, `colaborador_id`, `ubicacion_anterior`, `ubicacion_nueva`, `tipo`, `fecha_inicio`, `fecha_fin`, `motivo`, `usuario_id`, `audit_id`, `created_at`) VALUES
(1, 25, NULL, 'Edificio 232', 'EDIFICIO', '2026-07-20', NULL, 'Registro inicial de ubicación', 1, 82, '2026-07-20 14:02:10');

-- --------------------------------------------------------

--
-- Table structure for table `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int UNSIGNED NOT NULL,
  `colaborador_id` int UNSIGNED DEFAULT NULL,
  `nombre_usuario` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rol` enum('ADMIN','OPERADOR','COLABORADOR') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'OPERADOR',
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `estado_cuenta` enum('ACTIVO','BLOQUEADO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ACTIVO',
  `intentos_fallidos` tinyint UNSIGNED NOT NULL DEFAULT '0',
  `ultimo_login_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `usuarios`
--

INSERT INTO `usuarios` (`id`, `colaborador_id`, `nombre_usuario`, `email`, `password_hash`, `rol`, `activo`, `estado_cuenta`, `intentos_fallidos`, `ultimo_login_at`, `created_at`, `updated_at`) VALUES
(1, NULL, 'admin', 'admin@cmdb.local', '$argon2id$v=19$m=131072,t=4,p=2$SHgxcEZ2Z0ZkLzRBUWxDNQ$jRQOVP3CfrTnIh75olgGnCrs8vEM/CKE28aY2+KWlEs', 'ADMIN', 1, 'ACTIVO', 0, '2026-07-20 10:32:50', '2026-07-17 14:44:43', '2026-07-20 15:32:50'),
(2, NULL, 'operador', 'operador@cmdb.local', '$argon2id$v=19$m=131072,t=4,p=2$SHBDL2VteGRQN2c3cnpRMw$fR+DuA4yA2iFTzGKQQxNynnM7yEHYhM6/4nzrMHo5lc', 'OPERADOR', 1, 'ACTIVO', 0, '2026-07-20 10:23:56', '2026-07-17 14:44:43', '2026-07-20 15:23:56'),
(3, 1, 'sofia', 'sofia.martinez@cmdb.local', '$argon2id$v=19$m=131072,t=4,p=2$aUV5NGFTVnFsdmxTYmhGag$ObxIu7bA/YtPToRn1PdQyeqG6JInt//W11mnDqIRlpA', 'COLABORADOR', 1, 'ACTIVO', 0, '2026-07-20 10:32:37', '2026-07-17 14:44:43', '2026-07-20 15:32:37'),
(4, 4, 'ana.rodriguez', 'ana.rodriguez@cmdb.local', '$2y$12$/SgfYbw4agT2YOEDLGPsouUxXXisi8fP.8htY6OuwZ/HI27IYY2eG', 'COLABORADOR', 1, 'ACTIVO', 0, NULL, '2026-07-17 14:44:43', '2026-07-17 14:44:43'),
(5, 5, 'miguel.rios', 'miguel.rios@cmdb.local', '$2y$12$/SgfYbw4agT2YOEDLGPsouUxXXisi8fP.8htY6OuwZ/HI27IYY2eG', 'COLABORADOR', 1, 'ACTIVO', 0, NULL, '2026-07-17 14:44:43', '2026-07-17 14:44:43'),
(6, 7, 'soporte', 'soporte@cmdb.local', '$2y$12$Q9DAchW14gC1HJzLGrrfJezVjS7HOlaoHFCHyyJer.4c8XeVenht2', 'OPERADOR', 1, 'ACTIVO', 0, NULL, '2026-07-17 14:44:43', '2026-07-17 14:44:43'),
(19, 24, 'postman.colaborador', 'postman.colaborador@cmdb.local', '$argon2id$v=19$m=131072,t=4,p=2$U0s5enRKMkJlRzdJUlM5eQ$wt1xpe25A51bYSuaY4qx4e3LCEcxWzaK8tntUWR14Sc', 'COLABORADOR', 1, 'ACTIVO', 0, NULL, '2026-07-20 12:47:15', '2026-07-20 12:47:15'),
(24, NULL, 'Susy', 'susy@local.com', '$argon2id$v=19$m=131072,t=4,p=2$LmJGNzcucDFYU2ZQelZZZA$xvbgE7CEHdg8zd7VajYIDnrazcVu0H/XQUmYCpN52ag', 'OPERADOR', 1, 'ACTIVO', 0, '2026-07-20 10:30:27', '2026-07-20 15:03:07', '2026-07-20 15:30:27');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `accesos_portal_colaborador`
--
ALTER TABLE `accesos_portal_colaborador`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_portal_usuario` (`usuario_id`);

--
-- Indexes for table `api_tokens`
--
ALTER TABLE `api_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_api_tokens_hash` (`token_hash`),
  ADD KEY `idx_api_tokens_usuario` (`usuario_id`);

--
-- Indexes for table `asignaciones`
--
ALTER TABLE `asignaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_asignacion_inventario` (`inventario_id`),
  ADD KEY `fk_asignacion_colaborador` (`colaborador_id`),
  ADD KEY `idx_asignaciones_estado` (`estado`),
  ADD KEY `idx_asignaciones_usuario_asignador` (`usuario_asignador_id`),
  ADD KEY `idx_asignaciones_audit` (`audit_id`),
  ADD KEY `idx_asignaciones_firma` (`firma_id`);

--
-- Indexes for table `bitacora`
--
ALTER TABLE `bitacora`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_bitacora_usuario` (`usuario_id`),
  ADD KEY `idx_bitacora_created` (`created_at`),
  ADD KEY `idx_bitacora_correlation` (`correlation_id`),
  ADD KEY `idx_bitacora_entidad` (`entidad`,`entidad_id`),
  ADD KEY `idx_bitacora_hash` (`record_hash`),
  ADD KEY `idx_bitacora_firma` (`firma_id`);

--
-- Indexes for table `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indexes for table `colaboradores`
--
ALTER TABLE `colaboradores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `identificacion` (`identificacion`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `departamentos`
--
ALTER TABLE `departamentos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_departamentos_nombre` (`nombre`);

--
-- Indexes for table `devoluciones`
--
ALTER TABLE `devoluciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_devolucion_asignacion` (`asignacion_id`),
  ADD KEY `fk_devolucion_inventario` (`inventario_id`),
  ADD KEY `fk_devolucion_solicitado` (`solicitado_por`),
  ADD KEY `fk_devolucion_recibido` (`recibido_por`),
  ADD KEY `idx_devoluciones_estado` (`estado`),
  ADD KEY `idx_devoluciones_firma` (`firma_id`);

--
-- Indexes for table `firmas_digitales`
--
ALTER TABLE `firmas_digitales`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_firma_llave` (`llave_id`),
  ADD KEY `fk_firma_usuario` (`usuario_id`),
  ADD KEY `idx_firmas_digitales_audit_id` (`audit_id`),
  ADD KEY `idx_firmas_digitales_resultado` (`resultado_verificacion`),
  ADD KEY `idx_firmas_digitales_fingerprint` (`fingerprint`);

--
-- Indexes for table `intentos_login`
--
ALTER TABLE `intentos_login`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_intentos_usuario` (`usuario_id`);

--
-- Indexes for table `inventario`
--
ALTER TABLE `inventario`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo_activo` (`codigo_activo`),
  ADD UNIQUE KEY `serie` (`serie`),
  ADD KEY `fk_inventario_categoria` (`categoria_id`),
  ADD KEY `fk_inventario_evaluador_descarte` (`evaluador_descarte_id`),
  ADD KEY `idx_inventario_estado` (`estado`),
  ADD KEY `idx_inventario_tipo` (`tipo_activo`),
  ADD KEY `idx_inventario_licencia_estado` (`es_licencia`,`estado_licencia`),
  ADD KEY `idx_inventario_licencia_vencimiento` (`fecha_vencimiento_licencia`);

--
-- Indexes for table `inventario_estado_historial`
--
ALTER TABLE `inventario_estado_historial`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_historial_estado_usuario` (`usuario_id`),
  ADD KEY `fk_historial_estado_firma` (`firma_id`),
  ADD KEY `idx_historial_inventario` (`inventario_id`,`created_at`),
  ADD KEY `idx_historial_estado_origen` (`entidad_origen`,`entidad_id`);

--
-- Indexes for table `inventario_imagenes`
--
ALTER TABLE `inventario_imagenes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_imagen_inventario` (`inventario_id`);

--
-- Indexes for table `inventario_qr`
--
ALTER TABLE `inventario_qr`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD UNIQUE KEY `idx_qr_token_hash` (`token_hash`),
  ADD KEY `idx_qr_inventario` (`inventario_id`),
  ADD KEY `idx_qr_estado` (`inventario_id`,`estado`,`activo`),
  ADD KEY `idx_qr_acceso` (`last_accessed_at`),
  ADD KEY `idx_qr_regenerado_desde` (`regenerated_from_id`);

--
-- Indexes for table `licencia_asignaciones`
--
ALTER TABLE `licencia_asignaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_licencia_asignacion_usuario` (`usuario_id`),
  ADD KEY `idx_licencia_asignaciones_estado` (`inventario_id`,`estado`),
  ADD KEY `idx_licencia_asignaciones_colaborador` (`colaborador_id`,`estado`);

--
-- Indexes for table `llaves_rsa`
--
ALTER TABLE `llaves_rsa`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `fingerprint` (`fingerprint`),
  ADD KEY `idx_llaves_rsa_usuario_estado` (`usuario_id`,`estado`),
  ADD KEY `idx_llaves_rsa_fingerprint` (`fingerprint`);

--
-- Indexes for table `necesidades`
--
ALTER TABLE `necesidades`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_necesidad_colaborador` (`colaborador_id`),
  ADD KEY `fk_necesidad_categoria` (`categoria_id`),
  ADD KEY `idx_necesidades_estado` (`estado`),
  ADD KEY `idx_necesidades_estado_formal` (`estado`,`prioridad`),
  ADD KEY `idx_necesidades_procesador` (`usuario_procesador_id`,`fecha_procesamiento`);

--
-- Indexes for table `necesidades_historial`
--
ALTER TABLE `necesidades_historial`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_necesidad_historial_necesidad` (`necesidad_id`),
  ADD KEY `fk_necesidad_historial_usuario` (`usuario_id`),
  ADD KEY `idx_necesidades_historial_firma` (`firma_id`),
  ADD KEY `idx_necesidades_historial_audit` (`audit_id`);

--
-- Indexes for table `noticias`
--
ALTER TABLE `noticias`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_noticia_usuario` (`usuario_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_reset_usuario` (`usuario_id`);

--
-- Indexes for table `presupuestos`
--
ALTER TABLE `presupuestos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_presupuesto_usuario` (`created_by`);

--
-- Indexes for table `presupuesto_detalles`
--
ALTER TABLE `presupuesto_detalles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_presupuesto_detalle_categoria` (`categoria_id`),
  ADD KEY `fk_presupuesto_detalle_necesidad` (`necesidad_id`),
  ADD KEY `idx_presupuesto_detalles_anio` (`presupuesto_id`,`anio`),
  ADD KEY `idx_presupuesto_detalles_costo` (`presupuesto_id`,`tiene_costo`),
  ADD KEY `idx_presupuesto_detalles_filtros` (`anio`,`categoria_id`,`tipo_necesidad`,`prioridad`,`estado_solicitud`);

--
-- Indexes for table `revisiones_tecnicas`
--
ALTER TABLE `revisiones_tecnicas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_revision_devolucion` (`devolucion_id`),
  ADD KEY `fk_revision_inventario` (`inventario_id`),
  ADD KEY `fk_revision_tecnico` (`tecnico_id`),
  ADD KEY `fk_revision_firma` (`firma_id`),
  ADD KEY `idx_revision_aprobador` (`aprobador_id`);

--
-- Indexes for table `ubicaciones_historial`
--
ALTER TABLE `ubicaciones_historial`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_ubicacion_historial_usuario` (`usuario_id`),
  ADD KEY `idx_ubicaciones_historial_colaborador` (`colaborador_id`,`created_at`),
  ADD KEY `idx_ubicaciones_historial_audit` (`audit_id`);

--
-- Indexes for table `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre_usuario` (`nombre_usuario`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `fk_usuario_colaborador` (`colaborador_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `accesos_portal_colaborador`
--
ALTER TABLE `accesos_portal_colaborador`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `api_tokens`
--
ALTER TABLE `api_tokens`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `asignaciones`
--
ALTER TABLE `asignaciones`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `bitacora`
--
ALTER TABLE `bitacora`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=168;

--
-- AUTO_INCREMENT for table `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `colaboradores`
--
ALTER TABLE `colaboradores`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `departamentos`
--
ALTER TABLE `departamentos`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=253;

--
-- AUTO_INCREMENT for table `devoluciones`
--
ALTER TABLE `devoluciones`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `firmas_digitales`
--
ALTER TABLE `firmas_digitales`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `intentos_login`
--
ALTER TABLE `intentos_login`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT for table `inventario`
--
ALTER TABLE `inventario`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `inventario_estado_historial`
--
ALTER TABLE `inventario_estado_historial`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT for table `inventario_imagenes`
--
ALTER TABLE `inventario_imagenes`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `inventario_qr`
--
ALTER TABLE `inventario_qr`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `licencia_asignaciones`
--
ALTER TABLE `licencia_asignaciones`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `llaves_rsa`
--
ALTER TABLE `llaves_rsa`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `necesidades`
--
ALTER TABLE `necesidades`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `necesidades_historial`
--
ALTER TABLE `necesidades_historial`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `noticias`
--
ALTER TABLE `noticias`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `presupuestos`
--
ALTER TABLE `presupuestos`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `presupuesto_detalles`
--
ALTER TABLE `presupuesto_detalles`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `revisiones_tecnicas`
--
ALTER TABLE `revisiones_tecnicas`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `ubicaciones_historial`
--
ALTER TABLE `ubicaciones_historial`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `accesos_portal_colaborador`
--
ALTER TABLE `accesos_portal_colaborador`
  ADD CONSTRAINT `fk_portal_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `api_tokens`
--
ALTER TABLE `api_tokens`
  ADD CONSTRAINT `fk_api_tokens_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `asignaciones`
--
ALTER TABLE `asignaciones`
  ADD CONSTRAINT `fk_asignacion_colaborador` FOREIGN KEY (`colaborador_id`) REFERENCES `colaboradores` (`id`),
  ADD CONSTRAINT `fk_asignacion_inventario` FOREIGN KEY (`inventario_id`) REFERENCES `inventario` (`id`),
  ADD CONSTRAINT `fk_asignacion_usuario` FOREIGN KEY (`usuario_asignador_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `bitacora`
--
ALTER TABLE `bitacora`
  ADD CONSTRAINT `fk_bitacora_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `devoluciones`
--
ALTER TABLE `devoluciones`
  ADD CONSTRAINT `fk_devolucion_asignacion` FOREIGN KEY (`asignacion_id`) REFERENCES `asignaciones` (`id`),
  ADD CONSTRAINT `fk_devolucion_inventario` FOREIGN KEY (`inventario_id`) REFERENCES `inventario` (`id`),
  ADD CONSTRAINT `fk_devolucion_recibido` FOREIGN KEY (`recibido_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_devolucion_solicitado` FOREIGN KEY (`solicitado_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `firmas_digitales`
--
ALTER TABLE `firmas_digitales`
  ADD CONSTRAINT `fk_firma_llave` FOREIGN KEY (`llave_id`) REFERENCES `llaves_rsa` (`id`),
  ADD CONSTRAINT `fk_firma_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `intentos_login`
--
ALTER TABLE `intentos_login`
  ADD CONSTRAINT `fk_intentos_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `inventario`
--
ALTER TABLE `inventario`
  ADD CONSTRAINT `fk_inventario_categoria` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`),
  ADD CONSTRAINT `fk_inventario_evaluador_descarte` FOREIGN KEY (`evaluador_descarte_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `inventario_estado_historial`
--
ALTER TABLE `inventario_estado_historial`
  ADD CONSTRAINT `fk_historial_estado_firma` FOREIGN KEY (`firma_id`) REFERENCES `firmas_digitales` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_historial_estado_inventario` FOREIGN KEY (`inventario_id`) REFERENCES `inventario` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_historial_estado_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `inventario_imagenes`
--
ALTER TABLE `inventario_imagenes`
  ADD CONSTRAINT `fk_imagen_inventario` FOREIGN KEY (`inventario_id`) REFERENCES `inventario` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `inventario_qr`
--
ALTER TABLE `inventario_qr`
  ADD CONSTRAINT `fk_qr_inventario` FOREIGN KEY (`inventario_id`) REFERENCES `inventario` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `licencia_asignaciones`
--
ALTER TABLE `licencia_asignaciones`
  ADD CONSTRAINT `fk_licencia_asignacion_colaborador` FOREIGN KEY (`colaborador_id`) REFERENCES `colaboradores` (`id`),
  ADD CONSTRAINT `fk_licencia_asignacion_inventario` FOREIGN KEY (`inventario_id`) REFERENCES `inventario` (`id`),
  ADD CONSTRAINT `fk_licencia_asignacion_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `llaves_rsa`
--
ALTER TABLE `llaves_rsa`
  ADD CONSTRAINT `fk_llave_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `necesidades`
--
ALTER TABLE `necesidades`
  ADD CONSTRAINT `fk_necesidad_categoria` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`),
  ADD CONSTRAINT `fk_necesidad_colaborador` FOREIGN KEY (`colaborador_id`) REFERENCES `colaboradores` (`id`);

--
-- Constraints for table `necesidades_historial`
--
ALTER TABLE `necesidades_historial`
  ADD CONSTRAINT `fk_necesidad_historial_necesidad` FOREIGN KEY (`necesidad_id`) REFERENCES `necesidades` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_necesidad_historial_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `noticias`
--
ALTER TABLE `noticias`
  ADD CONSTRAINT `fk_noticia_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `fk_reset_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `presupuestos`
--
ALTER TABLE `presupuestos`
  ADD CONSTRAINT `fk_presupuesto_usuario` FOREIGN KEY (`created_by`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `presupuesto_detalles`
--
ALTER TABLE `presupuesto_detalles`
  ADD CONSTRAINT `fk_presupuesto_detalle_categoria` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_presupuesto_detalle_necesidad` FOREIGN KEY (`necesidad_id`) REFERENCES `necesidades` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_presupuesto_detalle_presupuesto` FOREIGN KEY (`presupuesto_id`) REFERENCES `presupuestos` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `revisiones_tecnicas`
--
ALTER TABLE `revisiones_tecnicas`
  ADD CONSTRAINT `fk_revision_devolucion` FOREIGN KEY (`devolucion_id`) REFERENCES `devoluciones` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_revision_firma` FOREIGN KEY (`firma_id`) REFERENCES `firmas_digitales` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_revision_inventario` FOREIGN KEY (`inventario_id`) REFERENCES `inventario` (`id`),
  ADD CONSTRAINT `fk_revision_tecnico` FOREIGN KEY (`tecnico_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `ubicaciones_historial`
--
ALTER TABLE `ubicaciones_historial`
  ADD CONSTRAINT `fk_ubicacion_historial_colaborador` FOREIGN KEY (`colaborador_id`) REFERENCES `colaboradores` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ubicacion_historial_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `fk_usuario_colaborador` FOREIGN KEY (`colaborador_id`) REFERENCES `colaboradores` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
