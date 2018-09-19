/* Create required tables for Stripe */
  CREATE TABLE IF NOT EXISTS `civicrm_stripe_customers` (
    `id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
    `contact_id` int(10) UNSIGNED DEFAULT NULL COMMENT 'FK ID from civicrm_contact',
    `is_live` tinyint(4) NOT NULL COMMENT 'Whether this is a live or test transaction',
    `processor_id` int(10) DEFAULT NULL COMMENT 'ID from civicrm_payment_processor',
    UNIQUE KEY `id` (`id`),
    UNIQUE KEY `contact_id` (`contact_id`),
    CONSTRAINT `FK_civicrm_stripe_customers_contact_id` FOREIGN KEY (`contact_id`)
      REFERENCES `civicrm_contact` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

  CREATE TABLE IF NOT EXISTS `civicrm_stripe_plans` (
    `plan_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
    `is_live` tinyint(4) NOT NULL COMMENT 'Whether this is a live or test transaction',
    `processor_id` int(10) DEFAULT NULL COMMENT 'ID from civicrm_payment_processor',
    UNIQUE KEY `plan_id` (`plan_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

  CREATE TABLE IF NOT EXISTS `civicrm_stripe_subscriptions` (
    `subscription_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
    `customer_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
    `contribution_recur_id` INT(10) UNSIGNED NULL DEFAULT NULL,
    `end_time` int(11) NOT NULL DEFAULT '0',
    `is_live` tinyint(4) NOT NULL COMMENT 'Whether this is a live or test transaction',
    `processor_id` int(10) DEFAULT NULL COMMENT 'ID from civicrm_payment_processor',
    KEY `end_time` (`end_time`), PRIMARY KEY `subscription_id` (`subscription_id`),
    CONSTRAINT `FK_civicrm_stripe_contribution_recur_id` FOREIGN KEY (`contribution_recur_id`)
    REFERENCES `civicrm_contribution_recur`(`id`) ON DELETE SET NULL ON UPDATE RESTRICT
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
