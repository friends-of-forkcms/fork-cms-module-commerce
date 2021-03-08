-- Install the CommerceDemo theme
INSERT INTO `themes_templates` (`id`, `theme`, `label`, `path`, `active`, `data`) VALUES
(3, 'CommerceDemo', 'Default', 'Core/Layout/Templates/Default.html.twig', 1, 'a:4:{s:6:\"format\";s:16:\"[main,main,main]\";s:5:\"image\";b:1;s:5:\"names\";a:1:{i:0;s:4:\"main\";}s:14:\"default_extras\";a:1:{s:4:\"main\";a:0:{}}}'),
(4, 'CommerceDemo', 'Home', 'Core/Layout/Templates/Home.html.twig', 1, 'a:4:{s:6:\"format\";s:24:\"[/,/,/],[main,main,main]\";s:5:\"image\";b:1;s:5:\"names\";a:1:{i:0;s:4:\"main\";}s:14:\"default_extras\";a:1:{s:4:\"main\";a:0:{}}}');

-- Activate the theme
UPDATE `modules_settings` SET value='s:12:\"CommerceDemo\";' WHERE module = 'Core' AND name = 'theme';
UPDATE `modules_settings` SET value = 'i:3;' WHERE module = 'Pages' AND name = 'default_template';

-- Create a Shop page and add a widget to the homepage
INSERT INTO `meta` (`id`, `keywords`, `keywords_overwrite`, `description`, `description_overwrite`, `title`, `title_overwrite`, `url`, `url_overwrite`, `custom`, `data`, `seo_follow`, `seo_index`) VALUES 
    (70,'Shop',0,'Shop',0,'Shop',0,'shop',0,NULL,NULL,NULL,NULL),
    (68,'Home',0,'Home',0,'Home',0,'home',0,NULL,NULL,NULL,NULL);
UPDATE pages SET status='archive' WHERE type='page' AND title='Home';
INSERT INTO `pages` (`id`, `revision_id`, `user_id`, `parent_id`, `template_id`, `meta_id`, `language`, `type`, `title`, `navigation_title`, `navigation_title_overwrite`, `hidden`, `status`, `publish_on`, `data`, `created_on`, `edited_on`, `allow_move`, `allow_children`, `allow_edit`, `allow_delete`, `sequence`) VALUES 
    (1,13,1,0,4,68,'en','page','Home','Home',0,0,'active',NOW(),'a:4:{s:5:"image";N;s:13:"auth_required";b:0;s:24:"remove_from_search_index";b:0;s:10:"link_class";s:0:"";}',NOW(),NOW(),0,1,1,0,1),
    (407,15,1,1,3,70,'en','page','Shop','Shop',0,0,'active',NOW(),'a:4:{s:5:"image";N;s:13:"auth_required";b:0;s:24:"remove_from_search_index";b:0;s:10:"link_class";s:0:"";}',NOW(),NOW(),1,1,1,1,2);
INSERT INTO `pages_blocks` (`revision_id`, `position`, `extra_id`, `extra_type`, `extra_data`, `html`, `created_on`, `edited_on`, `visible`, `sequence`) VALUES 
    (13,'main',21,'widget','',NULL,NOW(),NOW(),1,0),
    (15,'main',9,'block','',NULL,NOW(),NOW(),1,0);


-- Create a cart page in the root
INSERT INTO `meta` (`id`, `keywords`, `keywords_overwrite`, `description`, `description_overwrite`, `title`, `title_overwrite`, `url`, `url_overwrite`, `custom`, `data`, `seo_follow`, `seo_index`) VALUES
    (71, 'Cart', 0, 'Cart', 0, 'Cart', 0, 'cart', 0, NULL, NULL, NULL, NULL);
INSERT INTO `pages` (`id`, `revision_id`, `user_id`, `parent_id`, `template_id`, `meta_id`, `language`, `type`, `title`, `navigation_title`, `navigation_title_overwrite`, `hidden`, `status`, `publish_on`, `data`, `created_on`, `edited_on`, `allow_move`, `allow_children`, `allow_edit`, `allow_delete`, `sequence`) VALUES
    (408, 16, 1, 0, 3, 71, 'en', 'root', 'Cart', 'Cart', 0, 0, 'active', '2021-03-08 02:19:15', 'a:3:{s:5:\"image\";N;s:13:\"auth_required\";b:0;s:24:\"remove_from_search_index\";b:0;}', '2021-03-08 02:19:15', '2021-03-08 02:19:15', 1, 1, 1, 1, 4);
INSERT INTO `pages_blocks` (`revision_id`, `position`, `extra_id`, `extra_type`, `extra_data`, `html`, `created_on`, `edited_on`, `visible`, `sequence`) VALUES 
    (16,'main',11,'block','',NULL,NOW(),NOW(),1,0);
