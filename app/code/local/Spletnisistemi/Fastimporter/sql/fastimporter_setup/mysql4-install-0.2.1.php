<?php

$installer = $this;
$installer->startSetup();

$table = $installer->getTable('fastimporter');
$map = $installer->getTable('mapping');
$eav = $installer->getTable('catalog/eav_attribute');

$installer->run("
DROP TABLE IF EXISTS {$table};
CREATE TABLE {$table} (
  `profile_id` int(11) unsigned NOT NULL auto_increment,
  `profile_name` varchar(255) NOT NULL default '',
  `mode` varchar(255) NOT NULL default '',
  `filename` varchar(255) NOT NULL default '',
  `file_url` varchar(255) NOT NULL default '',
  `images_path` varchar(255) NOT NULL default '',
  `profile_status` smallint(6) NOT NULL default '0',

  `cronjob_executed` datetime NULL,
  `cronjob` varchar(255) NOT NULL default '',
  `created_time` datetime NULL,
  `update_time` datetime NULL,
  PRIMARY KEY (`profile_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS {$map};
CREATE TABLE {$map} (
  `map_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `profile_id` INTEGER NOT NULL REFERENCES {$table}(profile_id),
  `attribute_id` INTEGER NOT NULL REFERENCES {$eav}(attribute_id),
  `mapped` VARCHAR(255) DEFAULT NULL,
  `default` VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->endSetup();
