<?php

class Spletnisistemi_Fastimporter_Block_Fastimporter extends Mage_Core_Block_Template {
    public function _prepareLayout() {
        return parent::_prepareLayout();
    }

    public function getFastimporter() {
        if (!$this->hasData('fastimporter')) {
            $this->setData('fastimporter', Mage::registry('fastimporter'));
        }
        return $this->getData('fastimporter');

    }
}