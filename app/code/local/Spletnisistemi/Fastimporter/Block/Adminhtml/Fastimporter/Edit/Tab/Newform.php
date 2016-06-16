<?php

class Spletnisistemi_Fastimporter_Block_Adminhtml_Fastimporter_Edit_Tab_Newform extends Mage_Adminhtml_Block_Widget_Form {
    protected function _prepareForm() {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('fastimporter_form', array('legend' => Mage::helper('fastimporter')->__('Profile information')));

        $fieldset->addField('profile_name', 'text', array(
            'label'    => Mage::helper('fastimporter')->__('Profile name'),
            'title'    => Mage::helper('fastimporter')->__('Profile name'),
            'class'    => 'required-entry',
            'required' => true,
            'name'     => 'profile_name',
        ));

        $fieldset->addType('extended_label', 'Spletnisistemi_Fastimporter_Lib_Varien_Data_Form_Element_ExtendedLabel');
        $fieldset->addField('help-text', 'extended_label', array(
            'label'       => Mage::helper('fastimporter')->__('Upload file OR enter URL path to import file'),
            'required'    => false,
            'label_style' => 'font-weight: bold; font-size: 13px;',
            'note'        => 'If both are provided, url will be used!'
        ));

        $fieldset->addField('filename', 'file', array(
            'label'    => Mage::helper('fastimporter')->__('File'),
            'required' => false,
            'name'     => 'filename',
        ));

        $fieldset->addField('file_url', 'text', array(
            'name'     => 'file_url',
            'label'    => Mage::helper('fastimporter')->__('URL path to import file'),
            'title'    => Mage::helper('fastimporter')->__('URL path to import file'),
            'required' => false,
        ));


        $fieldset->addField('images_path', 'text', array(
            'name'     => 'images_path',
            'label'    => Mage::helper('fastimporter')->__('Path to images'),
            'title'    => Mage::helper('fastimporter')->__('Path to images'),
            'required' => false,
        ));
        $fieldset->addField('mode', 'select', array(
            'label'  => Mage::helper('fastimporter')->__('Import mode'),
            'name'   => 'mode',
            'values' => array(
                array(
                    'value' => 'update',
                    'label' => Mage::helper('fastimporter')->__('Update existing, skip new'),
                ),
                array(
                    'value' => 'create',
                    'label' => Mage::helper('fastimporter')->__('Create and Update'),
                ),
                array(
                    'value' => 'xcreate',
                    'label' => Mage::helper('fastimporter')->__('Create new, skip existing'),
                ),
                array(
                    'value' => 'delete',
                    'label' => Mage::helper('fastimporter')->__('Delete'),
                ),
                array(
                    'value' => 'delete-all',
                    'label' => Mage::helper('fastimporter')->__('Delete All Products'),
                ),
            ),
        ));
        $fieldset->addField('profile_status', 'select', array(
            'label'  => Mage::helper('fastimporter')->__('Profile Status'),
            'name'   => 'profile_status',
            'values' => array(
                array(
                    'value' => 1,
                    'label' => Mage::helper('fastimporter')->__('Enabled'),
                ),

                array(
                    'value' => 2,
                    'label' => Mage::helper('fastimporter')->__('Disabled'),
                ),
            ),
        ));

        $fieldset->addField('removeold', 'select', array(
            'label'  => Mage::helper('fastimporter')->__('Old products'),
            'name'   => 'old',
            'note'   => 'Not imported products.',
            'values' => array(
                array(
                    'value' => '',
                    'label' => Mage::helper('fastimporter')->__('Nothing')
                ),
                array(
                    'value' => 'disable',
                    'label' => Mage::helper('fastimporter')->__('Disable')
                ),
                array(
                    'value' => 'remove',
                    'label' => Mage::helper('fastimporter')->__('Remove')
                ),
            ),
        ));

        $fieldset->addField('cronjob', 'select', array(
            'label'  => Mage::helper('fastimporter')->__('Cronjob'),
            'name'   => 'cronjob',
            'values' => array(
                array(
                    'value' => '',
                    'label' => Mage::helper('fastimporter')->__('Never'),
                ),
                /*array(
                    'value'     => '* * * * *',
                    'label'     => Mage::helper('fastimporter')->__('At every minute'),
                ),*/
                array(
                    'value' => '0 * * * *',
                    'label' => Mage::helper('fastimporter')->__('At every full hour'),
                ),
                array(
                    'value' => '0 0 * * *',
                    'label' => Mage::helper('fastimporter')->__('On every day at 00:00'),
                ),
                array(
                    'value' => '0 0 * * 1',
                    'label' => Mage::helper('fastimporter')->__('On every weekday: Monday at 00:00'),
                ),
                array(
                    'value' => '0 0 1 * *',
                    'label' => Mage::helper('fastimporter')->__('On day 1 of every month at 00:00'),
                ),
            ),
        ));
        if (Mage::getSingleton('adminhtml/session')->getFastimporterData()) {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getFastimporterData());
            Mage::getSingleton('adminhtml/session')->setFastimporterData(null);
        } elseif (Mage::registry('fastimporter_data')) {
            $form->setValues(Mage::registry('fastimporter_data')->getData());
        }
        return parent::_prepareForm();
    }
}