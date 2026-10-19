-- UMP Event Booking — database schema
-- Works on MariaDB 10.4+ (XAMPP) and MySQL 8+.
-- Import in phpMyAdmin, or run:  php bin/setup.php

CREATE DATABASE IF NOT EXISTS `ump_event_booking`
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `ump_event_booking`;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `login_attempts`;
DROP TABLE IF EXISTS `bookings`;
DROP TABLE IF EXISTS `events`;
DROP TABLE IF EXISTS `users`;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE `users` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `full_name`     VARCHAR(100) NOT NULL,
  `email`         VARCHAR(120) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `role`          ENUM('user', 'admin') NOT NULL DEFAULT 'user',
  `created_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `events` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title`       VARCHAR(150) NOT NULL,
  `slug`        VARCHAR(170) NOT NULL,
  `description` TEXT NOT NULL,
  `category`    VARCHAR(30) NOT NULL DEFAULT 'social',
  `venue`       VARCHAR(120) NOT NULL,
  `starts_at`   DATETIME NOT NULL,
  `ends_at`     DATETIME NULL,
  `capacity`    INT UNSIGNED NOT NULL DEFAULT 100,
  `image_path`  VARCHAR(255) NULL,
  `status`      ENUM('published', 'draft', 'cancelled') NOT NULL DEFAULT 'published',
  `created_by`  INT UNSIGNED NULL,
  `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `events_slug_unique` (`slug`),
  KEY `events_status_starts_at_index` (`status`, `starts_at`),
  CONSTRAINT `events_created_by_fk` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `events_capacity_check` CHECK (`capacity` > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `bookings` (
  `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `reference`    CHAR(12) NOT NULL,
  `user_id`      INT UNSIGNED NOT NULL,
  `event_id`     INT UNSIGNED NOT NULL,
  `quantity`     TINYINT UNSIGNED NOT NULL DEFAULT 1,
  `status`       ENUM('confirmed', 'cancelled') NOT NULL DEFAULT 'confirmed',
  `created_at`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `cancelled_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `bookings_reference_unique` (`reference`),
  -- One booking row per user per event (re-booking re-activates it) — the database
  -- itself prevents duplicates even if two requests race.
  UNIQUE KEY `bookings_user_event_unique` (`user_id`, `event_id`),
  KEY `bookings_event_status_index` (`event_id`, `status`),
  CONSTRAINT `bookings_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `bookings_event_fk` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  CONSTRAINT `bookings_quantity_check` CHECK (`quantity` BETWEEN 1 AND 10)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Used to throttle repeated failed sign-ins (brute-force protection).
CREATE TABLE `login_attempts` (
  `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `email`        VARCHAR(120) NOT NULL,
  `ip_address`   VARCHAR(45) NOT NULL,
  `attempted_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `login_attempts_lookup_index` (`email`, `ip_address`, `attempted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
