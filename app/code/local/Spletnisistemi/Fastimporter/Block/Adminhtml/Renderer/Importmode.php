<?php

class Spletnisistemi_Fastimporter_Block_Adminhtml_Renderer_Importmode extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {
    public function render(Varien_Object $row) {
        if ($row["mode"] == "create") {
            return "Create and Update";
        }
        if ($row["mode"] == "xcreate") {
            return "Create";
        }
        if ($row["mode"] == "update") {
            return "Update";
        }
        if ($row["mode"] == "delete") {
            return "Delete";
        }
        if ($row["mode"] == "delete-all") {
            return "Delete All Products";
        }
    }
}