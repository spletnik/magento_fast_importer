<?php
$installer = $this;
$installer->startSetup();
$table = $installer->getTable('fastimporter');
$map = $installer->getTable('mapping');
$eav = $installer->getTable('catalog/eav_attribute');

$installer->run("
ALTER TABLE {$map} CHANGE `mapped` `mapped` VARCHAR(255) DEFAULT NULL;
ALTER TABLE {$map} ADD COLUMN `default` VARCHAR(255) DEFAULT NULL;
");

$installer->endSetup();
