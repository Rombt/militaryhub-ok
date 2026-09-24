SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;

ALTER TABLE `__payment_methods` ADD `max_discount_value` DECIMAL(5,2) NOT NULL DEFAULT '0.00' AFTER `enabled`, ADD INDEX (`max_discount_value`);

COMMIT;
