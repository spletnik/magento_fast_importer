<?php

class Spletnisistemi_Fastimporter_Model_Mysql4_Fastimporter extends Mage_Core_Model_Mysql4_Abstract {
    public function _construct() {
        // Note that the fastimporter_id refers to the key field in your database table.
        $this->_init('fastimporter/fastimporter', 'profile_id');
    }
}