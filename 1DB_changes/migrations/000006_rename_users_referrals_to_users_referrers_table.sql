SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

RENAME TABLE `mh_users_referrals` TO `mh_users_referrers`;

COMMIT;
