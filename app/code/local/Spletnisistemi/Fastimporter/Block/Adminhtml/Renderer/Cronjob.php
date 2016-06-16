<?php

class Spletnisistemi_Fastimporter_Block_Adminhtml_Renderer_Cronjob extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {
    public function render(Varien_Object $row) {
        if ($row["cronjob"] == "") {
            return "Off";
        }
        /*if ($row["cronjob"] == "* * * * *") {
          return "Every minute";
        }*/
        if ($row["cronjob"] == "0 * * * *") {
            return "Every hour";
        }
        if ($row["cronjob"] == "0 0 * * *") {
            return "Every day";
        }
        if ($row["cronjob"] == "0 0 * * 1") {
            return "Every week";
        }
        if ($row["cronjob"] == "0 0 1 * *") {
            return "Every month";
        }
        return $data;
    }
}