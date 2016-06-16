<?php

class Spletnisistemi_Fastimporter_Lib_Config {
    public function __construct($model) {
        $this->model = $model;
        $this->h = Mage::helper('fastimporter');
        $this->profile_dir = implode(DS, array('magmi', 'conf', $model->getProfileName()));
    }

    // Write all configuration
    public function writeConfig() {
        // Info
        $info_ds = '';
        $info_general = array('Magmi_OptimizerPlugin');
        $info_plugins = array();

        $methods = get_class_methods(get_class());
        foreach ($methods as $method) {
            if (preg_match('/^plugin_/', $method)) {
                $class = substr($method, strlen('plugin_'));

                $data = array();
                $prefix = '';
                if ($this->$method($data, $prefix, $class)) {
                    $this->write($class, $data, $prefix);
                    if (preg_match('/DataSource$/', $class))
                        $info_ds = $class;
                    else if (preg_match('/Plugin$/', $class))
                        $info_general[] = $class;
                    else
                        $info_plugins[] = $class;
                }
            }
        }

        // Write plugins.conf
        self::write_multi('plugins.conf', array(
            'PLUGINS_DATASOURCES'    => array(
                'class' => $info_ds,
            ),
            'PLUGINS_GENERAL'        => array(
                'classes' => implode(',', $info_general)
            ),
            'PLUGINS_ITEMPROCESSORS' => array(
                'classes' => implode(',', $info_plugins)
            ),
        ));

    }

    /*******************/
    /**** Plugins ******/
    /*******************/
    /*
      Configuration:
      $this->plugin_<Plugin class name>(&$data, &$prefix, &$override_plugin_name='not required')
      return:
         - true: add plugin to list
         - false: ignore plugin
     */

    // Datasource config

    private function write($plugin, $data, $prefix = '') {
        $file = $this->profile_dir . DS . $plugin . '.conf';
        $f = fopen($file, 'w') or die("Can't open: $file");
        fwrite($f, "[$plugin]\n");
        fwrite($f, $this->getMap($data, $prefix));
        fclose($f);
    }

    public function getMap($data, $prefix = '') {
        if ($prefix) $prefix = $prefix . ':';
        $conf = array();
        foreach ($data as $k => $v) {
            $k = $prefix . $k;
            $v = str_replace('"', ':DQUOTE:', $v);
            $v = str_replace("'", ':SQUOTE:', $v);
            array_push($conf, "$k = \"$v\"");
        }

        return implode("\n", $conf);
    }

    public function write_multi($file, $data) {
        $file = $this->profile_dir . DS . $file;
        $f = fopen($file, 'w') or die("Can't open: $file");
        $x = false;
        foreach ($data as $section => $kv) {
            if ($x) fwrite($f, "\n");
            fwrite($f, "[$section]\n");
            fwrite($f, $this->getMap($kv));
            $x = true;
        }
        fclose($f);
    }


    public function plugin_Datasource(&$data, &$prefix, &$plugin_name) {
        $mdata = $this->model->getData();
        $ext = $this->h->getFileExt($mdata);
        $local_remote = $this->h->getFileType($mdata);

        // General
        $data = array(
            'importmode' => $local_remote,
            'basedir'    => 'media/import',
            'filename'   => $this->h->getFilePath($mdata),
            'remoteurl'  => $this->model->getFileUrl(),
        );

        // Extra
        if ($ext == 'xml') {
            $node = $this->h->getXmlElementNode($this->h->getFilePath($mdata));
            $data['Product'] = $node;
        }

        $ext = strtoupper($ext);
        $prefix = $ext;
        $plugin_name = "Magmi_{$ext}DataSource";
        return true;
    }

    public function plugin_Magmi_ReindexingPlugin(&$data, &$prefix) {
        $prefix = 'REINDEX';
        $data = array(
            'indexes' => '',
            'phpcli'  => 'php',
        );
        return false; // disable
    }

    public function plugin_ImageAttributeItemProcessor(&$data, &$prefix) {
        $prefix = 'IMG';
        // Is valid url
        $valid_url = preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $this->model->getImagesPath());
        $data = array(
            'sourcedir'    => $valid_url ? 'media/import' : $this->model->getImagesPath(),
            'writemode'    => 'keep',
            'existingonly' => 'no',
            'predlcheck'   => 'yes',
            'renaming'     => '',
            'err_attrlist' => '',
            'debug'        => 'no'
        );
        return true;
    }

    public function plugin_ColumnMappingItemProcessor(&$data, &$prefix) {
        $prefix = 'CMAP';

        $cols = array();
        $data = array();
        foreach ($this->model->getMappedAttributes() as $map) {
            array_push($cols, $map->getMapped());
            $data[$map->getMapped()] = $map->getAttributeCode();
        }
        $data = array_merge(
            array('columnlist' => implode(',', $cols)),
            $data
        );
        return $cols ? true : false;
    }

    public function plugin_DefaultValuesItemProcessor(&$data, &$prefix) {
        $prefix = 'DEFAULT';
        $cols = array('import_id');
        $data = array(
            'import_id' => $this->model->getId(), // Always !
        );
        foreach ($this->model->getDefaultAttributes() as $map) {
            array_push($cols, $map->getAttributeCode());
            $data[$map->getAttributeCode()] = $map->getDefault();
        }
        $data = array_merge(
            array('columnlist' => implode(',', $cols)),
            $data
        );
        return true;
    }

    public function plugin_ValueReplacerItemProcessor(&$data, &$prefix) {
        $prefix = 'VREP';
        $cols = array();
        $data = array();
        foreach ($this->model->getReplaceAttributes() as $map) {
            array_push($cols, $map->getAttributeCode());
            $data[$map->getAttributeCode()] = $map->getReplace();
        }
        $data = array_merge(
            array('columnlist' => implode(',', $cols)),
            $data
        );
        return $cols ? true : false;
    }
    /**************/
    /**** END *****/
    /**************/

    // General config writer
    public function plugin_Magmi_ConfigurableItemProcessor(&$data, &$prefix) {
        $prefix = 'CFGR';
        $data = array(
            'simplesbeforeconf' => '1',
            'updsimplevis'      => '1',
            'nolink'            => '0',
        );
        return true;
    }

    public function plugin_Magmi_GroupedItemProcessor(&$data, &$prefix) {
        $prefix = 'APIGRP';
        $data = array(
            'groupedbeforegrp' => '0',
            'updgroupedvis'    => '0',
            'nolink'           => '0',
        );
        return true;
    }

    public function plugin_RemoveOldItemProcessor(&$data, &$prefix) {
        if (!$this->model->getOld()) return false;
        $prefix = 'ROLD';

        $data = array(
            'import_id' => $this->model->getId(),
            'mode'      => $this->model->getOld(),
        );
        return true;
    }
}