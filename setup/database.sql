CREATE DATABASE IF NOT EXISTS slobshop;

CREATE TABLE IF NOT EXISTS `lists`
(
  `id`         INT(11)  AUTO_INCREMENT,
  `name`       VARCHAR(100) NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
)
  ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `items`
(
  `id`         INT(11)  AUTO_INCREMENT,
  `list_id`    INT(11)      NOT NULL,
  `name`       VARCHAR(100) NOT NULL,
  `data`       BLOB         NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY item_list_id (`list_id`) REFERENCES `lists` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
)
  ENGINE = InnoDB;

ALTER TABLE `items` ADD COLUMN IF NOT EXISTS `checked` BOOLEAN NOT NULL DEFAULT FALSE;