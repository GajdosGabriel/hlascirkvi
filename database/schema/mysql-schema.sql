/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `addres_books`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `addres_books` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `first_name` varchar(191) NOT NULL,
  `last_name` varchar(191) DEFAULT NULL,
  `email` varchar(191) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `addres_books_user_id_index` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `buffer_publications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `buffer_publications` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `post_id` int(10) unsigned NOT NULL,
  `organization_id` int(10) unsigned NOT NULL,
  `slot_at` datetime NOT NULL,
  `archive` tinyint(1) NOT NULL DEFAULT 0,
  `arrived_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `buffer_publications_post_id_unique` (`post_id`),
  KEY `buffer_publications_organization_id_index` (`organization_id`),
  KEY `buffer_publications_slot_at_index` (`slot_at`),
  KEY `buffer_publications_arrived_at_index` (`arrived_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(191) NOT NULL,
  `slug` varchar(191) NOT NULL,
  `order` int(10) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `comments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `commentable_id` int(10) unsigned NOT NULL,
  `commentable_type` varchar(191) NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `body` text NOT NULL,
  `published` tinyint(1) NOT NULL DEFAULT 1,
  `type` enum('offer','look') DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `user_avatar` varchar(100) DEFAULT NULL,
  `user_name` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `comments_user_id_foreign` (`user_id`),
  KEY `comments_created_index` (`deleted_at`,`created_at`),
  KEY `comments_commentable_index` (`commentable_type`,`commentable_id`,`deleted_at`),
  CONSTRAINT `comments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `districts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `districts` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Unique identifier',
  `name` varchar(255) NOT NULL COMMENT 'Name',
  `veh_reg_num` varchar(255) NOT NULL COMMENT 'Vehicle registration number of district',
  `code` smallint(3) NOT NULL COMMENT 'Code',
  `region_id` int(11) NOT NULL COMMENT 'ID of region where district belongs to',
  `use` tinyint(4) NOT NULL DEFAULT 1 COMMENT '1 = use the row, 0 = not',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `favorites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `favorites` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `favorited_id` int(10) unsigned NOT NULL,
  `favorited_type` varchar(191) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `favorites_user_id_favorited_id_favorited_type_unique` (`user_id`,`favorited_id`,`favorited_type`),
  KEY `favorites_user_id_index` (`user_id`),
  KEY `favorites_favorited_index` (`favorited_type`,`favorited_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `first_names`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `first_names` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `vocative` varchar(100) DEFAULT NULL,
  `count` int(11) NOT NULL DEFAULT 0,
  `gender` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `images`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `images` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `fileable_id` int(10) unsigned NOT NULL,
  `fileable_type` varchar(191) NOT NULL,
  `name` varchar(191) NOT NULL,
  `url` varchar(191) NOT NULL,
  `thumb` varchar(191) NOT NULL,
  `variants` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`variants`)),
  `width` int(10) unsigned DEFAULT NULL,
  `height` int(10) unsigned DEFAULT NULL,
  `is_primary` tinyint(1) DEFAULT NULL,
  `org_name` varchar(191) NOT NULL,
  `size` int(11) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `mime` varchar(10) DEFAULT NULL,
  `type` enum('img','video','card') DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `images_fileable_type_fileable_id_url_unique` (`fileable_type`,`fileable_id`,`url`),
  KEY `images_fileable_id_index` (`fileable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(191) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `messengers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `messengers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `requested_user` int(10) unsigned NOT NULL,
  `body` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `messengers_user_id_index` (`user_id`),
  KEY `messengers_requested_user_index` (`requested_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(191) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `model_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `model_has_permissions` (
  `permission_id` int(10) unsigned NOT NULL,
  `model_type` varchar(191) NOT NULL,
  `model_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `model_has_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `model_has_roles` (
  `role_id` int(10) unsigned NOT NULL,
  `model_type` varchar(191) NOT NULL,
  `model_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notifications` (
  `id` char(36) NOT NULL,
  `type` varchar(191) NOT NULL,
  `notifiable_type` varchar(191) NOT NULL,
  `notifiable_id` bigint(20) unsigned NOT NULL,
  `data` text NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `organization_updater`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `organization_updater` (
  `organization_id` int(10) unsigned NOT NULL,
  `updater_id` int(10) unsigned NOT NULL,
  UNIQUE KEY `organization_updater_organization_id_updater_id_unique` (`organization_id`,`updater_id`),
  KEY `organization_updater_organization_id_index` (`organization_id`),
  KEY `organization_updater_updater_id_index` (`updater_id`),
  CONSTRAINT `organization_updater_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`),
  CONSTRAINT `organization_updater_updater_id_foreign` FOREIGN KEY (`updater_id`) REFERENCES `updaters` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `organization_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `organization_user` (
  `organization_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  KEY `organization_user_organization_id_index` (`organization_id`),
  KEY `organization_user_user_id_index` (`user_id`),
  CONSTRAINT `organization_user_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `organization_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `organizations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `organizations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `village_id` int(10) unsigned NOT NULL,
  `person` tinyint(1) NOT NULL DEFAULT 0,
  `avatar` varchar(200) DEFAULT NULL,
  `title` varchar(191) NOT NULL,
  `slug` varchar(191) NOT NULL,
  `street` varchar(191) DEFAULT NULL,
  `psc` int(11) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `description` mediumtext DEFAULT NULL,
  `mod_title` varchar(20) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `phone_numeric` varchar(20) DEFAULT NULL,
  `youtube_channel` varchar(40) DEFAULT NULL,
  `youtube_playlist` varchar(40) DEFAULT NULL,
  `url_www` varchar(191) DEFAULT NULL,
  `published` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `password_resets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_resets` (
  `email` varchar(191) NOT NULL,
  `token` varchar(191) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  KEY `password_resets_email_index` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permissions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `guard_name` varchar(191) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) unsigned NOT NULL,
  `name` text NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  KEY `personal_access_tokens_expires_at_index` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `persons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `persons` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `prefix` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `post`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `post` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `organization_id` int(10) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `body` text DEFAULT NULL,
  `blocked` tinyint(1) NOT NULL DEFAULT 0,
  `youtube_blocked` tinyint(1) NOT NULL DEFAULT 0,
  `video_available` tinyint(1) DEFAULT NULL,
  `video_id` varchar(255) DEFAULT NULL,
  `video_duration` varchar(255) DEFAULT NULL,
  `count_view` int(11) NOT NULL,
  `deleted_at` datetime NOT NULL,
  `published` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `post_seminar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `post_seminar` (
  `seminar_id` int(10) unsigned NOT NULL,
  `post_id` int(10) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  UNIQUE KEY `post_seminar_post_id_seminar_id_unique` (`post_id`,`seminar_id`),
  KEY `post_seminar_seminar_id_index` (`seminar_id`),
  KEY `post_seminar_post_id_index` (`post_id`),
  CONSTRAINT `post_seminar_post_id_foreign` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`),
  CONSTRAINT `post_seminar_seminar_id_foreign` FOREIGN KEY (`seminar_id`) REFERENCES `seminars` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `post_updater`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `post_updater` (
  `post_id` int(10) unsigned NOT NULL,
  `updater_id` int(10) unsigned NOT NULL,
  UNIQUE KEY `post_updater_post_id_updater_id_unique` (`post_id`,`updater_id`),
  KEY `post_updater_post_id_index` (`post_id`),
  KEY `post_updater_updater_id_index` (`updater_id`),
  CONSTRAINT `post_updater_post_id_foreign` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`),
  CONSTRAINT `post_updater_updater_id_foreign` FOREIGN KEY (`updater_id`) REFERENCES `updaters` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `posts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `posts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `organization_id` int(10) unsigned NOT NULL,
  `title` varchar(200) NOT NULL,
  `body` text NOT NULL,
  `slug` varchar(191) NOT NULL,
  `blocked` tinyint(1) NOT NULL DEFAULT 0,
  `youtube_blocked` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `video_id` varchar(191) DEFAULT NULL,
  `count_view` int(11) NOT NULL DEFAULT 0,
  `published` datetime DEFAULT NULL,
  `video_available` tinyint(1) DEFAULT NULL,
  `video_duration` varchar(15) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `posts_feed_created_index` (`youtube_blocked`,`video_available`,`deleted_at`,`created_at`),
  KEY `posts_feed_views_index` (`youtube_blocked`,`video_available`,`deleted_at`,`count_view`),
  KEY `posts_organization_created_index` (`organization_id`,`youtube_blocked`,`deleted_at`,`created_at`),
  KEY `posts_organization_count_index` (`youtube_blocked`,`organization_id`),
  KEY `posts_published_index` (`published`),
  KEY `posts_organization_views_index` (`organization_id`,`youtube_blocked`,`deleted_at`,`count_view`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `prayers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `prayers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL,
  `organization_id` int(11) NOT NULL,
  `user_name` varchar(255) DEFAULT NULL,
  `body` text NOT NULL,
  `fulfilled_at` datetime DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `prayers_created_index` (`deleted_at`,`created_at`),
  KEY `prayers_fulfilled_index` (`deleted_at`,`fulfilled_at`),
  KEY `prayers_organization_id_index` (`organization_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `role_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `role_has_permissions` (
  `permission_id` int(10) unsigned NOT NULL,
  `role_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `guard_name` varchar(191) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `seminars`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `seminars` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `youtube_playlist` varchar(255) DEFAULT NULL,
  `organization_id` int(10) unsigned NOT NULL,
  `published` datetime DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` text NOT NULL,
  `last_activity` int(11) NOT NULL,
  UNIQUE KEY `sessions_id_unique` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tags` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `updaters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `updaters` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(60) NOT NULL,
  `slug` varchar(60) NOT NULL,
  `identificator` int(10) unsigned DEFAULT NULL,
  `type` varchar(40) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `updaters_title_unique` (`title`),
  UNIQUE KEY `updaters_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `email_verified_at` datetime DEFAULT NULL,
  `password` varchar(191) NOT NULL,
  `verified` tinyint(1) NOT NULL DEFAULT 0,
  `avatar` varchar(200) DEFAULT NULL,
  `vocative` varchar(15) DEFAULT NULL,
  `gender` varchar(10) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `slug` varchar(230) NOT NULL,
  `send_email` tinyint(1) NOT NULL DEFAULT 1,
  `front_author` tinyint(1) NOT NULL DEFAULT 0,
  `disabled` tinyint(1) NOT NULL DEFAULT 0,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `set_denomination` int(3) DEFAULT NULL,
  `org_id` int(11) DEFAULT NULL,
  `api_token` varchar(60) NOT NULL,
  `notify_bell` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `venues`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `venues` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `village_id` int(10) unsigned DEFAULT NULL,
  `name` varchar(250) NOT NULL,
  `street` varchar(250) NOT NULL,
  `postcode` varchar(250) NOT NULL,
  `slug` varchar(250) NOT NULL,
  `body` text DEFAULT NULL,
  `website` varchar(50) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `verses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `verses` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(93) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `slug` varchar(255) NOT NULL,
  `autor` varchar(29) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `heslomesiaca_text` varchar(149) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `heslomesiaca_ref` varchar(21) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `nazov_sviatku` varchar(68) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `sviatocnyvers_text` varchar(156) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `sviatocnyvers_ref` varchar(18) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `szvers_intro` int(11) DEFAULT NULL,
  `szvers_text` varchar(183) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `szvers_ref` varchar(23) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `nzvers_intro` int(11) DEFAULT NULL,
  `biblicky_vers` varchar(310) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `biblicky_vers_ref` varchar(25) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `text_na_zamyslenie` varchar(31) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `zamyslenie` varchar(2284) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `modlitba` varchar(645) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `c_piesne` varchar(8) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `piesen` varchar(192) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `bibleref` varchar(69) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovak_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `views`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `views` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `viewable_type` varchar(60) NOT NULL,
  `viewable_id` int(10) unsigned NOT NULL,
  `visitor_hash` char(64) NOT NULL,
  `viewed_on` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `views_unique_per_day` (`viewable_type`,`viewable_id`,`visitor_hash`,`viewed_on`),
  KEY `views_target_day_index` (`viewable_type`,`viewable_id`,`viewed_on`),
  KEY `views_viewed_on_index` (`viewed_on`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `villages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `villages` (
  `id` int(11) NOT NULL COMMENT 'Unique identifier',
  `fullname` varchar(255) NOT NULL COMMENT 'Fullname',
  `shortname` varchar(255) NOT NULL COMMENT 'Shortname',
  `zip` varchar(6) NOT NULL COMMENT 'ZIP',
  `district_id` int(11) NOT NULL COMMENT 'ID of district where villages belongs to',
  `region_id` int(11) NOT NULL COMMENT 'ID of region where villages belongs to',
  `use` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1 = use the row, 0 = not'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (1,'2013_01_08_140242_user_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (2,'2014_10_12_100000_create_password_resets_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (3,'2017_10_09_222556_create_organizations_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (4,'2018_06_13_074916_create_posts_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (5,'2018_06_13_075102_create_categories_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (7,'2018_06_18_134004_create_messengers_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (8,'2018_06_25_211931_create_favorites_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (9,'2018_06_26_124309_create_comments_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (10,'2018_06_27_082414_create_events_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (11,'2018_06_28_123730_create_images_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (12,'2018_09_06_070857_create_event_subscribes_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (13,'2018_09_10_083247_create_notifications_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (14,'2018_10_05_080259_create_addres_books_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (15,'2018_10_05_173101_create_regions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (16,'2018_11_09_075128_create_big_thinks_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (17,'2018_11_12_080732_create_jobs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (18,'2018_11_22_182600_create_first_names_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (19,'2018_12_03_125902_create_permission_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (20,'2018_12_07_093519_create_verses_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (21,'2019_01_09_150039_organization_user',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (22,'2019_01_18_172820_create_updaters_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (23,'2019_01_18_173147_create_organization_updater_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (24,'2019_03_06_112552_create_views_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (26,'2019_03_10_221057_create_post_updater_table',3);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (27,'2019_08_19_000000_create_failed_jobs_table',4);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (28,'2021_02_13_094957_create_prayers_table',4);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (29,'2021_05_29_141546_create_post_tag_table',4);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (31,'2021_05_28_190456_create_tags_table',5);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (32,'2019_12_14_000001_create_personal_access_tokens_table',6);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (33,'2021_05_30_134305_create_seminars_table',7);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (34,'2021_05_31_082722_create_post_seminar_table',8);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (35,'2021_12_09_070925_create_sessions_table',8);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (36,'2022_01_13_090304_create_post_table',9);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (37,'2026_09_06_100000_add_expires_at_to_personal_access_tokens_table',9);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (38,'2026_09_07_120000_drop_local_event_tables',9);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (39,'2026_09_07_140000_add_performance_indexes',9);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (40,'2026_09_07_160000_add_organization_views_index',9);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (41,'2026_09_07_170000_create_buffer_publications_table',9);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (42,'2026_09_07_180000_add_variants_to_images_table',9);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (43,'2026_09_07_190000_rebuild_views_table',9);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (44,'2026_09_07_200000_add_dimensions_to_images_table',9);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (45,'2026_09_08_100000_drop_big_thinks_table',10);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (46,'2026_09_08_120000_add_commentable_index_to_comments_table',10);
