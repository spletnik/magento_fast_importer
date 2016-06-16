<?php

class Spletnisistemi_Fastimporter_Model_Mysql4_Attributes extends Mage_Core_Model_Mysql4_Abstract {
    public function _construct() {
        $this->_init('fastimporter/attributes', 'map_id');
    }
}
