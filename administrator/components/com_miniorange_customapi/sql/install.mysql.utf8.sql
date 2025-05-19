
CREATE TABLE IF NOT EXISTS `#__miniorange_customapi_customer_details` (
`id` int(11) UNSIGNED NOT NULL,
`email` VARCHAR(255) NOT NULL,
`password` VARCHAR(255) NOT NULL,
`admin_phone` VARCHAR(255) NOT NULL,
`customer_key` VARCHAR(255) NOT NULL,
`customer_token` VARCHAR(255) NOT NULL,
`api_key` VARCHAR(255) NOT NULL,
`status` VARCHAR(255) NOT NULL,
`login_status` tinyint(1) DEFAULT 0,
`registration_status` VARCHAR(255) NOT NULL,
`transaction_id` VARCHAR(255) NOT NULL,
`email_count` int(11),
`sms_count` int(11),
PRIMARY KEY (`id`)
) DEFAULT COLLATE=utf8_general_ci;

CREATE TABLE IF NOT EXISTS `#__miniorange_customapi_settings` (
`id` int(11) UNSIGNED NOT NULL,
`mo_custom_apis` text NOT NULL,
`mo_custom_sql_apis` text NOT NULL,
`mo_external_apis` text NOT NULL,
PRIMARY KEY (`id`)
) DEFAULT COLLATE=utf8_general_ci;

INSERT IGNORE INTO `#__miniorange_customapi_customer_details`(`id`,`login_status`) values (1,0);
INSERT IGNORE INTO `#__miniorange_customapi_settings`(`id`) values (1);

