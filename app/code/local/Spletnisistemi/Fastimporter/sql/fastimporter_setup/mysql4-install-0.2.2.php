<?php

$installer = $this;
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
$installer->startSetup();

$setup->addAttribute('catalog_product', 'import_id', array(
    'backend_type' => 'int',
    'type'         => 'int',
    'visible'      => 0,
    'required'     => 0,
    'user_defined' => 1,
    'global'       => 1,
));

$table = $installer->getTable('fastimporter');
$map = $installer->getTable('fastimporter_attributes');

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
  `old` varchar(255) NOT NULL,

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
  `attribute_code` VARCHAR(255) NOT NULL,
  `mapped` VARCHAR(255) DEFAULT NULL,
  `default` VARCHAR(255) DEFAULT NULL,
  `replace` VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->endSetup();
