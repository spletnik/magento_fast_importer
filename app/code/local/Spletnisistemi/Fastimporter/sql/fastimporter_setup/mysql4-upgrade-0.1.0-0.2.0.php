<?php
$installer = $this;
$installer->startSetup();
$table = $installer->getTable('fastimporter');
$map = $installer->getTable('mapping');
$eav = $installer->getTable('catalog/eav_attribute');

$installer->run("
ALTER TABLE {$table}
  DROP `attribute_set`,
  DROP `type`,
  DROP `sku`,
  DROP `name`,
  DROP `price`,
  DROP `description`,
  DROP `short_description`,
  DROP `status`,
  DROP `tax_class_id`,


  DROP `weight`,
  DROP `store`,
  DROP `websites`,
  DROP `category_ids`,
  DROP `visibility`,
  DROP `has_options`,
  DROP `manufacturer`,
  DROP `url_key`,
  DROP `meta_title`,
  DROP `meta_description`,
  DROP `image`,
  DROP `gift_message_available`,
  DROP `options_container`,
  DROP `custom_design`,
  DROP `url_path`,
  DROP `minimal_price`,
  DROP `model`,
  DROP `dimension`,
  DROP `in_depth`,
  DROP `meta_keyword`,
  DROP `custom_layout_update`,
  

  DROP `qty`,
  DROP `min_qty`,
  DROP `use_config_min_qty`,
  DROP `is_qty_decimal`,
  DROP `backorders`,
  DROP `use_config_backorders`,
  DROP `min_sale_qty`,
  DROP `use_config_min_sale_qty`,
  DROP `max_sale_qty`,
  DROP `use_config_max_sale_qty`,
  DROP `is_in_stock`,
  DROP `low_stock_date`,
  DROP `notify_stock_qty`,
  DROP `use_config_notify_stock_qty`,
  DROP `manage_stock`,
  DROP `use_config_manage_stock`,
  DROP `stock_status_changed_automatically`,
  DROP `use_config_qty_increments`,
  DROP `qty_increments`,
  DROP `use_config_enable_qty_increments`,
  DROP `enable_qty_increments`,


  DROP `country_orgin`,
  DROP `special_price`,
  DROP `special_from_date`;

DROP TABLE IF EXISTS {$map};
CREATE TABLE {$map} (
  `map_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `profile_id` INTEGER NOT NULL REFERENCES {$table}(profile_id),
  `attribute_id` INTEGER NOT NULL REFERENCES {$eav}(attribute_id),
  `mapped` VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->endSetup();
