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
$old = $installer->getTable('mapping');
$attrs = $installer->getTable('fastimporter_attributes');

$installer->run("
        ALTER TABLE $table ADD COLUMN `old` VARCHAR(255) NOT NULL;
	DROP TABLE IF EXISTS {$old};
	DROP TABLE IF EXISTS {$attrs};
	CREATE TABLE {$attrs} (
		`map_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
		`profile_id` INTEGER NOT NULL REFERENCES {$table}(profile_id),
		`attribute_code` VARCHAR(255) NOT NULL,
		`mapped` VARCHAR(255) DEFAULT NULL,
		`default` VARCHAR(255) DEFAULT NULL,
                `replace` VARCHAR(255) DEFAULT NULL
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->endSetup();
