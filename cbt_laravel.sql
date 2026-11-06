-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 06 Nov 2026 pada 12.05
-- Versi server: 8.4.11
-- Versi PHP: 8.4.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Basis data: `cbt_laravel`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `att_attendance_audit_events`
--

CREATE TABLE `att_attendance_audit_events` (
  `id` bigint UNSIGNED NOT NULL,
  `event_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `session_id` bigint UNSIGNED DEFAULT NULL,
  `student_id` bigint UNSIGNED DEFAULT NULL,
  `record_id` bigint UNSIGNED DEFAULT NULL,
  `actor_type` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `actor_id` bigint UNSIGNED DEFAULT NULL,
  `metadata` json DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `att_attendance_corrections`
--

CREATE TABLE `att_attendance_corrections` (
  `id` bigint UNSIGNED NOT NULL,
  `record_id` bigint UNSIGNED NOT NULL,
  `old_status` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `new_status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `old_method` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `new_method` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `old_check_in_at` datetime DEFAULT NULL,
  `new_check_in_at` datetime DEFAULT NULL,
  `reason` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `requested_by` bigint UNSIGNED DEFAULT NULL,
  `approved_by` bigint UNSIGNED DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `att_attendance_locations`
--

CREATE TABLE `att_attendance_locations` (
  `id` bigint UNSIGNED NOT NULL,
  `foundation_id` bigint UNSIGNED DEFAULT NULL,
  `school_id` bigint UNSIGNED NOT NULL,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `latitude` decimal(10,7) NOT NULL,
  `longitude` decimal(10,7) NOT NULL,
  `radius_meters` int UNSIGNED NOT NULL DEFAULT '50',
  `max_accuracy_meters` int UNSIGNED NOT NULL DEFAULT '100',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `valid_from` date DEFAULT NULL,
  `valid_to` date DEFAULT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `att_attendance_locations`
--

INSERT INTO `att_attendance_locations` (`id`, `foundation_id`, `school_id`, `name`, `latitude`, `longitude`, `radius_meters`, `max_accuracy_meters`, `is_active`, `valid_from`, `valid_to`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'SMK 1 BEKASI', -6.2685243, 107.1044362, 50, 100, 1, '2026-11-05', '2026-11-30', 16, '2026-11-05 23:29:00', '2026-11-06 01:55:10'),
(2, 1, 2, 'Gedung Utama', -6.2000000, 106.8166667, 50, 100, 1, NULL, NULL, NULL, '2026-11-05 23:55:25', '2026-11-05 23:55:25'),
(3, 1, 3, 'Gedung Utama', -6.2000000, 106.8166667, 50, 100, 1, NULL, NULL, NULL, '2026-11-05 23:55:25', '2026-11-05 23:55:25'),
(4, 1, 4, 'Gedung Utama', -6.2000000, 106.8166667, 50, 100, 1, NULL, NULL, NULL, '2026-11-05 23:55:25', '2026-11-05 23:55:25'),
(5, 1, 1, 'Gedung Utama', -6.2000000, 106.8166667, 50, 100, 1, NULL, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00');

-- --------------------------------------------------------

--
-- Struktur dari tabel `att_attendance_location_checks`
--

CREATE TABLE `att_attendance_location_checks` (
  `id` bigint UNSIGNED NOT NULL,
  `session_id` bigint UNSIGNED NOT NULL,
  `student_id` bigint UNSIGNED NOT NULL,
  `record_id` bigint UNSIGNED DEFAULT NULL,
  `foundation_id` bigint UNSIGNED DEFAULT NULL,
  `school_id` bigint UNSIGNED NOT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `accuracy_meters` decimal(8,2) DEFAULT NULL,
  `distance_meters` decimal(10,2) DEFAULT NULL,
  `location_id` bigint UNSIGNED DEFAULT NULL,
  `validation_status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `reason` text COLLATE utf8mb4_unicode_ci,
  `source` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'gps',
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `checked_at` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `att_attendance_policies`
--

CREATE TABLE `att_attendance_policies` (
  `id` bigint UNSIGNED NOT NULL,
  `foundation_id` bigint UNSIGNED DEFAULT NULL,
  `school_id` bigint UNSIGNED NOT NULL,
  `academic_year_id` bigint UNSIGNED DEFAULT NULL,
  `policy_type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'daily',
  `require_location` tinyint(1) NOT NULL DEFAULT '1',
  `allow_manual` tinyint(1) NOT NULL DEFAULT '1',
  `require_checkin` tinyint(1) NOT NULL DEFAULT '1',
  `require_checkout` tinyint(1) NOT NULL DEFAULT '0',
  `service_start` time DEFAULT NULL,
  `service_end` time DEFAULT NULL,
  `late_tolerance_minutes` int UNSIGNED NOT NULL DEFAULT '0',
  `max_accuracy_meters` int UNSIGNED NOT NULL DEFAULT '100',
  `is_required` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `att_attendance_policies`
--

INSERT INTO `att_attendance_policies` (`id`, `foundation_id`, `school_id`, `academic_year_id`, `policy_type`, `require_location`, `allow_manual`, `require_checkin`, `require_checkout`, `service_start`, `service_end`, `late_tolerance_minutes`, `max_accuracy_meters`, `is_required`, `created_at`, `updated_at`) VALUES
(1, 1, 3, NULL, 'daily', 1, 1, 1, 0, '07:00:00', '15:00:00', 15, 100, 0, '2026-11-05 05:48:03', '2026-11-06 00:13:00'),
(2, 1, 1, NULL, 'daily', 1, 1, 1, 0, '07:00:00', '15:00:00', 15, 100, 0, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(3, 1, 2, NULL, 'daily', 1, 1, 1, 0, '07:00:00', '15:00:00', 15, 100, 0, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(4, 1, 4, NULL, 'daily', 1, 1, 1, 0, '07:00:00', '15:00:00', 15, 100, 0, '2026-11-06 00:13:00', '2026-11-06 00:13:00');

-- --------------------------------------------------------

--
-- Struktur dari tabel `att_attendance_records`
--

CREATE TABLE `att_attendance_records` (
  `id` bigint UNSIGNED NOT NULL,
  `session_id` bigint UNSIGNED NOT NULL,
  `foundation_id` bigint UNSIGNED DEFAULT NULL,
  `school_id` bigint UNSIGNED NOT NULL,
  `student_id` bigint UNSIGNED NOT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'alpha',
  `method` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'manual',
  `check_in_at` datetime DEFAULT NULL,
  `check_out_at` datetime DEFAULT NULL,
  `location_id` bigint UNSIGNED DEFAULT NULL,
  `distance_meters` decimal(10,2) DEFAULT NULL,
  `accuracy_meters` decimal(8,2) DEFAULT NULL,
  `is_override` tinyint(1) NOT NULL DEFAULT '0',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `actor_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `att_attendance_records`
--

INSERT INTO `att_attendance_records` (`id`, `session_id`, `foundation_id`, `school_id`, `student_id`, `status`, `method`, `check_in_at`, `check_out_at`, `location_id`, `distance_meters`, `accuracy_meters`, `is_override`, `notes`, `actor_id`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 3, 41, 'sakit', 'gps', NULL, NULL, 3, NULL, NULL, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(2, 2, 1, 3, 41, 'hadir', 'gps', '2026-11-02 06:43:00', '2026-11-02 15:13:00', 3, 11.00, 13.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(3, 3, 1, 3, 41, 'hadir', 'gps', '2026-11-03 06:42:00', '2026-11-03 15:12:00', 3, 10.00, 12.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(4, 4, 1, 3, 41, 'hadir', 'gps', '2026-11-04 06:41:00', '2026-11-04 15:11:00', 3, 9.00, 11.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(5, 1, 1, 3, 42, 'hadir', 'gps', '2026-10-30 06:35:00', '2026-10-30 15:05:00', 3, 3.00, 5.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(6, 2, 1, 3, 42, 'terlambat', 'gps', '2026-11-02 07:39:00', '2026-11-02 15:14:00', 3, 12.00, 14.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(7, 3, 1, 3, 42, 'hadir', 'gps', '2026-11-03 06:43:00', '2026-11-03 15:13:00', 3, 11.00, 13.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(8, 4, 1, 3, 42, 'hadir', 'gps', '2026-11-04 06:42:00', '2026-11-04 15:12:00', 3, 10.00, 12.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(9, 1, 1, 3, 43, 'hadir', 'gps', '2026-10-30 06:36:00', '2026-10-30 15:06:00', 3, 4.00, 6.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(10, 2, 1, 3, 43, 'izin', 'gps', NULL, NULL, 3, NULL, NULL, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(11, 3, 1, 3, 43, 'terlambat', 'gps', '2026-11-03 07:39:00', '2026-11-03 15:14:00', 3, 12.00, 14.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(12, 4, 1, 3, 43, 'hadir', 'gps', '2026-11-04 06:43:00', '2026-11-04 15:13:00', 3, 11.00, 13.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(13, 1, 1, 3, 49, 'hadir', 'gps', '2026-10-30 06:42:00', '2026-10-30 15:12:00', 3, 10.00, 12.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(14, 2, 1, 3, 49, 'hadir', 'gps', '2026-11-02 06:39:00', '2026-11-02 15:09:00', 3, 7.00, 9.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(15, 3, 1, 3, 49, 'hadir', 'gps', '2026-11-03 06:38:00', '2026-11-03 15:08:00', 3, 6.00, 8.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(16, 4, 1, 3, 49, 'hadir', 'gps', '2026-11-04 06:37:00', '2026-11-04 15:07:00', 3, 5.00, 7.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(17, 1, 1, 3, 50, 'hadir', 'gps', '2026-10-30 06:43:00', '2026-10-30 15:13:00', 3, 11.00, 13.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(18, 2, 1, 3, 50, 'hadir', 'gps', '2026-11-02 06:40:00', '2026-11-02 15:10:00', 3, 8.00, 10.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(19, 3, 1, 3, 50, 'hadir', 'gps', '2026-11-03 06:39:00', '2026-11-03 15:09:00', 3, 7.00, 9.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(20, 4, 1, 3, 50, 'hadir', 'gps', '2026-11-04 06:38:00', '2026-11-04 15:08:00', 3, 6.00, 8.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(21, 1, 1, 3, 51, 'terlambat', 'gps', '2026-10-30 07:39:00', '2026-10-30 15:14:00', 3, 12.00, 14.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(22, 2, 1, 3, 51, 'hadir', 'gps', '2026-11-02 06:41:00', '2026-11-02 15:11:00', 3, 9.00, 11.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(23, 3, 1, 3, 51, 'hadir', 'gps', '2026-11-03 06:40:00', '2026-11-03 15:10:00', 3, 8.00, 10.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(24, 4, 1, 3, 51, 'hadir', 'gps', '2026-11-04 06:39:00', '2026-11-04 15:09:00', 3, 7.00, 9.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(25, 1, 1, 3, 52, 'izin', 'gps', NULL, NULL, 3, NULL, NULL, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(26, 2, 1, 3, 52, 'hadir', 'gps', '2026-11-02 06:42:00', '2026-11-02 15:12:00', 3, 10.00, 12.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(27, 3, 1, 3, 52, 'hadir', 'gps', '2026-11-03 06:41:00', '2026-11-03 15:11:00', 3, 9.00, 11.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(28, 4, 1, 3, 52, 'hadir', 'gps', '2026-11-04 06:40:00', '2026-11-04 15:10:00', 3, 8.00, 10.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(29, 1, 1, 3, 53, 'sakit', 'gps', NULL, NULL, 3, NULL, NULL, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(30, 2, 1, 3, 53, 'hadir', 'gps', '2026-11-02 06:43:00', '2026-11-02 15:13:00', 3, 11.00, 13.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(31, 3, 1, 3, 53, 'hadir', 'gps', '2026-11-03 06:42:00', '2026-11-03 15:12:00', 3, 10.00, 12.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(32, 4, 1, 3, 53, 'hadir', 'gps', '2026-11-04 06:41:00', '2026-11-04 15:11:00', 3, 9.00, 11.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(33, 5, 1, 1, 54, 'hadir', 'gps', '2026-10-30 06:35:00', '2026-10-30 15:05:00', 1, 3.00, 5.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(34, 6, 1, 1, 54, 'terlambat', 'gps', '2026-11-02 07:39:00', '2026-11-02 15:14:00', 1, 12.00, 14.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(35, 7, 1, 1, 54, 'hadir', 'gps', '2026-11-03 06:43:00', '2026-11-03 15:13:00', 1, 11.00, 13.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(36, 8, 1, 1, 54, 'hadir', 'gps', '2026-11-04 06:42:00', '2026-11-04 15:12:00', 1, 10.00, 12.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(37, 5, 1, 1, 55, 'hadir', 'gps', '2026-10-30 06:36:00', '2026-10-30 15:06:00', 1, 4.00, 6.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(38, 6, 1, 1, 55, 'izin', 'gps', NULL, NULL, 1, NULL, NULL, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(39, 7, 1, 1, 55, 'terlambat', 'gps', '2026-11-03 07:39:00', '2026-11-03 15:14:00', 1, 12.00, 14.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(40, 8, 1, 1, 55, 'hadir', 'gps', '2026-11-04 06:43:00', '2026-11-04 15:13:00', 1, 11.00, 13.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(41, 5, 1, 1, 56, 'hadir', 'gps', '2026-10-30 06:37:00', '2026-10-30 15:07:00', 1, 5.00, 7.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(42, 6, 1, 1, 56, 'sakit', 'gps', NULL, NULL, 1, NULL, NULL, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(43, 7, 1, 1, 56, 'izin', 'gps', NULL, NULL, 1, NULL, NULL, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(44, 8, 1, 1, 56, 'terlambat', 'gps', '2026-11-04 07:39:00', '2026-11-04 15:14:00', 1, 12.00, 14.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(45, 5, 1, 1, 57, 'hadir', 'gps', '2026-10-30 06:38:00', '2026-10-30 15:08:00', 1, 6.00, 8.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(46, 6, 1, 1, 57, 'hadir', 'gps', '2026-11-02 06:35:00', '2026-11-02 15:05:00', 1, 3.00, 5.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(47, 7, 1, 1, 57, 'sakit', 'gps', NULL, NULL, 1, NULL, NULL, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(48, 8, 1, 1, 57, 'izin', 'gps', NULL, NULL, 1, NULL, NULL, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(49, 5, 1, 1, 58, 'hadir', 'gps', '2026-10-30 06:39:00', '2026-10-30 15:09:00', 1, 7.00, 9.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(50, 6, 1, 1, 58, 'hadir', 'gps', '2026-11-02 06:36:00', '2026-11-02 15:06:00', 1, 4.00, 6.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(51, 7, 1, 1, 58, 'hadir', 'gps', '2026-11-03 06:35:00', '2026-11-03 15:05:00', 1, 3.00, 5.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(52, 8, 1, 1, 58, 'sakit', 'gps', NULL, NULL, 1, NULL, NULL, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(53, 5, 1, 1, 59, 'hadir', 'gps', '2026-10-30 06:40:00', '2026-10-30 15:10:00', 1, 8.00, 10.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(54, 6, 1, 1, 59, 'hadir', 'gps', '2026-11-02 06:37:00', '2026-11-02 15:07:00', 1, 5.00, 7.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(55, 7, 1, 1, 59, 'hadir', 'gps', '2026-11-03 06:36:00', '2026-11-03 15:06:00', 1, 4.00, 6.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(56, 8, 1, 1, 59, 'hadir', 'gps', '2026-11-04 06:35:00', '2026-11-04 15:05:00', 1, 3.00, 5.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(57, 5, 1, 1, 60, 'hadir', 'gps', '2026-10-30 06:41:00', '2026-10-30 15:11:00', 1, 9.00, 11.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(58, 6, 1, 1, 60, 'hadir', 'gps', '2026-11-02 06:38:00', '2026-11-02 15:08:00', 1, 6.00, 8.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(59, 7, 1, 1, 60, 'hadir', 'gps', '2026-11-03 06:37:00', '2026-11-03 15:07:00', 1, 5.00, 7.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(60, 8, 1, 1, 60, 'hadir', 'gps', '2026-11-04 06:36:00', '2026-11-04 15:06:00', 1, 4.00, 6.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(61, 5, 1, 1, 61, 'hadir', 'gps', '2026-10-30 06:42:00', '2026-10-30 15:12:00', 1, 10.00, 12.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(62, 6, 1, 1, 61, 'hadir', 'gps', '2026-11-02 06:39:00', '2026-11-02 15:09:00', 1, 7.00, 9.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(63, 7, 1, 1, 61, 'hadir', 'gps', '2026-11-03 06:38:00', '2026-11-03 15:08:00', 1, 6.00, 8.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(64, 8, 1, 1, 61, 'hadir', 'gps', '2026-11-04 06:37:00', '2026-11-04 15:07:00', 1, 5.00, 7.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(65, 5, 1, 1, 62, 'hadir', 'gps', '2026-10-30 06:43:00', '2026-10-30 15:13:00', 1, 11.00, 13.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(66, 6, 1, 1, 62, 'hadir', 'gps', '2026-11-02 06:40:00', '2026-11-02 15:10:00', 1, 8.00, 10.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(67, 7, 1, 1, 62, 'hadir', 'gps', '2026-11-03 06:39:00', '2026-11-03 15:09:00', 1, 7.00, 9.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(68, 8, 1, 1, 62, 'hadir', 'gps', '2026-11-04 06:38:00', '2026-11-04 15:08:00', 1, 6.00, 8.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(69, 5, 1, 1, 63, 'terlambat', 'gps', '2026-10-30 07:39:00', '2026-10-30 15:14:00', 1, 12.00, 14.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(70, 6, 1, 1, 63, 'hadir', 'gps', '2026-11-02 06:41:00', '2026-11-02 15:11:00', 1, 9.00, 11.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(71, 7, 1, 1, 63, 'hadir', 'gps', '2026-11-03 06:40:00', '2026-11-03 15:10:00', 1, 8.00, 10.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(72, 8, 1, 1, 63, 'hadir', 'gps', '2026-11-04 06:39:00', '2026-11-04 15:09:00', 1, 7.00, 9.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(73, 5, 1, 1, 64, 'izin', 'gps', NULL, NULL, 1, NULL, NULL, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(74, 6, 1, 1, 64, 'hadir', 'gps', '2026-11-02 06:42:00', '2026-11-02 15:12:00', 1, 10.00, 12.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(75, 7, 1, 1, 64, 'hadir', 'gps', '2026-11-03 06:41:00', '2026-11-03 15:11:00', 1, 9.00, 11.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(76, 8, 1, 1, 64, 'hadir', 'gps', '2026-11-04 06:40:00', '2026-11-04 15:10:00', 1, 8.00, 10.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(77, 5, 1, 1, 65, 'sakit', 'gps', NULL, NULL, 1, NULL, NULL, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(78, 6, 1, 1, 65, 'hadir', 'gps', '2026-11-02 06:43:00', '2026-11-02 15:13:00', 1, 11.00, 13.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(79, 7, 1, 1, 65, 'hadir', 'gps', '2026-11-03 06:42:00', '2026-11-03 15:12:00', 1, 10.00, 12.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(80, 8, 1, 1, 65, 'hadir', 'gps', '2026-11-04 06:41:00', '2026-11-04 15:11:00', 1, 9.00, 11.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(81, 9, 1, 2, 66, 'hadir', 'gps', '2026-10-30 06:35:00', '2026-10-30 15:05:00', 2, 3.00, 5.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(82, 10, 1, 2, 66, 'terlambat', 'gps', '2026-11-02 07:39:00', '2026-11-02 15:14:00', 2, 12.00, 14.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(83, 11, 1, 2, 66, 'hadir', 'gps', '2026-11-03 06:43:00', '2026-11-03 15:13:00', 2, 11.00, 13.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(84, 12, 1, 2, 66, 'hadir', 'gps', '2026-11-04 06:42:00', '2026-11-04 15:12:00', 2, 10.00, 12.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(85, 9, 1, 2, 67, 'hadir', 'gps', '2026-10-30 06:36:00', '2026-10-30 15:06:00', 2, 4.00, 6.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(86, 10, 1, 2, 67, 'izin', 'gps', NULL, NULL, 2, NULL, NULL, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(87, 11, 1, 2, 67, 'terlambat', 'gps', '2026-11-03 07:39:00', '2026-11-03 15:14:00', 2, 12.00, 14.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(88, 12, 1, 2, 67, 'hadir', 'gps', '2026-11-04 06:43:00', '2026-11-04 15:13:00', 2, 11.00, 13.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(89, 9, 1, 2, 68, 'hadir', 'gps', '2026-10-30 06:37:00', '2026-10-30 15:07:00', 2, 5.00, 7.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(90, 10, 1, 2, 68, 'sakit', 'gps', NULL, NULL, 2, NULL, NULL, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(91, 11, 1, 2, 68, 'izin', 'gps', NULL, NULL, 2, NULL, NULL, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(92, 12, 1, 2, 68, 'terlambat', 'gps', '2026-11-04 07:39:00', '2026-11-04 15:14:00', 2, 12.00, 14.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(93, 9, 1, 2, 69, 'hadir', 'gps', '2026-10-30 06:38:00', '2026-10-30 15:08:00', 2, 6.00, 8.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(94, 10, 1, 2, 69, 'hadir', 'gps', '2026-11-02 06:35:00', '2026-11-02 15:05:00', 2, 3.00, 5.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(95, 11, 1, 2, 69, 'sakit', 'gps', NULL, NULL, 2, NULL, NULL, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(96, 12, 1, 2, 69, 'izin', 'gps', NULL, NULL, 2, NULL, NULL, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(97, 9, 1, 2, 70, 'hadir', 'gps', '2026-10-30 06:39:00', '2026-10-30 15:09:00', 2, 7.00, 9.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(98, 10, 1, 2, 70, 'hadir', 'gps', '2026-11-02 06:36:00', '2026-11-02 15:06:00', 2, 4.00, 6.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(99, 11, 1, 2, 70, 'hadir', 'gps', '2026-11-03 06:35:00', '2026-11-03 15:05:00', 2, 3.00, 5.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(100, 12, 1, 2, 70, 'sakit', 'gps', NULL, NULL, 2, NULL, NULL, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(101, 9, 1, 2, 71, 'hadir', 'gps', '2026-10-30 06:40:00', '2026-10-30 15:10:00', 2, 8.00, 10.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(102, 10, 1, 2, 71, 'hadir', 'gps', '2026-11-02 06:37:00', '2026-11-02 15:07:00', 2, 5.00, 7.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(103, 11, 1, 2, 71, 'hadir', 'gps', '2026-11-03 06:36:00', '2026-11-03 15:06:00', 2, 4.00, 6.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(104, 12, 1, 2, 71, 'hadir', 'gps', '2026-11-04 06:35:00', '2026-11-04 15:05:00', 2, 3.00, 5.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(105, 1, 1, 3, 72, 'hadir', 'gps', '2026-10-30 06:41:00', '2026-10-30 15:11:00', 3, 9.00, 11.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(106, 2, 1, 3, 72, 'hadir', 'gps', '2026-11-02 06:38:00', '2026-11-02 15:08:00', 3, 6.00, 8.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(107, 3, 1, 3, 72, 'hadir', 'gps', '2026-11-03 06:37:00', '2026-11-03 15:07:00', 3, 5.00, 7.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(108, 4, 1, 3, 72, 'hadir', 'gps', '2026-11-04 06:36:00', '2026-11-04 15:06:00', 3, 4.00, 6.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(109, 1, 1, 3, 73, 'hadir', 'gps', '2026-10-30 06:42:00', '2026-10-30 15:12:00', 3, 10.00, 12.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(110, 2, 1, 3, 73, 'hadir', 'gps', '2026-11-02 06:39:00', '2026-11-02 15:09:00', 3, 7.00, 9.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(111, 3, 1, 3, 73, 'hadir', 'gps', '2026-11-03 06:38:00', '2026-11-03 15:08:00', 3, 6.00, 8.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(112, 4, 1, 3, 73, 'hadir', 'gps', '2026-11-04 06:37:00', '2026-11-04 15:07:00', 3, 5.00, 7.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(113, 1, 1, 3, 74, 'hadir', 'gps', '2026-10-30 06:43:00', '2026-10-30 15:13:00', 3, 11.00, 13.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(114, 2, 1, 3, 74, 'hadir', 'gps', '2026-11-02 06:40:00', '2026-11-02 15:10:00', 3, 8.00, 10.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(115, 3, 1, 3, 74, 'hadir', 'gps', '2026-11-03 06:39:00', '2026-11-03 15:09:00', 3, 7.00, 9.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(116, 4, 1, 3, 74, 'hadir', 'gps', '2026-11-04 06:38:00', '2026-11-04 15:08:00', 3, 6.00, 8.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(117, 1, 1, 3, 75, 'terlambat', 'gps', '2026-10-30 07:39:00', '2026-10-30 15:14:00', 3, 12.00, 14.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(118, 2, 1, 3, 75, 'hadir', 'gps', '2026-11-02 06:41:00', '2026-11-02 15:11:00', 3, 9.00, 11.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(119, 3, 1, 3, 75, 'hadir', 'gps', '2026-11-03 06:40:00', '2026-11-03 15:10:00', 3, 8.00, 10.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(120, 4, 1, 3, 75, 'hadir', 'gps', '2026-11-04 06:39:00', '2026-11-04 15:09:00', 3, 7.00, 9.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(121, 1, 1, 3, 76, 'izin', 'gps', NULL, NULL, 3, NULL, NULL, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(122, 2, 1, 3, 76, 'hadir', 'gps', '2026-11-02 06:42:00', '2026-11-02 15:12:00', 3, 10.00, 12.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(123, 3, 1, 3, 76, 'hadir', 'gps', '2026-11-03 06:41:00', '2026-11-03 15:11:00', 3, 9.00, 11.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(124, 4, 1, 3, 76, 'hadir', 'gps', '2026-11-04 06:40:00', '2026-11-04 15:10:00', 3, 8.00, 10.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(125, 1, 1, 3, 77, 'sakit', 'gps', NULL, NULL, 3, NULL, NULL, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(126, 2, 1, 3, 77, 'hadir', 'gps', '2026-11-02 06:43:00', '2026-11-02 15:13:00', 3, 11.00, 13.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(127, 3, 1, 3, 77, 'hadir', 'gps', '2026-11-03 06:42:00', '2026-11-03 15:12:00', 3, 10.00, 12.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(128, 4, 1, 3, 77, 'hadir', 'gps', '2026-11-04 06:41:00', '2026-11-04 15:11:00', 3, 9.00, 11.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(129, 13, 1, 4, 78, 'hadir', 'gps', '2026-10-30 06:35:00', '2026-10-30 15:05:00', 4, 3.00, 5.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(130, 14, 1, 4, 78, 'terlambat', 'gps', '2026-11-02 07:39:00', '2026-11-02 15:14:00', 4, 12.00, 14.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(131, 15, 1, 4, 78, 'hadir', 'gps', '2026-11-03 06:43:00', '2026-11-03 15:13:00', 4, 11.00, 13.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(132, 16, 1, 4, 78, 'hadir', 'gps', '2026-11-04 06:42:00', '2026-11-04 15:12:00', 4, 10.00, 12.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(133, 13, 1, 4, 79, 'hadir', 'gps', '2026-10-30 06:36:00', '2026-10-30 15:06:00', 4, 4.00, 6.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(134, 14, 1, 4, 79, 'izin', 'gps', NULL, NULL, 4, NULL, NULL, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(135, 15, 1, 4, 79, 'terlambat', 'gps', '2026-11-03 07:39:00', '2026-11-03 15:14:00', 4, 12.00, 14.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(136, 16, 1, 4, 79, 'hadir', 'gps', '2026-11-04 06:43:00', '2026-11-04 15:13:00', 4, 11.00, 13.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(137, 13, 1, 4, 80, 'hadir', 'gps', '2026-10-30 06:37:00', '2026-10-30 15:07:00', 4, 5.00, 7.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(138, 14, 1, 4, 80, 'sakit', 'gps', NULL, NULL, 4, NULL, NULL, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(139, 15, 1, 4, 80, 'izin', 'gps', NULL, NULL, 4, NULL, NULL, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(140, 16, 1, 4, 80, 'terlambat', 'gps', '2026-11-04 07:39:00', '2026-11-04 15:14:00', 4, 12.00, 14.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(141, 13, 1, 4, 81, 'hadir', 'gps', '2026-10-30 06:38:00', '2026-10-30 15:08:00', 4, 6.00, 8.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(142, 14, 1, 4, 81, 'hadir', 'gps', '2026-11-02 06:35:00', '2026-11-02 15:05:00', 4, 3.00, 5.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(143, 15, 1, 4, 81, 'sakit', 'gps', NULL, NULL, 4, NULL, NULL, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(144, 16, 1, 4, 81, 'izin', 'gps', NULL, NULL, 4, NULL, NULL, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(145, 13, 1, 4, 82, 'hadir', 'gps', '2026-10-30 06:39:00', '2026-10-30 15:09:00', 4, 7.00, 9.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(146, 14, 1, 4, 82, 'hadir', 'gps', '2026-11-02 06:36:00', '2026-11-02 15:06:00', 4, 4.00, 6.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(147, 15, 1, 4, 82, 'hadir', 'gps', '2026-11-03 06:35:00', '2026-11-03 15:05:00', 4, 3.00, 5.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(148, 16, 1, 4, 82, 'sakit', 'gps', NULL, NULL, 4, NULL, NULL, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(149, 13, 1, 4, 83, 'hadir', 'gps', '2026-10-30 06:40:00', '2026-10-30 15:10:00', 4, 8.00, 10.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(150, 14, 1, 4, 83, 'hadir', 'gps', '2026-11-02 06:37:00', '2026-11-02 15:07:00', 4, 5.00, 7.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(151, 15, 1, 4, 83, 'hadir', 'gps', '2026-11-03 06:36:00', '2026-11-03 15:06:00', 4, 4.00, 6.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(152, 16, 1, 4, 83, 'hadir', 'gps', '2026-11-04 06:35:00', '2026-11-04 15:05:00', 4, 3.00, 5.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(153, 1, 1, 3, 84, 'hadir', 'gps', '2026-10-30 06:41:00', '2026-10-30 15:11:00', 3, 9.00, 11.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(154, 2, 1, 3, 84, 'hadir', 'gps', '2026-11-02 06:38:00', '2026-11-02 15:08:00', 3, 6.00, 8.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(155, 3, 1, 3, 84, 'hadir', 'gps', '2026-11-03 06:37:00', '2026-11-03 15:07:00', 3, 5.00, 7.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(156, 4, 1, 3, 84, 'hadir', 'gps', '2026-11-04 06:36:00', '2026-11-04 15:06:00', 3, 4.00, 6.00, 0, NULL, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00');

-- --------------------------------------------------------

--
-- Struktur dari tabel `att_attendance_sessions`
--

CREATE TABLE `att_attendance_sessions` (
  `id` bigint UNSIGNED NOT NULL,
  `foundation_id` bigint UNSIGNED DEFAULT NULL,
  `school_id` bigint UNSIGNED NOT NULL,
  `academic_year_id` bigint UNSIGNED DEFAULT NULL,
  `exam_id` bigint UNSIGNED DEFAULT NULL,
  `session_type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'daily',
  `session_date` date NOT NULL,
  `starts_at` datetime DEFAULT NULL,
  `ends_at` datetime DEFAULT NULL,
  `location_id` bigint UNSIGNED DEFAULT NULL,
  `requires_checkin` tinyint(1) NOT NULL DEFAULT '1',
  `allow_manual` tinyint(1) NOT NULL DEFAULT '1',
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `att_attendance_sessions`
--

INSERT INTO `att_attendance_sessions` (`id`, `foundation_id`, `school_id`, `academic_year_id`, `exam_id`, `session_type`, `session_date`, `starts_at`, `ends_at`, `location_id`, `requires_checkin`, `allow_manual`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 1, 3, 3, NULL, 'daily', '2026-10-30', '2026-10-30 07:00:00', '2026-10-30 15:00:00', 3, 1, 1, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(2, 1, 3, 3, NULL, 'daily', '2026-11-02', '2026-11-02 07:00:00', '2026-11-02 15:00:00', 3, 1, 1, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(3, 1, 3, 3, NULL, 'daily', '2026-11-03', '2026-11-03 07:00:00', '2026-11-03 15:00:00', 3, 1, 1, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(4, 1, 3, 3, NULL, 'daily', '2026-11-04', '2026-11-04 07:00:00', '2026-11-04 15:00:00', 3, 1, 1, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(5, 1, 1, 1, NULL, 'daily', '2026-10-30', '2026-10-30 07:00:00', '2026-10-30 15:00:00', 1, 1, 1, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(6, 1, 1, 1, NULL, 'daily', '2026-11-02', '2026-11-02 07:00:00', '2026-11-02 15:00:00', 1, 1, 1, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(7, 1, 1, 1, NULL, 'daily', '2026-11-03', '2026-11-03 07:00:00', '2026-11-03 15:00:00', 1, 1, 1, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(8, 1, 1, 1, NULL, 'daily', '2026-11-04', '2026-11-04 07:00:00', '2026-11-04 15:00:00', 1, 1, 1, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(9, 1, 2, 2, NULL, 'daily', '2026-10-30', '2026-10-30 07:00:00', '2026-10-30 15:00:00', 2, 1, 1, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(10, 1, 2, 2, NULL, 'daily', '2026-11-02', '2026-11-02 07:00:00', '2026-11-02 15:00:00', 2, 1, 1, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(11, 1, 2, 2, NULL, 'daily', '2026-11-03', '2026-11-03 07:00:00', '2026-11-03 15:00:00', 2, 1, 1, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(12, 1, 2, 2, NULL, 'daily', '2026-11-04', '2026-11-04 07:00:00', '2026-11-04 15:00:00', 2, 1, 1, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(13, 1, 4, 4, NULL, 'daily', '2026-10-30', '2026-10-30 07:00:00', '2026-10-30 15:00:00', 4, 1, 1, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(14, 1, 4, 4, NULL, 'daily', '2026-11-02', '2026-11-02 07:00:00', '2026-11-02 15:00:00', 4, 1, 1, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(15, 1, 4, 4, NULL, 'daily', '2026-11-03', '2026-11-03 07:00:00', '2026-11-03 15:00:00', 4, 1, 1, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(16, 1, 4, 4, NULL, 'daily', '2026-11-04', '2026-11-04 07:00:00', '2026-11-04 15:00:00', 4, 1, 1, NULL, '2026-11-06 00:13:00', '2026-11-06 00:13:00');

-- --------------------------------------------------------

--
-- Struktur dari tabel `cbt_absensi`
--

CREATE TABLE `cbt_absensi` (
  `absensi_id` bigint UNSIGNED NOT NULL,
  `absensi_user_id` bigint UNSIGNED NOT NULL,
  `absensi_foundation_id` bigint UNSIGNED DEFAULT NULL,
  `absensi_school_id` bigint UNSIGNED DEFAULT NULL,
  `absensi_tes_id` bigint UNSIGNED DEFAULT NULL,
  `absensi_tanggal` date NOT NULL,
  `absensi_status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'hadir',
  `absensi_sumber` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ujian',
  `absensi_keterangan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `cbt_absensi`
--

INSERT INTO `cbt_absensi` (`absensi_id`, `absensi_user_id`, `absensi_foundation_id`, `absensi_school_id`, `absensi_tes_id`, `absensi_tanggal`, `absensi_status`, `absensi_sumber`, `absensi_keterangan`, `created_at`, `updated_at`) VALUES
(1, 84, 1, 3, 11, '2026-10-30', 'hadir', 'ujian', NULL, '2026-11-02 19:21:04', '2026-11-04 06:40:08'),
(2, 43, 1, 3, 12, '2026-11-01', 'hadir', 'ujian', NULL, '2026-11-02 19:21:04', '2026-11-04 06:40:08'),
(3, 84, 1, 3, NULL, '2026-10-30', 'izin', 'manual', 'Acara keluarga.', '2026-11-02 19:21:04', '2026-11-04 06:40:08'),
(4, 43, 1, 3, NULL, '2026-11-01', 'sakit', 'manual', 'Surat keterangan dokter.', '2026-11-02 19:21:04', '2026-11-04 06:40:08'),
(5, 41, 1, 3, 12, '2026-11-02', 'hadir', 'ujian', NULL, '2026-11-02 21:12:27', '2026-11-04 06:40:08'),
(6, 84, 1, 3, NULL, '2026-10-31', 'izin', 'manual', 'Acara keluarga.', '2026-11-04 06:40:08', '2026-11-04 06:40:08'),
(7, 43, 1, 3, NULL, '2026-11-02', 'sakit', 'manual', 'Surat keterangan dokter.', '2026-11-04 06:40:08', '2026-11-04 06:40:08'),
(9, 42, NULL, NULL, 12, '2026-11-05', 'hadir', 'ujian', NULL, '2026-11-06 03:54:03', '2026-11-06 03:54:03');

-- --------------------------------------------------------

--
-- Struktur dari tabel `cbt_jawaban`
--

CREATE TABLE `cbt_jawaban` (
  `jawaban_id` bigint UNSIGNED NOT NULL,
  `jawaban_soal_id` bigint UNSIGNED NOT NULL,
  `jawaban_detail` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `jawaban_benar` tinyint(1) NOT NULL DEFAULT '0',
  `jawaban_aktif` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `cbt_jawaban`
--

INSERT INTO `cbt_jawaban` (`jawaban_id`, `jawaban_soal_id`, `jawaban_detail`, `jawaban_benar`, `jawaban_aktif`) VALUES
(301, 86, '2', 0, 1),
(302, 86, '3', 0, 1),
(303, 86, '4', 1, 1),
(304, 86, '5', 0, 1),
(305, 87, 'x² + x - 6', 1, 1),
(306, 87, 'x² - x - 6', 0, 1),
(307, 87, 'x² + 5x - 6', 0, 1),
(308, 87, 'x² - 5x + 6', 0, 1),
(309, 88, '3', 0, 1),
(310, 88, '4', 0, 1),
(311, 88, '5', 1, 1),
(312, 88, '6', 0, 1),
(313, 89, '10', 0, 1),
(314, 89, '12', 0, 1),
(315, 89, '13', 1, 1),
(316, 89, '15', 0, 1),
(317, 90, '2x + 4y', 1, 1),
(318, 90, '2x + 2y', 0, 1),
(319, 90, '6x + 4y', 0, 1),
(320, 90, '6x + 2y', 0, 1),
(321, 91, '{-3, 3}', 1, 1),
(322, 91, '{-9, 9}', 0, 1),
(323, 91, '{-3, 0}', 0, 1),
(324, 91, '{0, 3}', 0, 1),
(325, 92, '30 cm²', 1, 1),
(326, 92, '40 cm²', 0, 1),
(327, 92, '50 cm²', 0, 1),
(328, 92, '60 cm²', 0, 1),
(329, 93, '21 cm', 0, 1),
(330, 93, '28 cm', 1, 1),
(331, 93, '35 cm', 0, 1),
(332, 93, '49 cm', 0, 1),
(333, 94, '90°', 0, 1),
(334, 94, '180°', 1, 1),
(335, 94, '270°', 0, 1),
(336, 94, '360°', 0, 1),
(337, 95, '16 cm³', 0, 1),
(338, 95, '32 cm³', 0, 1),
(339, 95, '48 cm³', 0, 1),
(340, 95, '64 cm³', 1, 1),
(341, 96, 'awal paragraf', 1, 1),
(342, 96, 'tengah paragraf', 0, 1),
(343, 96, 'akhir paragraf', 0, 1),
(344, 96, 'judul teks', 0, 1),
(345, 97, 'menghibur pembaca', 0, 1),
(346, 97, 'menjelaskan informasi', 1, 1),
(347, 97, 'membujuk pembaca', 0, 1),
(348, 97, 'menceritakan kisah', 0, 1),
(349, 98, 'apotik', 0, 1),
(350, 98, 'apotek', 1, 1),
(351, 98, 'apotiek', 0, 1),
(352, 98, 'aphotek', 0, 1),
(353, 99, 'teliti', 1, 1),
(354, 99, 'cepat', 0, 1),
(355, 99, 'ceroboh', 0, 1),
(356, 99, 'ragu', 0, 1),
(357, 100, 'maksimal', 0, 1),
(358, 100, 'terbaik', 0, 1),
(359, 100, 'minimal', 1, 1),
(360, 100, 'efisien', 0, 1),
(361, 104, '3', 0, 1),
(362, 104, '4', 0, 1),
(363, 104, '5', 1, 1),
(364, 104, '6', 0, 1),
(365, 105, 'a + 5b', 1, 1),
(366, 105, '3a + 5b', 0, 1),
(367, 105, 'a + b', 0, 1),
(368, 105, '3a + b', 0, 1),
(369, 106, '13', 0, 1),
(370, 106, '17', 0, 1),
(371, 106, '18', 0, 1),
(372, 106, '19', 1, 1),
(373, 107, 'menggambarkan objek secara rinci', 1, 1),
(374, 107, 'menceritakan urutan peristiwa', 0, 1),
(375, 107, 'membujuk pembaca', 0, 1),
(376, 107, 'menjelaskan sebab akibat', 0, 1),
(377, 108, 'menggunakan pancaindra', 1, 1),
(378, 108, 'berisi langkah-langkah', 0, 1),
(379, 108, 'berupa dialog', 0, 1),
(380, 108, 'berisi argumen', 0, 1),
(381, 109, 'buruk', 0, 1),
(382, 109, 'cantik', 1, 1),
(383, 109, 'ramai', 0, 1),
(384, 109, 'sunyi', 0, 1),
(385, 110, 'Merkurius', 1, 1),
(386, 110, 'Venus', 0, 1),
(387, 110, 'Bumi', 0, 1),
(388, 110, 'Mars', 0, 1),
(389, 111, 'Bulan', 1, 1),
(390, 111, 'Matahari', 0, 1),
(391, 111, 'Mars', 0, 1),
(392, 111, 'Venus', 0, 1),
(393, 112, 'Bumi', 0, 1),
(394, 112, 'Saturnus', 0, 1),
(395, 112, 'Jupiter', 1, 1),
(396, 112, 'Neptunus', 0, 1),
(397, 116, '10', 0, 1),
(398, 116, '12', 0, 1),
(399, 116, '13', 1, 1),
(400, 116, '15', 0, 1),
(401, 117, '{-3, 3}', 1, 1),
(402, 117, '{-9, 9}', 0, 1),
(403, 117, '{-3, 0}', 0, 1),
(404, 117, '{0, 3}', 0, 1),
(405, 119, '13', 0, 1),
(406, 119, '17', 0, 1),
(407, 119, '18', 0, 1),
(408, 119, '19', 1, 1);

-- --------------------------------------------------------

--
-- Struktur dari tabel `cbt_konfigurasi`
--

CREATE TABLE `cbt_konfigurasi` (
  `konfigurasi_id` bigint UNSIGNED NOT NULL,
  `konfigurasi_kode` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `konfigurasi_isi` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `konfigurasi_keterangan` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `cbt_konfigurasi`
--

INSERT INTO `cbt_konfigurasi` (`konfigurasi_id`, `konfigurasi_kode`, `konfigurasi_isi`, `konfigurasi_keterangan`) VALUES
(26, 'cbt_nama', 'Sistem Informasi Sekolah', 'cbt_nama'),
(27, 'cbt_keterangan', 'Platform Terpadu Manajemen dan Layanan Sekolah', 'cbt_keterangan'),
(28, 'cbt_informasi', '<p>Silakan pilih tes yang tersedia di bawah ini. Jika tes tidak muncul, hubungi operator yang bertugas.</p>', 'cbt_informasi'),
(29, 'link_login_operator', 'ya', 'link_login_operator'),
(30, 'cbt_mobile_lock_xambro', 'tidak', 'cbt_mobile_lock_xambro'),
(31, 'cbt_alamat', 'Jl. Pendidikan No. 1, Jakarta', 'cbt_alamat'),
(32, 'cbt_kepala_sekolah', 'Drs. Ahmad Subarjo, M.Pd.', 'cbt_kepala_sekolah'),
(33, 'cbt_nip_kepala_sekolah', '19700101 199501 1 001', 'cbt_nip_kepala_sekolah'),
(34, 'cbt_tahun_ajaran', '2025/2026', 'cbt_tahun_ajaran'),
(35, 'cbt_nilai_kelulusan', '70', 'cbt_nilai_kelulusan');

-- --------------------------------------------------------

--
-- Struktur dari tabel `cbt_modul`
--

CREATE TABLE `cbt_modul` (
  `modul_id` bigint UNSIGNED NOT NULL,
  `modul_nama` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `modul_aktif` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `cbt_modul`
--

INSERT INTO `cbt_modul` (`modul_id`, `modul_nama`, `modul_aktif`) VALUES
(11, 'Matematika', 1),
(12, 'Bahasa Indonesia', 1),
(13, 'IPA', 1);

-- --------------------------------------------------------

--
-- Struktur dari tabel `cbt_orang_tua`
--

CREATE TABLE `cbt_orang_tua` (
  `ortu_id` bigint UNSIGNED NOT NULL,
  `ortu_nama` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ortu_username` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ortu_password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ortu_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ortu_telepon` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `cbt_orang_tua`
--

INSERT INTO `cbt_orang_tua` (`ortu_id`, `ortu_nama`, `ortu_username`, `ortu_password`, `ortu_email`, `ortu_telepon`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Bapak Raka Pratama', 'orangtua1', '$2y$12$8TmIVkk9HVvFuxJaLs/ZQuQQfQniUUOGUTp83P/mgTQOQ42nsJ7Qm', 'orangtua1@example.com', '081234567890', NULL, '2026-11-02 19:21:03', '2026-11-05 22:45:55'),
(2, 'Ibu Aminah', 'orangtua2', '$2y$12$U8XNEihbic0Aj8KFLV40Iu5CzJ5tYB1Dm6InaGtdxa2kC9s8uuBda', 'orangtua2@example.com', '081298765432', NULL, '2026-11-02 19:21:03', '2026-11-06 00:29:52'),
(3, 'Orang Tua Joko Susilo', 'ortu42', '$2y$12$KRh/qSQZFu37Y0yPnSuw/uQmoL5Kp4D.og6ygBY3p4Ud5V.Q8MjPm', 'ortu42@example.com', '081200000042', NULL, '2026-11-06 00:14:17', '2026-11-06 00:14:17'),
(4, 'Orang Tua Budi Santoso', 'ortu49', '$2y$12$RUo8nVjnIcloHfUgVO/bsO7jfV.yA/zwuOdfB9A0gT8OFqSs5UdHG', 'ortu49@example.com', '081200000049', NULL, '2026-11-06 00:14:18', '2026-11-06 00:14:18'),
(5, 'Orang Tua Rina Wulandari', 'ortu50', '$2y$12$aRyGo7DJJZvDfBteMZDbcuq/nXwNbaKfo4TErq/Gx.TTybYCpR/ra', 'ortu50@example.com', '081200000050', NULL, '2026-11-06 00:14:18', '2026-11-06 00:14:18'),
(6, 'Orang Tua Andi Pratama', 'ortu51', '$2y$12$4P6qnK2bEwnTop9f2BnLVeb/b.0ZtquX/oTm4oChlgXGrqF4yD.QC', 'ortu51@example.com', '081200000051', NULL, '2026-11-06 00:14:18', '2026-11-06 00:14:18');

-- --------------------------------------------------------

--
-- Struktur dari tabel `cbt_orang_tua_peserta`
--

CREATE TABLE `cbt_orang_tua_peserta` (
  `ortu_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `cbt_orang_tua_peserta`
--

INSERT INTO `cbt_orang_tua_peserta` (`ortu_id`, `user_id`) VALUES
(1, 41),
(3, 42),
(2, 43),
(2, 49),
(4, 49),
(2, 50),
(5, 50),
(6, 51),
(1, 52),
(1, 84);

-- --------------------------------------------------------

--
-- Struktur dari tabel `cbt_pembayaran`
--

CREATE TABLE `cbt_pembayaran` (
  `pembayaran_id` bigint UNSIGNED NOT NULL,
  `pembayaran_user_id` bigint UNSIGNED NOT NULL,
  `pembayaran_foundation_id` bigint UNSIGNED DEFAULT NULL,
  `pembayaran_school_id` bigint UNSIGNED DEFAULT NULL,
  `pembayaran_tes_id` bigint UNSIGNED NOT NULL,
  `pembayaran_ortu_id` bigint UNSIGNED NOT NULL,
  `pembayaran_nominal` decimal(12,2) NOT NULL DEFAULT '0.00',
  `pembayaran_status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'menunggu',
  `pembayaran_bukti` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pembayaran_catatan` text COLLATE utf8mb4_unicode_ci,
  `pembayaran_catatan_admin` text COLLATE utf8mb4_unicode_ci,
  `pembayaran_verified_by` bigint UNSIGNED DEFAULT NULL,
  `pembayaran_verified_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `cbt_pembayaran`
--

INSERT INTO `cbt_pembayaran` (`pembayaran_id`, `pembayaran_user_id`, `pembayaran_foundation_id`, `pembayaran_school_id`, `pembayaran_tes_id`, `pembayaran_ortu_id`, `pembayaran_nominal`, `pembayaran_status`, `pembayaran_bukti`, `pembayaran_catatan`, `pembayaran_catatan_admin`, `pembayaran_verified_by`, `pembayaran_verified_at`, `created_at`, `updated_at`) VALUES
(1, 84, 1, 3, 11, 1, 75000.00, 'diverifikasi', 'bukti-pembayaran/demo-bukti.svg', 'Transfer dari rekening Bapak Raka.', 'Pembayaran sesuai.', 16, '2026-11-03 06:40:08', '2026-11-02 19:21:04', '2026-11-04 06:40:08'),
(2, 84, 1, 3, 12, 1, 50000.00, 'menunggu', 'bukti-pembayaran/demo-bukti.svg', 'Mohon diverifikasi.', NULL, NULL, NULL, '2026-11-02 19:21:04', '2026-11-04 06:40:08'),
(3, 43, 1, 3, 11, 2, 75000.00, 'ditolak', 'bukti-pembayaran/demo-bukti.svg', 'Sudah transfer.', 'Nominal belum sesuai. Mohon unggah ulang bukti yang benar.', 16, '2026-11-03 06:40:08', '2026-11-02 19:21:04', '2026-11-04 06:40:08'),
(6, 41, 1, 3, 11, 1, 75000.00, 'ditolak', 'bukti-pembayaran/demo-bukti.svg', 'Pembayaran biaya ujian sample', 'Nominal belum sesuai.', NULL, NULL, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(7, 42, 1, 3, 11, 3, 75000.00, 'diverifikasi', 'bukti-pembayaran/demo-bukti.svg', 'Pembayaran biaya ujian sample', NULL, 16, '2026-11-04 00:14:17', '2026-11-06 00:14:17', '2026-11-06 00:14:17'),
(8, 41, 1, 3, 12, 1, 50000.00, 'ditolak', 'bukti-pembayaran/demo-bukti.svg', 'Pembayaran biaya ujian sample', 'Nominal belum sesuai.', NULL, NULL, '2026-11-06 00:14:17', '2026-11-06 00:14:17'),
(9, 43, 1, 3, 12, 2, 50000.00, 'menunggu', 'bukti-pembayaran/demo-bukti.svg', 'Pembayaran biaya ujian sample', NULL, NULL, NULL, '2026-11-06 00:14:17', '2026-11-06 00:14:17'),
(10, 49, 1, 3, 12, 4, 50000.00, 'menunggu', 'bukti-pembayaran/demo-bukti.svg', 'Pembayaran biaya ujian sample', NULL, NULL, NULL, '2026-11-06 00:14:18', '2026-11-06 00:14:18'),
(11, 50, 1, 3, 12, 5, 50000.00, 'ditolak', 'bukti-pembayaran/demo-bukti.svg', 'Pembayaran biaya ujian sample', 'Nominal belum sesuai.', NULL, NULL, '2026-11-06 00:14:18', '2026-11-06 00:14:18'),
(12, 51, 1, 3, 12, 6, 50000.00, 'diverifikasi', 'bukti-pembayaran/demo-bukti.svg', 'Pembayaran biaya ujian sample', NULL, 16, '2026-11-04 00:14:18', '2026-11-06 00:14:18', '2026-11-06 00:14:18');

-- --------------------------------------------------------

--
-- Struktur dari tabel `cbt_raport`
--

CREATE TABLE `cbt_raport` (
  `raport_id` bigint UNSIGNED NOT NULL,
  `raport_user_id` bigint UNSIGNED NOT NULL,
  `raport_modul_id` bigint UNSIGNED NOT NULL,
  `raport_guru_id` bigint UNSIGNED NOT NULL,
  `raport_nilai` decimal(5,2) NOT NULL,
  `raport_catatan` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `raport_jenjang` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'SMA',
  `raport_tingkat` tinyint UNSIGNED NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `cbt_raport`
--

INSERT INTO `cbt_raport` (`raport_id`, `raport_user_id`, `raport_modul_id`, `raport_guru_id`, `raport_nilai`, `raport_catatan`, `created_at`, `updated_at`, `raport_jenjang`, `raport_tingkat`) VALUES
(1, 41, 11, 19, 88.00, 'Konsisten dan aktif dalam pembelajaran.', '2026-11-02 07:00:45', '2026-11-02 07:00:45', 'SMA', 2),
(2, 41, 12, 20, 85.00, 'Konsisten dan aktif dalam pembelajaran.', '2026-11-02 07:00:45', '2026-11-02 07:00:45', 'SMA', 2),
(3, 41, 13, 21, 90.00, 'Konsisten dan aktif dalam pembelajaran.', '2026-11-02 07:00:45', '2026-11-02 07:00:45', 'SMA', 2),
(4, 42, 11, 19, 80.00, 'Hasil belajar tercatat dengan baik.', '2026-11-02 07:00:45', '2026-11-02 07:09:21', 'SMA', 2),
(5, 42, 12, 20, 82.00, 'Hasil belajar tercatat dengan baik.', '2026-11-02 07:00:45', '2026-11-02 07:09:21', 'SMA', 2),
(6, 42, 13, 21, 84.00, 'Hasil belajar tercatat dengan baik.', '2026-11-02 07:00:45', '2026-11-02 07:09:21', 'SMA', 2),
(7, 43, 11, 19, 92.00, 'Sangat baik, pertahankan prestasi.', '2026-11-02 07:00:45', '2026-11-02 07:00:45', 'SMA', 2),
(8, 43, 12, 20, 90.00, 'Sangat baik, pertahankan prestasi.', '2026-11-02 07:00:45', '2026-11-02 07:00:45', 'SMA', 2),
(9, 43, 13, 21, 94.00, 'Sangat baik, pertahankan prestasi.', '2026-11-02 07:00:45', '2026-11-02 07:00:45', 'SMA', 2),
(10, 49, 11, 19, 76.00, 'Hasil belajar tercatat dengan baik.', '2026-11-02 07:00:45', '2026-11-02 07:09:21', 'SMA', 2),
(11, 49, 12, 20, 78.00, 'Hasil belajar tercatat dengan baik.', '2026-11-02 07:00:45', '2026-11-02 07:09:21', 'SMA', 2),
(12, 49, 13, 21, 80.00, 'Hasil belajar tercatat dengan baik.', '2026-11-02 07:00:45', '2026-11-02 07:09:21', 'SMA', 2),
(13, 50, 11, 19, 86.00, 'Pemahaman materi sudah baik.', '2026-11-02 07:00:45', '2026-11-02 07:00:45', 'SMA', 2),
(14, 50, 12, 20, 88.00, 'Pemahaman materi sudah baik.', '2026-11-02 07:00:45', '2026-11-02 07:00:45', 'SMA', 2),
(15, 50, 13, 21, 87.00, 'Pemahaman materi sudah baik.', '2026-11-02 07:00:45', '2026-11-02 07:00:45', 'SMA', 2),
(16, 51, 11, 19, 72.00, 'Hasil belajar tercatat dengan baik.', '2026-11-02 07:00:45', '2026-11-02 07:09:21', 'SMA', 2),
(17, 51, 12, 20, 75.00, 'Hasil belajar tercatat dengan baik.', '2026-11-02 07:00:45', '2026-11-02 07:09:21', 'SMA', 2),
(18, 51, 13, 21, 74.00, 'Hasil belajar tercatat dengan baik.', '2026-11-02 07:00:45', '2026-11-02 07:09:21', 'SMA', 2),
(19, 52, 11, 19, 90.00, 'Teliti dan bertanggung jawab.', '2026-11-02 07:00:45', '2026-11-02 07:00:45', 'SMA', 2),
(20, 52, 12, 20, 91.00, 'Teliti dan bertanggung jawab.', '2026-11-02 07:00:45', '2026-11-02 07:00:45', 'SMA', 2),
(21, 52, 13, 21, 89.00, 'Teliti dan bertanggung jawab.', '2026-11-02 07:00:45', '2026-11-02 07:00:45', 'SMA', 2),
(22, 53, 11, 19, 78.00, 'Hasil belajar tercatat dengan baik.', '2026-11-02 07:00:45', '2026-11-02 07:09:21', 'SMA', 2),
(23, 53, 12, 20, 80.00, 'Hasil belajar tercatat dengan baik.', '2026-11-02 07:00:45', '2026-11-02 07:09:21', 'SMA', 2),
(24, 53, 13, 21, 82.00, 'Hasil belajar tercatat dengan baik.', '2026-11-02 07:00:45', '2026-11-02 07:09:21', 'SMA', 2),
(25, 54, 11, 19, 78.00, 'Hasil belajar tercatat dengan baik.', '2026-11-02 07:09:21', '2026-11-02 07:09:21', 'SD', 1),
(26, 54, 12, 20, 81.00, 'Hasil belajar tercatat dengan baik.', '2026-11-02 07:09:21', '2026-11-02 07:09:21', 'SD', 1),
(27, 54, 13, 21, 84.00, 'Hasil belajar tercatat dengan baik.', '2026-11-02 07:09:21', '2026-11-02 07:09:21', 'SD', 1),
(28, 55, 11, 19, 79.00, 'Hasil belajar tercatat dengan baik.', '2026-11-02 07:09:21', '2026-11-02 07:09:21', 'SD', 1),
(29, 55, 12, 20, 82.00, 'Hasil belajar tercatat dengan baik.', '2026-11-02 07:09:21', '2026-11-02 07:09:21', 'SD', 1),
(30, 55, 13, 21, 85.00, 'Hasil belajar tercatat dengan baik.', '2026-11-02 07:09:21', '2026-11-02 07:09:21', 'SD', 1),
(31, 56, 11, 19, 80.00, 'Hasil belajar tercatat dengan baik.', '2026-11-02 07:09:21', '2026-11-02 07:09:21', 'SD', 2),
(32, 56, 12, 20, 83.00, 'Hasil belajar tercatat dengan baik.', '2026-11-02 07:09:21', '2026-11-02 07:09:21', 'SD', 2),
(33, 56, 13, 21, 86.00, 'Hasil belajar tercatat dengan baik.', '2026-11-02 07:09:21', '2026-11-02 07:09:21', 'SD', 2),
(34, 57, 11, 19, 81.00, 'Hasil belajar tercatat dengan baik.', '2026-11-02 07:09:21', '2026-11-02 07:09:21', 'SD', 2),
(35, 57, 12, 20, 84.00, 'Hasil belajar tercatat dengan baik.', '2026-11-02 07:09:21', '2026-11-02 07:09:21', 'SD', 2),
(36, 57, 13, 21, 87.00, 'Hasil belajar tercatat dengan baik.', '2026-11-02 07:09:21', '2026-11-02 07:09:21', 'SD', 2),
(37, 58, 11, 19, 82.00, 'Hasil belajar tercatat dengan baik.', '2026-11-02 07:09:21', '2026-11-02 07:09:21', 'SD', 3),
(38, 58, 12, 20, 85.00, 'Hasil belajar tercatat dengan baik.', '2026-11-02 07:09:21', '2026-11-02 07:09:21', 'SD', 3),
(39, 58, 13, 21, 88.00, 'Hasil belajar tercatat dengan baik.', '2026-11-02 07:09:21', '2026-11-02 07:09:21', 'SD', 3),
(40, 59, 11, 19, 83.00, 'Hasil belajar tercatat dengan baik.', '2026-11-02 07:09:21', '2026-11-02 07:09:21', 'SD', 3),
(41, 59, 12, 20, 86.00, 'Hasil belajar tercatat dengan baik.', '2026-11-02 07:09:21', '2026-11-02 07:09:21', 'SD', 3),
(42, 59, 13, 21, 89.00, 'Hasil belajar tercatat dengan baik.', '2026-11-02 07:09:21', '2026-11-02 07:09:21', 'SD', 3),
(43, 60, 11, 19, 84.00, 'Hasil belajar tercatat dengan baik.', '2026-11-02 07:09:21', '2026-11-02 07:09:21', 'SD', 4),
(44, 60, 12, 20, 87.00, 'Hasil belajar tercatat dengan baik.', '2026-11-02 07:09:21', '2026-11-02 07:09:21', 'SD', 4),
(45, 60, 13, 21, 90.00, 'Hasil belajar tercatat dengan baik.', '2026-11-02 07:09:21', '2026-11-02 07:09:21', 'SD', 4),
(46, 61, 11, 19, 85.00, 'Hasil belajar tercatat dengan baik.', '2026-11-02 07:09:21', '2026-11-02 07:09:21', 'SD', 4),
(47, 61, 12, 20, 88.00, 'Hasil belajar tercatat dengan baik.', '2026-11-02 07:09:21', '2026-11-02 07:09:21', 'SD', 4),
(48, 61, 13, 21, 91.00, 'Hasil belajar tercatat dengan baik.', '2026-11-02 07:09:21', '2026-11-02 07:09:21', 'SD', 4),
(49, 62, 11, 19, 86.00, 'Hasil belajar tercatat dengan baik.', '2026-11-02 07:09:21', '2026-11-02 07:09:21', 'SD', 5),
(50, 62, 12, 20, 89.00, 'Hasil belajar tercatat dengan baik.', '2026-11-02 07:09:21', '2026-11-02 07:09:21', 'SD', 5),
(51, 62, 13, 21, 92.00, 'Hasil belajar tercatat dengan baik.', '2026-11-02 07:09:21', '2026-11-02 07:09:21', 'SD', 5),
(52, 63, 11, 19, 87.00, 'Hasil belajar tercatat dengan baik.', '2026-11-02 07:09:21', '2026-11-02 07:09:21', 'SD', 5),
(53, 63, 12, 20, 90.00, 'Hasil belajar tercatat dengan baik.', '2026-11-02 07:09:21', '2026-11-02 07:09:21', 'SD', 5),
(54, 63, 13, 21, 93.00, 'Hasil belajar tercatat dengan baik.', '2026-11-02 07:09:21', '2026-11-02 07:09:21', 'SD', 5),
(55, 64, 11, 19, 88.00, 'Hasil belajar tercatat dengan baik.', '2026-11-02 07:09:21', '2026-11-02 07:09:21', 'SD', 6),
(56, 64, 12, 20, 91.00, 'Hasil belajar tercatat dengan baik.', '2026-11-02 07:09:21', '2026-11-02 07:09:21', 'SD', 6),
(57, 64, 13, 21, 94.00, 'Hasil belajar tercatat dengan baik.', '2026-11-02 07:09:21', '2026-11-02 07:09:21', 'SD', 6),
(58, 65, 11, 19, 89.00, 'Hasil belajar tercatat dengan baik.', '2026-11-02 07:09:21', '2026-11-02 07:09:21', 'SD', 6),
(59, 65, 12, 20, 92.00, 'Hasil belajar tercatat dengan baik.', '2026-11-02 07:09:21', '2026-11-02 07:09:21', 'SD', 6),
(60, 65, 13, 21, 95.00, 'Hasil belajar tercatat dengan baik.', '2026-11-02 07:09:21', '2026-11-02 07:09:21', 'SD', 6),
(61, 66, 11, 19, 90.00, 'Hasil belajar tercatat dengan baik.', '2026-11-02 07:09:21', '2026-11-02 07:09:21', 'SMP', 1),
(62, 66, 12, 20, 93.00, 'Hasil belajar tercatat dengan baik.', '2026-11-02 07:09:21', '2026-11-02 07:09:21', 'SMP', 1),
(63, 66, 13, 21, 78.00, 'Hasil belajar tercatat dengan baik.', '2026-11-02 07:09:21', '2026-11-02 07:09:21', 'SMP', 1),
(64, 67, 11, 19, 91.00, 'Hasil belajar tercatat dengan baik.', '2026-11-02 07:09:21', '2026-11-02 07:09:21', 'SMP', 1),
(65, 67, 12, 20, 94.00, 'Hasil belajar tercatat dengan baik.', '2026-11-02 07:09:21', '2026-11-02 07:09:21', 'SMP', 1),
(66, 67, 13, 21, 79.00, 'Hasil belajar tercatat dengan baik.', '2026-11-02 07:09:21', '2026-11-02 07:09:21', 'SMP', 1),
(67, 68, 11, 19, 92.00, 'Hasil belajar tercatat dengan baik.', '2026-11-02 07:09:21', '2026-11-02 07:09:21', 'SMP', 2),
(68, 68, 12, 20, 95.00, 'Hasil belajar tercatat dengan baik.', '2026-11-02 07:09:21', '2026-11-02 07:09:21', 'SMP', 2),
(69, 68, 13, 21, 80.00, 'Hasil belajar tercatat dengan baik.', '2026-11-02 07:09:21', '2026-11-02 07:09:21', 'SMP', 2),
(70, 69, 11, 19, 93.00, 'Hasil belajar tercatat dengan baik.', '2026-11-02 07:09:21', '2026-11-02 07:09:21', 'SMP', 2),
(71, 69, 12, 20, 78.00, 'Hasil belajar tercatat dengan baik.', '2026-11-02 07:09:21', '2026-11-02 07:09:21', 'SMP', 2),
(72, 69, 13, 21, 81.00, 'Hasil belajar tercatat dengan baik.', '2026-11-02 07:09:21', '2026-11-02 07:09:21', 'SMP', 2),
(73, 70, 11, 19, 94.00, 'Hasil belajar tercatat dengan baik.', '2026-11-02 07:09:21', '2026-11-02 07:09:21', 'SMP', 3),
(74, 70, 12, 20, 79.00, 'Hasil belajar tercatat dengan baik.', '2026-11-02 07:09:21', '2026-11-02 07:09:21', 'SMP', 3),
(75, 70, 13, 21, 82.00, 'Hasil belajar tercatat dengan baik.', '2026-11-02 07:09:21', '2026-11-02 07:09:21', 'SMP', 3),
(76, 71, 11, 19, 95.00, 'Hasil belajar tercatat dengan baik.', '2026-11-02 07:09:21', '2026-11-02 07:09:21', 'SMP', 3),
(77, 71, 12, 20, 80.00, 'Hasil belajar tercatat dengan baik.', '2026-11-02 07:09:21', '2026-11-02 07:09:21', 'SMP', 3),
(78, 71, 13, 21, 83.00, 'Hasil belajar tercatat dengan baik.', '2026-11-02 07:09:21', '2026-11-02 07:09:21', 'SMP', 3),
(79, 72, 11, 19, 78.00, 'Hasil belajar tercatat dengan baik.', '2026-11-02 07:09:21', '2026-11-02 07:09:21', 'SMA', 1),
(80, 72, 12, 20, 81.00, 'Hasil belajar tercatat dengan baik.', '2026-11-02 07:09:21', '2026-11-02 07:09:21', 'SMA', 1),
(81, 72, 13, 21, 84.00, 'Hasil belajar tercatat dengan baik.', '2026-11-02 07:09:21', '2026-11-02 07:09:21', 'SMA', 1),
(82, 73, 11, 19, 79.00, 'Hasil belajar tercatat dengan baik.', '2026-11-02 07:09:21', '2026-11-02 07:09:21', 'SMA', 1),
(83, 73, 12, 20, 82.00, 'Hasil belajar tercatat dengan baik.', '2026-11-02 07:09:21', '2026-11-02 07:09:21', 'SMA', 1),
(84, 73, 13, 21, 85.00, 'Hasil belajar tercatat dengan baik.', '2026-11-02 07:09:21', '2026-11-02 07:09:21', 'SMA', 1),
(85, 74, 11, 19, 80.00, 'Hasil belajar tercatat dengan baik.', '2026-11-02 07:09:21', '2026-11-02 07:09:21', 'SMA', 2),
(86, 74, 12, 20, 83.00, 'Hasil belajar tercatat dengan baik.', '2026-11-02 07:09:21', '2026-11-02 07:09:21', 'SMA', 2),
(87, 74, 13, 21, 86.00, 'Hasil belajar tercatat dengan baik.', '2026-11-02 07:09:21', '2026-11-02 07:09:21', 'SMA', 2),
(88, 75, 11, 19, 81.00, 'Hasil belajar tercatat dengan baik.', '2026-11-02 07:09:21', '2026-11-02 07:09:21', 'SMA', 2),
(89, 75, 12, 20, 84.00, 'Hasil belajar tercatat dengan baik.', '2026-11-02 07:09:21', '2026-11-02 07:09:21', 'SMA', 2),
(90, 75, 13, 21, 87.00, 'Hasil belajar tercatat dengan baik.', '2026-11-02 07:09:21', '2026-11-02 07:09:21', 'SMA', 2),
(91, 76, 11, 19, 82.00, 'Hasil belajar tercatat dengan baik.', '2026-11-02 07:09:21', '2026-11-02 07:09:21', 'SMA', 3),
(92, 76, 12, 20, 85.00, 'Hasil belajar tercatat dengan baik.', '2026-11-02 07:09:21', '2026-11-02 07:09:21', 'SMA', 3),
(93, 76, 13, 21, 88.00, 'Hasil belajar tercatat dengan baik.', '2026-11-02 07:09:21', '2026-11-02 07:09:21', 'SMA', 3),
(94, 77, 11, 19, 83.00, 'Hasil belajar tercatat dengan baik.', '2026-11-02 07:09:21', '2026-11-02 07:09:21', 'SMA', 3),
(95, 77, 12, 20, 86.00, 'Hasil belajar tercatat dengan baik.', '2026-11-02 07:09:21', '2026-11-02 07:09:21', 'SMA', 3),
(96, 77, 13, 21, 89.00, 'Hasil belajar tercatat dengan baik.', '2026-11-02 07:09:21', '2026-11-02 07:09:21', 'SMA', 3),
(97, 78, 11, 19, 84.00, 'Hasil belajar tercatat dengan baik.', '2026-11-02 07:19:50', '2026-11-02 07:19:50', 'SMK', 1),
(98, 78, 12, 20, 87.00, 'Hasil belajar tercatat dengan baik.', '2026-11-02 07:19:50', '2026-11-02 07:19:50', 'SMK', 1),
(99, 78, 13, 21, 90.00, 'Hasil belajar tercatat dengan baik.', '2026-11-02 07:19:50', '2026-11-02 07:19:50', 'SMK', 1),
(100, 79, 11, 19, 85.00, 'Hasil belajar tercatat dengan baik.', '2026-11-02 07:19:50', '2026-11-02 07:19:50', 'SMK', 1),
(101, 79, 12, 20, 88.00, 'Hasil belajar tercatat dengan baik.', '2026-11-02 07:19:50', '2026-11-02 07:19:50', 'SMK', 1),
(102, 79, 13, 21, 91.00, 'Hasil belajar tercatat dengan baik.', '2026-11-02 07:19:50', '2026-11-02 07:19:50', 'SMK', 1),
(103, 80, 11, 19, 86.00, 'Hasil belajar tercatat dengan baik.', '2026-11-02 07:19:50', '2026-11-02 07:19:50', 'SMK', 2),
(104, 80, 12, 20, 89.00, 'Hasil belajar tercatat dengan baik.', '2026-11-02 07:19:50', '2026-11-02 07:19:50', 'SMK', 2),
(105, 80, 13, 21, 92.00, 'Hasil belajar tercatat dengan baik.', '2026-11-02 07:19:50', '2026-11-02 07:19:50', 'SMK', 2),
(106, 81, 11, 19, 87.00, 'Hasil belajar tercatat dengan baik.', '2026-11-02 07:19:50', '2026-11-02 07:19:50', 'SMK', 2),
(107, 81, 12, 20, 90.00, 'Hasil belajar tercatat dengan baik.', '2026-11-02 07:19:50', '2026-11-02 07:19:50', 'SMK', 2),
(108, 81, 13, 21, 93.00, 'Hasil belajar tercatat dengan baik.', '2026-11-02 07:19:50', '2026-11-02 07:19:50', 'SMK', 2),
(109, 82, 11, 19, 88.00, 'Hasil belajar tercatat dengan baik.', '2026-11-02 07:19:50', '2026-11-02 07:19:50', 'SMK', 3),
(110, 82, 12, 20, 91.00, 'Hasil belajar tercatat dengan baik.', '2026-11-02 07:19:50', '2026-11-02 07:19:50', 'SMK', 3),
(111, 82, 13, 21, 94.00, 'Hasil belajar tercatat dengan baik.', '2026-11-02 07:19:50', '2026-11-02 07:19:50', 'SMK', 3),
(112, 83, 11, 19, 89.00, 'Hasil belajar tercatat dengan baik.', '2026-11-02 07:19:50', '2026-11-02 07:19:50', 'SMK', 3),
(113, 83, 12, 20, 92.00, 'Hasil belajar tercatat dengan baik.', '2026-11-02 07:19:50', '2026-11-02 07:19:50', 'SMK', 3),
(114, 83, 13, 21, 95.00, 'Hasil belajar tercatat dengan baik.', '2026-11-02 07:19:50', '2026-11-02 07:19:50', 'SMK', 3),
(115, 84, 11, 19, 92.00, 'Nilai raport SMA kelas 3.', '2026-11-02 07:30:06', '2026-11-04 06:40:07', 'SMA', 3),
(116, 84, 12, 20, 94.00, 'Nilai raport SMA kelas 3.', '2026-11-02 07:30:06', '2026-11-04 06:40:07', 'SMA', 3),
(117, 84, 13, 21, 96.00, 'Nilai raport SMA kelas 3.', '2026-11-02 07:30:06', '2026-11-04 06:40:07', 'SMA', 3),
(118, 84, 11, 19, 78.00, 'Nilai raport SD kelas 1.', '2026-11-02 07:30:06', '2026-11-02 07:30:06', 'SD', 1),
(119, 84, 12, 20, 80.00, 'Nilai raport SD kelas 1.', '2026-11-02 07:30:06', '2026-11-02 07:30:06', 'SD', 1),
(120, 84, 13, 21, 82.00, 'Nilai raport SD kelas 1.', '2026-11-02 07:30:06', '2026-11-02 07:30:06', 'SD', 1),
(121, 84, 11, 19, 81.00, 'Nilai raport SD kelas 2.', '2026-11-02 07:30:06', '2026-11-02 07:30:06', 'SD', 2),
(122, 84, 12, 20, 83.00, 'Nilai raport SD kelas 2.', '2026-11-02 07:30:06', '2026-11-02 07:30:06', 'SD', 2),
(123, 84, 13, 21, 85.00, 'Nilai raport SD kelas 2.', '2026-11-02 07:30:06', '2026-11-02 07:30:06', 'SD', 2),
(124, 84, 11, 19, 84.00, 'Nilai raport SD kelas 3.', '2026-11-02 07:30:06', '2026-11-02 07:30:06', 'SD', 3),
(125, 84, 12, 20, 86.00, 'Nilai raport SD kelas 3.', '2026-11-02 07:30:06', '2026-11-02 07:30:06', 'SD', 3),
(126, 84, 13, 21, 88.00, 'Nilai raport SD kelas 3.', '2026-11-02 07:30:06', '2026-11-02 07:30:06', 'SD', 3),
(127, 84, 11, 19, 87.00, 'Nilai raport SD kelas 4.', '2026-11-02 07:30:06', '2026-11-02 07:30:06', 'SD', 4),
(128, 84, 12, 20, 89.00, 'Nilai raport SD kelas 4.', '2026-11-02 07:30:06', '2026-11-02 07:30:06', 'SD', 4),
(129, 84, 13, 21, 91.00, 'Nilai raport SD kelas 4.', '2026-11-02 07:30:06', '2026-11-02 07:30:06', 'SD', 4),
(130, 84, 11, 19, 90.00, 'Nilai raport SD kelas 5.', '2026-11-02 07:30:06', '2026-11-02 07:30:06', 'SD', 5),
(131, 84, 12, 20, 92.00, 'Nilai raport SD kelas 5.', '2026-11-02 07:30:06', '2026-11-02 07:30:06', 'SD', 5),
(132, 84, 13, 21, 94.00, 'Nilai raport SD kelas 5.', '2026-11-02 07:30:06', '2026-11-02 07:30:06', 'SD', 5),
(133, 84, 11, 19, 93.00, 'Nilai raport SD kelas 6.', '2026-11-02 07:30:06', '2026-11-02 07:30:06', 'SD', 6),
(134, 84, 12, 20, 95.00, 'Nilai raport SD kelas 6.', '2026-11-02 07:30:06', '2026-11-02 07:30:06', 'SD', 6),
(135, 84, 13, 21, 78.00, 'Nilai raport SD kelas 6.', '2026-11-02 07:30:06', '2026-11-02 07:30:06', 'SD', 6),
(136, 84, 11, 19, 96.00, 'Nilai raport SMP kelas 1.', '2026-11-02 07:30:06', '2026-11-02 07:30:06', 'SMP', 1),
(137, 84, 12, 20, 79.00, 'Nilai raport SMP kelas 1.', '2026-11-02 07:30:06', '2026-11-02 07:30:06', 'SMP', 1),
(138, 84, 13, 21, 81.00, 'Nilai raport SMP kelas 1.', '2026-11-02 07:30:06', '2026-11-02 07:30:06', 'SMP', 1),
(139, 84, 11, 19, 80.00, 'Nilai raport SMP kelas 2.', '2026-11-02 07:30:06', '2026-11-02 07:30:06', 'SMP', 2),
(140, 84, 12, 20, 82.00, 'Nilai raport SMP kelas 2.', '2026-11-02 07:30:06', '2026-11-02 07:30:06', 'SMP', 2),
(141, 84, 13, 21, 84.00, 'Nilai raport SMP kelas 2.', '2026-11-02 07:30:06', '2026-11-02 07:30:06', 'SMP', 2),
(142, 84, 11, 19, 83.00, 'Nilai raport SMP kelas 3.', '2026-11-02 07:30:06', '2026-11-02 07:30:06', 'SMP', 3),
(143, 84, 12, 20, 85.00, 'Nilai raport SMP kelas 3.', '2026-11-02 07:30:06', '2026-11-02 07:30:06', 'SMP', 3),
(144, 84, 13, 21, 87.00, 'Nilai raport SMP kelas 3.', '2026-11-02 07:30:06', '2026-11-02 07:30:06', 'SMP', 3),
(145, 84, 11, 19, 86.00, 'Nilai raport SMA kelas 1.', '2026-11-02 07:30:06', '2026-11-02 07:30:06', 'SMA', 1),
(146, 84, 12, 20, 88.00, 'Nilai raport SMA kelas 1.', '2026-11-02 07:30:06', '2026-11-02 07:30:06', 'SMA', 1),
(147, 84, 13, 21, 90.00, 'Nilai raport SMA kelas 1.', '2026-11-02 07:30:06', '2026-11-02 07:30:06', 'SMA', 1),
(148, 84, 11, 19, 89.00, 'Nilai raport SMA kelas 2.', '2026-11-02 07:30:06', '2026-11-02 07:30:06', 'SMA', 2),
(149, 84, 12, 20, 91.00, 'Nilai raport SMA kelas 2.', '2026-11-02 07:30:06', '2026-11-02 07:30:06', 'SMA', 2),
(150, 84, 13, 21, 93.00, 'Nilai raport SMA kelas 2.', '2026-11-02 07:30:06', '2026-11-02 07:30:06', 'SMA', 2);

-- --------------------------------------------------------

--
-- Struktur dari tabel `cbt_soal`
--

CREATE TABLE `cbt_soal` (
  `soal_id` bigint UNSIGNED NOT NULL,
  `soal_topik_id` bigint UNSIGNED NOT NULL,
  `soal_detail` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `soal_tipe` smallint UNSIGNED NOT NULL DEFAULT '1',
  `soal_kunci` text COLLATE utf8mb4_unicode_ci,
  `soal_difficulty` smallint NOT NULL DEFAULT '1',
  `soal_aktif` tinyint(1) NOT NULL DEFAULT '0',
  `soal_audio` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `soal_audio_play` int NOT NULL DEFAULT '0',
  `soal_timer` smallint DEFAULT NULL,
  `soal_inline_answers` tinyint(1) NOT NULL DEFAULT '0',
  `soal_auto_next` tinyint(1) NOT NULL DEFAULT '0',
  `soal_creator_id` bigint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `cbt_soal`
--

INSERT INTO `cbt_soal` (`soal_id`, `soal_topik_id`, `soal_detail`, `soal_tipe`, `soal_kunci`, `soal_difficulty`, `soal_aktif`, `soal_audio`, `soal_audio_play`, `soal_timer`, `soal_inline_answers`, `soal_auto_next`, `soal_creator_id`) VALUES
(86, 16, 'Tentukan nilai x dari persamaan 2x + 6 = 14.', 1, NULL, 2, 1, NULL, 0, NULL, 0, 0, 16),
(87, 16, 'Hasil dari (x + 3)(x - 2) adalah ...', 1, NULL, 2, 1, NULL, 0, NULL, 0, 0, 16),
(88, 16, 'Tentukan nilai x dari 3x - 5 = 10.', 1, NULL, 2, 1, NULL, 0, NULL, 0, 0, 16),
(89, 16, 'Jika a = 2 dan b = 3, nilai a² + b² adalah ...', 1, NULL, 2, 1, NULL, 0, NULL, 0, 0, 16),
(90, 16, 'Bentuk sederhana dari 4x + 3y - 2x + y adalah ...', 1, NULL, 2, 1, NULL, 0, NULL, 0, 0, 16),
(91, 16, 'Himpunan penyelesaian dari x² - 9 = 0 adalah ...', 1, NULL, 2, 1, NULL, 0, NULL, 0, 0, 16),
(92, 17, 'Luas segitiga dengan alas 10 cm dan tinggi 6 cm adalah ...', 1, NULL, 1, 1, NULL, 0, NULL, 0, 0, 16),
(93, 17, 'Keliling persegi dengan panjang sisi 7 cm adalah ...', 1, NULL, 1, 1, NULL, 0, NULL, 0, 0, 16),
(94, 17, 'Jumlah besar sudut dalam sebuah segitiga adalah ...', 1, NULL, 1, 1, NULL, 0, NULL, 0, 0, 16),
(95, 17, 'Volume kubus dengan panjang rusuk 4 cm adalah ...', 1, NULL, 1, 1, NULL, 0, NULL, 0, 0, 16),
(96, 18, 'Kalimat utama pada paragraf eksposisi umumnya terletak di ...', 1, NULL, 2, 1, NULL, 0, NULL, 0, 0, 16),
(97, 18, 'Tujuan utama teks eksposisi adalah ...', 1, NULL, 2, 1, NULL, 0, NULL, 0, 0, 16),
(98, 18, 'Penulisan kata baku yang benar adalah ...', 1, NULL, 2, 1, NULL, 0, NULL, 0, 0, 16),
(99, 18, 'Sinonim dari kata \"cermat\" adalah ...', 1, NULL, 2, 1, NULL, 0, NULL, 0, 0, 16),
(100, 18, 'Antonim dari kata \"optimal\" adalah ...', 1, NULL, 2, 1, NULL, 0, NULL, 0, 0, 16),
(102, 16, 'Jelaskan langkah-langkah menyelesaikan persamaan linear satu variabel.', 2, NULL, 2, 1, NULL, 0, NULL, 0, 0, 16),
(103, 16, 'Hasil dari 5 + 3 × 2 adalah ...', 3, '11', 2, 1, NULL, 0, NULL, 0, 0, 16),
(104, 19, 'Nilai x dari 4x - 8 = 12 adalah ...', 1, NULL, 2, 1, NULL, 0, NULL, 0, 0, 19),
(105, 19, 'Bentuk sederhana dari 2a + 3b - a + 2b adalah ...', 1, NULL, 2, 1, NULL, 0, NULL, 0, 0, 19),
(106, 19, 'Jika x = 3, nilai 2xA� + 1 adalah ...', 1, NULL, 2, 1, NULL, 0, NULL, 0, 0, 19),
(107, 20, 'Tujuan teks deskripsi adalah ...', 1, NULL, 1, 1, NULL, 0, NULL, 0, 0, 20),
(108, 20, 'Ciri khas teks deskripsi adalah ...', 1, NULL, 1, 1, NULL, 0, NULL, 0, 0, 20),
(109, 20, 'Sinonim dari kata \"indah\" adalah ...', 1, NULL, 1, 1, NULL, 0, NULL, 0, 0, 20),
(110, 21, 'Planet yang paling dekat dengan Matahari adalah ...', 1, NULL, 1, 1, NULL, 0, NULL, 0, 0, 21),
(111, 21, 'Satelit alami Bumi adalah ...', 1, NULL, 1, 1, NULL, 0, NULL, 0, 0, 21),
(112, 21, 'Planet terbesar di tata surya adalah ...', 1, NULL, 2, 1, NULL, 0, NULL, 0, 0, 21),
(113, 19, 'Hasil dari 5 + 3 A- 2 adalah ...', 3, '11', 2, 1, NULL, 0, NULL, 0, 0, 19),
(114, 20, 'Kata baku dari \"aktifitas\" adalah ...', 3, 'aktivitas', 1, 1, NULL, 0, NULL, 0, 0, 20),
(115, 21, 'Planet yang dikenal sebagai planet merah adalah ...', 3, 'Mars', 1, 1, NULL, 0, NULL, 0, 0, 21),
(116, 16, 'Jika a = 2 dan b = 3, nilai aÂ² + bÂ² adalah ...', 1, NULL, 2, 1, NULL, 0, NULL, 0, 0, 16),
(117, 16, 'Himpunan penyelesaian dari xÂ² - 9 = 0 adalah ...', 1, NULL, 2, 1, NULL, 0, NULL, 0, 0, 16),
(118, 16, 'Hasil dari 5 + 3 Ã— 2 adalah ...', 3, '11', 2, 1, NULL, 0, NULL, 0, 0, 16),
(119, 19, 'Jika x = 3, nilai 2xAï¿½ + 1 adalah ...', 1, NULL, 2, 1, NULL, 0, NULL, 0, 0, 19);

-- --------------------------------------------------------

--
-- Struktur dari tabel `cbt_tes`
--

CREATE TABLE `cbt_tes` (
  `tes_id` bigint UNSIGNED NOT NULL,
  `tes_nama` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tes_foundation_id` bigint UNSIGNED DEFAULT NULL,
  `tes_school_id` bigint UNSIGNED DEFAULT NULL,
  `tes_academic_year_id` bigint UNSIGNED DEFAULT NULL,
  `tes_detail` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `tes_begin_time` datetime DEFAULT NULL,
  `tes_end_time` datetime DEFAULT NULL,
  `tes_duration_time` smallint UNSIGNED NOT NULL DEFAULT '0',
  `tes_ip_range` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '*.*.*.*',
  `tes_results_to_users` tinyint(1) NOT NULL DEFAULT '0',
  `tes_detail_to_users` tinyint(1) NOT NULL DEFAULT '0',
  `tes_score_right` decimal(10,2) NOT NULL DEFAULT '1.00',
  `tes_score_wrong` decimal(10,2) NOT NULL DEFAULT '0.00',
  `tes_score_unanswered` decimal(10,2) NOT NULL DEFAULT '0.00',
  `tes_max_score` decimal(10,2) NOT NULL DEFAULT '0.00',
  `tes_token` tinyint(1) NOT NULL DEFAULT '0',
  `tes_ranking_to_users` tinyint(1) NOT NULL DEFAULT '0',
  `tes_biaya` decimal(12,2) NOT NULL DEFAULT '0.00',
  `tes_absensi_wajib` tinyint(1) NOT NULL DEFAULT '0',
  `tes_absensi_location_id` bigint UNSIGNED DEFAULT NULL,
  `tes_checkin_mulai` datetime DEFAULT NULL,
  `tes_checkin_selesai` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `cbt_tes`
--

INSERT INTO `cbt_tes` (`tes_id`, `tes_nama`, `tes_foundation_id`, `tes_school_id`, `tes_academic_year_id`, `tes_detail`, `tes_begin_time`, `tes_end_time`, `tes_duration_time`, `tes_ip_range`, `tes_results_to_users`, `tes_detail_to_users`, `tes_score_right`, `tes_score_wrong`, `tes_score_unanswered`, `tes_max_score`, `tes_token`, `tes_ranking_to_users`, `tes_biaya`, `tes_absensi_wajib`, `tes_absensi_location_id`, `tes_checkin_mulai`, `tes_checkin_selesai`) VALUES
(11, 'Try Out Matematika', 1, 3, 3, 'Try out mata pelajaran Matematika untuk kelas XI. Bacalah setiap soal dengan teliti.', '2026-11-02 22:40:07', '2026-12-03 22:40:07', 60, '*.*.*.*', 1, 1, 10.00, 0.00, 0.00, 100.00, 0, 0, 75000.00, 0, NULL, NULL, NULL),
(12, 'Ulangan Bahasa Indonesia', 1, 3, 3, 'Ulangan harian Bahasa Indonesia materi teks eksposisi.', '2026-11-02 22:40:07', '2026-11-17 22:40:07', 45, '*.*.*.*', 1, 0, 20.00, 0.00, 0.00, 100.00, 1, 1, 50000.00, 0, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `cbt_tesgrup`
--

CREATE TABLE `cbt_tesgrup` (
  `tstgrp_tes_id` bigint UNSIGNED NOT NULL,
  `tstgrp_grup_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `cbt_tesgrup`
--

INSERT INTO `cbt_tesgrup` (`tstgrp_tes_id`, `tstgrp_grup_id`) VALUES
(11, 11),
(11, 12),
(11, 13),
(11, 14),
(11, 15),
(11, 16),
(11, 17),
(11, 18),
(11, 19),
(11, 20),
(11, 21),
(11, 22),
(11, 23),
(11, 24),
(11, 25),
(11, 26),
(11, 27),
(11, 28),
(12, 11),
(12, 12),
(12, 13),
(12, 14),
(12, 15),
(12, 16),
(12, 17),
(12, 18),
(12, 19),
(12, 20),
(12, 21),
(12, 22),
(12, 23),
(12, 24),
(12, 25),
(12, 26),
(12, 27),
(12, 28);

-- --------------------------------------------------------

--
-- Struktur dari tabel `cbt_tes_soal`
--

CREATE TABLE `cbt_tes_soal` (
  `tessoal_id` bigint UNSIGNED NOT NULL,
  `tessoal_tesuser_id` bigint UNSIGNED NOT NULL,
  `tessoal_user_ip` varchar(39) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tessoal_soal_id` bigint UNSIGNED NOT NULL,
  `tessoal_jawaban_text` text COLLATE utf8mb4_unicode_ci,
  `tessoal_nilai` decimal(10,2) DEFAULT NULL,
  `tessoal_creation_time` datetime DEFAULT NULL,
  `tessoal_display_time` datetime DEFAULT NULL,
  `tessoal_change_time` datetime DEFAULT NULL,
  `tessoal_reaction_time` bigint UNSIGNED NOT NULL DEFAULT '0',
  `tessoal_ragu` int NOT NULL DEFAULT '0',
  `tessoal_order` smallint NOT NULL DEFAULT '1',
  `tessoal_num_answers` smallint UNSIGNED NOT NULL DEFAULT '0',
  `tessoal_comment` text COLLATE utf8mb4_unicode_ci,
  `tessoal_audio_play` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `cbt_tes_soal`
--

INSERT INTO `cbt_tes_soal` (`tessoal_id`, `tessoal_tesuser_id`, `tessoal_user_ip`, `tessoal_soal_id`, `tessoal_jawaban_text`, `tessoal_nilai`, `tessoal_creation_time`, `tessoal_display_time`, `tessoal_change_time`, `tessoal_reaction_time`, `tessoal_ragu`, `tessoal_order`, `tessoal_num_answers`, `tessoal_comment`, `tessoal_audio_play`) VALUES
(116, 23, NULL, 96, NULL, 20.00, '2026-10-28 09:00:00', '2026-10-28 09:00:00', '2026-10-28 09:01:00', 60, 0, 1, 0, NULL, 0),
(117, 23, NULL, 97, NULL, 20.00, '2026-10-28 09:00:00', '2026-10-28 09:00:00', '2026-10-28 09:02:00', 60, 0, 2, 0, NULL, 0),
(118, 23, NULL, 98, NULL, 20.00, '2026-10-28 09:00:00', '2026-10-28 09:00:00', '2026-10-28 09:03:00', 60, 0, 3, 0, NULL, 0),
(119, 23, NULL, 99, NULL, 20.00, '2026-10-28 09:00:00', '2026-10-28 09:00:00', '2026-10-28 09:04:00', 60, 0, 4, 0, NULL, 0),
(120, 23, NULL, 100, NULL, 0.00, '2026-10-28 09:00:00', '2026-10-28 09:00:00', '2026-10-28 09:05:00', 60, 0, 5, 0, NULL, 0),
(136, 27, '127.0.0.1', 90, NULL, 10.00, '2026-11-01 20:06:43', '2026-11-01 20:06:43', '2026-11-01 20:06:56', 13, 0, 1, 1, NULL, 0),
(137, 27, '127.0.0.1', 86, NULL, 10.00, '2026-11-01 20:06:43', '2026-11-01 20:06:56', '2026-11-01 20:07:05', 9, 0, 2, 1, NULL, 0),
(138, 27, '127.0.0.1', 89, NULL, 0.00, '2026-11-01 20:06:43', '2026-11-01 20:07:05', '2026-11-01 20:07:08', 3, 0, 3, 1, NULL, 0),
(139, 27, '127.0.0.1', 91, NULL, 0.00, '2026-11-01 20:06:43', '2026-11-01 20:07:09', '2026-11-01 20:07:11', 2, 0, 4, 1, NULL, 0),
(140, 27, '127.0.0.1', 88, NULL, 0.00, '2026-11-01 20:06:43', '2026-11-01 20:07:12', '2026-11-01 20:07:15', 3, 0, 5, 1, NULL, 0),
(141, 27, '127.0.0.1', 87, NULL, 0.00, '2026-11-01 20:06:43', '2026-11-01 20:07:15', '2026-11-01 20:07:18', 3, 0, 6, 1, NULL, 0),
(142, 27, '127.0.0.1', 93, NULL, 10.00, '2026-11-01 20:06:43', '2026-11-01 20:07:18', '2026-11-01 20:07:20', 2, 0, 7, 1, NULL, 0),
(143, 27, '127.0.0.1', 95, NULL, 10.00, '2026-11-01 20:06:43', '2026-11-01 20:07:20', '2026-11-01 20:07:22', 2, 0, 8, 1, NULL, 0),
(144, 27, '127.0.0.1', 94, NULL, 0.00, '2026-11-01 20:06:43', '2026-11-01 20:07:22', '2026-11-01 20:07:25', 3, 0, 9, 1, NULL, 0),
(145, 27, '127.0.0.1', 92, NULL, 0.00, '2026-11-01 20:06:43', '2026-11-01 20:07:25', '2026-11-01 20:07:33', 8, 0, 10, 1, NULL, 0),
(146, 28, '127.0.0.1', 90, NULL, 0.00, '2026-11-01 20:18:58', '2026-11-01 20:18:58', '2026-11-01 20:19:00', 2, 0, 1, 1, NULL, 0),
(147, 28, '127.0.0.1', 86, NULL, 0.00, '2026-11-01 20:18:58', '2026-11-01 20:19:01', '2026-11-01 20:19:03', 2, 0, 2, 1, NULL, 0),
(148, 28, '127.0.0.1', 88, NULL, 10.00, '2026-11-01 20:18:58', '2026-11-01 20:19:03', '2026-11-01 20:19:06', 3, 0, 3, 1, NULL, 0),
(149, 28, '127.0.0.1', 87, NULL, 10.00, '2026-11-01 20:18:58', '2026-11-01 20:19:06', '2026-11-01 20:19:09', 3, 0, 4, 1, NULL, 0),
(150, 28, '127.0.0.1', 91, NULL, 10.00, '2026-11-01 20:18:58', '2026-11-01 20:19:09', '2026-11-01 20:19:13', 4, 0, 5, 1, NULL, 0),
(151, 28, '127.0.0.1', 89, NULL, 0.00, '2026-11-01 20:18:58', '2026-11-01 20:19:13', '2026-11-01 20:19:15', 2, 0, 6, 1, NULL, 0),
(152, 28, '127.0.0.1', 92, NULL, 10.00, '2026-11-01 20:18:58', '2026-11-01 20:19:15', '2026-11-01 20:19:17', 2, 0, 7, 1, NULL, 0),
(153, 28, '127.0.0.1', 95, NULL, 10.00, '2026-11-01 20:18:58', '2026-11-01 20:19:17', '2026-11-01 20:19:20', 3, 0, 8, 1, NULL, 0),
(154, 28, '127.0.0.1', 93, NULL, 0.00, '2026-11-01 20:18:58', '2026-11-01 20:19:20', '2026-11-01 20:19:22', 2, 0, 9, 1, NULL, 0),
(155, 28, '127.0.0.1', 94, NULL, 0.00, '2026-11-01 20:18:58', '2026-11-01 20:19:23', '2026-11-01 20:19:31', 8, 0, 10, 1, NULL, 0),
(156, 29, '127.0.0.1', 87, NULL, 0.00, '2026-11-01 21:54:28', '2026-11-01 21:54:28', '2026-11-01 21:54:32', 4, 0, 1, 1, NULL, 0),
(157, 29, '127.0.0.1', 91, NULL, 0.00, '2026-11-01 21:54:28', '2026-11-01 21:54:32', '2026-11-01 21:54:34', 2, 0, 2, 1, NULL, 0),
(158, 29, '127.0.0.1', 86, NULL, 0.00, '2026-11-01 21:54:28', '2026-11-01 21:54:34', '2026-11-01 21:54:36', 2, 0, 3, 1, NULL, 0),
(159, 29, '127.0.0.1', 88, NULL, 10.00, '2026-11-01 21:54:28', '2026-11-01 21:54:36', '2026-11-01 21:54:38', 2, 0, 4, 1, NULL, 0),
(160, 29, '127.0.0.1', 90, NULL, 10.00, '2026-11-01 21:54:28', '2026-11-01 21:54:38', '2026-11-01 21:54:40', 2, 0, 5, 1, NULL, 0),
(161, 29, '127.0.0.1', 89, NULL, 0.00, '2026-11-01 21:54:28', '2026-11-01 21:54:41', '2026-11-01 21:54:44', 3, 0, 6, 1, NULL, 0),
(162, 29, '127.0.0.1', 93, NULL, 0.00, '2026-11-01 21:54:28', '2026-11-01 21:54:44', '2026-11-01 21:54:46', 2, 0, 7, 1, NULL, 0),
(163, 29, '127.0.0.1', 94, NULL, 10.00, '2026-11-01 21:54:28', '2026-11-01 21:54:47', '2026-11-01 21:54:49', 2, 0, 8, 1, NULL, 0),
(164, 29, '127.0.0.1', 92, NULL, 0.00, '2026-11-01 21:54:28', '2026-11-01 21:54:49', '2026-11-01 21:54:52', 3, 0, 9, 1, NULL, 0),
(165, 29, '127.0.0.1', 95, NULL, 0.00, '2026-11-01 21:54:28', '2026-11-01 21:54:52', '2026-11-01 21:54:59', 7, 0, 10, 1, NULL, 0),
(166, 30, NULL, 96, NULL, 20.00, '2026-10-29 09:00:00', '2026-10-29 09:00:00', '2026-10-29 09:01:00', 60, 0, 1, 0, NULL, 0),
(167, 30, NULL, 97, NULL, 20.00, '2026-10-29 09:00:00', '2026-10-29 09:00:00', '2026-10-29 09:02:00', 60, 0, 2, 0, NULL, 0),
(168, 30, NULL, 98, NULL, 20.00, '2026-10-29 09:00:00', '2026-10-29 09:00:00', '2026-10-29 09:03:00', 60, 0, 3, 0, NULL, 0),
(169, 30, NULL, 99, NULL, 0.00, '2026-10-29 09:00:00', '2026-10-29 09:00:00', '2026-10-29 09:04:00', 60, 0, 4, 0, NULL, 0),
(170, 30, NULL, 100, NULL, 0.00, '2026-10-29 09:00:00', '2026-10-29 09:00:00', '2026-10-29 09:05:00', 60, 0, 5, 0, NULL, 0),
(171, 31, NULL, 96, NULL, 20.00, '2026-10-30 09:00:00', '2026-10-30 09:00:00', '2026-10-30 09:01:00', 60, 0, 1, 0, NULL, 0),
(172, 31, NULL, 97, NULL, 20.00, '2026-10-30 09:00:00', '2026-10-30 09:00:00', '2026-10-30 09:02:00', 60, 0, 2, 0, NULL, 0),
(173, 31, NULL, 98, NULL, 20.00, '2026-10-30 09:00:00', '2026-10-30 09:00:00', '2026-10-30 09:03:00', 60, 0, 3, 0, NULL, 0),
(174, 31, NULL, 99, NULL, 20.00, '2026-10-30 09:00:00', '2026-10-30 09:00:00', '2026-10-30 09:04:00', 60, 0, 4, 0, NULL, 0),
(175, 31, NULL, 100, NULL, 20.00, '2026-10-30 09:00:00', '2026-10-30 09:00:00', '2026-10-30 09:05:00', 60, 0, 5, 0, NULL, 0),
(176, 32, NULL, 96, NULL, 20.00, '2026-10-31 09:00:00', '2026-10-31 09:00:00', '2026-10-31 09:01:00', 60, 0, 1, 0, NULL, 0),
(177, 32, NULL, 97, NULL, 20.00, '2026-10-31 09:00:00', '2026-10-31 09:00:00', '2026-10-31 09:02:00', 60, 0, 2, 0, NULL, 0),
(178, 32, NULL, 98, NULL, 0.00, '2026-10-31 09:00:00', '2026-10-31 09:00:00', '2026-10-31 09:03:00', 60, 0, 3, 0, NULL, 0),
(179, 32, NULL, 99, NULL, 0.00, '2026-10-31 09:00:00', '2026-10-31 09:00:00', '2026-10-31 09:04:00', 60, 0, 4, 0, NULL, 0),
(180, 32, NULL, 100, NULL, 0.00, '2026-10-31 09:00:00', '2026-10-31 09:00:00', '2026-10-31 09:05:00', 60, 0, 5, 0, NULL, 0),
(181, 33, '127.0.0.1', 97, NULL, 0.00, '2026-11-02 13:12:27', '2026-11-02 13:12:27', '2026-11-02 13:12:33', 6, 0, 1, 1, NULL, 0),
(182, 33, '127.0.0.1', 96, NULL, 0.00, '2026-11-02 13:12:27', '2026-11-02 13:12:33', '2026-11-02 13:12:35', 2, 0, 2, 1, NULL, 0),
(183, 33, '127.0.0.1', 99, NULL, 20.00, '2026-11-02 13:12:27', '2026-11-02 13:12:35', '2026-11-02 13:12:38', 3, 0, 3, 1, NULL, 0),
(184, 33, '127.0.0.1', 100, NULL, 0.00, '2026-11-02 13:12:27', '2026-11-02 13:12:38', '2026-11-02 13:12:40', 2, 0, 4, 1, NULL, 0),
(185, 33, '127.0.0.1', 98, NULL, 0.00, '2026-11-02 13:12:27', '2026-11-02 13:12:41', '2026-11-02 13:12:51', 10, 0, 5, 1, NULL, 0),
(196, 35, '100.65.125.5', 98, NULL, 20.00, '2026-11-05 19:54:03', '2026-11-05 19:54:03', '2026-11-05 19:54:14', 11, 0, 1, 1, NULL, 0),
(197, 35, '100.65.125.5', 100, NULL, 0.00, '2026-11-05 19:54:03', '2026-11-05 19:54:14', '2026-11-05 19:54:32', 18, 0, 2, 1, NULL, 0),
(198, 35, '100.65.125.5', 99, NULL, 20.00, '2026-11-05 19:54:03', '2026-11-05 19:54:32', '2026-11-05 19:54:38', 6, 0, 3, 1, NULL, 0),
(199, 35, '100.65.125.5', 97, NULL, 20.00, '2026-11-05 19:54:03', '2026-11-05 19:54:39', '2026-11-05 19:58:47', 248, 0, 4, 1, NULL, 0),
(200, 35, '100.65.125.5', 96, NULL, 0.00, '2026-11-05 19:54:03', '2026-11-05 19:58:47', '2026-11-05 19:59:27', 40, 0, 5, 1, NULL, 0);

-- --------------------------------------------------------

--
-- Struktur dari tabel `cbt_tes_soal_jawaban`
--

CREATE TABLE `cbt_tes_soal_jawaban` (
  `soaljawaban_tessoal_id` bigint UNSIGNED NOT NULL,
  `soaljawaban_jawaban_id` bigint UNSIGNED NOT NULL,
  `soaljawaban_selected` smallint NOT NULL DEFAULT '-1',
  `soaljawaban_order` smallint NOT NULL DEFAULT '1',
  `soaljawaban_position` bigint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `cbt_tes_soal_jawaban`
--

INSERT INTO `cbt_tes_soal_jawaban` (`soaljawaban_tessoal_id`, `soaljawaban_jawaban_id`, `soaljawaban_selected`, `soaljawaban_order`, `soaljawaban_position`) VALUES
(116, 341, 1, 1, NULL),
(116, 342, -1, 2, NULL),
(116, 343, -1, 3, NULL),
(116, 344, -1, 4, NULL),
(117, 345, -1, 1, NULL),
(117, 346, 1, 2, NULL),
(117, 347, -1, 3, NULL),
(117, 348, -1, 4, NULL),
(118, 349, -1, 1, NULL),
(118, 350, 1, 2, NULL),
(118, 351, -1, 3, NULL),
(118, 352, -1, 4, NULL),
(119, 353, 1, 1, NULL),
(119, 354, -1, 2, NULL),
(119, 355, -1, 3, NULL),
(119, 356, -1, 4, NULL),
(120, 357, 1, 1, NULL),
(120, 358, -1, 2, NULL),
(120, 359, -1, 3, NULL),
(120, 360, -1, 4, NULL),
(136, 317, 1, 1, NULL),
(136, 318, -1, 2, NULL),
(136, 319, -1, 4, NULL),
(136, 320, -1, 3, NULL),
(137, 301, -1, 4, NULL),
(137, 302, -1, 1, NULL),
(137, 303, 1, 2, NULL),
(137, 304, -1, 3, NULL),
(138, 313, -1, 2, NULL),
(138, 314, -1, 4, NULL),
(138, 315, -1, 1, NULL),
(138, 316, 1, 3, NULL),
(139, 321, -1, 1, NULL),
(139, 322, 1, 3, NULL),
(139, 323, -1, 4, NULL),
(139, 324, -1, 2, NULL),
(140, 309, -1, 4, NULL),
(140, 310, 1, 3, NULL),
(140, 311, -1, 2, NULL),
(140, 312, -1, 1, NULL),
(141, 305, -1, 4, NULL),
(141, 306, 1, 3, NULL),
(141, 307, -1, 2, NULL),
(141, 308, -1, 1, NULL),
(142, 329, -1, 1, NULL),
(142, 330, 1, 3, NULL),
(142, 331, -1, 4, NULL),
(142, 332, -1, 2, NULL),
(143, 337, -1, 4, NULL),
(143, 338, -1, 1, NULL),
(143, 339, -1, 2, NULL),
(143, 340, 1, 3, NULL),
(144, 333, -1, 2, NULL),
(144, 334, -1, 4, NULL),
(144, 335, 1, 3, NULL),
(144, 336, -1, 1, NULL),
(145, 325, -1, 3, NULL),
(145, 326, -1, 1, NULL),
(145, 327, -1, 2, NULL),
(145, 328, 1, 4, NULL),
(146, 317, -1, 4, NULL),
(146, 318, -1, 1, NULL),
(146, 319, -1, 3, NULL),
(146, 320, 1, 2, NULL),
(147, 301, -1, 4, NULL),
(147, 302, 1, 1, NULL),
(147, 303, -1, 2, NULL),
(147, 304, -1, 3, NULL),
(148, 309, -1, 2, NULL),
(148, 310, -1, 3, NULL),
(148, 311, 1, 1, NULL),
(148, 312, -1, 4, NULL),
(149, 305, 1, 2, NULL),
(149, 306, -1, 4, NULL),
(149, 307, -1, 3, NULL),
(149, 308, -1, 1, NULL),
(150, 321, 1, 2, NULL),
(150, 322, -1, 1, NULL),
(150, 323, -1, 3, NULL),
(150, 324, -1, 4, NULL),
(151, 313, -1, 1, NULL),
(151, 314, -1, 4, NULL),
(151, 315, -1, 2, NULL),
(151, 316, 1, 3, NULL),
(152, 325, 1, 2, NULL),
(152, 326, -1, 4, NULL),
(152, 327, -1, 3, NULL),
(152, 328, -1, 1, NULL),
(153, 337, -1, 1, NULL),
(153, 338, -1, 4, NULL),
(153, 339, -1, 2, NULL),
(153, 340, 1, 3, NULL),
(154, 329, -1, 4, NULL),
(154, 330, -1, 3, NULL),
(154, 331, 1, 2, NULL),
(154, 332, -1, 1, NULL),
(155, 333, -1, 2, NULL),
(155, 334, -1, 3, NULL),
(155, 335, -1, 4, NULL),
(155, 336, 1, 1, NULL),
(156, 305, -1, 4, NULL),
(156, 306, -1, 2, NULL),
(156, 307, -1, 3, NULL),
(156, 308, 1, 1, NULL),
(157, 321, -1, 4, NULL),
(157, 322, 1, 1, NULL),
(157, 323, -1, 2, NULL),
(157, 324, -1, 3, NULL),
(158, 301, 1, 2, NULL),
(158, 302, -1, 1, NULL),
(158, 303, -1, 4, NULL),
(158, 304, -1, 3, NULL),
(159, 309, -1, 4, NULL),
(159, 310, -1, 2, NULL),
(159, 311, 1, 1, NULL),
(159, 312, -1, 3, NULL),
(160, 317, 1, 1, NULL),
(160, 318, -1, 2, NULL),
(160, 319, -1, 4, NULL),
(160, 320, -1, 3, NULL),
(161, 313, -1, 3, NULL),
(161, 314, 1, 1, NULL),
(161, 315, -1, 4, NULL),
(161, 316, -1, 2, NULL),
(162, 329, -1, 2, NULL),
(162, 330, -1, 4, NULL),
(162, 331, -1, 3, NULL),
(162, 332, 1, 1, NULL),
(163, 333, -1, 3, NULL),
(163, 334, 1, 1, NULL),
(163, 335, -1, 4, NULL),
(163, 336, -1, 2, NULL),
(164, 325, -1, 1, NULL),
(164, 326, -1, 4, NULL),
(164, 327, 1, 2, NULL),
(164, 328, -1, 3, NULL),
(165, 337, 1, 2, NULL),
(165, 338, -1, 3, NULL),
(165, 339, -1, 1, NULL),
(165, 340, -1, 4, NULL),
(166, 341, 1, 1, NULL),
(166, 342, -1, 2, NULL),
(166, 343, -1, 3, NULL),
(166, 344, -1, 4, NULL),
(167, 345, -1, 1, NULL),
(167, 346, 1, 2, NULL),
(167, 347, -1, 3, NULL),
(167, 348, -1, 4, NULL),
(168, 349, -1, 1, NULL),
(168, 350, 1, 2, NULL),
(168, 351, -1, 3, NULL),
(168, 352, -1, 4, NULL),
(169, 353, -1, 1, NULL),
(169, 354, 1, 2, NULL),
(169, 355, -1, 3, NULL),
(169, 356, -1, 4, NULL),
(170, 357, 1, 1, NULL),
(170, 358, -1, 2, NULL),
(170, 359, -1, 3, NULL),
(170, 360, -1, 4, NULL),
(171, 341, 1, 1, NULL),
(171, 342, -1, 2, NULL),
(171, 343, -1, 3, NULL),
(171, 344, -1, 4, NULL),
(172, 345, -1, 1, NULL),
(172, 346, 1, 2, NULL),
(172, 347, -1, 3, NULL),
(172, 348, -1, 4, NULL),
(173, 349, -1, 1, NULL),
(173, 350, 1, 2, NULL),
(173, 351, -1, 3, NULL),
(173, 352, -1, 4, NULL),
(174, 353, 1, 1, NULL),
(174, 354, -1, 2, NULL),
(174, 355, -1, 3, NULL),
(174, 356, -1, 4, NULL),
(175, 357, -1, 1, NULL),
(175, 358, -1, 2, NULL),
(175, 359, 1, 3, NULL),
(175, 360, -1, 4, NULL),
(176, 341, 1, 1, NULL),
(176, 342, -1, 2, NULL),
(176, 343, -1, 3, NULL),
(176, 344, -1, 4, NULL),
(177, 345, -1, 1, NULL),
(177, 346, 1, 2, NULL),
(177, 347, -1, 3, NULL),
(177, 348, -1, 4, NULL),
(178, 349, 1, 1, NULL),
(178, 350, -1, 2, NULL),
(178, 351, -1, 3, NULL),
(178, 352, -1, 4, NULL),
(179, 353, -1, 1, NULL),
(179, 354, 1, 2, NULL),
(179, 355, -1, 3, NULL),
(179, 356, -1, 4, NULL),
(180, 357, 1, 1, NULL),
(180, 358, -1, 2, NULL),
(180, 359, -1, 3, NULL),
(180, 360, -1, 4, NULL),
(181, 345, -1, 4, NULL),
(181, 346, -1, 2, NULL),
(181, 347, 1, 1, NULL),
(181, 348, -1, 3, NULL),
(182, 341, -1, 2, NULL),
(182, 342, -1, 4, NULL),
(182, 343, 1, 1, NULL),
(182, 344, -1, 3, NULL),
(183, 353, 1, 1, NULL),
(183, 354, -1, 2, NULL),
(183, 355, -1, 3, NULL),
(183, 356, -1, 4, NULL),
(184, 357, -1, 4, NULL),
(184, 358, 1, 1, NULL),
(184, 359, -1, 2, NULL),
(184, 360, -1, 3, NULL),
(185, 349, -1, 4, NULL),
(185, 350, -1, 2, NULL),
(185, 351, -1, 3, NULL),
(185, 352, 1, 1, NULL),
(196, 349, -1, 4, NULL),
(196, 350, 1, 1, NULL),
(196, 351, -1, 2, NULL),
(196, 352, -1, 3, NULL),
(197, 357, 1, 1, NULL),
(197, 358, -1, 4, NULL),
(197, 359, -1, 2, NULL),
(197, 360, -1, 3, NULL),
(198, 353, 1, 1, NULL),
(198, 354, -1, 2, NULL),
(198, 355, -1, 4, NULL),
(198, 356, -1, 3, NULL),
(199, 345, -1, 2, NULL),
(199, 346, 1, 4, NULL),
(199, 347, -1, 3, NULL),
(199, 348, -1, 1, NULL),
(200, 341, -1, 4, NULL),
(200, 342, 1, 3, NULL),
(200, 343, -1, 1, NULL),
(200, 344, -1, 2, NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `cbt_tes_token`
--

CREATE TABLE `cbt_tes_token` (
  `token_id` bigint UNSIGNED NOT NULL,
  `token_isi` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token_user_id` bigint UNSIGNED NOT NULL,
  `token_ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `token_aktif` int NOT NULL DEFAULT '1',
  `token_tes_id` bigint UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `cbt_tes_token`
--

INSERT INTO `cbt_tes_token` (`token_id`, `token_isi`, `token_user_id`, `token_ts`, `token_aktif`, `token_tes_id`) VALUES
(12, '123456', 16, '2026-11-02 07:30:06', 1, 12),
(14, '123456', 16, '2026-11-02 19:25:08', 1, 12),
(15, '123456', 16, '2026-11-04 06:40:07', 1, 12),
(16, '123456', 16, '2026-11-06 03:53:47', 120, 12);

-- --------------------------------------------------------

--
-- Struktur dari tabel `cbt_tes_topik_set`
--

CREATE TABLE `cbt_tes_topik_set` (
  `tset_id` bigint UNSIGNED NOT NULL,
  `tset_tes_id` bigint UNSIGNED NOT NULL,
  `tset_topik_id` bigint UNSIGNED NOT NULL,
  `tset_tipe` smallint NOT NULL DEFAULT '1',
  `tset_difficulty` smallint NOT NULL DEFAULT '1',
  `tset_jumlah` smallint NOT NULL DEFAULT '1',
  `tset_jawaban` smallint NOT NULL DEFAULT '0',
  `tset_acak_jawaban` int NOT NULL DEFAULT '1',
  `tset_acak_soal` int NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `cbt_tes_topik_set`
--

INSERT INTO `cbt_tes_topik_set` (`tset_id`, `tset_tes_id`, `tset_topik_id`, `tset_tipe`, `tset_difficulty`, `tset_jumlah`, `tset_jawaban`, `tset_acak_jawaban`, `tset_acak_soal`) VALUES
(40, 11, 16, 1, 2, 6, 0, 1, 1),
(41, 11, 17, 1, 1, 4, 0, 1, 1),
(42, 12, 18, 1, 2, 5, 0, 1, 1);

-- --------------------------------------------------------

--
-- Struktur dari tabel `cbt_tes_user`
--

CREATE TABLE `cbt_tes_user` (
  `tesuser_id` bigint UNSIGNED NOT NULL,
  `tesuser_tes_id` bigint UNSIGNED NOT NULL,
  `tesuser_user_id` bigint UNSIGNED NOT NULL,
  `tesuser_status` smallint UNSIGNED NOT NULL DEFAULT '0',
  `tesuser_creation_time` datetime NOT NULL,
  `tesuser_comment` text COLLATE utf8mb4_unicode_ci,
  `tesuser_token` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `cbt_tes_user`
--

INSERT INTO `cbt_tes_user` (`tesuser_id`, `tesuser_tes_id`, `tesuser_user_id`, `tesuser_status`, `tesuser_creation_time`, `tesuser_comment`, `tesuser_token`) VALUES
(23, 12, 43, 4, '2026-10-28 09:00:00', NULL, '123456'),
(27, 11, 41, 4, '2026-11-01 20:06:43', NULL, NULL),
(28, 11, 42, 4, '2026-11-01 20:18:58', NULL, NULL),
(29, 11, 43, 4, '2026-11-01 21:54:28', NULL, NULL),
(30, 12, 49, 4, '2026-10-29 09:00:00', NULL, '123456'),
(31, 12, 50, 4, '2026-10-30 09:00:00', NULL, '123456'),
(32, 12, 51, 4, '2026-10-31 09:00:00', NULL, '123456'),
(33, 12, 41, 4, '2026-11-02 13:12:27', NULL, '123456'),
(35, 12, 42, 4, '2026-11-05 19:54:03', NULL, '123456');

-- --------------------------------------------------------

--
-- Struktur dari tabel `cbt_topik`
--

CREATE TABLE `cbt_topik` (
  `topik_id` bigint UNSIGNED NOT NULL,
  `topik_modul_id` bigint UNSIGNED NOT NULL DEFAULT '1',
  `topik_nama` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `topik_tipe_ujian` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'um',
  `topik_guru_id` bigint UNSIGNED DEFAULT NULL,
  `topik_detail` text COLLATE utf8mb4_unicode_ci,
  `topik_aktif` tinyint(1) NOT NULL DEFAULT '0',
  `topik_creation_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `cbt_topik`
--

INSERT INTO `cbt_topik` (`topik_id`, `topik_modul_id`, `topik_nama`, `topik_tipe_ujian`, `topik_guru_id`, `topik_detail`, `topik_aktif`, `topik_creation_time`) VALUES
(16, 11, 'Aljabar', 'uts', 16, 'Persamaan, pertidaksamaan, dan operasi aljabar.', 1, '2026-11-01 13:04:21'),
(17, 11, 'Geometri', 'uas', 16, 'Bangun datar dan bangun ruang.', 1, '2026-11-01 13:04:21'),
(18, 12, 'Teks Eksposisi', 'um', 16, 'Struktur, kaidah, dan isi teks eksposisi.', 1, '2026-11-01 13:04:21'),
(19, 11, 'Aljabar - Guru A', 'uts', 19, 'Contoh grup soal milik Guru A.', 1, '2026-11-01 15:42:25'),
(20, 12, 'Teks Deskripsi - Guru B', 'uas', 20, 'Contoh grup soal milik Guru B.', 1, '2026-11-01 15:42:25'),
(21, 13, 'Sistem Tata Surya - Guru C', 'um', 21, 'Contoh grup soal milik Guru C.', 1, '2026-11-01 15:42:25');

-- --------------------------------------------------------

--
-- Struktur dari tabel `cbt_user`
--

CREATE TABLE `cbt_user` (
  `user_id` bigint UNSIGNED NOT NULL,
  `user_grup_id` bigint UNSIGNED NOT NULL,
  `user_foundation_id` bigint UNSIGNED DEFAULT NULL,
  `user_school_id` bigint UNSIGNED DEFAULT NULL,
  `user_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_nis` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_nisn` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_regdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_ip` varchar(39) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_firstname` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_gender` varchar(1) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_birthdate` date DEFAULT NULL,
  `user_birthplace` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_level` smallint UNSIGNED NOT NULL DEFAULT '1',
  `user_detail` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `cbt_user`
--

INSERT INTO `cbt_user` (`user_id`, `user_grup_id`, `user_foundation_id`, `user_school_id`, `user_name`, `user_nis`, `user_nisn`, `user_password`, `user_email`, `user_regdate`, `user_ip`, `user_firstname`, `user_gender`, `user_birthdate`, `user_birthplace`, `user_level`, `user_detail`, `remember_token`) VALUES
(41, 11, 1, 3, 'lutfi', 'lutfi', NULL, '$2y$12$59ZlOVaHWdKfbJfeo0AkN.nilWN3uNKkdzZnNNbOgJvV.CqfAK5m6', 'lutfi@example.com', '2026-11-04 06:40:05', NULL, 'Lutfi Hakim', NULL, NULL, NULL, 1, 'Ruang 1, Sesi 1', NULL),
(42, 12, 1, 3, 'joko', 'joko', NULL, '$2y$12$bXLjWNjMlQOuqLLndlsXneFozGawW2JWz1WFB5xSOcgmKIijZqsJ6', 'joko@example.com', '2026-11-04 06:40:05', NULL, 'Joko Susilo', NULL, NULL, NULL, 1, 'Ruang 1, Sesi 1', NULL),
(43, 11, 1, 3, 'siti', 'siti', NULL, '$2y$12$zk5abBj3fMBvbDud8ZOwgunXlEO38gGR.CXvJO5BSvcFlKDFcppdO', 'siti@example.com', '2026-11-04 06:40:05', NULL, 'Siti Aminah', NULL, NULL, NULL, 1, 'Ruang 1, Sesi 2', NULL),
(49, 12, 1, 3, 'budi', 'budi', NULL, '$2y$10$J71SV/kjMJVkbFUu9eII6uFukj09AjalMpb8YCJIHAglSvxST4jWy', 'budi@example.com', '2026-11-04 06:40:05', NULL, 'Budi Santoso', NULL, NULL, NULL, 1, 'Ruang 2, Sesi 1', NULL),
(50, 11, 1, 3, 'rina', 'rina', NULL, '$2y$10$CGTXLsGrWacI7/Wvn/wmFezP9sPmHTc/5IOHsCmYF.Nc/f8BHEOlW', 'rina@example.com', '2026-11-04 06:40:05', NULL, 'Rina Wulandari', NULL, NULL, NULL, 1, 'Ruang 2, Sesi 1', NULL),
(51, 12, 1, 3, 'andi', 'andi', NULL, '$2y$10$nv3p9ZhUAC7UWqHpDDzL9ulNMJQHXrakvRik6HwzUSY8LPg60Trra', 'andi@example.com', '2026-11-04 06:40:05', NULL, 'Andi Pratama', NULL, NULL, NULL, 1, 'Ruang 2, Sesi 2', NULL),
(52, 11, 1, 3, 'dewi', 'dewi', NULL, '$2y$12$OGO1Xub6M0dni.Vft0/WnedOCL.sPQEe4TnOfcXCfYChpb8o03qzq', 'dewi@example.com', '2026-11-04 06:40:05', NULL, 'Dewi Lestari', NULL, NULL, NULL, 1, 'Ruang 3, Sesi 1', NULL),
(53, 12, 1, 3, 'fajar', 'fajar', NULL, '$2y$10$sYvOoHvDVlXKEiILqAOl5OKnKP1mv7Rpc8XTE8igVx7L6wzRErKaa', 'fajar@example.com', '2026-11-04 06:40:05', NULL, 'Fajar Nugroho', NULL, NULL, NULL, 1, 'Ruang 3, Sesi 1', NULL),
(54, 13, 1, 1, 'sd1a', 'sd1a', NULL, '$2y$10$z9xjWtiLEasdqbEH6sAnoOSmYQjrh2Ua2ZhsKjXoylwya7xVARN2i', 'sd1a@example.com', '2026-11-04 06:40:05', NULL, 'Aisyah Putri', NULL, '2019-02-01', 'Jakarta', 1, 'Ruang 1, Sesi 1', NULL),
(55, 13, 1, 1, 'sd1b', 'sd1b', NULL, '$2y$10$Ck5HxR.dprhh9Oa6hEe1ieE9vIdQnB5CkK/vVY4H3971dZRBGELpy', 'sd1b@example.com', '2026-11-04 06:40:06', NULL, 'Bagas Pratama', NULL, '2019-06-01', 'Jakarta', 1, 'Ruang 2, Sesi 2', NULL),
(56, 14, 1, 1, 'sd2a', 'sd2a', NULL, '$2y$10$7WxJ3j4YQmJDlb/ZyzRM3OHsO.hu/5fR6ANh9.xIRgP/A/MMLetIu', 'sd2a@example.com', '2026-11-04 06:40:06', NULL, 'Citra Lestari', NULL, '2018-02-01', 'Jakarta', 1, 'Ruang 1, Sesi 1', NULL),
(57, 14, 1, 1, 'sd2b', 'sd2b', NULL, '$2y$10$KaVHsqRSkRoPGjTJQhNOjuMXQMQsAMu5HWT6mz2Fum0t7SNb9vp.e', 'sd2b@example.com', '2026-11-04 06:40:06', NULL, 'Dimas Saputra', NULL, '2018-06-01', 'Jakarta', 1, 'Ruang 2, Sesi 2', NULL),
(58, 15, 1, 1, 'sd3a', 'sd3a', NULL, '$2y$10$9aWv34OrnrHp92QAQz0rQez2bMT8GsDSS1TT.Zxyr4/fN1PQ1OSMO', 'sd3a@example.com', '2026-11-04 06:40:06', NULL, 'Elang Nugraha', NULL, '2017-02-01', 'Jakarta', 1, 'Ruang 1, Sesi 1', NULL),
(59, 15, 1, 1, 'sd3b', 'sd3b', NULL, '$2y$10$pi4xDgWYsXiq92L.B49VaOHyKUF69.Qws1LSTjRuR2KqZHwSwgP9y', 'sd3b@example.com', '2026-11-04 06:40:06', NULL, 'Fitri Handayani', NULL, '2017-06-01', 'Jakarta', 1, 'Ruang 2, Sesi 2', NULL),
(60, 16, 1, 1, 'sd4a', 'sd4a', NULL, '$2y$10$oBZ8vprBzF46gmV3oXFovuvlWF7KxQy6J6T2Ru.URYNj0.N6sO19W', 'sd4a@example.com', '2026-11-04 06:40:06', NULL, 'Galih Pratama', NULL, '2016-02-01', 'Jakarta', 1, 'Ruang 1, Sesi 1', NULL),
(61, 16, 1, 1, 'sd4b', 'sd4b', NULL, '$2y$10$0CgXyX9r0MXxLcqZlIw.seAZBWmWDLLblZvpVnXWQHOkUPtM4LUES', 'sd4b@example.com', '2026-11-04 06:40:06', NULL, 'Hana Safitri', NULL, '2016-06-01', 'Jakarta', 1, 'Ruang 2, Sesi 2', NULL),
(62, 17, 1, 1, 'sd5a', 'sd5a', NULL, '$2y$10$/5AKuaW4qzR4ajoRc/yV/uOiVeuacIP3tcZS0zeQTUnA7Vspbrij2', 'sd5a@example.com', '2026-11-04 06:40:06', NULL, 'Indra Kusuma', NULL, '2015-02-01', 'Jakarta', 1, 'Ruang 1, Sesi 1', NULL),
(63, 17, 1, 1, 'sd5b', 'sd5b', NULL, '$2y$10$49zXT5V.apmEGonughnUcOrOEmJodANoxaQ783aLfdVEyZ8BY9haW', 'sd5b@example.com', '2026-11-04 06:40:06', NULL, 'Jasmine Aulia', NULL, '2015-06-01', 'Jakarta', 1, 'Ruang 2, Sesi 2', NULL),
(64, 18, 1, 1, 'sd6a', 'sd6a', NULL, '$2y$10$gWQXmFUulvW6oWHuiNJfDevlxn9gjopcPyAD7G46GVyfhq1E.nEiq', 'sd6a@example.com', '2026-11-04 06:40:06', NULL, 'Kurniawan Adi', NULL, '2014-02-01', 'Jakarta', 1, 'Ruang 1, Sesi 1', NULL),
(65, 18, 1, 1, 'sd6b', 'sd6b', NULL, '$2y$10$MdZr1GDwz14Qsh.INOmj.u2dfurkqHMRnFx5VlEsn.CkeZCFRSHnK', 'sd6b@example.com', '2026-11-04 06:40:06', NULL, 'Laila Rahma', NULL, '2014-06-01', 'Jakarta', 1, 'Ruang 2, Sesi 2', NULL),
(66, 19, 1, 2, 'smp1a', 'smp1a', NULL, '$2y$10$nAEwz6xyDw1JqbFj.NSpyuafOe71KPoopeai2tUZAyvB30BLx.RY2', 'smp1a@example.com', '2026-11-04 06:40:06', NULL, 'Miko Saputra', NULL, '2013-02-01', 'Jakarta', 1, 'Ruang 1, Sesi 1', NULL),
(67, 19, 1, 2, 'smp1b', 'smp1b', NULL, '$2y$10$UsIonRSFKHtLP2gZzdPw.eCm6Nr9At3jTeCA2i7OF5y5llWtv6.yG', 'smp1b@example.com', '2026-11-04 06:40:06', NULL, 'Nabila Putri', NULL, '2013-06-01', 'Jakarta', 1, 'Ruang 2, Sesi 2', NULL),
(68, 20, 1, 2, 'smp2a', 'smp2a', NULL, '$2y$10$HxxuDnSPiNtV6d5WtEIxyecbp0.dimdbbvxVvRcIlYsSTw67RhOu6', 'smp2a@example.com', '2026-11-04 06:40:06', NULL, 'Oscar Pratama', NULL, '2012-02-01', 'Jakarta', 1, 'Ruang 1, Sesi 1', NULL),
(69, 20, 1, 2, 'smp2b', 'smp2b', NULL, '$2y$10$m/KdqrMfVHR1STXd.61b9.HswqytHQQEDcSzkRXnlIpSSMtNwR3d6', 'smp2b@example.com', '2026-11-04 06:40:06', NULL, 'Putri Ayu', NULL, '2012-06-01', 'Jakarta', 1, 'Ruang 2, Sesi 2', NULL),
(70, 21, 1, 2, 'smp3a', 'smp3a', NULL, '$2y$10$/kB7EDlUjDVkYc5grJiok.s7nW8LxcCPlzBIhKBFRZfqRUM.1uvbO', 'smp3a@example.com', '2026-11-04 06:40:06', NULL, 'Qori Ramadhan', NULL, '2011-02-01', 'Jakarta', 1, 'Ruang 1, Sesi 1', NULL),
(71, 21, 1, 2, 'smp3b', 'smp3b', NULL, '$2y$10$/M20z2lPPqkF3W5rOt17Me4Z9HlbDTZq7UVaD6pgqoiTa3Xy/77he', 'smp3b@example.com', '2026-11-04 06:40:06', NULL, 'Rani Safitri', NULL, '2011-06-01', 'Jakarta', 1, 'Ruang 2, Sesi 2', NULL),
(72, 22, 1, 3, 'sma1a', 'sma1a', NULL, '$2y$10$KXvt/4J7yzBsLGZ47V/g/Oo2HNEq5A4OUcy0mPAo.XjMoYhlxPM4S', 'sma1a@example.com', '2026-11-04 06:40:06', NULL, 'Satria Wijaya', NULL, '2010-02-01', 'Jakarta', 1, 'Ruang 1, Sesi 1', NULL),
(73, 22, 1, 3, 'sma1b', 'sma1b', NULL, '$2y$10$0u1pDAcNSTbdkdYBLgPtJ.PvjcZKLJHgO3c8MWaG/Rhzaqhk5ENme', 'sma1b@example.com', '2026-11-04 06:40:07', NULL, 'Tania Lestari', NULL, '2010-06-01', 'Jakarta', 1, 'Ruang 2, Sesi 2', NULL),
(74, 23, 1, 3, 'sma2a', 'sma2a', NULL, '$2y$10$yl./vUQhA5haqxalFuD0KeN/zg28vrNR6eqSu4sNGeSDdVVipV4P2', 'sma2a@example.com', '2026-11-04 06:40:07', NULL, 'Umar Hidayat', NULL, '2009-02-01', 'Jakarta', 1, 'Ruang 1, Sesi 1', NULL),
(75, 23, 1, 3, 'sma2b', 'sma2b', NULL, '$2y$10$CkldkuQWCcbKS8mk1hG/b.py6Rise0DS1Xoag/BysZKIfcGxj3Ugi', 'sma2b@example.com', '2026-11-04 06:40:07', NULL, 'Vina Oktaviani', NULL, '2009-06-01', 'Jakarta', 1, 'Ruang 2, Sesi 2', NULL),
(76, 24, 1, 3, 'sma3a', 'sma3a', NULL, '$2y$10$p.C2QOB/R0IvaY32suWh1ONVVi0b1TgzrwVqdy28.LxyHPQ99L.1e', 'sma3a@example.com', '2026-11-04 06:40:07', NULL, 'Wawan Setiawan', NULL, '2008-02-01', 'Jakarta', 1, 'Ruang 1, Sesi 1', NULL),
(77, 24, 1, 3, 'sma3b', 'sma3b', NULL, '$2y$10$v3KH0XFJi1Nh8ZpmdtcSmOXpiZDfTCOD8SQl9cbByjCTmLW5dsu.K', 'sma3b@example.com', '2026-11-04 06:40:07', NULL, 'Yuni Kartika', NULL, '2008-06-01', 'Jakarta', 1, 'Ruang 2, Sesi 2', NULL),
(78, 25, 1, 4, 'smk1a', 'smk1a', NULL, '$2y$10$kCjyzVQ4k174jHuCNpZLceh759hZMtHsOjbdw2VaUH.yBYDLClXmC', 'smk1a@example.com', '2026-11-04 06:40:07', NULL, 'Adrian Maulana', NULL, '2010-02-01', 'Jakarta', 1, 'Ruang 1, Sesi 1', NULL),
(79, 25, 1, 4, 'smk1b', 'smk1b', NULL, '$2y$10$ksBt3Iuo2AGQ5iTy9BODoOnuqKZA96iZDamOZmgh9VLoV4I3Aa8oa', 'smk1b@example.com', '2026-11-04 06:40:07', NULL, 'Bella Anjani', NULL, '2010-06-01', 'Jakarta', 1, 'Ruang 2, Sesi 2', NULL),
(80, 26, 1, 4, 'smk2a', 'smk2a', NULL, '$2y$10$x5xfI724dHXX1DeRX8LRJOYyMlCfawyyp4LHp6qM95K7efl0Q7QvO', 'smk2a@example.com', '2026-11-04 06:40:07', NULL, 'Candra Wijaya', NULL, '2009-02-01', 'Jakarta', 1, 'Ruang 1, Sesi 1', NULL),
(81, 26, 1, 4, 'smk2b', 'smk2b', NULL, '$2y$10$jnGyf5NzwnHoIiZ4RSOCWefB56ah3diB43dpzImBIr9K2AM0azcmW', 'smk2b@example.com', '2026-11-04 06:40:07', NULL, 'Dinda Permata', NULL, '2009-06-01', 'Jakarta', 1, 'Ruang 2, Sesi 2', NULL),
(82, 27, 1, 4, 'smk3a', 'smk3a', NULL, '$2y$10$E/NbJndOOg2cF1Ts0Sgw7eJ5dDlVzG/wRA0dkaLHCNUyTwJarjMqe', 'smk3a@example.com', '2026-11-04 06:40:07', NULL, 'Zaki Firmansyah', NULL, '2008-02-01', 'Jakarta', 1, 'Ruang 1, Sesi 1', NULL),
(83, 27, 1, 4, 'smk3b', 'smk3b', NULL, '$2y$10$QsAq8olBX7U2OaZlMdSvqevSapvZ7yokEIVIOWkjbiT3Q30LNbUgG', 'smk3b@example.com', '2026-11-04 06:40:07', NULL, 'Zahra Amelia', NULL, '2008-06-01', 'Jakarta', 1, 'Ruang 2, Sesi 2', NULL),
(84, 28, 1, 3, 'raka', 'raka', NULL, '$2y$12$bBckNeK3LjtZrYqGVX4qxuN10iMAJNKw4MnV3BmWjiiDmtmVRiSza', 'raka@example.com', '2026-11-04 06:40:07', NULL, 'Raka Pratama', NULL, '2008-04-01', 'Jakarta', 1, 'Ruang 1, Sesi 1', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `cbt_user_grup`
--

CREATE TABLE `cbt_user_grup` (
  `grup_id` bigint UNSIGNED NOT NULL,
  `grup_nama` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `grup_foundation_id` bigint UNSIGNED DEFAULT NULL,
  `grup_school_id` bigint UNSIGNED DEFAULT NULL,
  `grup_academic_year_id` bigint UNSIGNED DEFAULT NULL,
  `grup_jenjang` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'SMA',
  `grup_tingkat` tinyint UNSIGNED NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `cbt_user_grup`
--

INSERT INTO `cbt_user_grup` (`grup_id`, `grup_nama`, `grup_foundation_id`, `grup_school_id`, `grup_academic_year_id`, `grup_jenjang`, `grup_tingkat`) VALUES
(11, 'XI MM', 1, 3, 3, 'SMA', 2),
(12, 'XI TKJ', 1, 3, 3, 'SMA', 2),
(13, 'SD 1', 1, 1, 1, 'SD', 1),
(14, 'SD 2', 1, 1, 1, 'SD', 2),
(15, 'SD 3', 1, 1, 1, 'SD', 3),
(16, 'SD 4', 1, 1, 1, 'SD', 4),
(17, 'SD 5', 1, 1, 1, 'SD', 5),
(18, 'SD 6', 1, 1, 1, 'SD', 6),
(19, 'SMP 1', 1, 2, 2, 'SMP', 1),
(20, 'SMP 2', 1, 2, 2, 'SMP', 2),
(21, 'SMP 3', 1, 2, 2, 'SMP', 3),
(22, 'SMA 1', 1, 3, 3, 'SMA', 1),
(23, 'SMA 2', 1, 3, 3, 'SMA', 2),
(24, 'SMA 3', 1, 3, 3, 'SMA', 3),
(25, 'SMK 1', 1, 4, 4, 'SMK', 1),
(26, 'SMK 2', 1, 4, 4, 'SMK', 2),
(27, 'SMK 3', 1, 4, 4, 'SMK', 3),
(28, 'SMA 3 (Riwayat)', 1, 3, 3, 'SMA', 3);

-- --------------------------------------------------------

--
-- Struktur dari tabel `core_academic_years`
--

CREATE TABLE `core_academic_years` (
  `id` bigint UNSIGNED NOT NULL,
  `foundation_id` bigint UNSIGNED NOT NULL,
  `school_id` bigint UNSIGNED NOT NULL,
  `year_name` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `core_academic_years`
--

INSERT INTO `core_academic_years` (`id`, `foundation_id`, `school_id`, `year_name`, `start_date`, `end_date`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 1, '2026/2027', '2026-07-01', '2027-06-30', 1, '2026-11-04 06:40:04', '2026-11-04 06:40:04'),
(2, 1, 2, '2026/2027', '2026-07-01', '2027-06-30', 1, '2026-11-04 06:40:04', '2026-11-04 06:40:04'),
(3, 1, 3, '2026/2027', '2026-07-01', '2027-06-30', 1, '2026-11-04 06:40:04', '2026-11-04 06:40:04'),
(4, 1, 4, '2026/2027', '2026-07-01', '2027-06-30', 1, '2026-11-04 06:40:04', '2026-11-04 06:40:04');

-- --------------------------------------------------------

--
-- Struktur dari tabel `core_foundations`
--

CREATE TABLE `core_foundations` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `foundation_code` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `foundation_name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `legal_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `phone` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `core_foundations`
--

INSERT INTO `core_foundations` (`id`, `uuid`, `foundation_code`, `foundation_name`, `legal_name`, `address`, `phone`, `email`, `is_active`, `created_at`, `updated_at`) VALUES
(1, '45acc54a-7291-4f16-950b-6dc7ec3837cd', 'YPN', 'Yayasan Pendidikan Nusantara', 'Yayasan Pendidikan Nusantara', 'Jl. Pendidikan No. 1, Jakarta', '021-5550100', 'admin@ypn.example', 1, '2026-11-04 06:40:04', '2026-11-04 06:40:04');

-- --------------------------------------------------------

--
-- Struktur dari tabel `core_schools`
--

CREATE TABLE `core_schools` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `foundation_id` bigint UNSIGNED NOT NULL,
  `school_level_id` bigint UNSIGNED NOT NULL,
  `school_code` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `npsn` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `school_name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `phone` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `core_schools`
--

INSERT INTO `core_schools` (`id`, `uuid`, `foundation_id`, `school_level_id`, `school_code`, `npsn`, `school_name`, `address`, `phone`, `email`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'febe9840-af97-4a82-8bec-d91a53accc2d', 1, 1, 'SD-N1', '20101001', 'SD Nusantara 1', 'Jl. Pendidikan No. 1, Jakarta', '021-5550100', 'sd-n1@ypn.example', 1, '2026-11-04 06:40:04', '2026-11-04 06:40:04'),
(2, 'bcfe8c89-5ff9-445b-9de1-4b38f1f14b2e', 1, 2, 'SMP-N1', '20201001', 'SMP Nusantara 1', 'Jl. Pendidikan No. 1, Jakarta', '021-5550100', 'smp-n1@ypn.example', 1, '2026-11-04 06:40:04', '2026-11-04 06:40:04'),
(3, 'f27421d7-9aa4-405b-ab14-d3b62e01bb42', 1, 3, 'SMA-N2', '20301001', 'SMA Nusantara 2', 'Jl. Pendidikan No. 1, Jakarta', '021-5550100', 'sma-n2@ypn.example', 1, '2026-11-04 06:40:04', '2026-11-04 06:40:04'),
(4, 'ab6f16fd-310f-49a2-906c-1c53a7b5110c', 1, 4, 'SMK-N3', '20401001', 'SMK Nusantara 3', 'Jl. Pendidikan No. 1, Jakarta', '021-5550100', 'smk-n3@ypn.example', 1, '2026-11-04 06:40:04', '2026-11-04 06:40:04');

-- --------------------------------------------------------

--
-- Struktur dari tabel `core_school_levels`
--

CREATE TABLE `core_school_levels` (
  `id` bigint UNSIGNED NOT NULL,
  `level_code` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `level_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `education_category` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `core_school_levels`
--

INSERT INTO `core_school_levels` (`id`, `level_code`, `level_name`, `education_category`, `created_at`, `updated_at`) VALUES
(1, 'SD', 'Sekolah Dasar', 'Dasar', '2026-11-04 06:40:04', '2026-11-04 06:40:04'),
(2, 'SMP', 'Sekolah Menengah Pertama', 'Menengah', '2026-11-04 06:40:04', '2026-11-04 06:40:04'),
(3, 'SMA', 'Sekolah Menengah Atas', 'Menengah', '2026-11-04 06:40:04', '2026-11-04 06:40:04'),
(4, 'SMK', 'Sekolah Menengah Kejuruan', 'Menengah', '2026-11-04 06:40:04', '2026-11-04 06:40:04'),
(5, 'MI', 'Madrasah Ibtidaiyah', 'Dasar', '2026-11-04 06:40:04', '2026-11-04 06:40:04'),
(6, 'MTs', 'Madrasah Tsanawiyah', 'Menengah', '2026-11-04 06:40:04', '2026-11-04 06:40:04'),
(7, 'MA', 'Madrasah Aliyah', 'Menengah', '2026-11-04 06:40:04', '2026-11-04 06:40:04');

-- --------------------------------------------------------

--
-- Struktur dari tabel `core_semesters`
--

CREATE TABLE `core_semesters` (
  `id` bigint UNSIGNED NOT NULL,
  `school_id` bigint UNSIGNED NOT NULL,
  `academic_year_id` bigint UNSIGNED NOT NULL,
  `semester_number` tinyint UNSIGNED NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `core_semesters`
--

INSERT INTO `core_semesters` (`id`, `school_id`, `academic_year_id`, `semester_number`, `start_date`, `end_date`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, '2026-07-01', '2026-12-31', 1, '2026-11-04 06:40:04', '2026-11-04 06:40:04'),
(2, 1, 1, 2, '2027-01-01', '2027-06-30', 0, '2026-11-04 06:40:04', '2026-11-04 06:40:04'),
(3, 2, 2, 1, '2026-07-01', '2026-12-31', 1, '2026-11-04 06:40:04', '2026-11-04 06:40:04'),
(4, 2, 2, 2, '2027-01-01', '2027-06-30', 0, '2026-11-04 06:40:04', '2026-11-04 06:40:04'),
(5, 3, 3, 1, '2026-07-01', '2026-12-31', 1, '2026-11-04 06:40:04', '2026-11-04 06:40:04'),
(6, 3, 3, 2, '2027-01-01', '2027-06-30', 0, '2026-11-04 06:40:04', '2026-11-04 06:40:04'),
(7, 4, 4, 1, '2026-07-01', '2026-12-31', 1, '2026-11-04 06:40:04', '2026-11-04 06:40:04'),
(8, 4, 4, 2, '2027-01-01', '2027-06-30', 0, '2026-11-04 06:40:04', '2026-11-04 06:40:04');

-- --------------------------------------------------------

--
-- Struktur dari tabel `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `fin_cash_accounts`
--

CREATE TABLE `fin_cash_accounts` (
  `id` bigint UNSIGNED NOT NULL,
  `foundation_id` bigint UNSIGNED NOT NULL,
  `school_id` bigint UNSIGNED NOT NULL,
  `account_code` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `account_name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `account_type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'cash',
  `opening_balance` decimal(18,2) NOT NULL DEFAULT '0.00',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `fin_fee_categories`
--

CREATE TABLE `fin_fee_categories` (
  `id` bigint UNSIGNED NOT NULL,
  `foundation_id` bigint UNSIGNED NOT NULL,
  `school_id` bigint UNSIGNED NOT NULL,
  `category_code` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category_name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_recurring` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `fin_fee_categories`
--

INSERT INTO `fin_fee_categories` (`id`, `foundation_id`, `school_id`, `category_code`, `category_name`, `is_recurring`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'SPP', 'SPP Bulanan', 1, 1, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(2, 1, 1, 'GEDUNG', 'Uang Gedung', 0, 1, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(3, 1, 1, 'KEGIATAN', 'Kegiatan Sekolah', 0, 1, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(4, 1, 2, 'SPP', 'SPP Bulanan', 1, 1, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(5, 1, 2, 'GEDUNG', 'Uang Gedung', 0, 1, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(6, 1, 2, 'KEGIATAN', 'Kegiatan Sekolah', 0, 1, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(7, 1, 3, 'SPP', 'SPP Bulanan', 1, 1, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(8, 1, 3, 'GEDUNG', 'Uang Gedung', 0, 1, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(9, 1, 3, 'KEGIATAN', 'Kegiatan Sekolah', 0, 1, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(10, 1, 4, 'SPP', 'SPP Bulanan', 1, 1, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(11, 1, 4, 'GEDUNG', 'Uang Gedung', 0, 1, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(12, 1, 4, 'KEGIATAN', 'Kegiatan Sekolah', 0, 1, '2026-11-06 00:13:01', '2026-11-06 00:13:01');

-- --------------------------------------------------------

--
-- Struktur dari tabel `fin_fee_structures`
--

CREATE TABLE `fin_fee_structures` (
  `id` bigint UNSIGNED NOT NULL,
  `foundation_id` bigint UNSIGNED NOT NULL,
  `school_id` bigint UNSIGNED NOT NULL,
  `academic_year_id` bigint UNSIGNED DEFAULT NULL,
  `fee_category_id` bigint UNSIGNED NOT NULL,
  `grade_code` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount` decimal(18,2) NOT NULL,
  `effective_from` date DEFAULT NULL,
  `effective_until` date DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `fin_fee_structures`
--

INSERT INTO `fin_fee_structures` (`id`, `foundation_id`, `school_id`, `academic_year_id`, `fee_category_id`, `grade_code`, `amount`, `effective_from`, `effective_until`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 1, NULL, 1, NULL, 250000.00, NULL, NULL, 1, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(2, 1, 1, NULL, 2, NULL, 1500000.00, NULL, NULL, 1, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(3, 1, 1, NULL, 3, NULL, 350000.00, NULL, NULL, 1, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(4, 1, 2, NULL, 4, NULL, 250000.00, NULL, NULL, 1, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(5, 1, 2, NULL, 5, NULL, 1500000.00, NULL, NULL, 1, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(6, 1, 2, NULL, 6, NULL, 350000.00, NULL, NULL, 1, '2026-11-06 00:13:00', '2026-11-06 00:13:00'),
(7, 1, 3, NULL, 7, NULL, 250000.00, NULL, NULL, 1, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(8, 1, 3, NULL, 8, NULL, 1500000.00, NULL, NULL, 1, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(9, 1, 3, NULL, 9, NULL, 350000.00, NULL, NULL, 1, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(10, 1, 4, NULL, 10, NULL, 250000.00, NULL, NULL, 1, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(11, 1, 4, NULL, 11, NULL, 1500000.00, NULL, NULL, 1, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(12, 1, 4, NULL, 12, NULL, 350000.00, NULL, NULL, 1, '2026-11-06 00:13:01', '2026-11-06 00:13:01');

-- --------------------------------------------------------

--
-- Struktur dari tabel `fin_payments`
--

CREATE TABLE `fin_payments` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `foundation_id` bigint UNSIGNED NOT NULL,
  `school_id` bigint UNSIGNED NOT NULL,
  `student_id` bigint UNSIGNED NOT NULL,
  `bill_id` bigint UNSIGNED DEFAULT NULL,
  `guardian_id` bigint UNSIGNED DEFAULT NULL,
  `payment_method_id` bigint UNSIGNED DEFAULT NULL,
  `recorded_by` bigint UNSIGNED DEFAULT NULL,
  `verified_by` bigint UNSIGNED DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `payment_number` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(18,2) NOT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `provider_reference` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `idempotency_key` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `proof_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `rejection_reason` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `fin_payments`
--

INSERT INTO `fin_payments` (`id`, `uuid`, `foundation_id`, `school_id`, `student_id`, `bill_id`, `guardian_id`, `payment_method_id`, `recorded_by`, `verified_by`, `verified_at`, `payment_number`, `amount`, `status`, `provider_reference`, `idempotency_key`, `proof_path`, `paid_at`, `notes`, `rejection_reason`, `created_at`, `updated_at`) VALUES
(1, '28ff0bb5-4105-44c3-9034-cdf585c8506c', 1, 3, 41, 1, 1, NULL, NULL, NULL, NULL, 'PAY-SMA-N2-202611-BOM3ER', 100000.00, 'pending', NULL, NULL, 'bukti-spp/sample-bukti.svg', NULL, 'Pembayaran sample', NULL, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(2, '46162a2d-bde0-42d4-b240-1718d8577207', 1, 3, 42, 2, NULL, NULL, NULL, NULL, NULL, 'PAY-SMA-N2-202611-PRUYGL', 250000.00, 'rejected', NULL, NULL, NULL, NULL, 'Pembayaran sample', 'Nominal belum sesuai dengan tagihan.', '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(3, 'b913f27e-c0e8-4cc9-9b15-2360e616385b', 1, 3, 49, 4, NULL, NULL, NULL, NULL, NULL, 'PAY-SMA-N2-202611-LRWYZU', 100000.00, 'pending', NULL, NULL, 'bukti-spp/sample-bukti.svg', NULL, 'Pembayaran sample', NULL, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(4, '6208d2a5-f865-4124-8bc0-3ba6bd3ab268', 1, 3, 50, 5, NULL, NULL, NULL, NULL, NULL, 'PAY-SMA-N2-202611-APWRXY', 250000.00, 'rejected', NULL, NULL, NULL, NULL, 'Pembayaran sample', 'Nominal belum sesuai dengan tagihan.', '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(5, '4629366b-c06d-44b9-8b39-94dd52b4f0d1', 1, 3, 52, 7, NULL, NULL, 16, 16, '2026-10-31 23:13:01', 'PAY-SMA-N2-202611-CJWT0B', 250000.00, 'verified', NULL, NULL, NULL, '2026-10-31 23:13:01', 'Pembayaran sample', NULL, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(6, 'b3ad2962-9d45-4e2f-994c-161eb181626b', 1, 3, 53, 8, NULL, NULL, NULL, NULL, NULL, 'PAY-SMA-N2-202611-TWNLWL', 100000.00, 'pending', NULL, NULL, 'bukti-spp/sample-bukti.svg', NULL, 'Pembayaran sample', NULL, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(7, '973e1e48-4071-40c0-aa10-75625578a5b4', 1, 1, 54, 9, NULL, NULL, NULL, NULL, NULL, 'PAY-SD-N1-202611-PWICHI', 250000.00, 'rejected', NULL, NULL, NULL, NULL, 'Pembayaran sample', 'Nominal belum sesuai dengan tagihan.', '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(8, '49bfc700-bfbc-44d2-925a-65490973440c', 1, 1, 56, 11, NULL, NULL, 16, 16, '2026-10-31 23:13:01', 'PAY-SD-N1-202611-JRWIPN', 250000.00, 'verified', NULL, NULL, NULL, '2026-10-31 23:13:01', 'Pembayaran sample', NULL, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(9, 'cc21acb6-aa42-470f-8dca-02a76203121d', 1, 1, 57, 12, NULL, NULL, NULL, NULL, NULL, 'PAY-SD-N1-202611-3GOH2H', 100000.00, 'pending', NULL, NULL, 'bukti-spp/sample-bukti.svg', NULL, 'Pembayaran sample', NULL, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(10, '6f4f0100-5b6d-437d-9a59-3387187ab573', 1, 1, 58, 13, NULL, NULL, NULL, NULL, NULL, 'PAY-SD-N1-202611-O7KEC7', 250000.00, 'rejected', NULL, NULL, NULL, NULL, 'Pembayaran sample', 'Nominal belum sesuai dengan tagihan.', '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(11, '2024388d-243e-461a-90a7-06af0d2fcd0e', 1, 1, 60, 15, NULL, NULL, 16, 16, '2026-10-31 23:13:01', 'PAY-SD-N1-202611-UT8QGV', 250000.00, 'verified', NULL, NULL, NULL, '2026-10-31 23:13:01', 'Pembayaran sample', NULL, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(12, 'c7b86fef-1d59-4ab7-ac19-b96ea787ab25', 1, 1, 61, 16, NULL, NULL, NULL, NULL, NULL, 'PAY-SD-N1-202611-Y2N6AM', 100000.00, 'pending', NULL, NULL, 'bukti-spp/sample-bukti.svg', NULL, 'Pembayaran sample', NULL, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(13, '81cf89dc-e8d0-4f43-8123-2509990071a1', 1, 1, 62, 17, NULL, NULL, NULL, NULL, NULL, 'PAY-SD-N1-202611-G4GVRD', 250000.00, 'rejected', NULL, NULL, NULL, NULL, 'Pembayaran sample', 'Nominal belum sesuai dengan tagihan.', '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(14, '214f5468-4c84-49e4-978a-c77dbebc7019', 1, 1, 64, 19, NULL, NULL, 16, 16, '2026-10-31 23:13:01', 'PAY-SD-N1-202611-U4KYZQ', 250000.00, 'verified', NULL, NULL, NULL, '2026-10-31 23:13:01', 'Pembayaran sample', NULL, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(15, '395c2a8a-5c72-468d-932f-75ef4244a5ec', 1, 1, 65, 20, NULL, NULL, NULL, NULL, NULL, 'PAY-SD-N1-202611-TDMTFC', 100000.00, 'pending', NULL, NULL, 'bukti-spp/sample-bukti.svg', NULL, 'Pembayaran sample', NULL, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(16, '8187b6c5-df96-494f-a78a-16b17553d476', 1, 2, 66, 21, NULL, NULL, NULL, NULL, NULL, 'PAY-SMP-N1-202611-9HYAE8', 250000.00, 'rejected', NULL, NULL, NULL, NULL, 'Pembayaran sample', 'Nominal belum sesuai dengan tagihan.', '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(17, 'bc672b6b-79c9-4160-ae89-2978a3eef6ce', 1, 2, 68, 23, NULL, NULL, 16, 16, '2026-10-31 23:13:01', 'PAY-SMP-N1-202611-RH6NZI', 250000.00, 'verified', NULL, NULL, NULL, '2026-10-31 23:13:01', 'Pembayaran sample', NULL, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(18, 'c7581c67-282a-44ad-bb02-0d1d0a3d410b', 1, 2, 69, 24, NULL, NULL, NULL, NULL, NULL, 'PAY-SMP-N1-202611-BUUGKM', 100000.00, 'pending', NULL, NULL, 'bukti-spp/sample-bukti.svg', NULL, 'Pembayaran sample', NULL, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(19, 'db1de46f-490c-4b89-b7bd-b7b40f7b3dc2', 1, 2, 70, 25, NULL, NULL, NULL, NULL, NULL, 'PAY-SMP-N1-202611-K4XAET', 250000.00, 'rejected', NULL, NULL, NULL, NULL, 'Pembayaran sample', 'Nominal belum sesuai dengan tagihan.', '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(20, '75567f41-4ff7-48b9-9a34-ab7455e81beb', 1, 3, 72, 27, NULL, NULL, 16, 16, '2026-10-31 23:13:01', 'PAY-SMA-N2-202611-7PELYT', 250000.00, 'verified', NULL, NULL, NULL, '2026-10-31 23:13:01', 'Pembayaran sample', NULL, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(21, 'fe1dbb09-00a6-4ff2-9c2f-6c5625b25d4f', 1, 3, 73, 28, NULL, NULL, NULL, NULL, NULL, 'PAY-SMA-N2-202611-UUXFLV', 100000.00, 'pending', NULL, NULL, 'bukti-spp/sample-bukti.svg', NULL, 'Pembayaran sample', NULL, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(22, 'b4be024e-a7eb-4092-a271-497c8c99b5b9', 1, 3, 74, 29, NULL, NULL, NULL, NULL, NULL, 'PAY-SMA-N2-202611-GEFQZT', 250000.00, 'rejected', NULL, NULL, NULL, NULL, 'Pembayaran sample', 'Nominal belum sesuai dengan tagihan.', '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(23, 'ef60eba3-9293-45b8-b061-375659de7e73', 1, 3, 76, 31, NULL, NULL, 16, 16, '2026-10-31 23:13:01', 'PAY-SMA-N2-202611-25C7PP', 250000.00, 'verified', NULL, NULL, NULL, '2026-10-31 23:13:01', 'Pembayaran sample', NULL, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(24, '1a790b7a-6b73-4301-aa36-351b67d53e2b', 1, 3, 77, 32, NULL, NULL, NULL, NULL, NULL, 'PAY-SMA-N2-202611-H2OVVK', 100000.00, 'pending', NULL, NULL, 'bukti-spp/sample-bukti.svg', NULL, 'Pembayaran sample', NULL, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(25, '4161f33d-a4c3-4f14-b937-02c3752f25cc', 1, 4, 78, 33, NULL, NULL, NULL, NULL, NULL, 'PAY-SMK-N3-202611-QFVFN4', 250000.00, 'rejected', NULL, NULL, NULL, NULL, 'Pembayaran sample', 'Nominal belum sesuai dengan tagihan.', '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(26, '6380def6-e6ce-45b8-aa18-46eb0ee480b7', 1, 4, 80, 35, NULL, NULL, 16, 16, '2026-10-31 23:13:01', 'PAY-SMK-N3-202611-J90VHC', 250000.00, 'verified', NULL, NULL, NULL, '2026-10-31 23:13:01', 'Pembayaran sample', NULL, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(27, 'c2512963-28fd-429b-bb1c-6e6439a17b43', 1, 4, 81, 36, NULL, NULL, NULL, NULL, NULL, 'PAY-SMK-N3-202611-BFDVQ1', 100000.00, 'pending', NULL, NULL, 'bukti-spp/sample-bukti.svg', NULL, 'Pembayaran sample', NULL, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(28, '1625a912-1a79-4d74-9bc7-227463255f61', 1, 4, 82, 37, NULL, NULL, NULL, NULL, NULL, 'PAY-SMK-N3-202611-GEMFHO', 250000.00, 'rejected', NULL, NULL, NULL, NULL, 'Pembayaran sample', 'Nominal belum sesuai dengan tagihan.', '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(29, '78bff2e1-6fc5-4384-a6a3-cb1ea8480ef4', 1, 3, 84, 39, 1, NULL, 16, 16, '2026-10-31 23:13:01', 'PAY-SMA-N2-202611-2MOP48', 250000.00, 'verified', NULL, NULL, NULL, '2026-10-31 23:13:01', 'Pembayaran sample', NULL, '2026-11-06 00:13:01', '2026-11-06 00:13:01');

-- --------------------------------------------------------

--
-- Struktur dari tabel `fin_payment_allocations`
--

CREATE TABLE `fin_payment_allocations` (
  `id` bigint UNSIGNED NOT NULL,
  `payment_id` bigint UNSIGNED NOT NULL,
  `bill_id` bigint UNSIGNED NOT NULL,
  `allocated_amount` decimal(18,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `fin_payment_allocations`
--

INSERT INTO `fin_payment_allocations` (`id`, `payment_id`, `bill_id`, `allocated_amount`, `created_at`, `updated_at`) VALUES
(1, 5, 7, 250000.00, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(2, 8, 11, 250000.00, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(3, 11, 15, 250000.00, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(4, 14, 19, 250000.00, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(5, 17, 23, 250000.00, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(6, 20, 27, 250000.00, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(7, 23, 31, 250000.00, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(8, 26, 35, 250000.00, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(9, 29, 39, 250000.00, '2026-11-06 00:13:01', '2026-11-06 00:13:01');

-- --------------------------------------------------------

--
-- Struktur dari tabel `fin_payment_methods`
--

CREATE TABLE `fin_payment_methods` (
  `id` bigint UNSIGNED NOT NULL,
  `school_id` bigint UNSIGNED NOT NULL,
  `method_code` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `method_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `bank_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `account_number` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `account_holder` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `show_to_guardian` tinyint(1) NOT NULL DEFAULT '1',
  `sort_order` int UNSIGNED NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `fin_payment_methods`
--

INSERT INTO `fin_payment_methods` (`id`, `school_id`, `method_code`, `method_name`, `bank_name`, `account_number`, `account_holder`, `is_active`, `show_to_guardian`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 1, 'BCA', 'Bank BCA', 'Bank BCA', '1234567890', 'Yayasan Pendidikan Nusantara', 1, 1, 1, '2026-11-05 23:40:23', '2026-11-05 23:40:23'),
(2, 1, 'MANDIRI', 'Bank Mandiri', 'Bank Mandiri', '0987654321', 'Yayasan Pendidikan Nusantara', 1, 1, 2, '2026-11-05 23:40:23', '2026-11-05 23:40:23'),
(3, 2, 'BCA', 'Bank BCA', 'Bank BCA', '1234567890', 'Yayasan Pendidikan Nusantara', 1, 1, 1, '2026-11-05 23:40:23', '2026-11-05 23:40:23'),
(4, 2, 'MANDIRI', 'Bank Mandiri', 'Bank Mandiri', '0987654321', 'Yayasan Pendidikan Nusantara', 1, 1, 2, '2026-11-05 23:40:23', '2026-11-05 23:40:23'),
(5, 3, 'BCA', 'Bank BCA', 'Bank BCA', '1234567890', 'Yayasan Pendidikan Nusantara', 1, 1, 1, '2026-11-05 23:40:23', '2026-11-05 23:40:23'),
(6, 3, 'MANDIRI', 'Bank Mandiri', 'Bank Mandiri', '0987654321', 'Yayasan Pendidikan Nusantara', 1, 1, 2, '2026-11-05 23:40:23', '2026-11-05 23:40:23'),
(7, 4, 'BCA', 'Bank BCA', 'Bank BCA', '1234567890', 'Yayasan Pendidikan Nusantara', 1, 1, 1, '2026-11-05 23:40:23', '2026-11-05 23:40:23'),
(8, 4, 'MANDIRI', 'Bank Mandiri', 'Bank Mandiri', '0987654321', 'Yayasan Pendidikan Nusantara', 1, 1, 2, '2026-11-05 23:40:23', '2026-11-05 23:40:23');

-- --------------------------------------------------------

--
-- Struktur dari tabel `fin_student_bills`
--

CREATE TABLE `fin_student_bills` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `foundation_id` bigint UNSIGNED NOT NULL,
  `school_id` bigint UNSIGNED NOT NULL,
  `academic_year_id` bigint UNSIGNED DEFAULT NULL,
  `student_id` bigint UNSIGNED NOT NULL,
  `bill_number` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `issue_date` date NOT NULL,
  `due_date` date DEFAULT NULL,
  `total_amount` decimal(18,2) NOT NULL DEFAULT '0.00',
  `paid_amount` decimal(18,2) NOT NULL DEFAULT '0.00',
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `fin_student_bills`
--

INSERT INTO `fin_student_bills` (`id`, `uuid`, `foundation_id`, `school_id`, `academic_year_id`, `student_id`, `bill_number`, `issue_date`, `due_date`, `total_amount`, `paid_amount`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'be0f28fc-54e3-4bfc-87ac-3109ed7e1b9d', 1, 3, 3, 41, 'INV-SMA-N2-0041', '2026-10-26', '2026-11-25', 250000.00, 0.00, 'draft', 'Tagihan SPP sample', 16, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(2, '7da6723f-4daa-4e76-8e34-352d74ec43d4', 1, 3, 3, 42, 'INV-SMA-N2-0042', '2026-10-26', '2026-11-25', 250000.00, 0.00, 'draft', 'Tagihan SPP sample', 16, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(3, 'f84c102b-d4fe-492c-b7a1-a1daa0c2c73d', 1, 3, 3, 43, 'INV-SMA-N2-0043', '2026-10-26', '2026-11-25', 250000.00, 0.00, 'draft', 'Tagihan SPP sample', 16, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(4, '71d606ae-b92a-497f-80cf-4d469e0255c6', 1, 3, 3, 49, 'INV-SMA-N2-0049', '2026-10-26', '2026-11-25', 250000.00, 0.00, 'draft', 'Tagihan SPP sample', 16, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(5, '6c291f36-b6dc-4d86-b1e4-611b7d26a05b', 1, 3, 3, 50, 'INV-SMA-N2-0050', '2026-10-26', '2026-11-25', 250000.00, 0.00, 'draft', 'Tagihan SPP sample', 16, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(6, '2bc12607-8d16-49b8-818e-498ec33a9179', 1, 3, 3, 51, 'INV-SMA-N2-0051', '2026-10-26', '2026-11-25', 250000.00, 0.00, 'draft', 'Tagihan SPP sample', 16, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(7, '6202ac03-a997-4fd7-83eb-459555b3f53a', 1, 3, 3, 52, 'INV-SMA-N2-0052', '2026-10-26', '2026-11-25', 250000.00, 250000.00, 'paid', 'Tagihan SPP sample', 16, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(8, 'a01b72f6-6ac4-47b9-82de-dac37283a3aa', 1, 3, 3, 53, 'INV-SMA-N2-0053', '2026-10-26', '2026-11-25', 250000.00, 0.00, 'draft', 'Tagihan SPP sample', 16, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(9, 'cc9812bd-ce2c-4269-90e2-f65faa2592e5', 1, 1, 1, 54, 'INV-SD-N1-0054', '2026-10-26', '2026-11-25', 250000.00, 0.00, 'draft', 'Tagihan SPP sample', 16, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(10, '4af2d3f1-f44a-4f6a-9e39-fbc64a8afe56', 1, 1, 1, 55, 'INV-SD-N1-0055', '2026-10-26', '2026-11-25', 250000.00, 0.00, 'draft', 'Tagihan SPP sample', 16, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(11, '02139db8-67bb-4d7b-b745-d3365f89e11d', 1, 1, 1, 56, 'INV-SD-N1-0056', '2026-10-26', '2026-11-25', 250000.00, 250000.00, 'paid', 'Tagihan SPP sample', 16, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(12, 'a6a954d8-ea21-45fc-b12e-af5bd228e858', 1, 1, 1, 57, 'INV-SD-N1-0057', '2026-10-26', '2026-11-25', 250000.00, 0.00, 'draft', 'Tagihan SPP sample', 16, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(13, 'b8f6ec0b-d750-4e30-96e9-0ffb034de438', 1, 1, 1, 58, 'INV-SD-N1-0058', '2026-10-26', '2026-11-25', 250000.00, 0.00, 'draft', 'Tagihan SPP sample', 16, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(14, 'f362b504-e91f-4f63-8966-1a944b6522bc', 1, 1, 1, 59, 'INV-SD-N1-0059', '2026-10-26', '2026-11-25', 250000.00, 0.00, 'draft', 'Tagihan SPP sample', 16, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(15, '511885d5-1609-4030-bd1a-c3c2cfa8e60a', 1, 1, 1, 60, 'INV-SD-N1-0060', '2026-10-26', '2026-11-25', 250000.00, 250000.00, 'paid', 'Tagihan SPP sample', 16, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(16, 'c41e3405-ddf2-42f8-90eb-18d3be85dfef', 1, 1, 1, 61, 'INV-SD-N1-0061', '2026-10-26', '2026-11-25', 250000.00, 0.00, 'draft', 'Tagihan SPP sample', 16, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(17, '339d8bf1-8ef5-4137-8ac8-fa7fe54e8a81', 1, 1, 1, 62, 'INV-SD-N1-0062', '2026-10-26', '2026-11-25', 250000.00, 0.00, 'draft', 'Tagihan SPP sample', 16, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(18, '96d8d68a-f49e-4f93-adc5-8518c303f902', 1, 1, 1, 63, 'INV-SD-N1-0063', '2026-10-26', '2026-11-25', 250000.00, 0.00, 'draft', 'Tagihan SPP sample', 16, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(19, 'a57a5c59-553e-4951-a72c-57d282214cc0', 1, 1, 1, 64, 'INV-SD-N1-0064', '2026-10-26', '2026-11-25', 250000.00, 250000.00, 'paid', 'Tagihan SPP sample', 16, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(20, 'd7d7356e-05a2-46c8-b680-7dc1dc687bf1', 1, 1, 1, 65, 'INV-SD-N1-0065', '2026-10-26', '2026-11-25', 250000.00, 0.00, 'draft', 'Tagihan SPP sample', 16, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(21, 'aa28c6a1-9e33-480a-ac1d-3bcc999385e0', 1, 2, 2, 66, 'INV-SMP-N1-0066', '2026-10-26', '2026-11-25', 250000.00, 0.00, 'draft', 'Tagihan SPP sample', 16, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(22, '61adba66-a5ad-48f6-ab63-e49a550b09a3', 1, 2, 2, 67, 'INV-SMP-N1-0067', '2026-10-26', '2026-11-25', 250000.00, 0.00, 'draft', 'Tagihan SPP sample', 16, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(23, '20178158-effc-4193-a513-9632614d856f', 1, 2, 2, 68, 'INV-SMP-N1-0068', '2026-10-26', '2026-11-25', 250000.00, 250000.00, 'paid', 'Tagihan SPP sample', 16, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(24, '30c6420e-2f59-4910-81f4-1ae8eda5c3fc', 1, 2, 2, 69, 'INV-SMP-N1-0069', '2026-10-26', '2026-11-25', 250000.00, 0.00, 'draft', 'Tagihan SPP sample', 16, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(25, 'd92a18ac-84f7-43c6-96b2-766b71b003ee', 1, 2, 2, 70, 'INV-SMP-N1-0070', '2026-10-26', '2026-11-25', 250000.00, 0.00, 'draft', 'Tagihan SPP sample', 16, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(26, '79eccfb0-a450-46e1-a314-a3b575b4547d', 1, 2, 2, 71, 'INV-SMP-N1-0071', '2026-10-26', '2026-11-25', 250000.00, 0.00, 'draft', 'Tagihan SPP sample', 16, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(27, '1d29d048-dfba-4e66-ab66-52ebc784a4bd', 1, 3, 3, 72, 'INV-SMA-N2-0072', '2026-10-26', '2026-11-25', 250000.00, 250000.00, 'paid', 'Tagihan SPP sample', 16, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(28, 'f44ae13e-b0e4-4915-8032-279d4850592a', 1, 3, 3, 73, 'INV-SMA-N2-0073', '2026-10-26', '2026-11-25', 250000.00, 0.00, 'draft', 'Tagihan SPP sample', 16, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(29, '8153d769-8329-467f-a507-d4debc8bcfe6', 1, 3, 3, 74, 'INV-SMA-N2-0074', '2026-10-26', '2026-11-25', 250000.00, 0.00, 'draft', 'Tagihan SPP sample', 16, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(30, 'a9324f17-4d3f-41f9-b2b3-bb669c77e41e', 1, 3, 3, 75, 'INV-SMA-N2-0075', '2026-10-26', '2026-11-25', 250000.00, 0.00, 'draft', 'Tagihan SPP sample', 16, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(31, '931a7483-83ca-46d1-b272-8b25acedc7bc', 1, 3, 3, 76, 'INV-SMA-N2-0076', '2026-10-26', '2026-11-25', 250000.00, 250000.00, 'paid', 'Tagihan SPP sample', 16, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(32, 'efd4fea4-71e9-4d2e-ab95-e7740688f4b2', 1, 3, 3, 77, 'INV-SMA-N2-0077', '2026-10-26', '2026-11-25', 250000.00, 0.00, 'draft', 'Tagihan SPP sample', 16, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(33, 'cf186a4b-a075-434d-b081-cf16ad73fb15', 1, 4, 4, 78, 'INV-SMK-N3-0078', '2026-10-26', '2026-11-25', 250000.00, 0.00, 'draft', 'Tagihan SPP sample', 16, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(34, '7920311f-5f09-4b17-b43c-75243bbc300c', 1, 4, 4, 79, 'INV-SMK-N3-0079', '2026-10-26', '2026-11-25', 250000.00, 0.00, 'draft', 'Tagihan SPP sample', 16, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(35, 'db5f09e8-92c7-4cb2-aa9b-5337ed5ddaa4', 1, 4, 4, 80, 'INV-SMK-N3-0080', '2026-10-26', '2026-11-25', 250000.00, 250000.00, 'paid', 'Tagihan SPP sample', 16, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(36, '8cced6ae-17e2-4999-ab4e-027061188fba', 1, 4, 4, 81, 'INV-SMK-N3-0081', '2026-10-26', '2026-11-25', 250000.00, 0.00, 'draft', 'Tagihan SPP sample', 16, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(37, '824ecd96-ce06-4aae-b6fc-21d118d3ec92', 1, 4, 4, 82, 'INV-SMK-N3-0082', '2026-10-26', '2026-11-25', 250000.00, 0.00, 'draft', 'Tagihan SPP sample', 16, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(38, 'dafbe27c-7e77-4896-82a1-20734888367f', 1, 4, 4, 83, 'INV-SMK-N3-0083', '2026-10-26', '2026-11-25', 250000.00, 0.00, 'draft', 'Tagihan SPP sample', 16, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(39, '76af6a94-e255-48fe-b6b7-008de7e0f9a7', 1, 3, 3, 84, 'INV-SMA-N2-0084', '2026-10-26', '2026-11-25', 250000.00, 250000.00, 'paid', 'Tagihan SPP sample', 16, '2026-11-06 00:13:01', '2026-11-06 00:13:01');

-- --------------------------------------------------------

--
-- Struktur dari tabel `fin_student_bill_items`
--

CREATE TABLE `fin_student_bill_items` (
  `id` bigint UNSIGNED NOT NULL,
  `bill_id` bigint UNSIGNED NOT NULL,
  `fee_category_id` bigint UNSIGNED DEFAULT NULL,
  `description` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` decimal(12,2) NOT NULL DEFAULT '1.00',
  `unit_amount` decimal(18,2) NOT NULL,
  `line_total` decimal(18,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `fin_student_bill_items`
--

INSERT INTO `fin_student_bill_items` (`id`, `bill_id`, `fee_category_id`, `description`, `quantity`, `unit_amount`, `line_total`, `created_at`, `updated_at`) VALUES
(1, 1, 7, 'SPP Bulanan', 1.00, 250000.00, 250000.00, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(2, 2, 7, 'SPP Bulanan', 1.00, 250000.00, 250000.00, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(3, 3, 7, 'SPP Bulanan', 1.00, 250000.00, 250000.00, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(4, 4, 7, 'SPP Bulanan', 1.00, 250000.00, 250000.00, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(5, 5, 7, 'SPP Bulanan', 1.00, 250000.00, 250000.00, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(6, 6, 7, 'SPP Bulanan', 1.00, 250000.00, 250000.00, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(7, 7, 7, 'SPP Bulanan', 1.00, 250000.00, 250000.00, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(8, 8, 7, 'SPP Bulanan', 1.00, 250000.00, 250000.00, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(9, 9, 1, 'SPP Bulanan', 1.00, 250000.00, 250000.00, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(10, 10, 1, 'SPP Bulanan', 1.00, 250000.00, 250000.00, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(11, 11, 1, 'SPP Bulanan', 1.00, 250000.00, 250000.00, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(12, 12, 1, 'SPP Bulanan', 1.00, 250000.00, 250000.00, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(13, 13, 1, 'SPP Bulanan', 1.00, 250000.00, 250000.00, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(14, 14, 1, 'SPP Bulanan', 1.00, 250000.00, 250000.00, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(15, 15, 1, 'SPP Bulanan', 1.00, 250000.00, 250000.00, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(16, 16, 1, 'SPP Bulanan', 1.00, 250000.00, 250000.00, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(17, 17, 1, 'SPP Bulanan', 1.00, 250000.00, 250000.00, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(18, 18, 1, 'SPP Bulanan', 1.00, 250000.00, 250000.00, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(19, 19, 1, 'SPP Bulanan', 1.00, 250000.00, 250000.00, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(20, 20, 1, 'SPP Bulanan', 1.00, 250000.00, 250000.00, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(21, 21, 4, 'SPP Bulanan', 1.00, 250000.00, 250000.00, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(22, 22, 4, 'SPP Bulanan', 1.00, 250000.00, 250000.00, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(23, 23, 4, 'SPP Bulanan', 1.00, 250000.00, 250000.00, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(24, 24, 4, 'SPP Bulanan', 1.00, 250000.00, 250000.00, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(25, 25, 4, 'SPP Bulanan', 1.00, 250000.00, 250000.00, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(26, 26, 4, 'SPP Bulanan', 1.00, 250000.00, 250000.00, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(27, 27, 7, 'SPP Bulanan', 1.00, 250000.00, 250000.00, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(28, 28, 7, 'SPP Bulanan', 1.00, 250000.00, 250000.00, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(29, 29, 7, 'SPP Bulanan', 1.00, 250000.00, 250000.00, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(30, 30, 7, 'SPP Bulanan', 1.00, 250000.00, 250000.00, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(31, 31, 7, 'SPP Bulanan', 1.00, 250000.00, 250000.00, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(32, 32, 7, 'SPP Bulanan', 1.00, 250000.00, 250000.00, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(33, 33, 10, 'SPP Bulanan', 1.00, 250000.00, 250000.00, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(34, 34, 10, 'SPP Bulanan', 1.00, 250000.00, 250000.00, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(35, 35, 10, 'SPP Bulanan', 1.00, 250000.00, 250000.00, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(36, 36, 10, 'SPP Bulanan', 1.00, 250000.00, 250000.00, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(37, 37, 10, 'SPP Bulanan', 1.00, 250000.00, 250000.00, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(38, 38, 10, 'SPP Bulanan', 1.00, 250000.00, 250000.00, '2026-11-06 00:13:01', '2026-11-06 00:13:01'),
(39, 39, 7, 'SPP Bulanan', 1.00, 250000.00, 250000.00, '2026-11-06 00:13:01', '2026-11-06 00:13:01');

-- --------------------------------------------------------

--
-- Struktur dari tabel `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2019_08_19_000000_create_failed_jobs_table', 1),
(2, '2019_12_14_000001_create_personal_access_tokens_table', 1),
(3, '2024_01_01_000001_create_admin_tables', 1),
(4, '2024_01_01_000002_create_question_bank_tables', 1),
(5, '2024_01_01_000003_create_exam_tables', 1),
(6, '2024_01_01_000004_add_remember_token_to_auth_tables', 1),
(7, '2024_01_01_000005_add_creation_time_to_topik_table', 1),
(8, '2024_01_01_000006_add_creator_and_ranking_columns', 2),
(9, '2024_01_01_000007_add_guru_menu', 3),
(10, '2024_01_01_000008_add_tipe_ujian_to_topik', 4),
(11, '2024_01_01_000009_add_jenjang_kelas_and_raport', 5),
(12, '2024_01_01_000010_add_kelas_to_raport', 6),
(13, '2024_01_01_000011_add_kelulusan_menu', 7),
(14, '2024_01_01_000012_create_parent_attendance_payment_tables', 8),
(15, '2024_01_01_000013_create_enterprise_tenant_tables', 9),
(16, '2026_11_01_000014_create_school_fee_finance_tables', 10),
(17, '2026_11_01_000015_add_spp_menus', 11),
(18, '2026_11_01_000016_create_user_access_and_preferences', 12),
(19, '2026_11_01_000017_add_action_permissions', 13),
(20, '2026_11_01_000018_add_spp_payment_verification', 13),
(21, '2026_11_02_000019_create_attendance_geofence_tables', 14),
(22, '2026_11_02_000020_add_attendance_geofence_menus', 14),
(23, '2026_11_02_000021_add_payment_account_fields_to_fin_payment_methods', 14);

-- --------------------------------------------------------

--
-- Struktur dari tabel `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `user`
--

CREATE TABLE `user` (
  `id` bigint UNSIGNED NOT NULL,
  `username` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `opsi1` varchar(75) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `opsi2` varchar(75) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `keterangan` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `level` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `foundation_id` bigint UNSIGNED DEFAULT NULL,
  `school_id` bigint UNSIGNED DEFAULT NULL,
  `scope_type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'system',
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `user`
--

INSERT INTO `user` (`id`, `username`, `password`, `nama`, `opsi1`, `opsi2`, `keterangan`, `level`, `foundation_id`, `school_id`, `scope_type`, `ts`, `remember_token`) VALUES
(16, 'admin', '$2y$12$hJ.GA.9c64zt0TvZKkTM7uk3a6EdiN91O0c6NhlX8Bb0paAQTrT1u', 'Administrator', '', '', 'Akun administrator utama', 'admin', 1, NULL, 'system', '2026-11-04 06:40:05', NULL),
(17, 'operator', '$2y$10$fyzhZJPquK04mel827OqG.5UYZb5tzXdN6NtflAUq/ly2pD8BNbOm', 'Operator Ujian', '', '', 'Operator pelaksanaan ujian', 'operator-tes', 1, NULL, 'system', '2026-11-04 06:40:05', NULL),
(18, 'operatorsoal', '$2y$10$/Dax451vHByIjYrCnOzXWuWVCoYWK9tUbvr2N0sqZmleS2bWXzpry', 'Operator Soal', '', '', 'Operator pengelola bank soal', 'operator-soal', 1, NULL, 'system', '2026-11-04 06:40:05', NULL),
(19, 'gurua', '$2y$10$5L8Svu0Moz8c/.3ILdx8UeDZFV6R7VB1q7W0TiEc2KbagxBUhZ/tK', 'Guru A (Matematika)', '', '', 'Akun guru contoh Matematika; menu ditentukan lewat penugasan akun', 'guru', 1, NULL, 'system', '2026-11-04 06:40:05', NULL),
(20, 'gurub', '$2y$10$LuEqvc3kmRbd5o56gLt7A.Hyz4vIS8hDizmAECWOGVQlEoiepo2K2', 'Guru B (Bahasa Indonesia)', '', '', 'Akun guru contoh Bahasa Indonesia; menu ditentukan lewat penugasan akun', 'guru', 1, NULL, 'system', '2026-11-04 06:40:05', NULL),
(21, 'guruc', '$2y$10$dfVzL3PEaIzyp5v7FodJ..llDA4XmxdRkgbdxD9IBtevWnV1J3GDu', 'Guru C (IPA)', '', '', 'Akun guru contoh IPA; menu ditentukan lewat penugasan akun', 'guru', 1, NULL, 'system', '2026-11-04 06:40:05', NULL),
(22, 'adminyayasan', '$2y$10$Nq95NQyx4pCUsjHgpTVCMe0TrRva.vsMA13ygHlKlqOh1Tq17IAiK', 'Admin Yayasan', '', '', 'Admin dengan scope satu yayasan', 'admin', 1, NULL, 'foundation', '2026-11-04 06:40:05', NULL),
(23, 'adminsekolah', '$2y$10$Yok3JqSjoNJlwbYwhI3.EeXdaLfNBZdWvCn4ecX7hN6hPV9YpA7Zu', 'Admin SMA Nusantara 2', '', '', 'Admin dengan scope satu sekolah', 'admin', 1, 3, 'school', '2026-11-04 06:40:05', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `user_akses`
--

CREATE TABLE `user_akses` (
  `id` bigint UNSIGNED NOT NULL,
  `level` varchar(75) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kode_menu` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `add` tinyint(1) NOT NULL DEFAULT '1',
  `edit` tinyint(1) NOT NULL DEFAULT '1',
  `can_delete` tinyint(1) DEFAULT NULL,
  `can_approve` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `user_akses`
--

INSERT INTO `user_akses` (`id`, `level`, `kode_menu`, `add`, `edit`, `can_delete`, `can_approve`) VALUES
(342, 'admin', 'absensi', 1, 1, NULL, NULL),
(343, 'admin', 'dashboard', 1, 1, NULL, NULL),
(344, 'admin', 'data-bank', 1, 1, NULL, NULL),
(345, 'admin', 'data-peserta', 1, 1, NULL, NULL),
(346, 'admin', 'enterprise', 1, 1, NULL, NULL),
(347, 'admin', 'grup', 1, 1, NULL, NULL),
(348, 'admin', 'guru', 1, 1, NULL, NULL),
(349, 'admin', 'hasil', 1, 1, NULL, NULL),
(350, 'admin', 'kelulusan', 1, 1, NULL, NULL),
(351, 'admin', 'keuangan', 1, 1, NULL, NULL),
(352, 'admin', 'laporan', 1, 1, NULL, NULL),
(353, 'admin', 'level', 1, 1, NULL, NULL),
(354, 'admin', 'manajemen-ujian', 1, 1, NULL, NULL),
(355, 'admin', 'modul', 1, 1, NULL, NULL),
(356, 'admin', 'orangtua', 1, 1, NULL, NULL),
(357, 'admin', 'pembayaran', 1, 1, NULL, NULL),
(358, 'admin', 'pengaturan', 1, 1, NULL, NULL),
(359, 'admin', 'peserta', 1, 1, NULL, NULL),
(360, 'admin', 'sekolah', 1, 1, NULL, NULL),
(361, 'admin', 'sistem', 1, 1, NULL, NULL),
(362, 'admin', 'soal', 1, 1, NULL, NULL),
(363, 'admin', 'spp', 1, 1, NULL, NULL),
(364, 'admin', 'spp-pembayaran', 1, 1, NULL, NULL),
(365, 'admin', 'spp-pengaturan', 1, 1, NULL, NULL),
(366, 'admin', 'tes', 1, 1, NULL, NULL),
(367, 'admin', 'token', 1, 1, NULL, NULL),
(368, 'admin', 'topik', 1, 1, NULL, NULL),
(369, 'admin', 'user', 1, 1, NULL, NULL),
(370, 'admin', 'yayasan', 1, 1, NULL, NULL),
(371, 'operator-soal', 'modul', 1, 1, NULL, NULL),
(372, 'operator-soal', 'topik', 1, 1, NULL, NULL),
(373, 'operator-soal', 'soal', 1, 1, NULL, NULL),
(374, 'operator-soal', 'guru', 1, 1, NULL, NULL),
(375, 'operator-soal', 'absensi', 1, 1, NULL, NULL),
(376, 'operator-tes', 'tes', 1, 1, NULL, NULL),
(377, 'operator-tes', 'token', 1, 1, NULL, NULL),
(378, 'operator-tes', 'hasil', 1, 1, NULL, NULL),
(379, 'operator-tes', 'guru', 1, 1, NULL, NULL),
(380, 'operator-tes', 'absensi', 1, 1, NULL, NULL),
(381, 'administrator-sekolah', 'dashboard', 1, 1, NULL, NULL),
(382, 'administrator-sekolah', 'sekolah', 1, 1, NULL, NULL),
(383, 'administrator-sekolah', 'modul', 1, 1, NULL, NULL),
(384, 'administrator-sekolah', 'topik', 1, 1, NULL, NULL),
(385, 'administrator-sekolah', 'soal', 1, 1, NULL, NULL),
(386, 'administrator-sekolah', 'grup', 1, 1, NULL, NULL),
(387, 'administrator-sekolah', 'peserta', 1, 1, NULL, NULL),
(388, 'administrator-sekolah', 'orangtua', 1, 1, NULL, NULL),
(389, 'administrator-sekolah', 'absensi', 1, 1, NULL, NULL),
(390, 'administrator-sekolah', 'tes', 1, 1, NULL, NULL),
(391, 'administrator-sekolah', 'token', 1, 1, NULL, NULL),
(392, 'administrator-sekolah', 'hasil', 1, 1, NULL, NULL),
(393, 'administrator-sekolah', 'pembayaran', 1, 1, NULL, NULL),
(394, 'administrator-sekolah', 'spp', 1, 1, NULL, NULL),
(395, 'administrator-sekolah', 'spp-pembayaran', 1, 1, NULL, NULL),
(396, 'administrator-sekolah', 'spp-pengaturan', 1, 1, NULL, NULL),
(397, 'administrator-sekolah', 'guru', 1, 1, NULL, NULL),
(398, 'administrator-sekolah', 'kelulusan', 1, 1, NULL, NULL),
(399, 'administrator-sekolah', 'laporan', 1, 1, NULL, NULL),
(400, 'administrator-sekolah', 'pengaturan', 1, 1, NULL, NULL),
(401, 'kepala-sekolah', 'dashboard', 1, 1, NULL, NULL),
(402, 'kepala-sekolah', 'sekolah', 1, 1, NULL, NULL),
(403, 'kepala-sekolah', 'peserta', 1, 1, NULL, NULL),
(404, 'kepala-sekolah', 'guru', 1, 1, NULL, NULL),
(405, 'kepala-sekolah', 'absensi', 1, 1, NULL, NULL),
(406, 'kepala-sekolah', 'hasil', 1, 1, NULL, NULL),
(407, 'kepala-sekolah', 'pembayaran', 1, 1, NULL, NULL),
(408, 'kepala-sekolah', 'spp', 1, 1, NULL, NULL),
(409, 'kepala-sekolah', 'spp-pembayaran', 1, 1, NULL, NULL),
(410, 'kepala-sekolah', 'kelulusan', 1, 1, NULL, NULL),
(411, 'kepala-sekolah', 'laporan', 1, 1, NULL, NULL),
(412, 'guru', 'dashboard', 1, 1, NULL, NULL),
(413, 'wali-kelas', 'dashboard', 1, 1, NULL, NULL),
(414, 'wali-kelas', 'guru', 1, 1, NULL, NULL),
(415, 'wali-kelas', 'peserta', 1, 1, NULL, NULL),
(416, 'wali-kelas', 'absensi', 1, 1, NULL, NULL),
(417, 'wali-kelas', 'hasil', 1, 1, NULL, NULL),
(418, 'wali-kelas', 'kelulusan', 1, 1, NULL, NULL),
(419, 'wali-kelas', 'laporan', 1, 1, NULL, NULL),
(420, 'bendahara', 'dashboard', 1, 1, NULL, NULL),
(421, 'bendahara', 'pembayaran', 1, 1, NULL, NULL),
(422, 'bendahara', 'spp', 1, 1, NULL, NULL),
(423, 'bendahara', 'spp-pembayaran', 1, 1, NULL, NULL),
(424, 'bendahara', 'spp-pengaturan', 1, 1, NULL, NULL),
(425, 'bendahara', 'laporan', 1, 1, NULL, NULL),
(426, 'petugas-pembayaran', 'dashboard', 1, 1, NULL, NULL),
(427, 'petugas-pembayaran', 'pembayaran', 1, 1, NULL, NULL),
(428, 'petugas-pembayaran', 'spp', 1, 1, NULL, NULL),
(429, 'petugas-pembayaran', 'spp-pembayaran', 1, 1, NULL, NULL),
(430, 'petugas-kasir', 'dashboard', 1, 1, NULL, NULL),
(431, 'petugas-kasir', 'pembayaran', 1, 1, NULL, NULL),
(432, 'petugas-kasir', 'spp', 1, 1, NULL, NULL),
(433, 'petugas-kasir', 'spp-pembayaran', 1, 1, NULL, NULL),
(434, 'petugas-spp', 'dashboard', 1, 1, NULL, NULL),
(435, 'petugas-spp', 'spp', 1, 1, NULL, NULL),
(436, 'petugas-spp', 'spp-pembayaran', 1, 1, NULL, NULL),
(437, 'operator-sekolah', 'dashboard', 1, 1, NULL, NULL),
(438, 'operator-sekolah', 'sekolah', 1, 1, NULL, NULL),
(439, 'operator-sekolah', 'grup', 1, 1, NULL, NULL),
(440, 'operator-sekolah', 'peserta', 1, 1, NULL, NULL),
(441, 'operator-sekolah', 'orangtua', 1, 1, NULL, NULL),
(442, 'operator-sekolah', 'guru', 1, 1, NULL, NULL),
(443, 'operator-sekolah', 'laporan', 1, 1, NULL, NULL),
(444, 'petugas-perpustakaan', 'dashboard', 1, 1, NULL, NULL),
(445, 'petugas-bk', 'dashboard', 1, 1, NULL, NULL),
(446, 'petugas-bk', 'peserta', 1, 1, NULL, NULL),
(447, 'petugas-bk', 'laporan', 1, 1, NULL, NULL),
(448, 'petugas-uks', 'dashboard', 1, 1, NULL, NULL),
(449, 'petugas-uks', 'peserta', 1, 1, NULL, NULL),
(450, 'petugas-uks', 'absensi', 1, 1, NULL, NULL),
(451, 'petugas-tata-usaha', 'dashboard', 1, 1, NULL, NULL),
(452, 'petugas-tata-usaha', 'peserta', 1, 1, NULL, NULL),
(453, 'petugas-tata-usaha', 'orangtua', 1, 1, NULL, NULL),
(454, 'petugas-tata-usaha', 'grup', 1, 1, NULL, NULL),
(455, 'petugas-tata-usaha', 'absensi', 1, 1, NULL, NULL),
(456, 'admin', 'absensi-lokasi', 1, 1, NULL, NULL),
(457, 'admin', 'absensi-monitoring', 1, 1, NULL, NULL),
(458, 'operator-soal', 'absensi-lokasi', 1, 1, NULL, NULL),
(459, 'operator-soal', 'absensi-monitoring', 1, 1, NULL, NULL),
(460, 'operator-tes', 'absensi-lokasi', 1, 1, NULL, NULL),
(461, 'operator-tes', 'absensi-monitoring', 1, 1, NULL, NULL),
(462, 'administrator-sekolah', 'absensi-lokasi', 1, 1, NULL, NULL),
(463, 'administrator-sekolah', 'absensi-monitoring', 1, 1, NULL, NULL),
(464, 'kepala-sekolah', 'absensi-lokasi', 1, 1, NULL, NULL),
(465, 'kepala-sekolah', 'absensi-monitoring', 1, 1, NULL, NULL),
(466, 'wali-kelas', 'absensi-lokasi', 1, 1, NULL, NULL),
(467, 'wali-kelas', 'absensi-monitoring', 1, 1, NULL, NULL),
(468, 'petugas-uks', 'absensi-lokasi', 1, 1, NULL, NULL),
(469, 'petugas-uks', 'absensi-monitoring', 1, 1, NULL, NULL),
(470, 'petugas-tata-usaha', 'absensi-lokasi', 1, 1, NULL, NULL),
(471, 'petugas-tata-usaha', 'absensi-monitoring', 1, 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `user_level`
--

CREATE TABLE `user_level` (
  `id` bigint UNSIGNED NOT NULL,
  `level` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `keterangan` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `user_level`
--

INSERT INTO `user_level` (`id`, `level`, `keterangan`) VALUES
(16, 'admin', 'Administrator'),
(17, 'operator-soal', 'Operator Soal'),
(18, 'operator-tes', 'Operator Tes'),
(19, 'administrator-sekolah', 'Administrator Sekolah'),
(20, 'kepala-sekolah', 'Kepala Sekolah'),
(21, 'guru', 'Guru Mata Pelajaran'),
(22, 'wali-kelas', 'Wali Kelas'),
(23, 'bendahara', 'Bendahara Sekolah'),
(24, 'petugas-pembayaran', 'Petugas Pembayaran'),
(25, 'petugas-kasir', 'Kasir Seluruh Pembayaran'),
(26, 'petugas-spp', 'Petugas Pembayaran SPP'),
(27, 'operator-sekolah', 'Operator Data Sekolah'),
(28, 'petugas-perpustakaan', 'Petugas Perpustakaan'),
(29, 'petugas-bk', 'Guru Bimbingan Konseling'),
(30, 'petugas-uks', 'Petugas UKS'),
(31, 'petugas-tata-usaha', 'Petugas Tata Usaha');

-- --------------------------------------------------------

--
-- Struktur dari tabel `user_log`
--

CREATE TABLE `user_log` (
  `id` bigint UNSIGNED NOT NULL,
  `username` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `log` varchar(250) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `user_log`
--

INSERT INTO `user_log` (`id`, `username`, `log`, `ts`) VALUES
(3, 'admin', 'Login ke panel administrator', '2026-11-02 04:04:50'),
(4, 'admin', 'Login ke panel administrator', '2026-11-02 20:00:48'),
(5, 'admin', 'Logout dari panel administrator', '2026-11-02 21:07:03'),
(6, 'admin', 'Login ke panel administrator', '2026-11-02 21:32:10'),
(7, 'admin', 'Login ke panel administrator', '2026-11-02 21:34:12'),
(8, 'admin', 'Login ke panel administrator', '2026-11-02 21:35:20'),
(9, 'admin', 'Login ke panel administrator', '2026-11-02 21:37:32'),
(10, 'admin', 'Login ke panel administrator', '2026-11-02 21:43:16'),
(11, 'admin', 'Login ke panel administrator', '2026-11-04 06:47:38'),
(12, 'admin', 'Login ke panel administrator', '2026-11-05 05:42:08'),
(13, 'admin', 'Logout dari panel administrator', '2026-11-05 05:47:40'),
(14, 'admin', 'Login ke panel administrator', '2026-11-05 22:44:47'),
(15, 'admin', 'Login ke panel administrator', '2026-11-05 22:46:34'),
(16, 'admin', 'Login ke panel administrator', '2026-11-05 22:50:20'),
(17, 'admin', 'Login ke panel administrator', '2026-11-05 22:52:39'),
(18, 'admin', 'Login ke panel administrator', '2026-11-05 22:52:54'),
(19, 'admin', 'Login ke panel administrator', '2026-11-05 22:53:14'),
(20, 'admin', 'Login ke panel administrator', '2026-11-05 22:53:28'),
(21, 'admin', 'Login ke panel administrator', '2026-11-05 22:54:48'),
(22, 'admin', 'Login ke panel administrator', '2026-11-05 23:11:08'),
(23, 'admin', 'Login ke panel administrator', '2026-11-05 23:11:24'),
(24, 'admin', 'Login ke panel administrator', '2026-11-05 23:14:03'),
(25, 'admin', 'Login ke panel administrator', '2026-11-05 23:20:45'),
(26, 'admin', 'Login ke panel administrator', '2026-11-05 23:21:40'),
(27, 'admin', 'Login ke panel administrator', '2026-11-05 23:32:36'),
(28, 'admin', 'Logout dari panel administrator', '2026-11-05 23:35:11'),
(29, 'admin', 'Login ke panel administrator', '2026-11-05 23:41:07'),
(30, 'admin', 'Login ke panel administrator', '2026-11-06 00:01:34'),
(31, 'admin', 'Logout dari panel administrator', '2026-11-06 00:02:42'),
(32, 'admin', 'Login ke panel administrator', '2026-11-06 00:24:53'),
(33, 'admin', 'Login ke panel administrator', '2026-11-06 00:36:26'),
(34, 'admin', 'Login ke panel administrator', '2026-11-06 00:41:42'),
(35, 'admin', 'Login ke panel administrator', '2026-11-06 00:47:33'),
(36, 'admin', 'Login ke panel administrator', '2026-11-06 03:36:36'),
(37, 'admin', 'Logout dari panel administrator', '2026-11-06 03:44:04');

-- --------------------------------------------------------

--
-- Struktur dari tabel `user_menu`
--

CREATE TABLE `user_menu` (
  `id` bigint UNSIGNED NOT NULL,
  `tipe` tinyint NOT NULL DEFAULT '1',
  `parent` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `kode_menu` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_menu` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `url` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#',
  `icon` varchar(75) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'bi-circle',
  `urutan` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `user_menu`
--

INSERT INTO `user_menu` (`id`, `tipe`, `parent`, `kode_menu`, `nama_menu`, `url`, `icon`, `urutan`) VALUES
(86, 0, NULL, 'dashboard', 'Dashboard', 'admin.dashboard', 'bi-speedometer2', 1),
(87, 0, NULL, 'data-bank', 'Bank Soal', '#', 'bi-collection', 3),
(88, 1, 'data-bank', 'modul', 'Modul', 'admin.modul.index', 'bi-folder2-open', 1),
(89, 1, 'data-bank', 'topik', 'Topik', 'admin.topik.index', 'bi-diagram-3', 2),
(90, 1, 'data-bank', 'soal', 'Soal', 'admin.soal.index', 'bi-patch-question', 3),
(91, 0, NULL, 'data-peserta', 'Peserta', '#', 'bi-people', 4),
(92, 1, 'data-peserta', 'grup', 'Group Peserta', 'admin.grup.index', 'bi-collection', 1),
(93, 1, 'data-peserta', 'peserta', 'Daftar Peserta', 'admin.peserta.index', 'bi-person-vcard', 2),
(94, 0, NULL, 'manajemen-ujian', 'Manajemen Ujian', '#', 'bi-clipboard-check', 5),
(95, 1, 'manajemen-ujian', 'tes', 'Daftar Tes', 'admin.tes.index', 'bi-journal-text', 1),
(96, 1, 'manajemen-ujian', 'token', 'Token Ujian', 'admin.token.index', 'bi-key', 2),
(97, 1, 'manajemen-ujian', 'hasil', 'Hasil & Evaluasi', 'admin.hasil.index', 'bi-bar-chart', 3),
(98, 0, NULL, 'laporan', 'Laporan', 'admin.laporan.rekap', 'bi-graph-up-arrow', 9),
(99, 0, NULL, 'sistem', 'Sistem', '#', 'bi-gear', 10),
(100, 1, 'sistem', 'pengaturan', 'Pengaturan Aplikasi', 'admin.pengaturan.index', 'bi-sliders', 1),
(101, 1, 'sistem', 'user', 'User Administrator', 'admin.user.index', 'bi-person-gear', 2),
(102, 1, 'sistem', 'level', 'Level & Hak Akses', 'admin.level.index', 'bi-shield-lock', 3),
(103, 0, NULL, 'guru', 'Guru', 'admin.guru.index', 'bi-person-video3', 7),
(104, 0, NULL, 'kelulusan', 'Kelulusan', 'admin.kelulusan.index', 'bi-award', 8),
(105, 1, 'data-peserta', 'orangtua', 'Akun Orang Tua', 'admin.orangtua.index', 'bi-people', 3),
(106, 1, 'data-peserta', 'absensi', 'Absensi Peserta', 'admin.absensi.index', 'bi-calendar-check', 4),
(107, 1, 'manajemen-ujian', 'pembayaran', 'Pembayaran Ujian', 'admin.pembayaran.index', 'bi-wallet2', 4),
(108, 0, NULL, 'keuangan', 'Keuangan', '#', 'bi-cash-stack', 6),
(109, 1, 'keuangan', 'spp', 'Tagihan SPP', 'admin.spp.index', 'bi-receipt', 1),
(110, 1, 'keuangan', 'spp-pembayaran', 'Pembayaran SPP', 'admin.spp.payments', 'bi-cash-coin', 2),
(111, 1, 'keuangan', 'spp-pengaturan', 'Kategori & Tarif', 'admin.spp.setup', 'bi-sliders2', 3),
(112, 0, NULL, 'enterprise', 'Yayasan & Sekolah', '#', 'bi-buildings', 2),
(113, 1, 'enterprise', 'yayasan', 'Yayasan', 'admin.yayasan.index', 'bi-bank', 1),
(114, 1, 'enterprise', 'sekolah', 'Sekolah & Tahun Ajaran', 'admin.sekolah.index', 'bi-building-gear', 2),
(115, 1, 'data-peserta', 'absensi-lokasi', 'Lokasi & Kebijakan Absensi', 'admin.absensi.lokasi', 'bi-geo-alt', 5),
(116, 1, 'data-peserta', 'absensi-monitoring', 'Monitoring Kehadiran', 'admin.absensi.monitoring', 'bi-broadcast', 6);

-- --------------------------------------------------------

--
-- Struktur dari tabel `user_menu_user_access`
--

CREATE TABLE `user_menu_user_access` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `kode_menu` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_allowed` tinyint(1) NOT NULL DEFAULT '1',
  `can_add` tinyint(1) NOT NULL DEFAULT '0',
  `can_edit` tinyint(1) NOT NULL DEFAULT '0',
  `can_delete` tinyint(1) NOT NULL DEFAULT '0',
  `can_approve` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `user_menu_user_access`
--

INSERT INTO `user_menu_user_access` (`id`, `user_id`, `kode_menu`, `is_allowed`, `can_add`, `can_edit`, `can_delete`, `can_approve`, `created_at`, `updated_at`) VALUES
(1, 19, 'soal', 1, 1, 1, 1, 0, '2026-11-04 06:40:07', '2026-11-04 06:40:07'),
(2, 19, 'topik', 1, 1, 1, 1, 0, '2026-11-04 06:40:07', '2026-11-04 06:40:07'),
(3, 19, 'grup', 1, 1, 1, 1, 0, '2026-11-04 06:40:07', '2026-11-04 06:40:07'),
(4, 19, 'guru', 1, 1, 1, 1, 0, '2026-11-04 06:40:07', '2026-11-04 06:40:07'),
(5, 20, 'soal', 1, 1, 1, 1, 0, '2026-11-04 06:40:07', '2026-11-04 06:40:07'),
(6, 20, 'topik', 1, 1, 1, 1, 0, '2026-11-04 06:40:07', '2026-11-04 06:40:07'),
(7, 20, 'grup', 1, 1, 1, 1, 0, '2026-11-04 06:40:07', '2026-11-04 06:40:07'),
(8, 20, 'guru', 1, 1, 1, 1, 0, '2026-11-04 06:40:07', '2026-11-04 06:40:07'),
(9, 21, 'soal', 1, 1, 1, 1, 0, '2026-11-04 06:40:07', '2026-11-04 06:40:07'),
(10, 21, 'topik', 1, 1, 1, 1, 0, '2026-11-04 06:40:07', '2026-11-04 06:40:07'),
(11, 21, 'grup', 1, 1, 1, 1, 0, '2026-11-04 06:40:07', '2026-11-04 06:40:07'),
(12, 21, 'guru', 1, 1, 1, 1, 0, '2026-11-04 06:40:07', '2026-11-04 06:40:07');

-- --------------------------------------------------------

--
-- Struktur dari tabel `user_preferences`
--

CREATE TABLE `user_preferences` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `theme_mode` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'light',
  `primary_color` varchar(7) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#6366f1',
  `accent_color` varchar(7) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#06b6d4',
  `sidebar_collapsed` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `user_preferences`
--

INSERT INTO `user_preferences` (`id`, `user_id`, `theme_mode`, `primary_color`, `accent_color`, `sidebar_collapsed`, `created_at`, `updated_at`) VALUES
(1, 16, 'light', '#6366f1', '#06b6d4', 0, '2026-11-05 22:44:59', '2026-11-05 22:44:59');

--
-- Indeks untuk tabel yang dibuang
--

--
-- Indeks untuk tabel `att_attendance_audit_events`
--
ALTER TABLE `att_attendance_audit_events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_att_audit_event_time` (`event_type`,`created_at`),
  ADD KEY `idx_att_audit_session_student` (`session_id`,`student_id`);

--
-- Indeks untuk tabel `att_attendance_corrections`
--
ALTER TABLE `att_attendance_corrections`
  ADD PRIMARY KEY (`id`),
  ADD KEY `att_attendance_corrections_record_id_index` (`record_id`);

--
-- Indeks untuk tabel `att_attendance_locations`
--
ALTER TABLE `att_attendance_locations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `att_attendance_locations_school_id_is_active_index` (`school_id`,`is_active`),
  ADD KEY `att_attendance_locations_foundation_id_index` (`foundation_id`);

--
-- Indeks untuk tabel `att_attendance_location_checks`
--
ALTER TABLE `att_attendance_location_checks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_att_check_session_student` (`session_id`,`student_id`),
  ADD KEY `idx_att_check_status_time` (`validation_status`,`checked_at`);

--
-- Indeks untuk tabel `att_attendance_policies`
--
ALTER TABLE `att_attendance_policies`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ak_att_policy_scope` (`school_id`,`policy_type`,`academic_year_id`),
  ADD KEY `att_attendance_policies_foundation_id_index` (`foundation_id`);

--
-- Indeks untuk tabel `att_attendance_records`
--
ALTER TABLE `att_attendance_records`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ak_att_record_session_student` (`session_id`,`student_id`),
  ADD KEY `att_attendance_records_student_id_status_index` (`student_id`,`status`),
  ADD KEY `att_attendance_records_school_id_status_index` (`school_id`,`status`);

--
-- Indeks untuk tabel `att_attendance_sessions`
--
ALTER TABLE `att_attendance_sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ak_att_session_exam` (`exam_id`,`session_type`),
  ADD KEY `idx_att_session_school_date` (`school_id`,`session_date`,`session_type`),
  ADD KEY `att_attendance_sessions_foundation_id_index` (`foundation_id`),
  ADD KEY `att_attendance_sessions_location_id_index` (`location_id`);

--
-- Indeks untuk tabel `cbt_absensi`
--
ALTER TABLE `cbt_absensi`
  ADD PRIMARY KEY (`absensi_id`),
  ADD UNIQUE KEY `ak_absensi_user_tes` (`absensi_user_id`,`absensi_tes_id`),
  ADD KEY `cbt_absensi_absensi_tanggal_absensi_status_index` (`absensi_tanggal`,`absensi_status`),
  ADD KEY `cbt_absensi_absensi_foundation_id_index` (`absensi_foundation_id`),
  ADD KEY `cbt_absensi_absensi_school_id_index` (`absensi_school_id`);

--
-- Indeks untuk tabel `cbt_jawaban`
--
ALTER TABLE `cbt_jawaban`
  ADD PRIMARY KEY (`jawaban_id`),
  ADD KEY `cbt_jawaban_jawaban_soal_id_index` (`jawaban_soal_id`);

--
-- Indeks untuk tabel `cbt_konfigurasi`
--
ALTER TABLE `cbt_konfigurasi`
  ADD PRIMARY KEY (`konfigurasi_id`),
  ADD UNIQUE KEY `cbt_konfigurasi_konfigurasi_kode_unique` (`konfigurasi_kode`);

--
-- Indeks untuk tabel `cbt_modul`
--
ALTER TABLE `cbt_modul`
  ADD PRIMARY KEY (`modul_id`),
  ADD UNIQUE KEY `cbt_modul_modul_nama_unique` (`modul_nama`);

--
-- Indeks untuk tabel `cbt_orang_tua`
--
ALTER TABLE `cbt_orang_tua`
  ADD PRIMARY KEY (`ortu_id`),
  ADD UNIQUE KEY `cbt_orang_tua_ortu_username_unique` (`ortu_username`);

--
-- Indeks untuk tabel `cbt_orang_tua_peserta`
--
ALTER TABLE `cbt_orang_tua_peserta`
  ADD PRIMARY KEY (`ortu_id`,`user_id`),
  ADD KEY `cbt_orang_tua_peserta_user_id_index` (`user_id`);

--
-- Indeks untuk tabel `cbt_pembayaran`
--
ALTER TABLE `cbt_pembayaran`
  ADD PRIMARY KEY (`pembayaran_id`),
  ADD UNIQUE KEY `ak_pembayaran_user_tes` (`pembayaran_user_id`,`pembayaran_tes_id`),
  ADD KEY `cbt_pembayaran_pembayaran_status_pembayaran_verified_at_index` (`pembayaran_status`,`pembayaran_verified_at`),
  ADD KEY `cbt_pembayaran_pembayaran_foundation_id_index` (`pembayaran_foundation_id`),
  ADD KEY `cbt_pembayaran_pembayaran_school_id_index` (`pembayaran_school_id`);

--
-- Indeks untuk tabel `cbt_raport`
--
ALTER TABLE `cbt_raport`
  ADD PRIMARY KEY (`raport_id`),
  ADD UNIQUE KEY `ak_raport_user_modul_kelas` (`raport_user_id`,`raport_modul_id`,`raport_jenjang`,`raport_tingkat`),
  ADD KEY `cbt_raport_raport_modul_id_index` (`raport_modul_id`),
  ADD KEY `cbt_raport_raport_guru_id_index` (`raport_guru_id`);

--
-- Indeks untuk tabel `cbt_soal`
--
ALTER TABLE `cbt_soal`
  ADD PRIMARY KEY (`soal_id`),
  ADD KEY `cbt_soal_soal_topik_id_index` (`soal_topik_id`),
  ADD KEY `cbt_soal_soal_creator_id_index` (`soal_creator_id`);

--
-- Indeks untuk tabel `cbt_tes`
--
ALTER TABLE `cbt_tes`
  ADD PRIMARY KEY (`tes_id`),
  ADD UNIQUE KEY `cbt_tes_tes_nama_unique` (`tes_nama`),
  ADD KEY `cbt_tes_tes_foundation_id_index` (`tes_foundation_id`),
  ADD KEY `cbt_tes_tes_school_id_index` (`tes_school_id`),
  ADD KEY `cbt_tes_tes_academic_year_id_index` (`tes_academic_year_id`);

--
-- Indeks untuk tabel `cbt_tesgrup`
--
ALTER TABLE `cbt_tesgrup`
  ADD PRIMARY KEY (`tstgrp_tes_id`,`tstgrp_grup_id`);

--
-- Indeks untuk tabel `cbt_tes_soal`
--
ALTER TABLE `cbt_tes_soal`
  ADD PRIMARY KEY (`tessoal_id`),
  ADD UNIQUE KEY `ak_testuser_question` (`tessoal_tesuser_id`,`tessoal_soal_id`),
  ADD KEY `cbt_tes_soal_tessoal_tesuser_id_index` (`tessoal_tesuser_id`);

--
-- Indeks untuk tabel `cbt_tes_soal_jawaban`
--
ALTER TABLE `cbt_tes_soal_jawaban`
  ADD PRIMARY KEY (`soaljawaban_tessoal_id`,`soaljawaban_jawaban_id`);

--
-- Indeks untuk tabel `cbt_tes_token`
--
ALTER TABLE `cbt_tes_token`
  ADD PRIMARY KEY (`token_id`),
  ADD KEY `cbt_tes_token_token_user_id_index` (`token_user_id`);

--
-- Indeks untuk tabel `cbt_tes_topik_set`
--
ALTER TABLE `cbt_tes_topik_set`
  ADD PRIMARY KEY (`tset_id`),
  ADD KEY `cbt_tes_topik_set_tset_tes_id_index` (`tset_tes_id`);

--
-- Indeks untuk tabel `cbt_tes_user`
--
ALTER TABLE `cbt_tes_user`
  ADD PRIMARY KEY (`tesuser_id`),
  ADD UNIQUE KEY `ak_tesuser` (`tesuser_tes_id`,`tesuser_user_id`,`tesuser_status`),
  ADD KEY `cbt_tes_user_tesuser_user_id_index` (`tesuser_user_id`);

--
-- Indeks untuk tabel `cbt_topik`
--
ALTER TABLE `cbt_topik`
  ADD PRIMARY KEY (`topik_id`),
  ADD KEY `cbt_topik_topik_modul_id_index` (`topik_modul_id`),
  ADD KEY `cbt_topik_topik_guru_id_index` (`topik_guru_id`);

--
-- Indeks untuk tabel `cbt_user`
--
ALTER TABLE `cbt_user`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `cbt_user_user_name_unique` (`user_name`),
  ADD KEY `cbt_user_user_grup_id_index` (`user_grup_id`),
  ADD KEY `cbt_user_user_foundation_id_index` (`user_foundation_id`),
  ADD KEY `cbt_user_user_school_id_index` (`user_school_id`);

--
-- Indeks untuk tabel `cbt_user_grup`
--
ALTER TABLE `cbt_user_grup`
  ADD PRIMARY KEY (`grup_id`),
  ADD UNIQUE KEY `cbt_user_grup_grup_nama_unique` (`grup_nama`),
  ADD KEY `cbt_user_grup_grup_jenjang_grup_tingkat_index` (`grup_jenjang`,`grup_tingkat`),
  ADD KEY `cbt_user_grup_grup_foundation_id_index` (`grup_foundation_id`),
  ADD KEY `cbt_user_grup_grup_school_id_index` (`grup_school_id`),
  ADD KEY `cbt_user_grup_grup_academic_year_id_index` (`grup_academic_year_id`);

--
-- Indeks untuk tabel `core_academic_years`
--
ALTER TABLE `core_academic_years`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `core_academic_years_school_id_year_name_unique` (`school_id`,`year_name`),
  ADD KEY `core_academic_years_foundation_id_foreign` (`foundation_id`);

--
-- Indeks untuk tabel `core_foundations`
--
ALTER TABLE `core_foundations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `core_foundations_uuid_unique` (`uuid`),
  ADD UNIQUE KEY `core_foundations_foundation_code_unique` (`foundation_code`);

--
-- Indeks untuk tabel `core_schools`
--
ALTER TABLE `core_schools`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `core_schools_foundation_id_school_code_unique` (`foundation_id`,`school_code`),
  ADD UNIQUE KEY `core_schools_uuid_unique` (`uuid`),
  ADD KEY `core_schools_school_level_id_foreign` (`school_level_id`);

--
-- Indeks untuk tabel `core_school_levels`
--
ALTER TABLE `core_school_levels`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `core_school_levels_level_code_unique` (`level_code`);

--
-- Indeks untuk tabel `core_semesters`
--
ALTER TABLE `core_semesters`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `core_semesters_academic_year_id_semester_number_unique` (`academic_year_id`,`semester_number`),
  ADD KEY `core_semesters_school_id_foreign` (`school_id`);

--
-- Indeks untuk tabel `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indeks untuk tabel `fin_cash_accounts`
--
ALTER TABLE `fin_cash_accounts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `fin_cash_accounts_school_id_account_code_unique` (`school_id`,`account_code`),
  ADD KEY `fin_cash_accounts_foundation_id_foreign` (`foundation_id`);

--
-- Indeks untuk tabel `fin_fee_categories`
--
ALTER TABLE `fin_fee_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `fin_fee_categories_school_id_category_code_unique` (`school_id`,`category_code`),
  ADD KEY `fin_fee_categories_foundation_id_school_id_is_active_index` (`foundation_id`,`school_id`,`is_active`);

--
-- Indeks untuk tabel `fin_fee_structures`
--
ALTER TABLE `fin_fee_structures`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fin_fee_structures_foundation_id_foreign` (`foundation_id`),
  ADD KEY `fin_fee_structures_academic_year_id_foreign` (`academic_year_id`),
  ADD KEY `fin_fee_structures_fee_category_id_foreign` (`fee_category_id`),
  ADD KEY `fin_fee_structure_school_year_category_idx` (`school_id`,`academic_year_id`,`fee_category_id`);

--
-- Indeks untuk tabel `fin_payments`
--
ALTER TABLE `fin_payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `fin_payments_school_id_payment_number_unique` (`school_id`,`payment_number`),
  ADD UNIQUE KEY `fin_payments_uuid_unique` (`uuid`),
  ADD UNIQUE KEY `fin_payments_provider_reference_unique` (`provider_reference`),
  ADD UNIQUE KEY `fin_payments_idempotency_key_unique` (`idempotency_key`),
  ADD KEY `fin_payments_student_id_foreign` (`student_id`),
  ADD KEY `fin_payments_guardian_id_foreign` (`guardian_id`),
  ADD KEY `fin_payments_payment_method_id_foreign` (`payment_method_id`),
  ADD KEY `fin_payments_foundation_id_school_id_student_id_status_index` (`foundation_id`,`school_id`,`student_id`,`status`),
  ADD KEY `fin_payments_bill_id_index` (`bill_id`),
  ADD KEY `fin_payments_status_paid_at_index` (`status`,`paid_at`);

--
-- Indeks untuk tabel `fin_payment_allocations`
--
ALTER TABLE `fin_payment_allocations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `fin_payment_allocations_payment_id_bill_id_unique` (`payment_id`,`bill_id`),
  ADD KEY `fin_payment_allocations_bill_id_index` (`bill_id`);

--
-- Indeks untuk tabel `fin_payment_methods`
--
ALTER TABLE `fin_payment_methods`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `fin_payment_methods_school_id_method_code_unique` (`school_id`,`method_code`);

--
-- Indeks untuk tabel `fin_student_bills`
--
ALTER TABLE `fin_student_bills`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `fin_student_bills_school_id_bill_number_unique` (`school_id`,`bill_number`),
  ADD UNIQUE KEY `fin_student_bills_uuid_unique` (`uuid`),
  ADD KEY `fin_student_bills_academic_year_id_foreign` (`academic_year_id`),
  ADD KEY `fin_student_bills_student_id_foreign` (`student_id`),
  ADD KEY `fin_student_bills_foundation_student_status_idx` (`foundation_id`,`school_id`,`student_id`,`status`),
  ADD KEY `fin_student_bills_school_id_due_date_status_index` (`school_id`,`due_date`,`status`);

--
-- Indeks untuk tabel `fin_student_bill_items`
--
ALTER TABLE `fin_student_bill_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fin_student_bill_items_fee_category_id_foreign` (`fee_category_id`),
  ADD KEY `fin_student_bill_items_bill_id_index` (`bill_id`);

--
-- Indeks untuk tabel `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indeks untuk tabel `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_username_unique` (`username`),
  ADD KEY `user_level_index` (`level`),
  ADD KEY `user_foundation_id_foreign` (`foundation_id`),
  ADD KEY `user_school_id_foreign` (`school_id`);

--
-- Indeks untuk tabel `user_akses`
--
ALTER TABLE `user_akses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_akses_kode_menu_index` (`kode_menu`),
  ADD KEY `user_akses_level_index` (`level`);

--
-- Indeks untuk tabel `user_level`
--
ALTER TABLE `user_level`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_level_level_unique` (`level`);

--
-- Indeks untuk tabel `user_log`
--
ALTER TABLE `user_log`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `user_menu`
--
ALTER TABLE `user_menu`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_menu_kode_menu_unique` (`kode_menu`);

--
-- Indeks untuk tabel `user_menu_user_access`
--
ALTER TABLE `user_menu_user_access`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_menu_user_access_user_id_kode_menu_unique` (`user_id`,`kode_menu`),
  ADD KEY `user_menu_user_access_kode_menu_is_allowed_index` (`kode_menu`,`is_allowed`);

--
-- Indeks untuk tabel `user_preferences`
--
ALTER TABLE `user_preferences`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_preferences_user_id_unique` (`user_id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `att_attendance_audit_events`
--
ALTER TABLE `att_attendance_audit_events`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `att_attendance_corrections`
--
ALTER TABLE `att_attendance_corrections`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `att_attendance_locations`
--
ALTER TABLE `att_attendance_locations`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `att_attendance_location_checks`
--
ALTER TABLE `att_attendance_location_checks`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `att_attendance_policies`
--
ALTER TABLE `att_attendance_policies`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `att_attendance_records`
--
ALTER TABLE `att_attendance_records`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=157;

--
-- AUTO_INCREMENT untuk tabel `att_attendance_sessions`
--
ALTER TABLE `att_attendance_sessions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT untuk tabel `cbt_absensi`
--
ALTER TABLE `cbt_absensi`
  MODIFY `absensi_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT untuk tabel `cbt_jawaban`
--
ALTER TABLE `cbt_jawaban`
  MODIFY `jawaban_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=409;

--
-- AUTO_INCREMENT untuk tabel `cbt_konfigurasi`
--
ALTER TABLE `cbt_konfigurasi`
  MODIFY `konfigurasi_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT untuk tabel `cbt_modul`
--
ALTER TABLE `cbt_modul`
  MODIFY `modul_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT untuk tabel `cbt_orang_tua`
--
ALTER TABLE `cbt_orang_tua`
  MODIFY `ortu_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `cbt_pembayaran`
--
ALTER TABLE `cbt_pembayaran`
  MODIFY `pembayaran_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT untuk tabel `cbt_raport`
--
ALTER TABLE `cbt_raport`
  MODIFY `raport_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=151;

--
-- AUTO_INCREMENT untuk tabel `cbt_soal`
--
ALTER TABLE `cbt_soal`
  MODIFY `soal_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=120;

--
-- AUTO_INCREMENT untuk tabel `cbt_tes`
--
ALTER TABLE `cbt_tes`
  MODIFY `tes_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT untuk tabel `cbt_tes_soal`
--
ALTER TABLE `cbt_tes_soal`
  MODIFY `tessoal_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=201;

--
-- AUTO_INCREMENT untuk tabel `cbt_tes_token`
--
ALTER TABLE `cbt_tes_token`
  MODIFY `token_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT untuk tabel `cbt_tes_topik_set`
--
ALTER TABLE `cbt_tes_topik_set`
  MODIFY `tset_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT untuk tabel `cbt_tes_user`
--
ALTER TABLE `cbt_tes_user`
  MODIFY `tesuser_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT untuk tabel `cbt_topik`
--
ALTER TABLE `cbt_topik`
  MODIFY `topik_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT untuk tabel `cbt_user`
--
ALTER TABLE `cbt_user`
  MODIFY `user_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=85;

--
-- AUTO_INCREMENT untuk tabel `cbt_user_grup`
--
ALTER TABLE `cbt_user_grup`
  MODIFY `grup_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT untuk tabel `core_academic_years`
--
ALTER TABLE `core_academic_years`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `core_foundations`
--
ALTER TABLE `core_foundations`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `core_schools`
--
ALTER TABLE `core_schools`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `core_school_levels`
--
ALTER TABLE `core_school_levels`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT untuk tabel `core_semesters`
--
ALTER TABLE `core_semesters`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT untuk tabel `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `fin_cash_accounts`
--
ALTER TABLE `fin_cash_accounts`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `fin_fee_categories`
--
ALTER TABLE `fin_fee_categories`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT untuk tabel `fin_fee_structures`
--
ALTER TABLE `fin_fee_structures`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT untuk tabel `fin_payments`
--
ALTER TABLE `fin_payments`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT untuk tabel `fin_payment_allocations`
--
ALTER TABLE `fin_payment_allocations`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT untuk tabel `fin_payment_methods`
--
ALTER TABLE `fin_payment_methods`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT untuk tabel `fin_student_bills`
--
ALTER TABLE `fin_student_bills`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT untuk tabel `fin_student_bill_items`
--
ALTER TABLE `fin_student_bill_items`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT untuk tabel `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT untuk tabel `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `user`
--
ALTER TABLE `user`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT untuk tabel `user_akses`
--
ALTER TABLE `user_akses`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=472;

--
-- AUTO_INCREMENT untuk tabel `user_level`
--
ALTER TABLE `user_level`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT untuk tabel `user_log`
--
ALTER TABLE `user_log`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT untuk tabel `user_menu`
--
ALTER TABLE `user_menu`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=117;

--
-- AUTO_INCREMENT untuk tabel `user_menu_user_access`
--
ALTER TABLE `user_menu_user_access`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT untuk tabel `user_preferences`
--
ALTER TABLE `user_preferences`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `core_academic_years`
--
ALTER TABLE `core_academic_years`
  ADD CONSTRAINT `core_academic_years_foundation_id_foreign` FOREIGN KEY (`foundation_id`) REFERENCES `core_foundations` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `core_academic_years_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `core_schools` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `core_schools`
--
ALTER TABLE `core_schools`
  ADD CONSTRAINT `core_schools_foundation_id_foreign` FOREIGN KEY (`foundation_id`) REFERENCES `core_foundations` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `core_schools_school_level_id_foreign` FOREIGN KEY (`school_level_id`) REFERENCES `core_school_levels` (`id`) ON DELETE RESTRICT;

--
-- Ketidakleluasaan untuk tabel `core_semesters`
--
ALTER TABLE `core_semesters`
  ADD CONSTRAINT `core_semesters_academic_year_id_foreign` FOREIGN KEY (`academic_year_id`) REFERENCES `core_academic_years` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `core_semesters_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `core_schools` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `fin_cash_accounts`
--
ALTER TABLE `fin_cash_accounts`
  ADD CONSTRAINT `fin_cash_accounts_foundation_id_foreign` FOREIGN KEY (`foundation_id`) REFERENCES `core_foundations` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `fin_cash_accounts_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `core_schools` (`id`) ON DELETE RESTRICT;

--
-- Ketidakleluasaan untuk tabel `fin_fee_categories`
--
ALTER TABLE `fin_fee_categories`
  ADD CONSTRAINT `fin_fee_categories_foundation_id_foreign` FOREIGN KEY (`foundation_id`) REFERENCES `core_foundations` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `fin_fee_categories_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `core_schools` (`id`) ON DELETE RESTRICT;

--
-- Ketidakleluasaan untuk tabel `fin_fee_structures`
--
ALTER TABLE `fin_fee_structures`
  ADD CONSTRAINT `fin_fee_structures_academic_year_id_foreign` FOREIGN KEY (`academic_year_id`) REFERENCES `core_academic_years` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fin_fee_structures_fee_category_id_foreign` FOREIGN KEY (`fee_category_id`) REFERENCES `fin_fee_categories` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `fin_fee_structures_foundation_id_foreign` FOREIGN KEY (`foundation_id`) REFERENCES `core_foundations` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `fin_fee_structures_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `core_schools` (`id`) ON DELETE RESTRICT;

--
-- Ketidakleluasaan untuk tabel `fin_payments`
--
ALTER TABLE `fin_payments`
  ADD CONSTRAINT `fin_payments_foundation_id_foreign` FOREIGN KEY (`foundation_id`) REFERENCES `core_foundations` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `fin_payments_guardian_id_foreign` FOREIGN KEY (`guardian_id`) REFERENCES `cbt_orang_tua` (`ortu_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fin_payments_payment_method_id_foreign` FOREIGN KEY (`payment_method_id`) REFERENCES `fin_payment_methods` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fin_payments_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `core_schools` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `fin_payments_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `cbt_user` (`user_id`) ON DELETE RESTRICT;

--
-- Ketidakleluasaan untuk tabel `fin_payment_allocations`
--
ALTER TABLE `fin_payment_allocations`
  ADD CONSTRAINT `fin_payment_allocations_bill_id_foreign` FOREIGN KEY (`bill_id`) REFERENCES `fin_student_bills` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `fin_payment_allocations_payment_id_foreign` FOREIGN KEY (`payment_id`) REFERENCES `fin_payments` (`id`) ON DELETE RESTRICT;

--
-- Ketidakleluasaan untuk tabel `fin_payment_methods`
--
ALTER TABLE `fin_payment_methods`
  ADD CONSTRAINT `fin_payment_methods_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `core_schools` (`id`) ON DELETE RESTRICT;

--
-- Ketidakleluasaan untuk tabel `fin_student_bills`
--
ALTER TABLE `fin_student_bills`
  ADD CONSTRAINT `fin_student_bills_academic_year_id_foreign` FOREIGN KEY (`academic_year_id`) REFERENCES `core_academic_years` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fin_student_bills_foundation_id_foreign` FOREIGN KEY (`foundation_id`) REFERENCES `core_foundations` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `fin_student_bills_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `core_schools` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `fin_student_bills_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `cbt_user` (`user_id`) ON DELETE RESTRICT;

--
-- Ketidakleluasaan untuk tabel `fin_student_bill_items`
--
ALTER TABLE `fin_student_bill_items`
  ADD CONSTRAINT `fin_student_bill_items_bill_id_foreign` FOREIGN KEY (`bill_id`) REFERENCES `fin_student_bills` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `fin_student_bill_items_fee_category_id_foreign` FOREIGN KEY (`fee_category_id`) REFERENCES `fin_fee_categories` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `user`
--
ALTER TABLE `user`
  ADD CONSTRAINT `user_foundation_id_foreign` FOREIGN KEY (`foundation_id`) REFERENCES `core_foundations` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `user_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `core_schools` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `user_menu_user_access`
--
ALTER TABLE `user_menu_user_access`
  ADD CONSTRAINT `user_menu_user_access_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `user_preferences`
--
ALTER TABLE `user_preferences`
  ADD CONSTRAINT `user_preferences_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
