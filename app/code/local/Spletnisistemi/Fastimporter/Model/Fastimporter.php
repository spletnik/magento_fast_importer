<?php

class Spletnisistemi_Fastimporter_Model_Fastimporter extends Mage_Core_Model_Abstract {
    public function _construct() {
        parent::_construct();
        $this->_init('fastimporter/fastimporter');
    }

    public function getAttributes() {
        return Mage::getModel('fastimporter/attributes')
            ->getCollection()
            ->addFieldToFilter('profile_id', $this->getId());
    }

    public function getMappedAttributes() {
        return Mage::getModel('fastimporter/attributes')
            ->getCollection()
            ->addFieldToFilter('profile_id', $this->getId())
            ->addFieldToFilter('mapped', array('notnull' => true));
    }

    public function getDefaultAttributes() {
        return Mage::getModel('fastimporter/attributes')
            ->getCollection()
            ->addFieldToFilter('profile_id', $this->getId())
            ->addFieldToFilter('`default`', array('notnull' => true));
    }

    public function getReplaceAttributes() {
        return Mage::getModel('fastimporter/attributes')
            ->getCollection()
            ->addFieldToFilter('profile_id', $this->getId())
            ->addFieldToFilter('`replace`', array('notnull' => true));
    }
}
