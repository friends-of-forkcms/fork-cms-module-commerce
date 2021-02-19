-- Execute these queries to uninstall the module (used for module development)

-- Drop module tables
SET FOREIGN_KEY_CHECKS=0;
DROP TABLE IF EXISTS catalog_brands;
DROP TABLE IF EXISTS catalog_cart_cart_rules;
DROP TABLE IF EXISTS catalog_cart_rules;
DROP TABLE IF EXISTS catalog_cart_value_options;
DROP TABLE IF EXISTS catalog_cart_values;
DROP TABLE IF EXISTS catalog_carts;
DROP TABLE IF EXISTS catalog_categories;
DROP TABLE IF EXISTS catalog_countries;
DROP TABLE IF EXISTS catalog_order_addresses;
DROP TABLE IF EXISTS catalog_order_histories;
DROP TABLE IF EXISTS catalog_order_product_options;
DROP TABLE IF EXISTS catalog_order_products;
DROP TABLE IF EXISTS catalog_order_statuses;
DROP TABLE IF EXISTS catalog_order_vats;
DROP TABLE IF EXISTS catalog_orders;
DROP TABLE IF EXISTS catalog_payment_methods;
DROP TABLE IF EXISTS catalog_product_dimensions;
DROP TABLE IF EXISTS catalog_product_option_values;
DROP TABLE IF EXISTS catalog_product_option_values_dependencies;
DROP TABLE IF EXISTS catalog_product_options;
DROP TABLE IF EXISTS catalog_product_specials;
DROP TABLE IF EXISTS catalog_specials;
DROP TABLE IF EXISTS catalog_products;
DROP TABLE IF EXISTS catalog_products_specification_values;
DROP TABLE IF EXISTS catalog_related_products;
DROP TABLE IF EXISTS catalog_shipment_methods;
DROP TABLE IF EXISTS catalog_specification_values;
DROP TABLE IF EXISTS catalog_specifications;
DROP TABLE IF EXISTS catalog_stock_statuses;
DROP TABLE IF EXISTS catalog_up_sell_products;
DROP TABLE IF EXISTS catalog_vats;
SET FOREIGN_KEY_CHECKS=1;

-- Remove from backend navigation
DELETE FROM backend_navigation WHERE label = 'Catalog';
DELETE FROM backend_navigation WHERE url LIKE '%catalog%';

-- Remove from groups_rights
DELETE FROM groups_rights_actions WHERE module = 'Catalog';
DELETE FROM groups_rights_modules WHERE module = 'Catalog';

-- Remove from locale
DELETE FROM locale WHERE module = 'Catalog';
DELETE FROM locale WHERE module = 'core' AND name LIKE 'Catalog%';

-- Remove from modules
DELETE FROM modules WHERE name = 'Catalog';
DELETE FROM modules_extras WHERE module = 'Catalog';
DELETE FROM modules_settings WHERE module = 'Catalog';
