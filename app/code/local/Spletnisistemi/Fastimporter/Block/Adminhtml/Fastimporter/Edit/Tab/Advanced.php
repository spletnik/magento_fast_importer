<?php

class Spletnisistemi_Fastimporter_Block_Adminhtml_Fastimporter_Edit_Tab_Advanced extends Mage_Adminhtml_Block_Widget_Form {
    protected function _prepareForm() {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('fastimporter_mapped', array('legend' => Mage::helper('fastimporter')->__('Attributes'), 'class' => 'expand'));
        $fieldset2 = $form->addFieldset('fastimporter_unmapped', array('legend' => Mage::helper('fastimporter')->__('Unused attributes'), 'class' => 'expand'));

        $fieldset->addType('multi_element', 'Spletnisistemi_Fastimporter_Lib_Varien_Data_Form_Element_MultiElement');
        $fieldset2->addType('multi_element', 'Spletnisistemi_Fastimporter_Lib_Varien_Data_Form_Element_MultiElement');
        $fieldset2->addType('extended_label', 'Spletnisistemi_Fastimporter_Lib_Varien_Data_Form_Element_ExtendedLabel');


        $fastimporter_data = Mage::registry('fastimporter_data')->getData();
        $h = Mage::helper('fastimporter');

        $ext = $h->getFileExt($fastimporter_data);
        $filename = $h->getFilePath($fastimporter_data);

        if ($ext == "csv") {
            /* csv datasource*/
            $f = fopen($filename, 'r');
            $row = fgetcsv($f, 2000, ',') or $row = array();
            fclose($f);
            $col_names = $row;
            /* end csv*/
        } elseif ($ext == "xls") {
            /* xls data source*/
            require_once("magmi/plugins/base/datasources/xls/magmi_xlsdatasource.php");

            $fileType = 'Excel5';
            $tmpfile = tempnam(sys_get_temp_dir(), 'fi_xls');
            copy($filename, $tmpfile);

            // Read the file
            $objReader = PHPExcel_IOFactory::createReader($fileType);
            $objReader->setReadDataOnly(true);
            $objPHPExcel = $objReader->load($tmpfile);
            $iterator = $objPHPExcel->setActiveSheetIndex(0)->getRowIterator();

            $columns = $objPHPExcel->setActiveSheetIndex(0)->getHighestColumn();
            $rows = $objPHPExcel->setActiveSheetIndex(0)->getHighestRow();

            $headingsArray = $objPHPExcel->setActiveSheetIndex(0)->rangeToArray('A1:' . $columns . '1', null, true, true, true);
            $col_names = $headingsArray[1];
            unlink($tmpfile);
            /* end xls*/
        } elseif ($ext == "xml") {
            $col_names = Mage::helper('fastimporter')->getXmlMap($filename);
        }

        // Prepare map
        if ($ext != "xml") {
            sort($col_names);
            foreach ($col_names as $col)
                $col_names_new[$col] = $col;
            $col_names = $col_names_new;
        }
        $col_names = array_merge(array('' => 'Please select...'), $col_names);


        /* fillform mora biti pred spodnjim fieldsetom, sicer mu povozi value */
        if (Mage::getSingleton('adminhtml/session')->getFastimporterData()) {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getFastimporterData());
            Mage::getSingleton('adminhtml/session')->setFastimporterData(null);
        } elseif (Mage::registry('fastimporter_data')) {
            $form->setValues(Mage::registry('fastimporter_data')->getData());
        }


        // Get defaults
        $default = array();
        $mapped = Mage::getModel('fastimporter/attributes')
            ->getCollection()
            ->addFieldToFilter('profile_id', $fastimporter_data['profile_id']);

        // Mapped attributes first
        $used = array();
        foreach ($mapped as $map) {
            $code = $map->getAttributeCode();
            $used[$code] = true;
            $this->addRow($fieldset, $code, $col_names, $map->getMapped(), $map->getDefault(), $map->getReplace());
        }

        // Get attribute type id
        $type = Mage::getModel('eav/entity_type')
            ->getCollection()
            ->addFieldToSelect('entity_type_id')
            ->addFieldToFilter('entity_type_code', 'catalog_product')
            ->load()->getFirstItem()->getEntityTypeId();

        // Get collection
        $attributes = Mage::getModel('eav/entity_attribute')
            ->getCollection()
            ->addFieldToSelect(array('attribute_code'))
            ->addFieldToFilter('entity_type_id', $type)
            ->setOrder('attribute_code', 'ASC');

        // Legend
        $fieldset2->addField('legend', 'extended_label', array(
            'value'       => '<span style="font-weight: bold">MAPPED ATTRIBUTE</span><span style="margin-left: 177px;font-weight: bold">DEFAULT VALUE</span>',
            'label'       => 'MAGENTO ATTRIBUTE',
            'unescaped'   => true,
            'label_style' => 'font-weight: bold',
        ));

        // Fill form
        foreach ($attributes as $attribute) {
            $code = $attribute->getAttributeCode();
            if ($used[$code]) continue;
            $this->addRow($fieldset2, $code, $col_names);
        }

        // Extra attributes
        foreach ($h->getExtraAttrs() as $code) {
            if ($used[$code]) continue;
            $this->addRow($fieldset2, $code, $col_names);
        }

        return parent::_prepareForm();
    }

    private function addRow($fset, $code, $options, $option = null, $default = '', $replace) {
        $h = Mage::helper('fastimporter');
        $fset->addField($code, 'multi_element', array(
            'label'   => $h->__($code),
            'style'   => 'width: 100%',
            'map'     => array(
                'type'   => 'select',
                'name'   => "attrs[$code][map]",
                'values' => $options,
                'value'  => $option,
            ),
            'default' => array(
                'type'  => 'text',
                'name'  => "attrs[$code][default]",
                'style' => 'border: 1px solid gray; margin-left: 10px;',
                'value' => $default,
            ),
            'replace' => array(
                'type'  => 'text',
                'name'  => "attrs[$code][replace]",
                'style' => 'border: 1px solid gray; margin-left: 10px;display:none;',
                'class' => 'replace-field',
                'value' => $replace,
            )
        ));
    }
}
