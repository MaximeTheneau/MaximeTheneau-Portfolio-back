-- Adminer 4.8.1 MySQL 8.0.30-0ubuntu0.22.04.1 dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

SET NAMES utf8mb4;

DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_title` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  `img_webp` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `img_svg` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `updated_at` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `categories` (`id`, `title`, `id_title`, `img_webp`, `img_svg`, `created_at`, `updated_at`) VALUES
(1,	'Accueil',	'#home',	'http://localhost/Back-Portofolio-Theneau-Maxime/public/images/webp/home-illustration.webp',	'http://localhost/Back-Portofolio-Theneau-Maxime/public/images/svg/home-illustration.svg',	'2017-01-01 00:00:00',	NULL),
(2,	'Expériences',	'#skills',	'http://localhost/Back-Portofolio-Theneau-Maxime/public/images/webp/experiences-illustration.webp',	'http://localhost/Back-Portofolio-Theneau-Maxime/public/images/svg/experiences-illustration.svg',	'2022-09-26 13:08:19',	NULL),
(3,	'Contact',	'#contact',	'http://localhost/Back-Portofolio-Theneau-Maxime/public/images/webp/contact-Illustation.webp',	'http://localhost/Back-Portofolio-Theneau-Maxime/public/images/svg/contact-Illustation.svg',	'2022-09-26 13:14:03',	NULL);

DROP TABLE IF EXISTS `doctrine_migration_versions`;
CREATE TABLE `doctrine_migration_versions` (
  `version` varchar(191) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `executed_at` datetime DEFAULT NULL,
  `execution_time` int DEFAULT NULL,
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;


DROP TABLE IF EXISTS `experiences`;
CREATE TABLE `experiences` (
  `id` int NOT NULL AUTO_INCREMENT,
  `categories_id` decimal(10,0) DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` date NOT NULL,
  `updated_at` time NOT NULL,
  `contents` varchar(1024) COLLATE utf8mb4_unicode_ci NOT NULL,
  `image_svg` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image_webp` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_82020E70A21214B7` (`categories_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `experiences` (`id`, `categories_id`, `title`, `created_at`, `updated_at`, `contents`, `image_svg`, `image_webp`) VALUES
(1,	1,	'Experience Test',	'2017-01-01',	'00:00:00',	'Lorem ipsum dolor sit amet. Qui internos eius et maxime voluptatem et consequatur quia eos veniam voluptatem eos consequatur rerum in asperiores necessitatibus? Quo quia quidem ut facere facere et quasi fugiat et sint inventore non exercitationem recusandae. Sed voluptas mollitia in corporis iure ad voluptatem magni non assumenda explicabo aut consequatur ullam qui laboriosam dolorem.  Et cupiditate laudantium est tenetur deserunt dolor itaque. Sed obcaecati dolorem cum explicabo pariatur rem debitis dolores et suscipit unde et dicta magnam est quas quae. Non neque excepturi et animi explicabo ab nisi architecto a aspernatur rerum.  Sed maxime voluptate aut quod officiis nam impedit doloremque est pariatur voluptatem. Et omnis amet 33 ipsum numquam vel galisum officiis molestiae corporis et numquam rem quos distinctio 33 officia aperiam.',	NULL,	NULL);

DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(180) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `roles` json NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_8D93D649E7927C74` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `user` (`id`, `email`, `roles`, `password`) VALUES
(1,	'admin@admin.com',	'[\"ROLE_ADMIN\"]',	'$2y$13$D13yg5F.mMCy9YPihe94l.Ra5CJXFgPlHa2Ew3YHkZt6rayQjL9HG');

-- 2022-09-26 11:44:44