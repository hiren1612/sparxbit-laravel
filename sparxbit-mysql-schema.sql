-- Sparx Bit — MySQL schema
-- Generated from Laravel migrations on 2026-10-22 07:34:16
-- Import into an empty `sparx_bit` database (schema only, no data).

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

create table `migrations` (`id` int unsigned not null auto_increment primary key, `migration` varchar(255) not null, `batch` int not null) default character set utf8mb4 collate 'utf8mb4_unicode_ci' engine = InnoDB;
create table `users` (`id` bigint unsigned not null auto_increment primary key, `name` varchar(255) not null, `email` varchar(255) not null, `email_verified_at` timestamp null, `password` varchar(255) not null, `remember_token` varchar(100) null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci' engine = InnoDB;
alter table `users` add unique `users_email_unique`(`email`);
create table `password_reset_tokens` (`email` varchar(255) not null, `token` varchar(255) not null, `created_at` timestamp null, primary key (`email`)) default character set utf8mb4 collate 'utf8mb4_unicode_ci' engine = InnoDB;
create table `sessions` (`id` varchar(255) not null, `user_id` bigint unsigned null, `ip_address` varchar(45) null, `user_agent` text null, `payload` longtext not null, `last_activity` int not null, primary key (`id`)) default character set utf8mb4 collate 'utf8mb4_unicode_ci' engine = InnoDB;
alter table `sessions` add index `sessions_user_id_index`(`user_id`);
alter table `sessions` add index `sessions_last_activity_index`(`last_activity`);
create table `cache` (`key` varchar(255) not null, `value` mediumtext not null, `expiration` int not null, primary key (`key`)) default character set utf8mb4 collate 'utf8mb4_unicode_ci' engine = InnoDB;
alter table `cache` add index `cache_expiration_index`(`expiration`);
create table `cache_locks` (`key` varchar(255) not null, `owner` varchar(255) not null, `expiration` int not null, primary key (`key`)) default character set utf8mb4 collate 'utf8mb4_unicode_ci' engine = InnoDB;
alter table `cache_locks` add index `cache_locks_expiration_index`(`expiration`);
create table `jobs` (`id` bigint unsigned not null auto_increment primary key, `queue` varchar(255) not null, `payload` longtext not null, `attempts` tinyint unsigned not null, `reserved_at` int unsigned null, `available_at` int unsigned not null, `created_at` int unsigned not null) default character set utf8mb4 collate 'utf8mb4_unicode_ci' engine = InnoDB;
alter table `jobs` add index `jobs_queue_index`(`queue`);
create table `job_batches` (`id` varchar(255) not null, `name` varchar(255) not null, `total_jobs` int not null, `pending_jobs` int not null, `failed_jobs` int not null, `failed_job_ids` longtext not null, `options` mediumtext null, `cancelled_at` int null, `created_at` int not null, `finished_at` int null, primary key (`id`)) default character set utf8mb4 collate 'utf8mb4_unicode_ci' engine = InnoDB;
create table `failed_jobs` (`id` bigint unsigned not null auto_increment primary key, `uuid` varchar(255) not null, `connection` text not null, `queue` text not null, `payload` longtext not null, `exception` longtext not null, `failed_at` timestamp not null default CURRENT_TIMESTAMP) default character set utf8mb4 collate 'utf8mb4_unicode_ci' engine = InnoDB;
alter table `failed_jobs` add unique `failed_jobs_uuid_unique`(`uuid`);
create table `personal_access_tokens` (`id` bigint unsigned not null auto_increment primary key, `tokenable_type` varchar(255) not null, `tokenable_id` bigint unsigned not null, `name` text not null, `token` varchar(64) not null, `abilities` text null, `last_used_at` timestamp null, `expires_at` timestamp null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci' engine = InnoDB;
alter table `personal_access_tokens` add index `personal_access_tokens_tokenable_type_tokenable_id_index`(`tokenable_type`, `tokenable_id`);
alter table `personal_access_tokens` add unique `personal_access_tokens_token_unique`(`token`);
alter table `personal_access_tokens` add index `personal_access_tokens_expires_at_index`(`expires_at`);
alter table `users` add `username` varchar(255) null after `email`;
alter table `users` add `bio` text null;
alter table `users` add `avatar_url` text null;
alter table `users` add `timezone` varchar(255) not null default 'UTC';
alter table `users` add `week_starts_on` enum('monday', 'sunday') not null default 'monday';
alter table `users` add `theme` enum('light', 'dark', 'system') not null default 'system';
alter table `users` add `language` varchar(8) not null default 'en';
alter table `users` add `notifications_push_enabled` tinyint(1) not null default '0';
alter table `users` add `default_reminder_time` varchar(255) null;
alter table `users` add `notifications_weekly_summary` tinyint(1) not null default '1';
alter table `users` add `notifications_product_updates` tinyint(1) not null default '0';
alter table `users` add `status` enum('active', 'archived', 'deleted') not null default 'active';
alter table `users` add `onboarding_completed_at` timestamp null;
alter table `users` add `archived_at` timestamp null;
alter table `users` add `auto_sync` tinyint(1) not null default '1';
alter table `users` add `referral_code` varchar(255) null;
alter table `users` add `referred_by_user_id` bigint unsigned null;
alter table `users` add `premium_tier` varchar(255) null;
alter table `users` add `business_name` varchar(255) null;
alter table `users` add `ad_tagline` varchar(255) null;
alter table `users` add `ad_link` varchar(255) null;
alter table `users` add `is_admin` tinyint(1) not null default '0';
alter table `users` add unique `users_username_unique`(`username`);
alter table `users` add unique `users_referral_code_unique`(`referral_code`);
alter table `users` add index `users_referred_by_user_id_index`(`referred_by_user_id`);
create table `goals` (`id` char(36) not null, `user_id` bigint unsigned null, `key` varchar(255) not null, `label` varchar(255) not null, `icon_key` varchar(255) not null, `created_at` timestamp null, `updated_at` timestamp null, primary key (`id`)) default character set utf8mb4 collate 'utf8mb4_unicode_ci' engine = InnoDB;
alter table `goals` add constraint `goals_user_id_foreign` foreign key (`user_id`) references `users` (`id`) on delete cascade;
alter table `goals` add unique `goals_user_id_key_unique`(`user_id`, `key`);
create table `categories` (`id` char(36) not null, `user_id` bigint unsigned not null, `name` varchar(30) not null, `color_hex` varchar(255) not null default '#6366F1', `is_system` tinyint(1) not null default '0', `created_at` timestamp null, `updated_at` timestamp null, primary key (`id`)) default character set utf8mb4 collate 'utf8mb4_unicode_ci' engine = InnoDB;
alter table `categories` add constraint `categories_user_id_foreign` foreign key (`user_id`) references `users` (`id`) on delete cascade;
alter table `categories` add unique `categories_user_id_name_unique`(`user_id`, `name`);
create table `suggestions` (`key` varchar(255) not null, `goal_key` varchar(255) not null, `title` varchar(255) not null, `default_why` text null, `default_goal_per_week` int not null default '5', `icon_key` varchar(255) not null default 'sparkle', `created_at` timestamp null, `updated_at` timestamp null, primary key (`key`)) default character set utf8mb4 collate 'utf8mb4_unicode_ci' engine = InnoDB;
create table `bits` (`id` char(36) not null, `user_id` bigint unsigned not null, `title` varchar(50) not null, `why` varchar(300) null, `description` varchar(500) null, `goal_per_week` int not null default '7', `start_date` date not null, `end_date` date null, `category_id` char(36) null, `goal_id` char(36) null, `icon_key` varchar(255) not null default 'sparkle', `reminder_enabled` tinyint(1) not null default '0', `reminder_time` varchar(255) null, `reminder_days` json null, `is_system` tinyint(1) not null default '0', `status` enum('active', 'paused', 'archived', 'completed') not null default 'active', `created_at` timestamp null, `updated_at` timestamp null, primary key (`id`)) default character set utf8mb4 collate 'utf8mb4_unicode_ci' engine = InnoDB;
alter table `bits` add constraint `bits_user_id_foreign` foreign key (`user_id`) references `users` (`id`) on delete cascade;
alter table `bits` add constraint `bits_category_id_foreign` foreign key (`category_id`) references `categories` (`id`) on delete set null;
alter table `bits` add constraint `bits_goal_id_foreign` foreign key (`goal_id`) references `goals` (`id`) on delete set null;
alter table `bits` add index `bits_user_id_index`(`user_id`);
create table `entries` (`id` char(36) not null, `bit_id` char(36) not null, `user_id` bigint unsigned not null, `date` date not null, `status` enum('done', 'missed', 'skipped') not null, `source` enum('manual', 'reminder_tap', 'backfill') not null default 'manual', `completed_at` timestamp null, `created_at` timestamp null, `updated_at` timestamp null, primary key (`id`)) default character set utf8mb4 collate 'utf8mb4_unicode_ci' engine = InnoDB;
alter table `entries` add constraint `entries_user_id_foreign` foreign key (`user_id`) references `users` (`id`) on delete cascade;
alter table `entries` add constraint `entries_bit_id_foreign` foreign key (`bit_id`) references `bits` (`id`) on delete cascade;
alter table `entries` add unique `entries_bit_id_date_unique`(`bit_id`, `date`);
alter table `entries` add index `entries_user_id_index`(`user_id`);
create table `referrals` (`id` char(36) not null, `referrer_id` bigint unsigned not null, `referee_id` bigint unsigned null, `code` varchar(255) not null, `status` enum('pending', 'completed', 'active', 'expired') not null default 'pending', `activated_at` timestamp null, `created_at` timestamp null, `updated_at` timestamp null, primary key (`id`)) default character set utf8mb4 collate 'utf8mb4_unicode_ci' engine = InnoDB;
alter table `referrals` add constraint `referrals_referrer_id_foreign` foreign key (`referrer_id`) references `users` (`id`) on delete cascade;
alter table `referrals` add constraint `referrals_referee_id_foreign` foreign key (`referee_id`) references `users` (`id`) on delete set null;
alter table `referrals` add unique `referrals_code_unique`(`code`);
create table `bit_changes` (`id` char(36) not null, `bit_id` char(36) not null, `user_id` bigint unsigned not null, `field` enum('goal', 'start', 'end') not null, `from_val` varchar(255) not null, `to_val` varchar(255) not null, `changed_at` timestamp not null default CURRENT_TIMESTAMP, primary key (`id`)) default character set utf8mb4 collate 'utf8mb4_unicode_ci' engine = InnoDB;
alter table `bit_changes` add constraint `bit_changes_user_id_foreign` foreign key (`user_id`) references `users` (`id`) on delete cascade;
alter table `bit_changes` add constraint `bit_changes_bit_id_foreign` foreign key (`bit_id`) references `bits` (`id`) on delete cascade;
alter table `bit_changes` add index `bit_changes_bit_id_index`(`bit_id`);
create table `feedback` (`id` char(36) not null, `user_id` bigint unsigned null, `type` enum('bug', 'feature', 'general') not null default 'general', `message` text not null, `created_at` timestamp null, `updated_at` timestamp null, primary key (`id`)) default character set utf8mb4 collate 'utf8mb4_unicode_ci' engine = InnoDB;
alter table `feedback` add constraint `feedback_user_id_foreign` foreign key (`user_id`) references `users` (`id`) on delete set null;
create table `magic_links` (`id` bigint unsigned not null auto_increment primary key, `email` varchar(255) not null, `token_hash` varchar(64) not null, `expires_at` timestamp not null, `used_at` timestamp null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci' engine = InnoDB;
alter table `magic_links` add index `magic_links_email_index`(`email`);
alter table `users` add `notifications_seen_at` timestamp null after `onboarding_completed_at`;
create table `push_tokens` (`id` bigint unsigned not null auto_increment primary key, `user_id` bigint unsigned not null, `token` varchar(255) not null, `platform` varchar(255) null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci' engine = InnoDB;
alter table `push_tokens` add constraint `push_tokens_user_id_foreign` foreign key (`user_id`) references `users` (`id`) on delete cascade;
alter table `push_tokens` add unique `push_tokens_token_unique`(`token`);
create table `legal_pages` (`id` bigint unsigned not null auto_increment primary key, `slug` varchar(255) not null, `title` varchar(255) not null, `body` longtext not null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci' engine = InnoDB;
alter table `legal_pages` add unique `legal_pages_slug_unique`(`slug`);
insert into `legal_pages` (`body`, `created_at`, `slug`, `title`, `updated_at`) values ('<p>Welcome to Sparx Bit. By creating an account or using the app (the "Service"), you agree to these Terms of Service. If you do not agree, please do not use the Service.</p>
<h3>1. The Service</h3>
<p>Sparx Bit is a habit-tracking application that lets you create habits ("Bits"), set weekly goals, and record your progress. The Service is provided on an "as is" and "as available" basis.</p>
<h3>2. Your account</h3>
<p>You are responsible for keeping your sign-in method secure and for all activity under your account. Please tell us immediately if you suspect any unauthorised use.</p>
<h3>3. Acceptable use</h3>
<p>You agree not to misuse the Service — including attempting to disrupt it, access it by unauthorised means, reverse-engineer it, or use it to break the law.</p>
<h3>4. Your content</h3>
<p>Your habits and check-ins belong to you. You can export or permanently delete your data at any time from Profile.</p>
<h3>5. Changes</h3>
<p>We may update these Terms from time to time. Continued use of the Service after changes means you accept the updated Terms.</p>
<h3>6. Contact</h3>
<p>Questions about these Terms? Reach us at info@yourdeveloperonline.com.</p>', '2026-10-22 07:34:16', 'terms', 'Terms of Service', '2026-10-22 07:34:16'), ('<p>This Privacy Policy explains what information Sparx Bit collects, how we use it, and the choices you have. We aim to collect only what we need to run the app for you.</p>
<h3>Information we collect</h3>
<p><strong>Account information</strong> — the email address (and name, if you add one) you sign in with.<br><strong>Your habit data</strong> — the Bits, weekly goals, check-ins, and notes you create.<br><strong>Basic diagnostics</strong> — limited technical data to help us fix issues.</p>
<h3>How we use your information</h3>
<p>To provide and improve the Service — to save your habits, sync them across devices, show your progress and insights, and send the reminders you choose to enable.</p>
<h3>Storage &amp; security</h3>
<p>Your data is stored securely on our servers. We never sell your personal data.</p>
<h3>Your choices</h3>
<p>You can edit your profile, turn reminders on or off, export a full copy of your data, or permanently delete your account — all from Profile.</p>
<h3>Contact</h3>
<p>Questions about privacy? Reach us at info@yourdeveloperonline.com.</p>', '2026-10-22 07:34:16', 'privacy', 'Privacy Policy', '2026-10-22 07:34:16');

-- Mark migrations as run
insert into `migrations` (`migration`, `batch`) values ('0001_01_01_000000_create_users_table', 1);
insert into `migrations` (`migration`, `batch`) values ('0001_01_01_000001_create_cache_table', 1);
insert into `migrations` (`migration`, `batch`) values ('0001_01_01_000002_create_jobs_table', 1);
insert into `migrations` (`migration`, `batch`) values ('2026_10_17_062130_create_personal_access_tokens_table', 1);
insert into `migrations` (`migration`, `batch`) values ('2026_10_17_100000_add_profile_fields_to_users', 1);
insert into `migrations` (`migration`, `batch`) values ('2026_10_17_100001_create_goals_table', 1);
insert into `migrations` (`migration`, `batch`) values ('2026_10_17_100002_create_categories_table', 1);
insert into `migrations` (`migration`, `batch`) values ('2026_10_17_100003_create_suggestions_table', 1);
insert into `migrations` (`migration`, `batch`) values ('2026_10_17_100004_create_bits_table', 1);
insert into `migrations` (`migration`, `batch`) values ('2026_10_17_100005_create_entries_table', 1);
insert into `migrations` (`migration`, `batch`) values ('2026_10_17_100006_create_referrals_table', 1);
insert into `migrations` (`migration`, `batch`) values ('2026_10_17_100007_create_bit_changes_table', 1);
insert into `migrations` (`migration`, `batch`) values ('2026_10_17_100008_create_feedback_table', 1);
insert into `migrations` (`migration`, `batch`) values ('2026_10_19_100000_create_magic_links_table', 1);
insert into `migrations` (`migration`, `batch`) values ('2026_10_19_120000_add_notifications_seen_at_to_users', 1);
insert into `migrations` (`migration`, `batch`) values ('2026_10_19_130000_create_push_tokens_table', 1);
insert into `migrations` (`migration`, `batch`) values ('2026_10_19_140000_create_legal_pages_table', 1);

SET FOREIGN_KEY_CHECKS = 1;
