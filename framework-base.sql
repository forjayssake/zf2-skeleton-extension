
# This is a fix for InnoDB in MySQL >= 4.1.x
# It "suspends judgement" for fkey relationships until are tables are set.
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `accessmanagementlogs`;

CREATE TABLE `accessmanagementlogs`
(
   `id` INTEGER NOT NULL AUTO_INCREMENT,
   `_user_id` INTEGER NOT NULL,
   `config` TEXT(10) NOT NULL,
	`created_at` DATETIME,
    `updated_at` DATETIME,
    PRIMARY KEY (`id`),
    INDEX `users_fi_6f6176` (`_user_id`)
) ENGINE=InnoDB CHARACTER SET='utf8' COLLATE='utf8_general_ci';

-- ---------------------------------------------------------------------
-- users
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `users`;

CREATE TABLE `users`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `_role_id` INTEGER NOT NULL,
    `title` VARCHAR(10) NOT NULL,
    `firstName` VARCHAR(255) NOT NULL,
    `lastName` VARCHAR(255) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `username` VARCHAR(255) NOT NULL,
    `authenticationType` TINYINT(1) DEFAULT 0 NOT NULL,
    `systemAdministrator` TINYINT(1) DEFAULT 0 NOT NULL,
    `created_at` DATETIME,
    `updated_at` DATETIME,
    PRIMARY KEY (`id`),
    UNIQUE INDEX `EmailUnique` (`email`(100)),
    UNIQUE INDEX `UsernameUnique` (`username`),
    INDEX `users_fi_6f6176` (`_role_id`)
) ENGINE=InnoDB CHARACTER SET='utf8' COLLATE='utf8_general_ci';


INSERT INTO `users` (`id`, `_role_id`, `title`, `firstName`, `lastName`, `password`, `email`, `username`, `authenticationType`, `systemAdministrator`, `created_at`, `updated_at`) VALUES
(1, 1, 'Mr', 'Test', 'User', '$2y$10$6OwSBW2FpGQ25QWRm4ylvO38Tld1osoz1J8rPj1xCpBxQXcr2ARJq', 'testuser@example.org', 'testuser', 0, 1, '2016-02-22 09:39:44', '2016-01-08 00:00:00');

-- ---------------------------------------------------------------------
-- roles
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `roles`;

CREATE TABLE `roles`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `_role_id` INTEGER COMMENT 'Parent Role',
    `name` VARCHAR(100) NOT NULL,
    `isDefault` TINYINT(1) DEFAULT 0 COMMENT 'Default Role for Users (only 1 default should exist) ',
    `description` TEXT,
    `constant` VARCHAR(100),
    PRIMARY KEY (`id`),
    UNIQUE INDEX `ConstantUnique` (`constant`(100)),
    INDEX `roles_fi_6f6176` (`_role_id`)
) ENGINE=InnoDB CHARACTER SET='utf8' COLLATE='utf8_general_ci';


INSERT INTO `roles` (`id`, `_role_id`, `name`, `isDefault`, `description`, `constant`) VALUES
(1, NULL, 'Guest', 1, 'Basic account', 'GUEST');

-- ---------------------------------------------------------------------
-- auditlogs
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `auditlogs`;

CREATE TABLE `auditlogs`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `_user_id` INTEGER NOT NULL,
    `object` VARCHAR(100) NOT NULL,
    `objectId` INTEGER NOT NULL,
    `data` TEXT NOT NULL,
    `created_at` DATETIME,
    `updated_at` DATETIME,
    PRIMARY KEY (`id`),
    INDEX `auditlogs_fi_da4e87` (`_user_id`)
) ENGINE=InnoDB CHARACTER SET='utf8' COLLATE='utf8_general_ci';

-- ---------------------------------------------------------------------
-- settings
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `settings`;

CREATE TABLE `settings`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `valueInt` INTEGER,
    `valueString` TEXT,
    `valueFloat` DECIMAL,
    `valueBool` TINYINT(1) DEFAULT 0,
    `created_at` DATETIME,
    `updated_at` DATETIME,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB CHARACTER SET='utf8' COLLATE='utf8_general_ci';

-- ---------------------------------------------------------------------
-- templates
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `templates`;

CREATE TABLE `templates`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `event` VARCHAR(255) NOT NULL,
    `subject` VARCHAR(255) NOT NULL,
    `body` TEXT NOT NULL,
    `created_at` DATETIME,
    `updated_at` DATETIME,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB CHARACTER SET='utf8' COLLATE='utf8_general_ci';

-- ---------------------------------------------------------------------
-- emaillogs
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `emaillogs`;

CREATE TABLE `emaillogs`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `status` TINYINT(1) DEFAULT 0 NOT NULL,
    `subject` VARCHAR(255) NOT NULL,
    `from` TEXT NOT NULL,
    `to` TEXT NOT NULL,
    `body` TEXT NOT NULL,
    `created_at` DATETIME,
    `updated_at` DATETIME,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB CHARACTER SET='utf8' COLLATE='utf8_general_ci';

-- ---------------------------------------------------------------------
-- users_archive
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `users_archive`;

CREATE TABLE `users_archive`
(
    `id` INTEGER NOT NULL,
    `_role_id` INTEGER NOT NULL,
    `title` VARCHAR(10) NOT NULL,
    `firstName` VARCHAR(255) NOT NULL,
    `lastName` VARCHAR(255) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `username` VARCHAR(255) NOT NULL,
    `authenticationType` TINYINT(1) DEFAULT 0 NOT NULL,
    `systemAdministrator` TINYINT(1) DEFAULT 0 NOT NULL,
    `created_at` DATETIME,
    `updated_at` DATETIME,
    `archived_at` DATETIME,
    PRIMARY KEY (`id`),
    INDEX `users_fi_6f6176` (`_role_id`),
    INDEX `users_archive_i_d80cda` (`email`(100)),
    INDEX `users_archive_i_f86ef3` (`username`)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- templates_archive
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `templates_archive`;

CREATE TABLE `templates_archive`
(
    `id` INTEGER NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `event` VARCHAR(255) NOT NULL,
    `subject` VARCHAR(255) NOT NULL,
    `body` TEXT NOT NULL,
    `created_at` DATETIME,
    `updated_at` DATETIME,
    `archived_at` DATETIME,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB;

# This restores the fkey checks, after having unset them earlier
SET FOREIGN_KEY_CHECKS = 1;
