-- Execute these queries to uninstall the module (used for module development)

-- Drop module tables
SET FOREIGN_KEY_CHECKS=0;
DROP TABLE IF EXISTS commerce_account;
DROP TABLE IF EXISTS commerce_brands;
DROP TABLE IF EXISTS commerce_cart_cart_rules;
DROP TABLE IF EXISTS commerce_cart_rules;
DROP TABLE IF EXISTS commerce_cart_value_options;
DROP TABLE IF EXISTS commerce_cart_values;
DROP TABLE IF EXISTS commerce_carts;
DROP TABLE IF EXISTS commerce_categories;
DROP TABLE IF EXISTS commerce_countries;
DROP TABLE IF EXISTS commerce_order_addresses;
DROP TABLE IF EXISTS commerce_order_histories;
DROP TABLE IF EXISTS commerce_order_product_notifications;
DROP TABLE IF EXISTS commerce_order_product_options;
DROP TABLE IF EXISTS commerce_order_products;
DROP TABLE IF EXISTS commerce_order_rules;
DROP TABLE IF EXISTS commerce_order_statuses;
DROP TABLE IF EXISTS commerce_order_vats;
DROP TABLE IF EXISTS commerce_orders;
DROP TABLE IF EXISTS commerce_payment_methods;
DROP TABLE IF EXISTS commerce_product_dimension_notification;
DROP TABLE IF EXISTS commerce_product_dimensions;
DROP TABLE IF EXISTS commerce_product_option_values;
DROP TABLE IF EXISTS commerce_product_option_values_dependencies;
DROP TABLE IF EXISTS commerce_product_options;
DROP TABLE IF EXISTS commerce_product_specials;
DROP TABLE IF EXISTS commerce_products;
DROP TABLE IF EXISTS commerce_products_specification_values;
DROP TABLE IF EXISTS commerce_related_products;
DROP TABLE IF EXISTS commerce_shipment_methods;
DROP TABLE IF EXISTS commerce_specification_values;
DROP TABLE IF EXISTS commerce_specifications;
DROP TABLE IF EXISTS commerce_stock_statuses;
DROP TABLE IF EXISTS commerce_up_sell_products;
DROP TABLE IF EXISTS commerce_vats;
SET FOREIGN_KEY_CHECKS=1;

-- Remove from backend navigation
DELETE FROM backend_navigation WHERE label = 'Commerce';
DELETE FROM backend_navigation WHERE url LIKE '%commerce%';

-- Remove from groups_rights
DELETE FROM groups_rights_actions WHERE module = 'Commerce';
DELETE FROM groups_rights_modules WHERE module = 'Commerce';

-- Remove from locale
DELETE FROM locale WHERE module = 'Commerce';
DELETE FROM locale WHERE module = 'core' AND name LIKE 'Commerce%';

-- Remove from modules
DELETE FROM modules WHERE name = 'Commerce';
DELETE FROM modules_extras WHERE module = 'Commerce';
DELETE FROM modules_settings WHERE module = 'Commerce';

-- Don't forget to clear cache: bin/console c:c
