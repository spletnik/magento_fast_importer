<?php

class Spletnisistemi_Fastimporter_Block_Adminhtml_Fastimporter_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {

    public function __construct() {
        parent::__construct();
        $this->setId('fastimporter_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('fastimporter')->__('Profile Information'));
    }

    protected function _beforeToHtml() {
        $this->addTab('form_section', array(
            'label'   => Mage::helper('fastimporter')->__('Profile Settings'),
            'title'   => Mage::helper('fastimporter')->__('Profile Settings'),
            'content' => $this->getLayout()->createBlock('fastimporter/adminhtml_fastimporter_edit_tab_form')->toHtml(),
        ));
        $this->addTab('advanced_section', array(
            'label'   => Mage::helper('fastimporter')->__('Advanced'),
            'title'   => Mage::helper('fastimporter')->__('Advanced'),
            'content' => $this->getLayout()->createBlock('fastimporter/adminhtml_fastimporter_edit_tab_advanced')->toHtml(),
        ));

        return parent::_beforeToHtml();
    }
}