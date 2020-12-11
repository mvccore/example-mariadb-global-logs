DROP DATABASE IF EXISTS `%database_name%`;
CREATE DATABASE IF NOT EXISTS `%database_name%` /*!40100 COLLATE 'utf8mb4_general_ci' */;

USE `%database_name%`;

DROP TABLE IF EXISTS `general_logs`;
CREATE TABLE IF NOT EXISTS `general_logs` (
	`id_general_log` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`file_name` VARCHAR(100) NOT NULL,
	`hash` VARCHAR(40) NOT NULL,
	`lines_count` BIGINT(14) UNSIGNED NULL DEFAULT NULL,
	`file_size` BIGINT(14) UNSIGNED NULL DEFAULT NULL,
	`created` DATETIME NULL DEFAULT NULL,
	`processed` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
	PRIMARY KEY (`id_general_log`) USING BTREE,
	UNIQUE INDEX `hash` (`hash`) USING HASH,
	INDEX `file_name` (`file_name`) USING BTREE,
	INDEX `lines_count` (`lines_count`) USING BTREE,
	INDEX `file_size` (`file_size`) USING BTREE,
	INDEX `created` (`created`) USING BTREE,
	INDEX `processed` (`processed`) USING BTREE
) ENGINE=InnoDB;

DROP TABLE IF EXISTS `bg_processes`;
CREATE TABLE IF NOT EXISTS `bg_processes` (
	`id_bg_process` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`id_general_log` INT(10) UNSIGNED NOT NULL,
	`hash` VARCHAR(64) NULL DEFAULT NULL,
	`progress` DECIMAL(9,6) UNSIGNED NULL DEFAULT NULL,
	`controller` VARCHAR(200) NOT NULL,
	`action` VARCHAR(50) NULL DEFAULT NULL,
	`params` MEDIUMTEXT NULL DEFAULT NULL,
	`created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
	`started` DATETIME NULL DEFAULT NULL,
	`finished` DATETIME NULL DEFAULT NULL,
	`result` TINYINT(1) NOT NULL DEFAULT 0,
	`message` MEDIUMTEXT NULL DEFAULT NULL,
	PRIMARY KEY (`id_bg_process`) USING BTREE,
	UNIQUE INDEX `hash` (`hash`) USING HASH,
	INDEX `id_general_log` (`id_general_log`) USING BTREE,
	INDEX `progress` (`progress`) USING BTREE,
	INDEX `controller` (`controller`) USING BTREE,
	INDEX `action` (`action`) USING BTREE,
	INDEX `created` (`created`) USING BTREE,
	INDEX `started` (`started`) USING BTREE,
	INDEX `finished` (`finished`) USING BTREE,
	INDEX `result` (`result`) USING BTREE,
	CONSTRAINT `FK_bg_processes_general_logs` 
		FOREIGN KEY (`id_general_log`) 
		REFERENCES `general_logs` (`id_general_log`) 
			ON UPDATE RESTRICT 
			ON DELETE RESTRICT
) ENGINE=InnoDB;

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
	`id_user` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`user_name` VARCHAR(100) NOT NULL,
	PRIMARY KEY (`id_user`) USING BTREE,
	UNIQUE INDEX `user_name` (`user_name`) USING BTREE
) ENGINE=InnoDB;

DROP TABLE IF EXISTS `databases`;
CREATE TABLE IF NOT EXISTS `databases` (
	`id_database` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`database_name` VARCHAR(100) NOT NULL,
	PRIMARY KEY (`id_database`) USING BTREE,
	UNIQUE INDEX `database_name` (`database_name`) USING BTREE
) ENGINE=InnoDB;

DROP TABLE IF EXISTS `connections`;
CREATE TABLE IF NOT EXISTS `connections` (
	`id_connection` BIGINT(21) UNSIGNED NOT NULL AUTO_INCREMENT,
	`id_general_log` INT(10) UNSIGNED NOT NULL,
	`id_user` INT(10) UNSIGNED NULL,
	`id_database` INT(10) UNSIGNED NULL,
	`id_thread` BIGINT(21) UNSIGNED NOT NULL,
	`connected` DATETIME NOT NULL,
	`disconnected` DATETIME NULL DEFAULT NULL,
	`requests_count` BIGINT(21) NOT NULL DEFAULT 0,
	`queries_count` BIGINT(21) NOT NULL DEFAULT 0,
	`mark` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
	PRIMARY KEY (`id_connection`) USING BTREE,
	INDEX `id_general_log` (`id_general_log`) USING BTREE,
	INDEX `id_user` (`id_user`) USING BTREE,
	INDEX `id_database` (`id_database`) USING BTREE,
	INDEX `id_thread` (`id_thread`) USING BTREE,
	INDEX `connected` (`connected`) USING BTREE,
	INDEX `disconnected` (`disconnected`) USING BTREE,
	INDEX `request_count` (`requests_count`) USING BTREE,
	INDEX `queries_count` (`queries_count`) USING BTREE,
	INDEX `mark` (`mark`) USING BTREE,
	CONSTRAINT `FK_connections_general_logs` 
		FOREIGN KEY (`id_general_log`) 
		REFERENCES `general_logs` (`id_general_log`) 
			ON UPDATE RESTRICT 
			ON DELETE RESTRICT,
	CONSTRAINT `FK_connections_databases` 
		FOREIGN KEY (`id_database`) 
		REFERENCES `databases` (`id_database`) 
			ON UPDATE RESTRICT 
			ON DELETE RESTRICT,
	CONSTRAINT `FK_connections_users` 
		FOREIGN KEY (`id_user`) 
		REFERENCES `users` (`id_user`) 
			ON UPDATE RESTRICT 
			ON DELETE RESTRICT
) ENGINE=InnoDB;

DROP TABLE IF EXISTS `query_types`;
CREATE TABLE IF NOT EXISTS `query_types` (
	`id_query_type` SMALLINT(3) UNSIGNED NOT NULL AUTO_INCREMENT,
	`query_type_name` VARCHAR(50) NULL DEFAULT NULL,
	PRIMARY KEY (`id_query_type`) USING BTREE,
	INDEX `query_type_name` (`query_type_name`) USING BTREE
) ENGINE=InnoDB;

DROP TABLE IF EXISTS `queries`;
CREATE TABLE IF NOT EXISTS `queries` (
	`id_query` BIGINT(21) UNSIGNED NOT NULL AUTO_INCREMENT,
	`id_connection` BIGINT(21) UNSIGNED NOT NULL,
	`id_query_type` SMALLINT(3) UNSIGNED NULL DEFAULT NULL,
	`request_number` BIGINT(13) UNSIGNED NOT NULL DEFAULT 0,
	`executed` DATETIME NOT NULL,
	`source_line_begin` BIGINT(13) UNSIGNED NOT NULL,
	`source_line_end` BIGINT(13) UNSIGNED NOT NULL,
	`query_text` MEDIUMTEXT NULL DEFAULT NULL,
	`mark` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
	PRIMARY KEY (`id_query`) USING BTREE,
	INDEX `request_number` (`request_number`) USING BTREE,
	INDEX `id_connection` (`id_connection`) USING BTREE,
	INDEX `id_query_type` (`id_query_type`) USING BTREE,
	INDEX `executed` (`executed`) USING BTREE,
	FULLTEXT INDEX `query_text` (`query_text`),
	INDEX `mark` (`mark`) USING BTREE,
	CONSTRAINT `FK_queries_connections` 
		FOREIGN KEY (`id_connection`) 
		REFERENCES `connections` (`id_connection`) 
			ON UPDATE RESTRICT 
			ON DELETE RESTRICT,
	CONSTRAINT `FK_queries_query_types` 
		FOREIGN KEY (`id_query_type`) 
		REFERENCES `query_types` (`id_query_type`) 
			ON UPDATE RESTRICT 
			ON DELETE RESTRICT
) ENGINE=InnoDB;

INSERT INTO `query_types` (`query_type_name`) VALUES 
('select'),('set'),('begin'),('start'),('commit'),('rollback'),('call'),('prepare'),('execute'),
('insert'),('update'),('delete'),('truncate'),('create'),('drop'),('deallocate'),('alter'),
('show'),('purge'),('describe'),('flush'),('kill'),('load'),('reset'),('shutdown'),('use'),
('cache'),('backup'),('analyze'),('explain');