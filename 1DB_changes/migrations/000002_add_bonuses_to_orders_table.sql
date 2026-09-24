SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

alter table __orders add bonuses DECIMAL(14,2) default NULL null;

COMMIT;
