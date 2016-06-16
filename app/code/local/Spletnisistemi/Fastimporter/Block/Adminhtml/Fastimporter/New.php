<?php

class Spletnisistemi_Fastimporter_Block_Adminhtml_Fastimporter_New extends Mage_Adminhtml_Block_Widget_Form_Container {
    public function __construct() {
        parent::__construct();

        $this->_objectId = 'id';
        $this->_blockGroup = 'fastimporter';
        $this->_controller = 'adminhtml_fastimporter';

        $this->_updateButton('save', 'label', Mage::helper('fastimporter')->__('Save'));
        $this->_updateButton('delete', 'label', Mage::helper('fastimporter')->__('Delete'));

        $this->_addButton('saveandcontinue', array(
            'label'   => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick' => 'saveAndContinueEdit()',
            'class'   => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('fastimporter_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'fastimporter_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'fastimporter_content');
                }
            }
            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText() {
        if (Mage::registry('fastimporter_data') && Mage::registry('fastimporter_data')->getId()) {
            return Mage::helper('fastimporter')->__("Edit '%s'", $this->htmlEscape(Mage::registry('fastimporter_data')->getData("profile_name")));
        } else {
            return Mage::helper('fastimporter')->__('Add');
        }
    }
}