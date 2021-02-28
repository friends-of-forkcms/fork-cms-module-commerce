-- Install the CommerceDemo theme
INSERT INTO `themes_templates` (`id`, `theme`, `label`, `path`, `active`, `data`) VALUES 
    (3,'CommerceDemo','Default','Core/Layout/Templates/Default.html.twig',1,'a:4:{s:6:"format";s:16:"[main,main,main]";s:5:"image";b:1;s:5:"names";a:1:{i:0;s:4:"main";}s:14:"default_extras";a:1:{s:4:"main";a:0:{}}}'),
    (4,'CommerceDemo','Home','Core/Layout/Templates/Home.html.twig',1,'a:4:{s:6:"format";s:24:"[/,/,/],[main,main,main]";s:5:"image";b:1;s:5:"names";a:1:{i:0;s:4:"main";}s:14:"default_extras";a:1:{s:4:"main";a:0:{}}}');

-- Activate the theme
UPDATE `modules_settings` SET value='s:12:\"CommerceDemo\";' WHERE module = 'Core' AND name = 'theme';

-- Create a Shop page and add a widget to the homepage
INSERT INTO `meta` (`id`, `keywords`, `keywords_overwrite`, `description`, `description_overwrite`, `title`, `title_overwrite`, `url`, `url_overwrite`, `custom`, `data`, `seo_follow`, `seo_index`) VALUES 
    (70,'Shop',0,'Shop',0,'Shop',0,'shop',0,NULL,NULL,NULL,NULL),
    (68,'Home',0,'Home',0,'Home',0,'home',0,NULL,NULL,NULL,NULL);
UPDATE pages SET status='archive' WHERE type='page' AND title='Home';
INSERT INTO `pages` (`id`, `revision_id`, `user_id`, `parent_id`, `template_id`, `meta_id`, `language`, `type`, `title`, `navigation_title`, `navigation_title_overwrite`, `hidden`, `status`, `publish_on`, `data`, `created_on`, `edited_on`, `allow_move`, `allow_children`, `allow_edit`, `allow_delete`, `sequence`) VALUES 
    (1,13,1,0,4,68,'en','page','Home','Home',0,0,'active','2021-02-25 20:56:55','a:4:{s:5:"image";N;s:13:"auth_required";b:0;s:24:"remove_from_search_index";b:0;s:10:"link_class";s:0:"";}','2021-02-25 20:56:55','2021-02-28 16:44:44',0,1,1,0,1),
    (407,15,1,1,3,70,'en','page','Shop','Shop',0,0,'active','2021-02-28 16:45:04','a:4:{s:5:"image";N;s:13:"auth_required";b:0;s:24:"remove_from_search_index";b:0;s:10:"link_class";s:0:"";}','2021-02-28 16:45:04','2021-02-28 16:45:11',1,1,1,1,2);
INSERT INTO `pages_blocks` (`revision_id`, `position`, `extra_id`, `extra_type`, `extra_data`, `html`, `created_on`, `edited_on`, `visible`, `sequence`) VALUES 
    (13,'main',21,'widget','',NULL,'2021-02-28 16:44:44','2021-02-28 16:44:44',1,0),
    (13,'main',NULL,'rich_text','','<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam id magna. Proin euismod vestibulum tortor. Vestibulum eget nisl. Donec interdum quam at nunc. In laoreet orci sit amet sem. In sed metus ac nunc blandit ultricies. Maecenas sed tortor. Sed velit velit, mollis quis, ultricies tincidunt, dictum ac, felis. Integer hendrerit consectetur libero. Duis sem. Mauris tellus justo, sollicitudin at, vehicula eget, auctor vel, odio. Proin mattis. Mauris mollis elit sit amet lectus. Vestibulum in tortor sodales elit sollicitudin gravida. Integer scelerisque sollicitudin velit. Aliquam erat volutpat. Sed ut nisl congue justo pharetra accumsan.</p>','2021-02-28 16:44:44','2021-02-28 16:44:44',1,1),
    (15,'main',9,'block','',NULL,'2021-02-28 16:45:11','2021-02-28 16:45:11',1,0);
