<?php

class Spletnisistemi_Fastimporter_Block_Adminhtml_Renderer_Csvfile extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {
    public function render(Varien_Object $row) {
        if ($row["filename"] != "") {
            return "LOCAL: " . $row["filename"];
        }
        return "REMOTE: " . $row["file_url"];
    }
}