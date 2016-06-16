<?php

class Spletnisistemi_Fastimporter_Lib_Varien_Data_Form_Element_MultiElement extends Varien_Data_Form_Element_Abstract {
    private $elements = array();

    public function __construct($attrs = array()) {
        $conf = array();
        foreach ($attrs as $key => $attr) {
            if (is_array($attr)) {
                $this->init_element($key, $attr);
            } else {
                $conf[$key] = $attr;
            }
        }
        parent::__construct($conf);
        $this->setType('label');
    }

    private function init_element($id, $conf) {
        $type = $conf['type'];
        unset($conf['type']);
        $type = ucfirst(strtolower($type));
        $class = "Varien_Data_Form_Element_$type";
        $this->elements[$conf['name']] = new $class($conf);
        //    $this->elements[$conf['name']]->setId($id);
    }

    public function getElementHtml() {
        $html = '';
        foreach ($this->elements as $name => $element) {
            $element->setForm($this->getForm());
            $html .= $element->getElementHtml();
        }
        return $html;
    }
}