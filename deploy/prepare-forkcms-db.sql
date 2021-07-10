-- Install the CommerceDemo theme
INSERT INTO `themes_templates` (`id`, `theme`, `label`, `path`, `active`, `data`) VALUES
(3, 'CommerceDemo', 'Default', 'Core/Layout/Templates/Default.html.twig', 1, 'a:4:{s:6:\"format\";s:16:\"[main,main,main]\";s:5:\"image\";b:1;s:5:\"names\";a:1:{i:0;s:4:\"main\";}s:14:\"default_extras\";a:1:{s:4:\"main\";a:0:{}}}'),
(4, 'CommerceDemo', 'Home', 'Core/Layout/Templates/Home.html.twig', 1, 'a:4:{s:6:\"format\";s:24:\"[/,/,/],[main,main,main]\";s:5:\"image\";b:1;s:5:\"names\";a:1:{i:0;s:4:\"main\";}s:14:\"default_extras\";a:1:{s:4:\"main\";a:0:{}}}');

-- Activate the theme
UPDATE `modules_settings` SET value='s:12:\"CommerceDemo\";' WHERE module = 'Core' AND name = 'theme';
UPDATE `modules_settings` SET value = 'i:3;' WHERE module = 'Pages' AND name = 'default_template';

-- Create a Shop page with the Commerce block assigned to it
INSERT INTO `meta` (`id`, `keywords`, `keywords_overwrite`, `description`, `description_overwrite`, `title`, `title_overwrite`, `url`, `url_overwrite`, `custom`, `data`, `seo_follow`, `seo_index`) VALUES 
    (NULL,'Shop',0,'Shop',0,'Shop',0,'shop',0,NULL,NULL,NULL,NULL);
SET @new_page_id = (SELECT MAX(id)+1 FROM pages);
INSERT INTO `pages` (`id`, `revision_id`, `user_id`, `parent_id`, `template_id`, `meta_id`, `language`, `type`, `title`, `navigation_title`, `navigation_title_overwrite`, `hidden`, `status`, `publish_on`, `data`, `created_on`, `edited_on`, `allow_move`, `allow_children`, `allow_edit`, `allow_delete`, `sequence`) VALUES 
    (@new_page_id,NULL,1,1,3,LAST_INSERT_ID(),'en','page','Shop','Shop',0,0,'active',NOW(),NULL,NOW(),NOW(),1,1,1,1,2);
SET @module_block_extra_id = (SELECT id FROM modules_extras WHERE module = 'Commerce' AND type = 'block' AND action IS NULL);
INSERT INTO `pages_blocks` (`revision_id`, `position`, `extra_id`, `extra_type`, `extra_data`, `html`, `created_on`, `edited_on`, `visible`, `sequence`) VALUES 
    (LAST_INSERT_ID(),'main',@module_block_extra_id,'block','',NULL,NOW(),NOW(),1,0);

-- Set the homepage to our Home template
UPDATE pages SET template_id = 4 WHERE id = 1;

-- Add a recent products widget on the homepage
SET @module_widget_extra_id = (SELECT id FROM modules_extras WHERE module = 'Commerce' AND type = 'widget' AND action = 'RecentProducts');
INSERT INTO `pages_blocks` (`revision_id`, `position`, `extra_id`, `extra_type`, `extra_data`, `html`, `created_on`, `edited_on`, `visible`, `sequence`) VALUES 
    (1,'main',@module_widget_extra_id,'widget','',NULL,NOW(),NOW(),1,0);

-- Create a cart page in the root with the Cart block
INSERT INTO `meta` (`id`, `keywords`, `keywords_overwrite`, `description`, `description_overwrite`, `title`, `title_overwrite`, `url`, `url_overwrite`, `custom`, `data`, `seo_follow`, `seo_index`) VALUES
    (NULL, 'Cart', 0, 'Cart', 0, 'Cart', 0, 'cart', 0, NULL, NULL, NULL, NULL);
SET @new_page_id = (SELECT MAX(id)+1 FROM pages);
INSERT INTO `pages` (`id`, `revision_id`, `user_id`, `parent_id`, `template_id`, `meta_id`, `language`, `type`, `title`, `navigation_title`, `navigation_title_overwrite`, `hidden`, `status`, `publish_on`, `data`, `created_on`, `edited_on`, `allow_move`, `allow_children`, `allow_edit`, `allow_delete`, `sequence`) VALUES 
    (@new_page_id,NULL,1,0,3,LAST_INSERT_ID(),'en','root','Cart','Cart',0,0,'active',NOW(),NULL,NOW(),NOW(),1,1,1,1,4);
SET @module_block_cart_extra_id = (SELECT id FROM modules_extras WHERE module = 'Commerce' AND type = 'block' AND action = 'Cart');
INSERT INTO `pages_blocks` (`revision_id`, `position`, `extra_id`, `extra_type`, `extra_data`, `html`, `created_on`, `edited_on`, `visible`, `sequence`) VALUES 
    (LAST_INSERT_ID(),'main',@module_block_cart_extra_id,'block','',NULL,NOW(),NOW(),1,0);
