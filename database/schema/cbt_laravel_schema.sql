-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 02 Nov 2026 pada 05.41
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

-- Data for `cbt_absensi` intentionally omitted from the distributable schema.

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

-- Data for `cbt_jawaban` intentionally omitted from the distributable schema.

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

-- Data for `cbt_konfigurasi` intentionally omitted from the distributable schema.

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

-- Data for `cbt_modul` intentionally omitted from the distributable schema.

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

-- Data for `cbt_orang_tua` intentionally omitted from the distributable schema.

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

-- Data for `cbt_orang_tua_peserta` intentionally omitted from the distributable schema.

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

-- Data for `cbt_pembayaran` intentionally omitted from the distributable schema.

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

-- Data for `cbt_raport` intentionally omitted from the distributable schema.

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

-- Data for `cbt_soal` intentionally omitted from the distributable schema.

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
  `tes_biaya` decimal(12,2) NOT NULL DEFAULT '0.00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `cbt_tes`
--

-- Data for `cbt_tes` intentionally omitted from the distributable schema.

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

-- Data for `cbt_tesgrup` intentionally omitted from the distributable schema.

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

-- Data for `cbt_tes_soal` intentionally omitted from the distributable schema.

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

-- Data for `cbt_tes_soal_jawaban` intentionally omitted from the distributable schema.

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

-- Data for `cbt_tes_token` intentionally omitted from the distributable schema.

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

-- Data for `cbt_tes_topik_set` intentionally omitted from the distributable schema.

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

-- Data for `cbt_tes_user` intentionally omitted from the distributable schema.

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

-- Data for `cbt_topik` intentionally omitted from the distributable schema.

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

-- Data for `cbt_user` intentionally omitted from the distributable schema.

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

-- Data for `cbt_user_grup` intentionally omitted from the distributable schema.

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
(15, '2024_01_01_000013_create_enterprise_tenant_tables', 9);

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

-- Data for `user` intentionally omitted from the distributable schema.

-- --------------------------------------------------------

--
-- Struktur dari tabel `user_akses`
--

CREATE TABLE `user_akses` (
  `id` bigint UNSIGNED NOT NULL,
  `level` varchar(75) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kode_menu` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `add` tinyint(1) NOT NULL DEFAULT '1',
  `edit` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `user_akses`
--

-- Data for `user_akses` intentionally omitted from the distributable schema.

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

-- Data for `user_level` intentionally omitted from the distributable schema.

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

-- Data for `user_log` intentionally omitted from the distributable schema.

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

-- Data for `user_menu` intentionally omitted from the distributable schema.

--
-- Indeks untuk tabel yang dibuang
--

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
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `cbt_absensi`
--
ALTER TABLE `cbt_absensi`
  MODIFY `absensi_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

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
  MODIFY `modul_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT untuk tabel `cbt_orang_tua`
--
ALTER TABLE `cbt_orang_tua`
  MODIFY `ortu_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `cbt_pembayaran`
--
ALTER TABLE `cbt_pembayaran`
  MODIFY `pembayaran_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

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
  MODIFY `tessoal_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=181;

--
-- AUTO_INCREMENT untuk tabel `cbt_tes_token`
--
ALTER TABLE `cbt_tes_token`
  MODIFY `token_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT untuk tabel `cbt_tes_topik_set`
--
ALTER TABLE `cbt_tes_topik_set`
  MODIFY `tset_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT untuk tabel `cbt_tes_user`
--
ALTER TABLE `cbt_tes_user`
  MODIFY `tesuser_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT untuk tabel `cbt_topik`
--
ALTER TABLE `cbt_topik`
  MODIFY `topik_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

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
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `core_foundations`
--
ALTER TABLE `core_foundations`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `core_schools`
--
ALTER TABLE `core_schools`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `core_school_levels`
--
ALTER TABLE `core_school_levels`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `core_semesters`
--
ALTER TABLE `core_semesters`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT untuk tabel `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `user`
--
ALTER TABLE `user`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT untuk tabel `user_akses`
--
ALTER TABLE `user_akses`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=332;

--
-- AUTO_INCREMENT untuk tabel `user_level`
--
ALTER TABLE `user_level`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT untuk tabel `user_log`
--
ALTER TABLE `user_log`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `user_menu`
--
ALTER TABLE `user_menu`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=108;

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
-- Ketidakleluasaan untuk tabel `user`
--
ALTER TABLE `user`
  ADD CONSTRAINT `user_foundation_id_foreign` FOREIGN KEY (`foundation_id`) REFERENCES `core_foundations` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `user_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `core_schools` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
