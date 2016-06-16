<?php

$installer = $this;

$installer->startSetup();

$installer->run("

DROP TABLE IF EXISTS {$this->getTable('fastimporter')};
CREATE TABLE {$this->getTable('fastimporter')} (
  `profile_id` int(11) unsigned NOT NULL auto_increment,
  `profile_name` varchar(255) NOT NULL default '',
  `mode` varchar(255) NOT NULL default '',
  `filename` varchar(255) NOT NULL default '',
  `file_url` varchar(255) NOT NULL default '',
  `images_path` varchar(255) NOT NULL default '',
  `profile_status` smallint(6) NOT NULL default '0',

  `attribute_set` varchar(255) NOT NULL default '',
  `type` varchar(255) NOT NULL default '',
  `sku` varchar(255) NOT NULL default '',
  `name` varchar(255) NOT NULL default '',
  `price` varchar(255) NOT NULL default '',
  `description` varchar(255) NOT NULL default '',
  `short_description` varchar(255) NOT NULL default '',
  `status` varchar(255) NOT NULL default '',
  `tax_class_id` varchar(255) NOT NULL default '',


  `weight` varchar(255) NOT NULL default '',
  `store` varchar(255) NOT NULL default '',
  `websites` varchar(255) NOT NULL default '',
  `category_ids` varchar(255) NOT NULL default '',
  `visibility` varchar(255) NOT NULL default '',
  `has_options` varchar(255) NOT NULL default '',
  `manufacturer` varchar(255) NOT NULL default '',
  `url_key` varchar(255) NOT NULL default '',
  `meta_title` varchar(255) NOT NULL default '',
  `meta_description` varchar(255) NOT NULL default '',
  `image` varchar(255) NOT NULL default '',
  `gift_message_available` varchar(255) NOT NULL default '',
  `options_container` varchar(255) NOT NULL default '',
  `custom_design` varchar(255) NOT NULL default '',
  `url_path` varchar(255) NOT NULL default '',
  `minimal_price` varchar(255) NOT NULL default '',
  `model` varchar(255) NOT NULL default '',
  `dimension` varchar(255) NOT NULL default '',
  `in_depth` varchar(255) NOT NULL default '',
  `meta_keyword` varchar(255) NOT NULL default '',
  `custom_layout_update` varchar(255) NOT NULL default '',
  

  `qty` varchar(255) NOT NULL default '',
  `min_qty` varchar(255) NOT NULL default '',
  `use_config_min_qty` varchar(255) NOT NULL default '',
  `is_qty_decimal` varchar(255) NOT NULL default '',
  `backorders` varchar(255) NOT NULL default '',
  `use_config_backorders` varchar(255) NOT NULL default '',
  `min_sale_qty` varchar(255) NOT NULL default '',
  `use_config_min_sale_qty` varchar(255) NOT NULL default '',
  `max_sale_qty` varchar(255) NOT NULL default '',
  `use_config_max_sale_qty` varchar(255) NOT NULL default '',
  `is_in_stock` varchar(255) NOT NULL default '',
  `low_stock_date` varchar(255) NOT NULL default '',
  `notify_stock_qty` varchar(255) NOT NULL default '',
  `use_config_notify_stock_qty` varchar(255) NOT NULL default '',
  `manage_stock` varchar(255) NOT NULL default '',
  `use_config_manage_stock` varchar(255) NOT NULL default '',
  `stock_status_changed_automatically` varchar(255) NOT NULL default '',
  `use_config_qty_increments` varchar(255) NOT NULL default '',
  `qty_increments` varchar(255) NOT NULL default '',
  `use_config_enable_qty_increments` varchar(255) NOT NULL default '',
  `enable_qty_increments` varchar(255) NOT NULL default '',


  `country_orgin` varchar(255) NOT NULL default '',
  `special_price` varchar(255) NOT NULL default '',
  `special_from_date` varchar(255) NOT NULL default '',


  `cronjob_executed` datetime NULL,
  `cronjob` varchar(255) NOT NULL default '',
  `created_time` datetime NULL,
  `update_time` datetime NULL,
  PRIMARY KEY (`profile_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

$installer->endSetup();


/*
old mapped attributes:




*/