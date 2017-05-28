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
  FOREIGN KEY item_list_id (`list_id`) REFERENCES `lists` (`id`)
    ON UPDATE CASCADE
    ON DELETE CASCADE
)
  ENGINE = InnoDB;

ALTER TABLE `items`
  ADD COLUMN IF NOT EXISTS `checked` BOOLEAN NOT NULL DEFAULT FALSE;
ALTER TABLE `lists`
  ADD COLUMN IF NOT EXISTS `checked` BOOLEAN NOT NULL DEFAULT FALSE;

ALTER TABLE `items`
  ADD COLUMN IF NOT EXISTS `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE `lists`
  ADD COLUMN IF NOT EXISTS `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP;

DELIMITER $$

CREATE
TRIGGER `lists_after_update`
BEFORE UPDATE
  ON `lists`
FOR EACH ROW
  BEGIN
    SET NEW.updated_at = NOW();
  END$$

DELIMITER ;

DELIMITER $$
CREATE
TRIGGER `items_after_insert`
AFTER INSERT
  ON `items`
FOR EACH ROW
  BEGIN
    UPDATE `lists`
    SET updated_at = NOW()
    WHERE id = NEW.list_id;
  END$$

DELIMITER ;

DELIMITER $$

CREATE
TRIGGER `items_after_update`
BEFORE UPDATE
  ON `items`
FOR EACH ROW
  BEGIN
    UPDATE `lists`
    SET updated_at = NOW()
    WHERE id = NEW.list_id;

    SET NEW.updated_at = NOW();

  END$$

DELIMITER ;

DELIMITER $$

CREATE
TRIGGER `items_before_delete`
BEFORE DELETE
  ON `items`
FOR EACH ROW
  BEGIN
    UPDATE `lists`
    SET updated_at = NOW()
    WHERE id = OLD.list_id;

  END$$

DELIMITER ;

ALTER TABLE `lists` ADD COLUMN identifier CHAR(36) NOT NULL AFTER `id`;
ALTER TABLE `items` ADD COLUMN identifier CHAR(36) NOT NULL AFTER `id`;

DELIMITER $$
CREATE
TRIGGER `lists_before_insert`
BEFORE INSERT
  ON `lists`
FOR EACH ROW
BEGIN
  IF LENGTH(new.identifier) <= 0
  THEN
    SET new.identifier = (SELECT uuid());
  END IF;
  END$$

DELIMITER ;

DELIMITER $$
CREATE
TRIGGER `items_before_insert`
BEFORE INSERT
  ON `items`
FOR EACH ROW
BEGIN
  IF LENGTH(new.identifier) <= 0
  THEN
    SET new.identifier = (SELECT uuid());
  END IF;
  END$$

DELIMITER ;