-- phpMyAdmin SQL Dump
-- version 5.0.1
-- https://www.phpmyadmin.net/
--
-- Хост: localhost
-- Час створення: Січ 30 2020 р., 12:14
-- Версія сервера: 5.7.27-0ubuntu0.18.04.1-log
-- Версія PHP: 7.2.24-0ubuntu0.18.04.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База даних: `admin_sfly`
--

-- --------------------------------------------------------

--
-- Структура таблиці `sfly_addresses_justin`
--

CREATE TABLE IF NOT EXISTS `sfly_addresses_justin` (
  `id` bigint(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `external_id` varchar(255) NOT NULL,
  `code` varchar(255) NOT NULL,
  `region` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `region_external_id` varchar(255) NOT NULL,
  `city` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `city_external_id` varchar(255) NOT NULL,
  `street` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `street_external_id` varchar(255) NOT NULL,
  `house` varchar(255) NOT NULL,
  `weight_limit` decimal(10,2) NOT NULL DEFAULT '0.00',
  `lat` decimal(10,6) NOT NULL,
  `lng` decimal(10,6) NOT NULL,
  `data` longtext NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `external_id` (`external_id`(191)),
  KEY `region_external_id` (`region_external_id`(191)),
  KEY `city_external_id` (`city_external_id`(191)),
  KEY `street_external_id` (`street_external_id`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Структура таблиці `sfly_addresses_novaposhta`
--

CREATE TABLE IF NOT EXISTS `sfly_addresses_novaposhta` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `sitekey` bigint(20) NOT NULL,
  `number` bigint(20) NOT NULL,
  `city_ref` varchar(255) NOT NULL,
  `city_description` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `ware_ref` varchar(255) NOT NULL,
  `ware_description` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `enabled` tinyint(1) UNSIGNED NOT NULL,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `data` longtext,
  PRIMARY KEY (`id`),
  KEY `city_ref` (`city_ref`),
  KEY `ware_ref` (`ware_ref`),
  KEY `sitekey` (`sitekey`),
  KEY `number` (`number`)
) ENGINE=InnoDB AUTO_INCREMENT=6474 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблиці `sfly_banners`
--

CREATE TABLE IF NOT EXISTS `sfly_banners` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_id` varchar(32) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL DEFAULT '',
  `position` int(11) NOT NULL DEFAULT '0',
  `visible` tinyint(1) NOT NULL DEFAULT '1',
  `show_all_pages` tinyint(1) NOT NULL DEFAULT '0',
  `categories` varchar(200) NOT NULL DEFAULT '0',
  `pages` varchar(200) NOT NULL DEFAULT '0',
  `brands` varchar(200) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `position` (`position`),
  KEY `visible` (`visible`),
  KEY `show_all_pages` (`show_all_pages`),
  KEY `category` (`categories`),
  KEY `pages` (`pages`),
  KEY `brands` (`brands`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблиці `sfly_banners_images`
--

CREATE TABLE IF NOT EXISTS `sfly_banners_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `banner_id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL DEFAULT '',
  `alt` varchar(255) NOT NULL DEFAULT '',
  `title` varchar(255) NOT NULL DEFAULT '',
  `annotation` text NOT NULL,
  `description` text NOT NULL,
  `url` varchar(255) NOT NULL DEFAULT '',
  `image` varchar(255) NOT NULL DEFAULT '',
  `position` int(11) NOT NULL DEFAULT '0',
  `visible` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `position` (`position`),
  KEY `visible` (`visible`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблиці `sfly_blog`
--

CREATE TABLE IF NOT EXISTS `sfly_blog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(512) NOT NULL DEFAULT '',
  `url` varchar(255) NOT NULL DEFAULT '',
  `meta_title` varchar(512) NOT NULL DEFAULT '',
  `meta_keywords` varchar(512) NOT NULL DEFAULT '',
  `meta_description` varchar(512) NOT NULL DEFAULT '',
  `annotation` text NOT NULL,
  `description` text NOT NULL,
  `visible` tinyint(1) NOT NULL DEFAULT '0',
  `date` timestamp NULL DEFAULT NULL,
  `image` varchar(255) NOT NULL DEFAULT '',
  `last_modify` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `type_post` enum('blog','news') NOT NULL DEFAULT 'blog',
  `featured` tinyint(1) DEFAULT '0',
  `rating` float(3,1) DEFAULT '0.0',
  `votes` int(11) DEFAULT '0',
  `views` int(11) DEFAULT '0',
  `author` varchar(60) NOT NULL DEFAULT 'Author',
  PRIMARY KEY (`id`),
  KEY `enabled` (`visible`),
  KEY `url` (`url`),
  KEY `featured` (`featured`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблиці `sfly_brands`
--

CREATE TABLE IF NOT EXISTS `sfly_brands` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `url` varchar(255) NOT NULL DEFAULT '',
  `meta_title` varchar(512) NOT NULL DEFAULT '',
  `meta_keywords` varchar(512) NOT NULL DEFAULT '',
  `meta_description` varchar(512) NOT NULL DEFAULT '',
  `annotation` text,
  `description` text,
  `image` varchar(255) DEFAULT NULL,
  `last_modify` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `position` int(11) NOT NULL DEFAULT '0',
  `visible` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `name` (`name`),
  KEY `url` (`url`)
) ENGINE=InnoDB AUTO_INCREMENT=59 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблиці `sfly_callbacks`
--

CREATE TABLE IF NOT EXISTS `sfly_callbacks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `name` varchar(255) NOT NULL DEFAULT '',
  `phone` varchar(255) NOT NULL DEFAULT '',
  `message` text,
  `processed` tinyint(1) NOT NULL DEFAULT '0',
  `url` varchar(255) NOT NULL DEFAULT '',
  `admin_notes` varchar(1024) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблиці `sfly_cart`
--

CREATE TABLE IF NOT EXISTS `sfly_cart` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(11) UNSIGNED NOT NULL,
  `variant_id` int(11) UNSIGNED NOT NULL,
  `amount` int(11) UNSIGNED NOT NULL DEFAULT '1',
  `last_modify` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `active` tinyint(1) UNSIGNED NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `user_id` (`user_id`),
  KEY `variant_id` (`variant_id`)
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблиці `sfly_categories`
--

CREATE TABLE IF NOT EXISTS `sfly_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL DEFAULT '',
  `name_h1` varchar(255) NOT NULL DEFAULT '',
  `yandex_name` varchar(255) NOT NULL DEFAULT '',
  `meta_title` varchar(512) NOT NULL DEFAULT '',
  `meta_keywords` varchar(512) NOT NULL DEFAULT '',
  `meta_description` varchar(512) NOT NULL DEFAULT '',
  `annotation` text,
  `description` text,
  `url` varchar(255) NOT NULL DEFAULT '',
  `image` varchar(255) NOT NULL DEFAULT '',
  `position` int(11) NOT NULL DEFAULT '0',
  `visible` tinyint(1) NOT NULL DEFAULT '1',
  `external_id` varchar(36) NOT NULL DEFAULT '',
  `level_depth` tinyint(1) NOT NULL DEFAULT '1',
  `auto_meta_title` varchar(512) NOT NULL DEFAULT '',
  `auto_meta_keywords` varchar(512) NOT NULL DEFAULT '',
  `auto_meta_desc` varchar(512) NOT NULL DEFAULT '',
  `auto_description` text,
  `last_modify` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created` timestamp NULL DEFAULT NULL,
  `attach_brand_id` varchar(100) NOT NULL DEFAULT '',
  `parent_menu_name` varchar(255) NOT NULL DEFAULT '',
  `google_name` varchar(255) NOT NULL DEFAULT '',
  `rozetka_name` varchar(255) NOT NULL DEFAULT '',
  `prom_category` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `url` (`url`),
  KEY `parent_id` (`parent_id`),
  KEY `position` (`position`),
  KEY `visible` (`visible`),
  KEY `external_id` (`external_id`),
  KEY `created` (`created`)
) ENGINE=InnoDB AUTO_INCREMENT=197 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблиці `sfly_categories_features`
--

CREATE TABLE IF NOT EXISTS `sfly_categories_features` (
  `category_id` int(11) NOT NULL,
  `feature_id` int(11) NOT NULL,
  PRIMARY KEY (`category_id`,`feature_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблиці `sfly_comments`
--

CREATE TABLE IF NOT EXISTS `sfly_comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT '0',
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ip` varchar(20) NOT NULL DEFAULT '',
  `object_id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL DEFAULT '',
  `email` varchar(255) NOT NULL DEFAULT '',
  `text` text NOT NULL,
  `type` enum('product','blog','news','page') NOT NULL DEFAULT 'product',
  `approved` int(1) NOT NULL DEFAULT '0',
  `lang_id` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `product_id` (`object_id`),
  KEY `type` (`type`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблиці `sfly_coupons`
--

CREATE TABLE IF NOT EXISTS `sfly_coupons` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(256) NOT NULL DEFAULT '',
  `expire` timestamp NULL DEFAULT NULL,
  `type` enum('absolute','percentage') NOT NULL DEFAULT 'absolute',
  `value` decimal(10,2) NOT NULL DEFAULT '0.00',
  `min_order_price` decimal(10,2) DEFAULT NULL,
  `single` tinyint(1) NOT NULL DEFAULT '0',
  `usages` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблиці `sfly_currencies`
--

CREATE TABLE IF NOT EXISTS `sfly_currencies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `sign` varchar(20) NOT NULL DEFAULT '',
  `code` char(3) NOT NULL DEFAULT '',
  `rate_from` decimal(10,2) NOT NULL DEFAULT '1.00',
  `rate_to` decimal(10,2) NOT NULL DEFAULT '1.00',
  `cents` int(1) NOT NULL DEFAULT '2',
  `position` int(11) NOT NULL DEFAULT '0',
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `position` (`position`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

--
-- Дамп даних таблиці `sfly_currencies`
--

INSERT INTO `sfly_currencies` (`id`, `name`, `sign`, `code`, `rate_from`, `rate_to`, `cents`, `position`, `enabled`) VALUES
(1, '', '', 'USD', '0.12', '3.32', 2, 3, 1),
(2, '', '', 'RUR', '1.00', '0.45', 0, 2, 1),
(4, 'гривны uk', 'грн', 'UAH', '0.06', '0.06', 0, 1, 1);

-- --------------------------------------------------------

--
-- Структура таблиці `sfly_delivery`
--

CREATE TABLE IF NOT EXISTS `sfly_delivery` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `free_from` decimal(10,2) NOT NULL DEFAULT '0.00',
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `enabled` tinyint(1) NOT NULL DEFAULT '0',
  `position` int(11) NOT NULL DEFAULT '0',
  `separate_payment` tinyint(1) DEFAULT '0',
  `image` varchar(255) NOT NULL DEFAULT '',
  `method` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `position` (`position`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблиці `sfly_delivery_payment`
--

CREATE TABLE IF NOT EXISTS `sfly_delivery_payment` (
  `delivery_id` int(11) NOT NULL,
  `payment_method_id` int(11) NOT NULL,
  PRIMARY KEY (`delivery_id`,`payment_method_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Связка способом оплаты и способов доставки';

-- --------------------------------------------------------

--
-- Структура таблиці `sfly_esputnik_messages`
--

CREATE TABLE IF NOT EXISTS `sfly_esputnik_messages` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `object` varchar(255) NOT NULL,
  `object_id` bigint(20) NOT NULL,
  `request_id` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL,
  `json` longtext NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `object` (`object`,`object_id`)
) ENGINE=InnoDB AUTO_INCREMENT=132 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблиці `sfly_features`
--

CREATE TABLE IF NOT EXISTS `sfly_features` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `position` int(11) NOT NULL DEFAULT '0',
  `in_filter` tinyint(1) DEFAULT '0',
  `yandex` tinyint(1) NOT NULL DEFAULT '1',
  `auto_name_id` varchar(64) NOT NULL DEFAULT '',
  `auto_value_id` varchar(64) NOT NULL DEFAULT '',
  `url` varchar(255) NOT NULL DEFAULT '',
  `external_id` varchar(36) NOT NULL DEFAULT '',
  `url_in_product` tinyint(1) DEFAULT '0',
  `to_index_new_value` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `position` (`position`),
  KEY `in_filter` (`in_filter`),
  KEY `yandex` (`yandex`)
) ENGINE=InnoDB AUTO_INCREMENT=107 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблиці `sfly_features_aliases`
--

CREATE TABLE IF NOT EXISTS `sfly_features_aliases` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `variable` varchar(255) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL,
  `position` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `variable` (`variable`),
  KEY `position` (`position`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблиці `sfly_features_aliases_values`
--

CREATE TABLE IF NOT EXISTS `sfly_features_aliases_values` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `feature_alias_id` int(11) NOT NULL,
  `value` varchar(255) NOT NULL DEFAULT '',
  `feature_id` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `feature_id` (`feature_id`),
  KEY `feature_alias_id` (`feature_alias_id`),
  KEY `value` (`value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблиці `sfly_features_values`
--

CREATE TABLE IF NOT EXISTS `sfly_features_values` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `feature_id` int(11) NOT NULL,
  `value` varchar(1024) NOT NULL DEFAULT '',
  `translit` varchar(255) NOT NULL DEFAULT '',
  `position` int(11) NOT NULL DEFAULT '0',
  `to_index` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `feature_id_translit` (`feature_id`,`translit`),
  KEY `feature_id` (`feature_id`),
  KEY `position` (`position`),
  KEY `value` (`value`(64))
) ENGINE=InnoDB AUTO_INCREMENT=10490 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблиці `sfly_feedbacks`
--

CREATE TABLE IF NOT EXISTS `sfly_feedbacks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ip` varchar(20) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL DEFAULT '',
  `email` varchar(255) NOT NULL DEFAULT '',
  `message` text NOT NULL,
  `processed` tinyint(1) NOT NULL DEFAULT '0',
  `lang_id` int(11) NOT NULL DEFAULT '0',
  `is_admin` tinyint(1) NOT NULL DEFAULT '0',
  `parent_id` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) UNSIGNED DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблиці `sfly_groups`
--

CREATE TABLE IF NOT EXISTS `sfly_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `discount` decimal(5,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблиці `sfly_images`
--

CREATE TABLE IF NOT EXISTS `sfly_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `product_id` int(11) NOT NULL DEFAULT '0',
  `variant_id` int(11) NOT NULL DEFAULT '0',
  `filename` varchar(255) NOT NULL DEFAULT '',
  `position` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `filename` (`filename`),
  KEY `product_id` (`product_id`),
  KEY `position` (`position`),
  KEY `variant_id` (`variant_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9662 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблиці `sfly_import_log`
--

CREATE TABLE IF NOT EXISTS `sfly_import_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `status` varchar(8) NOT NULL DEFAULT '',
  `product_name` varchar(255) NOT NULL DEFAULT '',
  `variant_name` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  KEY `status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=11944 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблиці `sfly_labels`
--

CREATE TABLE IF NOT EXISTS `sfly_labels` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `color` varchar(6) NOT NULL DEFAULT '',
  `position` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

--
-- Дамп даних таблиці `sfly_labels`
--

INSERT INTO `sfly_labels` (`id`, `name`, `color`, `position`) VALUES
(1, 'Перезвонить', 'ff00ff', 1),
(2, 'Ожидается товар', '00d5fa', 2),
(3, 'Тест', 'a3a3a3', 3),
(4, 'Ожидает отправки', '6de01b', 4),
(5, 'Отправлен', '00a64d', 5),
(6, 'Выполнен', '616161', 6);

-- --------------------------------------------------------

--
-- Структура таблиці `sfly_languages`
--

CREATE TABLE IF NOT EXISTS `sfly_languages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `label` varchar(10) NOT NULL,
  `href_lang` varchar(10) NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT '0',
  `position` int(11) NOT NULL DEFAULT '0',
  `name_ru` varchar(255) NOT NULL DEFAULT '',
  `name_ua` varchar(255) NOT NULL DEFAULT '',
  `name_en` varchar(255) NOT NULL DEFAULT '',
  `name_pl` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

--
-- Дамп даних таблиці `sfly_languages`
--

INSERT INTO `sfly_languages` (`id`, `name`, `label`, `href_lang`, `enabled`, `position`, `name_ru`, `name_ua`, `name_en`, `name_pl`) VALUES
(3, 'Украинский', 'ua', 'uk', 1, 1, 'Украинский', 'Українська', 'Ukrainian', 'Украинский'),
(4, 'Russian', 'ru', 'ru', 1, 4, 'Russian', 'Russian', '', '');

-- --------------------------------------------------------

--
-- Структура таблиці `sfly_lang_banners_images`
--

CREATE TABLE IF NOT EXISTS `sfly_lang_banners_images` (
  `lang_id` int(11) NOT NULL,
  `banner_image_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL DEFAULT '',
  `alt` varchar(255) NOT NULL DEFAULT '',
  `title` varchar(255) NOT NULL DEFAULT '',
  `url` varchar(255) NOT NULL DEFAULT '',
  `annotation` text NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY (`lang_id`,`banner_image_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблиці `sfly_lang_blog`
--

CREATE TABLE IF NOT EXISTS `sfly_lang_blog` (
  `lang_id` int(11) NOT NULL,
  `blog_id` int(11) NOT NULL,
  `name` varchar(512) NOT NULL DEFAULT '',
  `meta_title` varchar(512) NOT NULL DEFAULT '',
  `meta_keywords` varchar(512) NOT NULL DEFAULT '',
  `meta_description` varchar(512) NOT NULL DEFAULT '',
  `annotation` text NOT NULL,
  `description` text NOT NULL,
  UNIQUE KEY `lang_id` (`lang_id`,`blog_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблиці `sfly_lang_brands`
--

CREATE TABLE IF NOT EXISTS `sfly_lang_brands` (
  `lang_id` int(11) NOT NULL,
  `brand_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL DEFAULT '',
  `meta_title` varchar(512) NOT NULL DEFAULT '',
  `meta_keywords` varchar(512) NOT NULL DEFAULT '',
  `meta_description` varchar(512) NOT NULL DEFAULT '',
  `annotation` text,
  `description` text,
  UNIQUE KEY `lang_id` (`lang_id`,`brand_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблиці `sfly_lang_categories`
--

CREATE TABLE IF NOT EXISTS `sfly_lang_categories` (
  `lang_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL DEFAULT '',
  `name_h1` varchar(255) NOT NULL DEFAULT '',
  `meta_title` varchar(512) NOT NULL DEFAULT '',
  `meta_keywords` varchar(512) NOT NULL DEFAULT '',
  `meta_description` varchar(512) NOT NULL DEFAULT '',
  `annotation` text,
  `description` text,
  `auto_meta_title` varchar(512) NOT NULL DEFAULT '',
  `auto_meta_keywords` varchar(512) NOT NULL DEFAULT '',
  `auto_meta_desc` varchar(512) NOT NULL DEFAULT '',
  `auto_description` text,
  `parent_menu_name` varchar(255) NOT NULL DEFAULT '',
  UNIQUE KEY `lang_id` (`lang_id`,`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблиці `sfly_lang_currencies`
--

CREATE TABLE IF NOT EXISTS `sfly_lang_currencies` (
  `lang_id` int(11) NOT NULL,
  `currency_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL DEFAULT '',
  `sign` varchar(20) NOT NULL DEFAULT '',
  UNIQUE KEY `lang_id` (`lang_id`,`currency_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп даних таблиці `sfly_lang_currencies`
--

INSERT INTO `sfly_lang_currencies` (`lang_id`, `currency_id`, `name`, `sign`) VALUES
(3, 1, '', ''),
(3, 2, '', ''),
(3, 4, 'гривны uk', 'грн'),
(4, 1, 'доллары', '$'),
(4, 2, 'рубли', 'руб'),
(4, 4, 'гривны', 'грн');

-- --------------------------------------------------------

--
-- Структура таблиці `sfly_lang_delivery`
--

CREATE TABLE IF NOT EXISTS `sfly_lang_delivery` (
  `lang_id` int(11) NOT NULL,
  `delivery_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  UNIQUE KEY `lang_id` (`lang_id`,`delivery_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблиці `sfly_lang_features`
--

CREATE TABLE IF NOT EXISTS `sfly_lang_features` (
  `lang_id` int(11) NOT NULL,
  `feature_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL DEFAULT '',
  UNIQUE KEY `lang_id` (`lang_id`,`feature_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблиці `sfly_lang_features_aliases`
--

CREATE TABLE IF NOT EXISTS `sfly_lang_features_aliases` (
  `lang_id` tinyint(11) NOT NULL,
  `feature_alias_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  UNIQUE KEY `lang_id_feature_alias_id` (`lang_id`,`feature_alias_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблиці `sfly_lang_features_aliases_values`
--

CREATE TABLE IF NOT EXISTS `sfly_lang_features_aliases_values` (
  `lang_id` int(11) NOT NULL,
  `feature_alias_value_id` int(11) NOT NULL,
  `value` varchar(255) NOT NULL DEFAULT '',
  UNIQUE KEY `lang_id_feature_alias_value_id` (`lang_id`,`feature_alias_value_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблиці `sfly_lang_features_values`
--

CREATE TABLE IF NOT EXISTS `sfly_lang_features_values` (
  `lang_id` int(11) NOT NULL,
  `feature_value_id` int(11) NOT NULL,
  `feature_id` int(11) NOT NULL,
  `value` varchar(1024) NOT NULL,
  `translit` varchar(255) NOT NULL,
  KEY `translit_feature_id_lang_id` (`translit`,`feature_id`,`lang_id`),
  KEY `lang_id` (`lang_id`),
  KEY `feature_value_id` (`feature_value_id`),
  KEY `translit` (`translit`),
  KEY `value` (`value`(64))
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблиці `sfly_lang_menu_items`
--

CREATE TABLE IF NOT EXISTS `sfly_lang_menu_items` (
  `lang_id` int(11) NOT NULL,
  `menu_item_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`lang_id`,`menu_item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблиці `sfly_lang_orders_labels`
--

CREATE TABLE IF NOT EXISTS `sfly_lang_orders_labels` (
  `lang_id` int(11) NOT NULL,
  `order_labels_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL DEFAULT '',
  UNIQUE KEY `lang_id` (`lang_id`,`order_labels_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп даних таблиці `sfly_lang_orders_labels`
--

INSERT INTO `sfly_lang_orders_labels` (`lang_id`, `order_labels_id`, `name`) VALUES
(3, 1, 'Перезвонить'),
(3, 2, 'Ожидается товар'),
(3, 3, 'Тест'),
(3, 4, 'Ожидает отправки'),
(3, 5, 'Отправлен'),
(3, 6, 'Выполнен');

-- --------------------------------------------------------

--
-- Структура таблиці `sfly_lang_orders_status`
--

CREATE TABLE IF NOT EXISTS `sfly_lang_orders_status` (
  `lang_id` int(11) NOT NULL,
  `order_status_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL DEFAULT '',
  UNIQUE KEY `lang_id` (`lang_id`,`order_status_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп даних таблиці `sfly_lang_orders_status`
--

INSERT INTO `sfly_lang_orders_status` (`lang_id`, `order_status_id`, `name`) VALUES
(3, 1, 'Нові'),
(3, 2, 'Прийняті'),
(3, 3, 'У кур\'єра'),
(3, 4, 'Виконано'),
(3, 5, 'Вилучені'),
(3, 6, 'Передзамовлення'),
(3, 7, 'Нові 1 клік'),
(4, 1, 'Нові'),
(4, 2, 'Прийняті'),
(4, 3, 'У кур\'єра'),
(4, 4, 'Виконано'),
(4, 5, 'Вилучені'),
(4, 6, 'Передзамовлення'),
(4, 7, 'Нові 1 клік');

-- --------------------------------------------------------

--
-- Структура таблиці `sfly_lang_pages`
--

CREATE TABLE IF NOT EXISTS `sfly_lang_pages` (
  `lang_id` int(11) NOT NULL,
  `page_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL DEFAULT '',
  `name_h1` varchar(255) NOT NULL DEFAULT '',
  `meta_title` varchar(512) NOT NULL DEFAULT '',
  `meta_description` varchar(512) NOT NULL DEFAULT '',
  `meta_keywords` varchar(512) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  UNIQUE KEY `lang_id` (`lang_id`,`page_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблиці `sfly_lang_payment_methods`
--

CREATE TABLE IF NOT EXISTS `sfly_lang_payment_methods` (
  `lang_id` int(11) NOT NULL,
  `payment_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  UNIQUE KEY `lang_id` (`lang_id`,`payment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблиці `sfly_lang_products`
--

CREATE TABLE IF NOT EXISTS `sfly_lang_products` (
  `lang_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `name` varchar(512) NOT NULL DEFAULT '',
  `annotation` text NOT NULL,
  `description` text NOT NULL,
  `meta_title` varchar(512) NOT NULL DEFAULT '',
  `meta_keywords` varchar(512) NOT NULL DEFAULT '',
  `meta_description` varchar(512) NOT NULL DEFAULT '',
  `special` varchar(255) DEFAULT '',
  UNIQUE KEY `lang_id` (`lang_id`,`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблиці `sfly_lang_products_types`
--

CREATE TABLE IF NOT EXISTS `sfly_lang_products_types` (
  `lang_id` int(11) NOT NULL,
  `products_type_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `meta_title` varchar(255) NOT NULL,
  `meta_keywords` varchar(255) NOT NULL,
  `meta_description` text NOT NULL,
  KEY `lang_id` (`lang_id`,`products_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблиці `sfly_lang_seo_filter_patterns`
--

CREATE TABLE IF NOT EXISTS `sfly_lang_seo_filter_patterns` (
  `lang_id` tinyint(11) NOT NULL,
  `seo_filter_pattern_id` int(11) NOT NULL,
  `h1` varchar(512) DEFAULT '',
  `title` varchar(512) DEFAULT '',
  `keywords` varchar(512) DEFAULT '',
  `meta_description` varchar(512) DEFAULT '',
  `description` text,
  UNIQUE KEY `lang_id_filter_auto_meta_id` (`lang_id`,`seo_filter_pattern_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблиці `sfly_lang_stores`
--

CREATE TABLE IF NOT EXISTS `sfly_lang_stores` (
  `lang_id` int(11) NOT NULL,
  `lang_label` varchar(4) NOT NULL,
  `store_id` bigint(20) NOT NULL,
  `name` varchar(255) NOT NULL,
  `address` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблиці `sfly_lang_variants`
--

CREATE TABLE IF NOT EXISTS `sfly_lang_variants` (
  `lang_id` int(11) NOT NULL,
  `variant_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL DEFAULT '',
  `units` varchar(32) NOT NULL DEFAULT '',
  UNIQUE KEY `lang_id` (`lang_id`,`variant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблиці `sfly_managers`
--

CREATE TABLE IF NOT EXISTS `sfly_managers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lang` varchar(2) NOT NULL DEFAULT 'ru',
  `login` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `permissions` varchar(1024) DEFAULT NULL,
  `cnt_try` tinyint(4) NOT NULL DEFAULT '0',
  `last_try` date DEFAULT NULL,
  `comment` varchar(512) DEFAULT '',
  `menu_status` tinyint(1) NOT NULL DEFAULT '1',
  `menu` text,
  PRIMARY KEY (`id`),
  KEY `login` (`login`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

--
-- Дамп даних таблиці `sfly_managers`
--

INSERT INTO `sfly_managers` (`id`, `lang`, `login`, `password`, `permissions`, `cnt_try`, `last_try`, `comment`, `menu_status`, `menu`) VALUES
(1, 'ru', 'admin', '$apr1$q8tdnhag$4sh6LrlypzKALYSD4Cx9I.', 'products,categories,brands,features,orders,order_settings,users,groups,coupons,pages,blog,comments,feedbacks,import,export,stats,design,settings,currency,delivery,payment,managers,license,languages,banners,callbacks,robots,seo_patterns,support,subscribes,menu,seo_filter_patterns,settings_counter,features_aliases', 0, NULL, '', 1, 'a:15:{s:12:\"left_catalog\";a:4:{s:19:\"left_products_title\";s:13:\"ProductsAdmin\";s:17:\"left_brands_title\";s:11:\"BrandsAdmin\";s:21:\"left_categories_title\";s:15:\"CategoriesAdmin\";s:19:\"left_features_title\";s:13:\"FeaturesAdmin\";}s:11:\"left_orders\";a:2:{s:17:\"left_orders_title\";s:11:\"OrdersAdmin\";s:26:\"left_orders_settings_title\";s:18:\"OrderSettingsAdmin\";}s:10:\"left_users\";a:4:{s:16:\"left_users_title\";s:10:\"UsersAdmin\";s:17:\"left_groups_title\";s:15:\"UserGroupsAdmin\";s:18:\"left_coupons_title\";s:12:\"CouponsAdmin\";s:20:\"left_subscribe_title\";s:21:\"SubscribeMailingAdmin\";}s:10:\"left_pages\";a:2:{s:16:\"left_pages_title\";s:10:\"PagesAdmin\";s:16:\"left_menus_title\";s:10:\"MenusAdmin\";}s:9:\"left_blog\";a:1:{s:15:\"left_blog_title\";s:9:\"BlogAdmin\";}s:13:\"left_comments\";a:3:{s:19:\"left_comments_title\";s:13:\"CommentsAdmin\";s:20:\"left_feedbacks_title\";s:14:\"FeedbacksAdmin\";s:20:\"left_callbacks_title\";s:14:\"CallbacksAdmin\";}s:9:\"left_auto\";a:3:{s:17:\"left_import_title\";s:11:\"ImportAdmin\";s:17:\"left_export_title\";s:11:\"ExportAdmin\";s:14:\"left_log_title\";s:14:\"ImportLogAdmin\";}s:10:\"left_stats\";a:3:{s:16:\"left_stats_title\";s:10:\"StatsAdmin\";s:24:\"left_products_stat_title\";s:16:\"ReportStatsAdmin\";s:26:\"left_categories_stat_title\";s:18:\"CategoryStatsAdmin\";}s:8:\"left_seo\";a:5:{s:17:\"left_robots_title\";s:11:\"RobotsAdmin\";s:26:\"left_setting_counter_title\";s:20:\"SettingsCounterAdmin\";s:23:\"left_seo_patterns_title\";s:16:\"SeoPatternsAdmin\";s:30:\"left_seo_filter_patterns_title\";s:22:\"SeoFilterPatternsAdmin\";s:26:\"left_feature_aliases_title\";s:20:\"FeaturesAliasesAdmin\";}s:11:\"left_design\";a:6:{s:16:\"left_theme_title\";s:10:\"ThemeAdmin\";s:19:\"left_template_title\";s:14:\"TemplatesAdmin\";s:16:\"left_style_title\";s:11:\"StylesAdmin\";s:17:\"left_script_title\";s:12:\"ScriptsAdmin\";s:17:\"left_images_title\";s:11:\"ImagesAdmin\";s:23:\"left_translations_title\";s:17:\"TranslationsAdmin\";}s:12:\"left_banners\";a:2:{s:18:\"left_banners_title\";s:12:\"BannersAdmin\";s:25:\"left_banners_images_title\";s:18:\"BannersImagesAdmin\";}s:13:\"left_settings\";a:15:{s:26:\"left_setting_general_title\";s:20:\"SettingsGeneralAdmin\";s:25:\"left_setting_notify_title\";s:19:\"SettingsNotifyAdmin\";s:26:\"left_setting_catalog_title\";s:20:\"SettingsCatalogAdmin\";s:23:\"left_setting_feed_title\";s:17:\"SettingsFeedAdmin\";s:19:\"left_currency_title\";s:13:\"CurrencyAdmin\";s:19:\"left_delivery_title\";s:15:\"DeliveriesAdmin\";s:18:\"left_payment_title\";s:19:\"PaymentMethodsAdmin\";s:19:\"left_managers_title\";s:13:\"ManagersAdmin\";s:20:\"left_languages_title\";s:14:\"LanguagesAdmin\";s:17:\"left_system_title\";s:11:\"SystemAdmin\";s:19:\"left_turbosms_title\";s:21:\"SettingsTurboSMSAdmin\";s:27:\"left_setting_esputnik_title\";s:21:\"SettingsESputnikAdmin\";s:21:\"left_novaposhta_title\";s:23:\"SettingsNovaPoshtaAdmin\";s:17:\"left_justin_title\";s:19:\"SettingsJustInAdmin\";s:19:\"left_autorize_title\";s:21:\"SettingsAutorizeAdmin\";}s:11:\"left_stores\";a:1:{s:17:\"left_stores_title\";s:11:\"StoresAdmin\";}s:14:\"left_referrals\";a:1:{s:20:\"left_referrals_title\";s:14:\"ReferralsAdmin\";}s:9:\"left_apis\";a:5:{s:19:\"left_turbosms_title\";s:21:\"SettingsTurboSMSAdmin\";s:27:\"left_setting_esputnik_title\";s:21:\"SettingsESputnikAdmin\";s:21:\"left_novaposhta_title\";s:23:\"SettingsNovaPoshtaAdmin\";s:17:\"left_justin_title\";s:19:\"SettingsJustInAdmin\";s:19:\"left_autorize_title\";s:21:\"SettingsAutorizeAdmin\";}}'),
(2, 'en', 'admin_1c', '$apr1$fq01kibr$fP/5ZiTcciFapSxvuhdfA1', 'integration_1c', 0, NULL, '', 0, NULL),
(3, 'ru', 'AndriiK', '$apr1$1cfdvb6r$n.RmYi2cRBhHy7L.QUj2v0', NULL, 0, NULL, '', 1, ''),
(4, 'ru', 'inna', '$apr1$flzoju0c$jyCkBDlXpvObrpO4iESEk.', NULL, 0, NULL, '', 1, 'a:14:{s:12:\"left_catalog\";a:4:{s:21:\"left_categories_title\";s:15:\"CategoriesAdmin\";s:19:\"left_products_title\";s:13:\"ProductsAdmin\";s:17:\"left_brands_title\";s:11:\"BrandsAdmin\";s:19:\"left_features_title\";s:13:\"FeaturesAdmin\";}s:11:\"left_orders\";a:2:{s:17:\"left_orders_title\";s:11:\"OrdersAdmin\";s:26:\"left_orders_settings_title\";s:18:\"OrderSettingsAdmin\";}s:10:\"left_users\";a:4:{s:16:\"left_users_title\";s:10:\"UsersAdmin\";s:17:\"left_groups_title\";s:15:\"UserGroupsAdmin\";s:18:\"left_coupons_title\";s:12:\"CouponsAdmin\";s:20:\"left_subscribe_title\";s:21:\"SubscribeMailingAdmin\";}s:10:\"left_pages\";a:2:{s:16:\"left_pages_title\";s:10:\"PagesAdmin\";s:16:\"left_menus_title\";s:10:\"MenusAdmin\";}s:9:\"left_blog\";a:1:{s:15:\"left_blog_title\";s:9:\"BlogAdmin\";}s:13:\"left_comments\";a:3:{s:19:\"left_comments_title\";s:13:\"CommentsAdmin\";s:20:\"left_feedbacks_title\";s:14:\"FeedbacksAdmin\";s:20:\"left_callbacks_title\";s:14:\"CallbacksAdmin\";}s:9:\"left_auto\";a:3:{s:17:\"left_import_title\";s:11:\"ImportAdmin\";s:17:\"left_export_title\";s:11:\"ExportAdmin\";s:14:\"left_log_title\";s:14:\"ImportLogAdmin\";}s:10:\"left_stats\";a:3:{s:16:\"left_stats_title\";s:10:\"StatsAdmin\";s:24:\"left_products_stat_title\";s:16:\"ReportStatsAdmin\";s:26:\"left_categories_stat_title\";s:18:\"CategoryStatsAdmin\";}s:8:\"left_seo\";a:5:{s:17:\"left_robots_title\";s:11:\"RobotsAdmin\";s:26:\"left_setting_counter_title\";s:20:\"SettingsCounterAdmin\";s:23:\"left_seo_patterns_title\";s:16:\"SeoPatternsAdmin\";s:30:\"left_seo_filter_patterns_title\";s:22:\"SeoFilterPatternsAdmin\";s:26:\"left_feature_aliases_title\";s:20:\"FeaturesAliasesAdmin\";}s:11:\"left_design\";a:6:{s:16:\"left_theme_title\";s:10:\"ThemeAdmin\";s:19:\"left_template_title\";s:14:\"TemplatesAdmin\";s:16:\"left_style_title\";s:11:\"StylesAdmin\";s:17:\"left_script_title\";s:12:\"ScriptsAdmin\";s:17:\"left_images_title\";s:11:\"ImagesAdmin\";s:23:\"left_translations_title\";s:17:\"TranslationsAdmin\";}s:12:\"left_banners\";a:2:{s:18:\"left_banners_title\";s:12:\"BannersAdmin\";s:25:\"left_banners_images_title\";s:18:\"BannersImagesAdmin\";}s:13:\"left_settings\";a:15:{s:26:\"left_setting_general_title\";s:20:\"SettingsGeneralAdmin\";s:25:\"left_setting_notify_title\";s:19:\"SettingsNotifyAdmin\";s:26:\"left_setting_catalog_title\";s:20:\"SettingsCatalogAdmin\";s:23:\"left_setting_feed_title\";s:17:\"SettingsFeedAdmin\";s:19:\"left_currency_title\";s:13:\"CurrencyAdmin\";s:19:\"left_delivery_title\";s:15:\"DeliveriesAdmin\";s:18:\"left_payment_title\";s:19:\"PaymentMethodsAdmin\";s:19:\"left_managers_title\";s:13:\"ManagersAdmin\";s:20:\"left_languages_title\";s:14:\"LanguagesAdmin\";s:17:\"left_system_title\";s:11:\"SystemAdmin\";s:19:\"left_turbosms_title\";s:21:\"SettingsTurboSMSAdmin\";s:27:\"left_setting_esputnik_title\";s:21:\"SettingsESputnikAdmin\";s:21:\"left_novaposhta_title\";s:23:\"SettingsNovaPoshtaAdmin\";s:17:\"left_justin_title\";s:19:\"SettingsJustInAdmin\";s:19:\"left_autorize_title\";s:21:\"SettingsAutorizeAdmin\";}s:11:\"left_stores\";a:1:{s:17:\"left_stores_title\";s:11:\"StoresAdmin\";}s:14:\"left_referrals\";a:1:{s:20:\"left_referrals_title\";s:14:\"ReferralsAdmin\";}}'),
(5, 'ru', 'Seo_01', '$apr1$rwkdqngt$jvK0ye2Qy6RtMeGsY4rOw0', 'products,categories,brands,features,pages,blog,import,export,banners,robots,seo_patterns,menu,seo_filter_patterns,settings_counter,features_aliases,integration_1c', 0, NULL, '', 0, '');

-- --------------------------------------------------------

--
-- Структура таблиці `sfly_menu`
--

CREATE TABLE IF NOT EXISTS `sfly_menu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_id` varchar(32) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL DEFAULT '',
  `visible` tinyint(1) NOT NULL DEFAULT '1',
  `position` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `visible` (`visible`),
  KEY `position` (`position`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблиці `sfly_menu_items`
--

CREATE TABLE IF NOT EXISTS `sfly_menu_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `menu_id` int(11) NOT NULL DEFAULT '0',
  `parent_id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL DEFAULT '',
  `url` varchar(512) NOT NULL DEFAULT '',
  `is_target_blank` tinyint(1) NOT NULL DEFAULT '0',
  `visible` tinyint(1) NOT NULL DEFAULT '1',
  `position` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `menu_id` (`menu_id`),
  KEY `parent_id` (`parent_id`),
  KEY `visible` (`visible`),
  KEY `position` (`position`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблиці `sfly_options_aliases_values`
--

CREATE TABLE IF NOT EXISTS `sfly_options_aliases_values` (
  `feature_alias_id` int(11) NOT NULL,
  `translit` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL,
  `feature_id` int(11) NOT NULL,
  `lang_id` int(11) NOT NULL,
  KEY `feature_alias_id` (`feature_alias_id`),
  KEY `translit` (`translit`),
  KEY `feature_id` (`feature_id`),
  KEY `lang_id` (`lang_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблиці `sfly_orders`
--

CREATE TABLE IF NOT EXISTS `sfly_orders` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `delivery_id` int(11) DEFAULT '0',
  `delivery_price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `payment_method_id` int(11) DEFAULT '0',
  `paid` tinyint(1) NOT NULL DEFAULT '0',
  `payment_date` datetime DEFAULT NULL,
  `closed` tinyint(1) NOT NULL DEFAULT '0',
  `date` datetime DEFAULT CURRENT_TIMESTAMP,
  `user_id` int(11) DEFAULT '0',
  `name` varchar(255) NOT NULL DEFAULT '',
  `address` varchar(255) DEFAULT '',
  `phone` varchar(32) NOT NULL DEFAULT '',
  `email` varchar(255) NOT NULL DEFAULT '',
  `comment` varchar(1024) DEFAULT '',
  `status_id` int(11) NOT NULL DEFAULT '0',
  `url` varchar(255) DEFAULT '',
  `payment_details` text,
  `ip` varchar(20) NOT NULL DEFAULT '',
  `total_price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `note` varchar(1024) NOT NULL DEFAULT '',
  `discount` decimal(5,2) NOT NULL DEFAULT '0.00',
  `coupon_discount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `coupon_code` varchar(255) NOT NULL DEFAULT '',
  `separate_delivery` tinyint(1) DEFAULT '0',
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `lang_id` int(11) NOT NULL DEFAULT '0',
  `justin` longtext NOT NULL,
  `novaposhta` longtext NOT NULL,
  `contact_json` longtext NOT NULL,
  `store_id` int(11) NOT NULL DEFAULT '0',
  `referral_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `login` (`user_id`),
  KEY `written_off` (`closed`),
  KEY `date` (`date`),
  KEY `status` (`status_id`),
  KEY `code` (`url`),
  KEY `payment_status` (`paid`),
  KEY `referral_id` (`referral_id`)
) ENGINE=InnoDB AUTO_INCREMENT=110 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблиці `sfly_orders_labels`
--

CREATE TABLE IF NOT EXISTS `sfly_orders_labels` (
  `order_id` int(11) NOT NULL,
  `label_id` int(11) NOT NULL,
  PRIMARY KEY (`order_id`,`label_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблиці `sfly_orders_status`
--

CREATE TABLE IF NOT EXISTS `sfly_orders_status` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `is_close` tinyint(1) NOT NULL DEFAULT '0',
  `color` varchar(6) NOT NULL DEFAULT 'ffffff',
  `position` int(11) NOT NULL DEFAULT '0',
  `status_1c` enum('not_use','new','accepted','to_delete') DEFAULT 'not_use',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблиці `sfly_pages`
--

CREATE TABLE IF NOT EXISTS `sfly_pages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `url` varchar(255) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL DEFAULT '',
  `name_h1` varchar(255) NOT NULL DEFAULT '',
  `meta_title` varchar(512) NOT NULL DEFAULT '',
  `meta_description` varchar(512) NOT NULL DEFAULT '',
  `meta_keywords` varchar(512) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `position` int(11) NOT NULL DEFAULT '0',
  `visible` tinyint(1) NOT NULL DEFAULT '0',
  `last_modify` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `order_num` (`position`),
  KEY `url` (`url`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблиці `sfly_payment_methods`
--

CREATE TABLE IF NOT EXISTS `sfly_payment_methods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `module` varchar(255) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `currency_id` int(11) NOT NULL DEFAULT '0',
  `settings` text NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT '0',
  `position` int(11) NOT NULL DEFAULT '0',
  `image` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `position` (`position`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблиці `sfly_products`
--

CREATE TABLE IF NOT EXISTS `sfly_products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `url` varchar(255) NOT NULL DEFAULT '',
  `brand_id` int(11) DEFAULT '0',
  `name` varchar(512) NOT NULL DEFAULT '',
  `annotation` text NOT NULL,
  `description` text NOT NULL,
  `visible` tinyint(1) NOT NULL DEFAULT '1',
  `position` int(11) NOT NULL DEFAULT '0',
  `meta_title` varchar(512) NOT NULL DEFAULT '',
  `meta_keywords` varchar(512) NOT NULL DEFAULT '',
  `meta_description` varchar(512) NOT NULL DEFAULT '',
  `created` timestamp NULL DEFAULT NULL,
  `featured` tinyint(1) DEFAULT '0',
  `external_id` varchar(36) NOT NULL DEFAULT '',
  `rating` float(3,1) DEFAULT '0.0',
  `votes` int(11) DEFAULT '0',
  `special` varchar(255) DEFAULT '',
  `last_modify` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `main_category_id` int(11) DEFAULT NULL,
  `main_image_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `url` (`url`),
  KEY `brand_id` (`brand_id`),
  KEY `visible` (`visible`),
  KEY `position` (`position`),
  KEY `external_id` (`external_id`),
  KEY `hit` (`featured`),
  KEY `name` (`name`(333)),
  KEY `main_category_id` (`main_category_id`),
  KEY `main_image_id` (`main_image_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3622 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблиці `sfly_products_categories`
--

CREATE TABLE IF NOT EXISTS `sfly_products_categories` (
  `product_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `position` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`product_id`,`category_id`),
  KEY `position` (`position`),
  KEY `product_id` (`product_id`),
  KEY `category_id` (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблиці `sfly_products_features_values`
--

CREATE TABLE IF NOT EXISTS `sfly_products_features_values` (
  `product_id` int(11) NOT NULL,
  `value_id` int(11) NOT NULL,
  UNIQUE KEY `product_id_value_id` (`product_id`,`value_id`),
  KEY `product_id` (`product_id`),
  KEY `value_id` (`value_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблиці `sfly_products_types`
--

CREATE TABLE IF NOT EXISTS `sfly_products_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `brand_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `url` varchar(100) NOT NULL,
  `visible` tinyint(1) NOT NULL DEFAULT '0',
  `last_modify` datetime DEFAULT NULL,
  `position` int(11) NOT NULL DEFAULT '0',
  `meta_title` varchar(255) NOT NULL DEFAULT '',
  `meta_keywords` varchar(255) NOT NULL DEFAULT '',
  `meta_description` text NOT NULL,
  `image` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `visible` (`visible`),
  KEY `position` (`position`),
  KEY `brand_id` (`brand_id`)
) ENGINE=InnoDB AUTO_INCREMENT=63 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблиці `sfly_purchases`
--

CREATE TABLE IF NOT EXISTS `sfly_purchases` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL DEFAULT '0',
  `product_id` int(11) DEFAULT '0',
  `variant_id` int(11) DEFAULT '0',
  `product_name` varchar(255) NOT NULL DEFAULT '',
  `variant_name` varchar(255) NOT NULL DEFAULT '',
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `amount` int(11) NOT NULL DEFAULT '0',
  `sku` varchar(255) NOT NULL DEFAULT '',
  `units` varchar(32) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`),
  KEY `variant_id` (`variant_id`)
) ENGINE=InnoDB AUTO_INCREMENT=144 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблиці `sfly_referrals`
--

CREATE TABLE IF NOT EXISTS `sfly_referrals` (
  `id` bigint(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `code` varchar(100) NOT NULL,
  `type` enum('percentage','absolute','') NOT NULL DEFAULT 'percentage',
  `value` decimal(10,2) NOT NULL DEFAULT '0.00',
  `expire` timestamp NULL DEFAULT NULL,
  `single` tinyint(1) NOT NULL DEFAULT '0',
  `usages` int(11) NOT NULL DEFAULT '0',
  `visible` tinyint(1) UNSIGNED NOT NULL DEFAULT '1',
  `position` bigint(11) UNSIGNED NOT NULL DEFAULT '0',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_modify` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `visible` (`visible`),
  KEY `position` (`position`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Структура таблиці `sfly_related_blogs`
--

CREATE TABLE IF NOT EXISTS `sfly_related_blogs` (
  `post_id` int(11) NOT NULL,
  `related_id` int(11) NOT NULL,
  `position` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`post_id`,`related_id`),
  KEY `position` (`position`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблиці `sfly_related_products`
--

CREATE TABLE IF NOT EXISTS `sfly_related_products` (
  `product_id` int(11) NOT NULL,
  `related_id` int(11) NOT NULL,
  `position` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`product_id`,`related_id`),
  KEY `position` (`position`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблиці `sfly_seo_filter_patterns`
--

CREATE TABLE IF NOT EXISTS `sfly_seo_filter_patterns` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `type` enum('brand','feature') NOT NULL,
  `h1` varchar(512) DEFAULT '',
  `title` varchar(512) DEFAULT '',
  `keywords` varchar(512) DEFAULT '',
  `meta_description` varchar(512) DEFAULT '',
  `description` text,
  `feature_id` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `category_id_param_type_feature_id` (`category_id`,`type`,`feature_id`),
  KEY `category_id` (`category_id`),
  KEY `feature_id` (`feature_id`),
  KEY `param_type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблиці `sfly_settings`
--

CREATE TABLE IF NOT EXISTS `sfly_settings` (
  `setting_id` int(11) NOT NULL AUTO_INCREMENT,
  `param` varchar(255) NOT NULL DEFAULT '',
  `value` text NOT NULL,
  PRIMARY KEY (`setting_id`)
) ENGINE=InnoDB AUTO_INCREMENT=242 DEFAULT CHARSET=utf8;

--
-- Дамп даних таблиці `sfly_settings`
--

INSERT INTO `sfly_settings` (`setting_id`, `param`, `value`) VALUES
(30, 'theme', 'sportfly'),
(33, 'products_num', '15'),
(53, 'date_format', 'd.m.Y'),
(54, 'order_email', 'alexander.eurotech@gmail.com'),
(55, 'comment_email', 'support@sportfly.com.ua'),
(56, 'notify_from_email', 'support@sportfly.com.ua'),
(57, 'decimals_point', ','),
(58, 'thousands_separator', ' '),
(111, 'last_1c_orders_export_date', '2020-01-30 11:23:41'),
(113, 'max_order_amount', '50'),
(114, 'watermark_offset_x', '50'),
(115, 'watermark_offset_y', '50'),
(119, 'admin_email', 'sukonkoz@gmail.com'),
(120, 'comparison_count', '5'),
(121, 'is_preorder', '1'),
(125, 'yandex_export_not_in_stock', ''),
(126, 'yandex_available_for_retail_store', ''),
(127, 'yandex_available_for_reservation', ''),
(128, 'yandex_short_description', ''),
(129, 'yandex_has_manufacturer_warranty', ''),
(130, 'yandex_has_seller_warranty', ''),
(131, 'yandex_sales_notes', ''),
(132, 'posts_num', '3'),
(133, 'image_sizes', '200x200|60x60|50x50|219x172|162x77|183x183|35x35|400x300|100x100|1000x1000|800x600|300x300|87x72|330x300|77x77|165x90|150x150|300x120|55x55|250x250|75x75|70x70|360x360|1170x390|465x265|250x100|570x1170|570x830|570x630|570x730|370x500|48x48|265x500|40x40|58x58|100x70|70x100|80x80|870x870|340x340|650x650|640x640|800x800|870x8701|70x70w'),
(134, 'products_image_sizes', '200x200|50x50|1800x1200w|600x340|75x75|330x300|800x600|55x55|300x120|35x35|70x70|40x40|370x370|370x370w|340x340|265x500|250x250|870x870w|540x540|0x0|440x440|870x870|670x670|650x650|870x8701|200x200w|70x70w|370x350|400x400|800x800'),
(135, 'captcha_product', ''),
(136, 'captcha_post', '1'),
(137, 'captcha_cart', ''),
(138, 'captcha_register', ''),
(139, 'captcha_feedback', ''),
(140, 'site_work', 'on'),
(141, 'yandex_metrika_token', ''),
(145, 'topvisor_key', ''),
(148, 'y_metric', ''),
(149, 'yandex_metrika_app_id', ''),
(150, 'max_filter_brands', '1'),
(152, 'lastModifyPosts', '2019-12-06 17:17:23'),
(153, 'image_quality', '100'),
(154, 'email_lang', 'ua'),
(155, 'site_logo', 'Logo.png'),
(156, 'gather_enabled', ''),
(157, 'captcha_callback', ''),
(158, 'iframe_map_code', '<iframe  height=\"700\" style=\"border:0;\" allowfullscreen></iframe><br>'),
(159, 'captcha_type', 'default'),
(161, 'max_filter_filter', '1'),
(162, 'max_filter_features_values', '1'),
(163, 'max_filter_features', '1'),
(164, 'max_filter_depth', '1'),
(200, 'recaptcha_scores', 'a:3:{s:7:\"product\";d:0.5;s:4:\"cart\";d:0.7;s:5:\"other\";d:0.2;}'),
(201, 'admin_theme_managers', ''),
(202, 'admin_theme', ''),
(203, 'public_recaptcha', ''),
(204, 'secret_recaptcha', ''),
(205, 'public_recaptcha_invisible', ''),
(206, 'secret_recaptcha_invisible', ''),
(207, 'public_recaptcha_v3', ''),
(208, 'secret_recaptcha_v3', ''),
(209, 'np_api_key', '0aad8d1fd5266244a53ad3a1475e0c28'),
(210, 'novaposhta_deliveryType', 'WarehouseWarehouse'),
(211, 'novaposhta_cargoType', 'Cargo'),
(212, 'novaposhta_TypeOfPayer', 'Sender'),
(213, 'novaposhta_PaymentForm', 'NonCash'),
(214, 'esputnik_user', 'sukonkoz@gmail.com'),
(215, 'esputnik_pass', 'SportFly2019'),
(216, 'esputnik_notify_from_email', 'sukonkoz@gmail.com'),
(217, 'novaposhta_city', 'db5c891b-391c-11dd-90d9-001a92567626'),
(218, 'novaposhta_ware', '1ec09dac-e1c2-11e3-8c4a-0050568002cf'),
(219, 'auto_approved', '0'),
(220, 'use_smtp', '0'),
(221, 'smtp_server', ''),
(222, 'smtp_port', ''),
(223, 'smtp_user', ''),
(224, 'smtp_pass', ''),
(225, 'google_client_id', '198177097285-24dt95j9i5838eabbm09evvhgjbe0qer.apps.googleusercontent.com'),
(226, 'google_client_secret', 'RofyM_iP9uSQksQVosVAAd8L'),
(227, 'facebook_app_id', '1010758379267789'),
(228, 'facebook_app_secret', '64326fc6b4ffbb1e51541a051d9dae8b'),
(229, 'google_enabled', '1'),
(230, 'google_test_mode', ''),
(231, 'facebook_enabled', '1'),
(232, 'facebook_test_mode', ''),
(233, 'orders_status', 'a:2:{s:4:\"fast\";s:1:\"7\";s:8:\"preorder\";s:1:\"6\";}'),
(234, 'turbosms_enabled', '1'),
(235, 'turbosms_test_mode', ''),
(236, 'turbosms_sender', 'Sportfly'),
(237, 'turbosms_login', 'sportfly'),
(238, 'turbosms_pass', 'zVLcSuhe'),
(239, 'feature_id', 'a:2:{s:4:\"size\";s:2:\"99\";s:5:\"color\";s:3:\"100\";}'),
(240, 'css_version', '000016'),
(241, 'js_version', '000015');

-- --------------------------------------------------------

--
-- Структура таблиці `sfly_settings_lang`
--

CREATE TABLE IF NOT EXISTS `sfly_settings_lang` (
  `param` varchar(128) NOT NULL,
  `lang_id` int(11) NOT NULL DEFAULT '0',
  `value` text NOT NULL,
  PRIMARY KEY (`lang_id`,`param`),
  KEY `name` (`param`),
  KEY `lang_id` (`lang_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп даних таблиці `sfly_settings_lang`
--

INSERT INTO `sfly_settings_lang` (`param`, `lang_id`, `value`) VALUES
('default_products_seo_pattern', 3, 'a:4:{s:15:\"auto_meta_title\";s:137:\"Купити {$product} від виробника {$brand} у Кропивницькому на сайті спортфлай {$sitename}\";s:18:\"auto_meta_keywords\";s:33:\"Купити {$product}, {$brand}\";s:14:\"auto_meta_desc\";s:60:\"купити {$product}, {$brand} Кропивницький\";s:16:\"auto_description\";s:0:\"\";}'),
('notify_from_name', 3, 'Адміністратор'),
('site_annotation', 3, 'Технічне повідомлення на випадок відключення сайту'),
('site_name', 3, 'Sport Fly'),
('turbosms_messages', 3, 'a:2:{s:5:\"order\";s:60:\"Vash zakaz #{$order_id} priniat. Spasibo! +38(068) 433-10-33\";s:8:\"register\";s:59:\"Vash kod pidtverdzhennia: \"{$password}\". +38(068) 433-10-33\";}'),
('units', 3, 'шт'),
('default_products_seo_pattern', 4, 'a:4:{s:15:\"auto_meta_title\";s:137:\"Купити {$product} від виробника {$brand} у Кропивницькому на сайті спортфлай {$sitename}\";s:18:\"auto_meta_keywords\";s:33:\"Купити {$product}, {$brand}\";s:14:\"auto_meta_desc\";s:60:\"купити {$product}, {$brand} Кропивницький\";s:16:\"auto_description\";s:0:\"\";}'),
('notify_from_name', 4, 'Адміністратор'),
('site_annotation', 4, 'Технічне повідомлення на випадок відключення сайту'),
('site_name', 4, 'Sport Fly'),
('turbosms_messages', 4, 'a:2:{s:5:\"order\";s:60:\"Vash zakaz #{$order_id} priniat. Spasibo! +38(068) 433-10-33\";s:8:\"register\";s:59:\"Vash kod pidtverdzhennia: \"{$password}\". +38(068) 433-10-33\";}'),
('units', 4, 'шт');

-- --------------------------------------------------------

--
-- Структура таблиці `sfly_spec_img`
--

CREATE TABLE IF NOT EXISTS `sfly_spec_img` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `filename` varchar(255) NOT NULL DEFAULT '',
  `position` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблиці `sfly_stores`
--

CREATE TABLE IF NOT EXISTS `sfly_stores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `enabled` tinyint(1) DEFAULT '1',
  `position` int(11) DEFAULT '0',
  `last_modify` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблиці `sfly_subscribe_mailing`
--

CREATE TABLE IF NOT EXISTS `sfly_subscribe_mailing` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблиці `sfly_users`
--

CREATE TABLE IF NOT EXISTS `sfly_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL DEFAULT '',
  `password` varchar(255) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL DEFAULT '',
  `phone` varchar(32) NOT NULL DEFAULT '',
  `address` varchar(255) NOT NULL DEFAULT '',
  `group_id` int(11) NOT NULL DEFAULT '0',
  `last_ip` varchar(20) DEFAULT NULL,
  `created` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `remind_code` varchar(32) DEFAULT NULL,
  `remind_expire` timestamp NULL DEFAULT NULL,
  `image` varchar(255) NOT NULL DEFAULT '',
  `q_comment` int(11) NOT NULL DEFAULT '0',
  `facebook_id` varchar(255) DEFAULT NULL,
  `facebook_json` longtext NOT NULL,
  `google_id` varchar(255) DEFAULT NULL,
  `google_json` longtext NOT NULL,
  `referral_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблиці `sfly_users_referrals`
--

CREATE TABLE IF NOT EXISTS `sfly_users_referrals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `referral_id` int(11) NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Структура таблиці `sfly_variants`
--

CREATE TABLE IF NOT EXISTS `sfly_variants` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `sku` varchar(255) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL DEFAULT '',
  `weight` decimal(10,2) DEFAULT '0.00',
  `price` decimal(14,2) NOT NULL DEFAULT '0.00',
  `compare_price` decimal(14,2) DEFAULT NULL,
  `stock` mediumint(9) UNSIGNED DEFAULT NULL,
  `position` int(11) NOT NULL DEFAULT '0',
  `attachment` varchar(255) NOT NULL DEFAULT '',
  `external_id` varchar(36) NOT NULL DEFAULT '',
  `currency_id` int(11) NOT NULL DEFAULT '0',
  `feed` tinyint(1) DEFAULT '0',
  `units` varchar(32) NOT NULL DEFAULT '',
  `image` varchar(255) NOT NULL DEFAULT '',
  `size` varchar(100) NOT NULL DEFAULT '',
  `color` varchar(100) NOT NULL DEFAULT '',
  `feed_rozetka` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `feed_prom` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `feed_rss` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  KEY `sku` (`sku`),
  KEY `price` (`price`),
  KEY `stock` (`stock`),
  KEY `position` (`position`),
  KEY `external_id` (`external_id`),
  KEY `yandex` (`feed`),
  KEY `product_id_2` (`product_id`,`color`),
  KEY `size` (`size`),
  KEY `color` (`color`),
  KEY `currency_id` (`currency_id`),
  KEY `feed_rozetka` (`feed_rozetka`),
  KEY `feed_prom` (`feed_prom`),
  KEY `feed_rss` (`feed_rss`)
) ENGINE=InnoDB AUTO_INCREMENT=22732 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблиці `sfly_wishlist`
--

CREATE TABLE IF NOT EXISTS `sfly_wishlist` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(20) UNSIGNED NOT NULL,
  `products_ids` text NOT NULL,
  `last_modify` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
