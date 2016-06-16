<?php

class Spletnisistemi_Fastimporter_Model_Attributes extends Mage_Core_Model_Abstract {
    public function _construct() {
        parent::_construct();
        $this->_init('fastimporter/attributes');
    }
}
