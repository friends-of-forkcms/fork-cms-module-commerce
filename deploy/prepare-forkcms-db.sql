-- Set the website name
UPDATE modules_settings SET value = 's:13:"Commerce Demo";' WHERE module = 'Core' AND name LIKE 'site_title%';

-- Fix the god.png avatar
UPDATE users_settings SET value = REPLACE(value, 'god.jpg', 'god.png') WHERE name = 'avatar';

-- Install the CommerceDemo theme
INSERT INTO `themes_templates` (`id`, `theme`, `label`, `path`, `active`, `data`) VALUES
(3, 'CommerceDemo', 'Default', 'Core/Layout/Templates/Default.html.twig', 1, 'a:4:{s:6:\"format\";s:16:\"[main,main,main]\";s:5:\"image\";b:1;s:5:\"names\";a:1:{i:0;s:4:\"main\";}s:14:\"default_extras\";a:1:{s:4:\"main\";a:0:{}}}'),
(4, 'CommerceDemo', 'Home', 'Core/Layout/Templates/Home.html.twig', 1, 'a:4:{s:6:\"format\";s:24:\"[/,/,/],[main,main,main]\";s:5:\"image\";b:1;s:5:\"names\";a:1:{i:0;s:4:\"main\";}s:14:\"default_extras\";a:1:{s:4:\"main\";a:0:{}}}');

-- Activate the theme
UPDATE `modules_settings` SET value='s:12:\"CommerceDemo\";' WHERE module = 'Core' AND name = 'theme';
UPDATE `modules_settings` SET value = 'i:3;' WHERE module = 'Pages' AND name = 'default_template';

-- Set the homepage to our Home template
UPDATE pages SET template_id = 4 WHERE id = 1;

-- Add a recent products widget on the homepage
SET @module_widget_extra_id = (SELECT id FROM modules_extras WHERE module = 'Commerce' AND type = 'widget' AND action = 'RecentProducts');
INSERT INTO `pages_blocks` (`revision_id`, `position`, `extra_id`, `extra_type`, `extra_data`, `html`, `created_on`, `edited_on`, `visible`, `sequence`) VALUES
    (1,'main',@module_widget_extra_id,'widget','',NULL,NOW(),NOW(),1,0),
    (5,'main',@module_widget_extra_id,'widget','',NULL,NOW(),NOW(),1,0);

-- Setup a mailer for demo purposes using a free Mailjet account (200 mails per day, 6000 emails per month)
-- See: https://www.mailjet.com/pricing/#email
REPLACE INTO `modules_settings` (`module`, `name`, `value`) VALUES
   ('Core', 'mailer_from', 'a:2:{s:4:\"name\";s:17:\"Fork CMS Commerce\";s:5:\"email\";s:21:\"bey4i7gsj@mozmail.com\";}'),
   ('Core', 'mailer_reply_to', 'a:2:{s:4:\"name\";s:17:\"Fork CMS Commerce\";s:5:\"email\";s:21:\"bey4i7gsj@mozmail.com\";}'),
   ('Core', 'mailer_to', 'a:2:{s:4:\"name\";s:17:\"Fork CMS Commerce\";s:5:\"email\";s:21:\"bey4i7gsj@mozmail.com\";}'),
   ('Core', 'mailer_type', 's:4:\"smtp\";'),
   ('Core', 'smtp_password', 's:32:\"${SMTP_PASSWORD}\";'),
   ('Core', 'smtp_port', 's:3:\"587\";'),
   ('Core', 'smtp_secure_layer', 's:3:\"tls\";'),
   ('Core', 'smtp_server', 's:17:\"in-v3.mailjet.com\";'),
   ('Core', 'smtp_username', 's:32:\"${SMTP_USERNAME}\";');
