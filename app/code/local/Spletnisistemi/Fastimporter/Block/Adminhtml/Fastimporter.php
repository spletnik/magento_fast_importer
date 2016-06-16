<?php

class Spletnisistemi_Fastimporter_Block_Adminhtml_Fastimporter extends Mage_Adminhtml_Block_Widget_Grid_Container {
    public function __construct() {
        $this->_controller = 'adminhtml_fastimporter';
        $this->_blockGroup = 'fastimporter';
        $this->_headerText = Mage::helper('fastimporter')->__('Profile Manager');
        $this->_addButtonLabel = Mage::helper('fastimporter')->__('Add Profile');
        parent::__construct();
    }
}