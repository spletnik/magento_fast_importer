<?php

class Spletnisistemi_Fastimporter_Block_Adminhtml_Fastimporter_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {
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

        $run_import_url = $this->getUrl('fastimporter/adminhtml_fastimporter/import', array('id' => Mage::registry('fastimporter_data')->getId()));
        $status_import_url = $this->getUrl('fastimporter/adminhtml_fastimporter/status');//, array('id'=>Mage::registry('fastimporter_data')->getId()));
        $end_import_url = $this->getUrl('fastimporter/adminhtml_fastimporter/endimport');//, array('id'=>Mage::registry('fastimporter_data')->getId()));

        if (Mage::registry('fastimporter_data') && Mage::registry('fastimporter_data')->getProfileStatus() == 2) {
            $this->_addButton('run', array(
                'label' => '<strike>' . Mage::helper('adminhtml')->__('Start Importing') . '</strike>',
                'id'    => 'run_fast_importerx',
                'style' => "background: #a0a0a0; border: 1px solid #216B18;"
            ), -100);
        } else {
            $this->_addButton('run', array(
                'label' => Mage::helper('adminhtml')->__('Start Importing'),
                'id'    => 'run_fast_importer',
                'style' => "background: #5CC74E; border: 1px solid #216B18;"
            ), -100);
        }
        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('fastimporter_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'fastimporter_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'fastimporter_content');
                }
            }
            run_import_url = \"" . $run_import_url . "\";
            status_import_url = \"" . $status_import_url . "\";
            end_import_url = \"" . $end_import_url . "\";
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