SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

ALTER TABLE `sfly_users` ADD `birthday` DATE NULL DEFAULT NULL AFTER `address`;
ALTER TABLE `sfly_users` ADD INDEX(`birthday`);

COMMIT;
