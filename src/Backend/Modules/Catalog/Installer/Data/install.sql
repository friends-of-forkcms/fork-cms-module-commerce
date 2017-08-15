CREATE TABLE IF NOT EXISTS `catalog_comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `language` varchar(5) COLLATE utf8_unicode_ci NOT NULL,
  `created_on` datetime NOT NULL,
  `author` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `website` text COLLATE utf8_unicode_ci,
  `text` text COLLATE utf8_unicode_ci NOT NULL,
  `type` enum('comment','trackback') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'comment',
  `status` enum('published','moderation','spam') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'moderation',
  `data` text COLLATE utf8_unicode_ci COMMENT 'Serialized array with extra data',
  PRIMARY KEY (`id`),
  KEY `idx_post_id_status` (`product_id`,`status`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `catalog_files` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `filename` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `sequence` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `catalog_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `filename` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `sequence` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `catalog_related_products` (
  `product_id` int(11) NOT NULL,
  `related_product_id` int(11) NOT NULL,
  PRIMARY KEY (`product_id`,`related_product_id`),
  KEY `related_product_id` (`related_product_id`),
  KEY `related_product_id_2` (`related_product_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `catalog_videos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `embedded_url` text COLLATE utf8_unicode_ci NOT NULL,
  `sequence` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
