<?php

class Spletnisistemi_Fastimporter_Helper_Data extends Mage_Core_Helper_Abstract {
    public function getExtraAttrs() {
        return array('attribute_set', 'type', 'store', 'websites', 'qty', 'min_qty', 'use_config_min_qty', 'is_qty_decimal', 'min_sale_qty', 'use_config_min_sale_qty', 'max_sale_qty', 'use_config_max_sale_qty', 'backorders', 'use_config_backorders', 'is_in_stock', 'low_stock_date', 'notify_stock_qty', 'use_config_notify_stock_qty', 'manage_stock', 'use_config_manage_stock', 'stock_status_changed_automatically', 'use_config_qty_increments', 'qty_increments', 'use_config_enable_qty_increments', 'enable_qty_increments', 'country_orgin');
    }

    public function getFileType($data) {
        if (isset($data['file_url']) && !empty($data['file_url']))
            return "remote";
        return "local";
    }

    public function getFileExt($data) {
        $file = $this->getFilePath($data);
        $ext = pathinfo($file);
        return $ext['extension'];
    }

    public function getFilePath($data) {
        if (isset($data['file_url']) && !empty($data['file_url']))
            return $data['file_url'];
        return Mage::getBaseDir('media') . DS . "import/" . $data['filename'];
    }

    public function getXmlElementNode($file) {
        $xml = simplexml_load_file($file);
        $children = $xml->children();
        if (sizeof($children))
            return $children[0]->getName();
        else
            return 'empty';
    }

    public function getXmlMap($file) {
        $xml = simplexml_load_file($file);
        $children = $xml->children();
        if (sizeof($children)) {
            $map = array();
            $this->getXmlMapNode($children[0], array(), $map);
            return $map;
        }
        die("OUT");
        return array();
    }

    private function getXmlMapNode($xml, $path, &$map) {
        // attributes
        foreach ($xml->attributes() as $attr => $v) {
            $p = "#$attr";
            if ($path)
                $p = '@' . join('.@', $path) . '.' . $p;
            $n = join('->', $path) . "#$attr";
            $map[$p] = $n;
        }


        if (sizeof($xml->children())) {
            foreach ($xml->children() as $child) {
                // recursion
                array_push($path, $child->getName());
                $this->getXmlMapNode($child, $path, $map);
                array_pop($path);
            }
        } else {
            $p = join('.@', $path);
            $n = join('->', $path);
            $map['@' . $p] = $n;
        }
        return $map;
    }
}
