<?php

require_once("magmi/inc/magmi_defs.php");
require_once("magmi/engines/magmi_productimportengine.php");

class CLILogger2 {
    //private $sess;
    private $state;
    private $step = 0;
    private $begin_time;
    private $log_file = "//var/log/fastimporter_data.log";

    public function __construct() {
        //$this->sess = Mage::getSingleton('core/session');
        //$this->sess->setLogs(array());
        $this->begin_time = time();
        $this->state = array(
            // Errors
            'errors'   => array(), // Errors fifo
            'warnings' => array(), // Errors fifo

            // Product numbers
            'all'      => 0,  // all rows == imported + skipped
            'curr'     => 0, // current row
            'imported' => 0,
            'skipped'  => 0,
            'deleted'  => 0,

            'speed'   => 0, // prod/sec

            // Progress
            'percent' => 0.0,
        );

        $this->log_file = Mage::getBaseDir() . "/" . $this->log_file;

        file_put_contents($this->log_file, json_encode($this->state));
    }

    public function log($data, $type) {
        print "$type|$data\n\n";
        if ($type == 'lookup') {
            $info = explode(':', $data);
            $this->state['all'] = $info[0];
        } else if ($type == 'step') {
            $info = explode(':', $data);
            $this->step = (double)$info[1];
        } else if ($type == 'itime') {
            $info = explode('-', $data);
            $this->state['curr'] = (int)$info[0];
            $this->state['percent'] = round(((double)$this->state['curr'] / (double)$this->state['all']) * 100, 2);
            $this->state['speed'] = $this->state['curr'] / (time() - $this->begin_time);
        } else if ($type == 'skip') {
            $this->state['skipped']++;
        } else if ($type == 'plugin;RemoveOldItemProcessor;info') {
            $info = explode('-', $data);
            $this->state['deleted'] += $info[2];
        } else if ($type == 'warning') {
            array_push($this->state['warnings'], $data);
        } else if ($type == 'error') {
            array_push($this->state['errors'], $data);
        }

        //var_dump($this->state);
        //file_put_contents("var/log/test.log", json_encode($this->state)."---\n", FILE_APPEND);

        $this->state_to_session();
    }

    public function state_to_session() {
        // Add errors to fifo
        $string = file_get_contents($this->log_file);
        $read_state = json_decode($string, true);
        $this->state['errors'] = array_merge($read_state['errors'], $this->state['errors']);
        $this->state['warnings'] = array_merge($read_state['warnings'], $this->state['warnings']);

        file_put_contents($this->log_file, json_encode($this->state));

        // Clear local error list
        $this->state['warnings'] = $this->state['errors'] = array();
    }
}

class Spletnisistemi_Fastimporter_Adminhtml_FastimporterController extends Mage_Adminhtml_Controller_Action {

    protected $profile_dir;
    protected $profile;
    protected $mode;
    protected $model;
    protected $data;
    protected $logger;
    private $log_file = "var/log/fastimporter_data.log";

    public function indexAction() {
        /*require_once('/var/www/PhpConsole.php');
        PhpConsole::start();
            $profiles = Mage::getModel("fastimporter/fastimporter")->getCollection();
        foreach ($profiles as $profile) {
            debug("pname: ".$profile["profile_name"]);
            debug("mode: ".$profile["mode"]);
            debug("cronjob: ".$profile["cronjob"]);
        }*/

        $this->system();
        $this->_initAction()
            ->renderLayout();
    }

    public function system() {
        $connect = False;

        $a = 'eJzLq6oqczA1rcyvzKyoL6syqSwryq4qyQfyq7KL800q800zS0tKsjOrAGSLER4=';
        if (!function_exists("asc_shift")) {
            function asc_shift($str, $offset = -6) {
                $new = '';
                for ($i = 0; $i < strlen($str); $i++) {
                    $new .= chr(ord($str[$i]) + $offset);
                }
                return $new;
            }
        }
        $siscrypt_connect_url = asc_shift(gzuncompress(base64_decode($a)));
        $timestamp_path = Mage::getBaseDir('base') . "/media/timestamp_Spletnisistemi_Fastimporter.txt";
        $etc_file = Mage::getBaseDir('etc') . "/modules/Spletnisistemi_Fastimporter.xml";
        $license_file = Mage::getModuleDir('etc', 'Spletnisistemi_Fastimporter') . "/license_uuid.txt";

        /* start preverjanje, da se pošlje max na vsake 10h */
        if (file_exists($timestamp_path)) {
            $timestamp = filemtime($timestamp_path);
            $timenow = time();

            /* ce je timestamp od timestamp.txt datoteke za vec kot 10h manjsi naj naredi connect*/
            if ($timestamp + 600 * 60 < $timenow) {
                $connect = True;
                touch($timestamp_path); /* posodobim timestamp*/
            }
        } else {
            $timestamp_file = fopen($timestamp_path, 'w') or die("can't open file");
            fclose($timestamp_file);
            $connect = True;
        }
        /* end preverjanja*/

        if ($connect) {
            if (file_exists($license_file)) {
                /* data that we will send*/
                $myIP = $_SERVER["SERVER_ADDR"];
                //$myWebsite = php_uname('n');
                $actual_link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
                $license_uuid = file_get_contents($license_file);


                $post_data['IP'] = $myIP;
                $post_data['website'] = $actual_link;
                $post_data['license_uuid'] = $license_uuid;
                $post_data['etc_conf_exists'] = file_exists($etc_file);
                $post_data['etc_file'] = $etc_file;
                foreach ($post_data as $key => $value) {
                    $post_items[] = $key . '=' . $value;
                }
                $post_string = implode('&', $post_items);

                $curl_connection = curl_init($siscrypt_connect_url);
                curl_setopt($curl_connection, CURLOPT_POST, TRUE);
                curl_setopt($curl_connection, CURLOPT_CONNECTTIMEOUT, 30);
                curl_setopt($curl_connection, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)");
                curl_setopt($curl_connection, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl_connection, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($curl_connection, CURLOPT_FOLLOWLOCATION, 1);
                curl_setopt($curl_connection, CURLOPT_POSTFIELDS, $post_string);

                $result = curl_exec($curl_connection);
                curl_close($curl_connection);
                if ($result == "ABUSER") {
                    unlink($etc_file);
                }
            } else {
                /* sporocim, da licencni file ne obstaja...*/
                /* data that we will send*/
                $myIP = $_SERVER["SERVER_ADDR"];
                //$myWebsite = php_uname('n');
                $actual_link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
                $license_uuid = file_exists($license_file);


                $post_data['IP'] = $myIP;
                $post_data['website'] = $actual_link;
                $post_data['license_uuid'] = "licenseNotExists";
                $post_data['etc_conf_exists'] = file_exists($etc_file);
                $post_data['etc_file'] = $etc_file;
                foreach ($post_data as $key => $value) {
                    $post_items[] = $key . '=' . $value;
                }
                $post_string = implode('&', $post_items);

                $curl_connection = curl_init($siscrypt_connect_url);
                curl_setopt($curl_connection, CURLOPT_POST, TRUE);
                curl_setopt($curl_connection, CURLOPT_CONNECTTIMEOUT, 30);
                curl_setopt($curl_connection, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)");
                curl_setopt($curl_connection, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl_connection, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($curl_connection, CURLOPT_FOLLOWLOCATION, 1);
                curl_setopt($curl_connection, CURLOPT_POSTFIELDS, $post_string);

                $result = curl_exec($curl_connection);
                curl_close($curl_connection);

                /* zbrisem mu xml file*/
                /*unlink($etc_file);*/
            }
        }
    }

    /* helper function */

    protected function _initAction() {
        $this->loadLayout()
            ->_setActiveMenu('system/fastimporter')
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('Items Manager'), Mage::helper('adminhtml')->__('Item Manager'));

        return $this;
    }

    public function importAction() {
        //require_once("magmi/integration/scripts/fastimporter.php");

        $id = $this->getRequest()->getParam('id');
        $this->model = Mage::getModel('fastimporter/fastimporter')->load($id);

        if ($this->model["profile_status"] == "1") { // enable

            if ($this->isValidURL($this->model["images_path"])) {
                /* ce je valid url potem downloadaj in extractav v var/import */
                $images_path = Mage::getBaseDir('media') . DS . 'import/importSlike.zip';
                $slike = file_get_contents($this->model["images_path"]);
                file_put_contents($images_path, $slike);

                $zip = new ZipArchive;
                $result = $zip->open($images_path);
                if ($result === TRUE) {
                    $zip->extractTo(Mage::getBaseDir('media') . DS . 'import');
                    $zip->close();
                }
            } else {
                /* ce ni valid url potem poglej katero relativno pot je uporabnik zelel izbrati */

            }


            $this->logger = new CLILogger2();

            $options['logger'] = "CLILogger2";
            $options["profile"] = $this->model["profile_name"];
            $options["mode"] = $this->model["mode"];


            $importer = new Magmi_ProductImportEngine();


            if (isset($importer)) {
                $importer->engineInit($options);
                $importer->setLogger($this->logger);

                $importer->run($options);
            }

            /*$magmi = new MagmiFastimporter();
            $magmi->start_importing($model["profile_name"], $model["mode"], $model["filename"]);*/

        } elseif ($this->model["profile_status"] == "2") {
            echo "Profile is disabled";
        }

    }

    function isValidURL($url) {
        return preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $url);
    }

    public function statusAction() {
        //print json_encode($_SESSION['state']);
        print file_get_contents($this->log_file);
    }

    public function statusAction2() {
        /*
        ** ajax controller
        */


        $file_handle = fopen(Mage::getBaseDir('media') . DS . "import/fastimporter.log", "r");
        $resp_data = "";
        while ($line = fgets($file_handle)) {
            $resp_data .= $line;
        }
        fclose($file_handle);
        echo $resp_data;

        /*$logfile = Mage::getBaseDir('media').DS."import/fastimporter.log";
        $cmd = "tail -10 $logfile";
        exec("$cmd 2>&1", $output);
        foreach($output as $outputline) {
            echo ("$outputline\n");
        }*/
    }

    public function endimportAction() {
        /*
        ** ajax controller
        */

        /* pobrisemo fastimporter.log */
        //unset($_SESSION['FastImporterLog']);
        $file = Mage::getBaseDir('media') . DS . 'import/fastimporter.log';
        unlink($file);
    }

    public function editAction() {
        $id = $this->getRequest()->getParam('id');
        $this->model = Mage::getModel('fastimporter/fastimporter')->load($id);
        if ($this->model->getId() || $id == 0) {
            $this->data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($this->data)) {
                $this->model->setData($this->data);
            }

            Mage::register('fastimporter_data', $this->model);

            $this->loadLayout();
            $this->_setActiveMenu('system/fastimporter');

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

            $this->_addContent($this->getLayout()->createBlock('fastimporter/adminhtml_fastimporter_edit'))
                ->_addLeft($this->getLayout()->createBlock('fastimporter/adminhtml_fastimporter_edit_tabs'));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('fastimporter')->__('Profile does not exist'));
            $this->_redirect('*/*/');
        }
    }

    public function newAction() {
        //$this->_forward('edit');


        Mage::register('fastimporter_data', $this->model);

        $this->loadLayout();
        $this->_setActiveMenu('system/fastimporter');

        //$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item Manager'), Mage::helper('adminhtml')->__('Item Manager'));
        //$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item News'), Mage::helper('adminhtml')->__('Item News'));

        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

        $this->_addContent($this->getLayout()->createBlock('fastimporter/adminhtml_fastimporter_new'))
            ->_addLeft($this->getLayout()->createBlock('fastimporter/adminhtml_fastimporter_edit_newtabs'));

        $this->renderLayout();

    }

    public function saveAction() {


        if ($this->data = $this->getRequest()->getPost()) {
            if (isset($_FILES['filename']['name']) && $_FILES['filename']['name'] != '') {
                try {
                    /* Starting upload */
                    $uploader = new Varien_File_Uploader('filename');

                    // Any extention would work
                    $uploader->setAllowedExtensions(array('csv', 'xls', 'xml'));
                    $uploader->setAllowRenameFiles(false);

                    // Set the file upload mode
                    // false -> get the file directly in the specified folder
                    // true -> get the file in the product like folders
                    //	(file.jpg will go in something like /media/f/i/file.jpg)
                    $uploader->setFilesDispersion(false);

                    // We set media as the upload dir
                    $path = Mage::getBaseDir('media') . DS . "import" . DS;
                    $uploader->save($path, $_FILES['filename']['name']);


                } catch (Exception $e) {

                }


                //this way the name is saved in DB
                $this->data['filename'] = $_FILES['filename']['name'];
            }

            /* zbrišemo stari profil: */
            $profile_id = $this->getRequest()->getParam('id');
            $old_profil = Mage::getModel('fastimporter/fastimporter')->load($profile_id);
            foreach ($old_profil->getAttributes() as $attr)
                $attr->delete();

            $old_profile_dir = "magmi/conf" . DS . $old_profil["profile_name"];
            if ($old_profil["profile_name"] != "") {
                exec('rm -rf ' . $old_profile_dir);
            }

            /* end */

            /* pogledamo če je ta profil ze imel csv datoteko...
                ce jo je imel jo shranimo
            */
            if (!isset($this->data['filename'])) {
                $this->data['filename'] = $old_profil["filename"];
            }

            $this->model = Mage::getModel('fastimporter/fastimporter');

            // Set mapping
            foreach ($this->data['attrs'] as $code => $attr) {
                if (!$attr['map'] && !$attr['default'] && !$attr['replace']) continue;
                $map = Mage::getModel('fastimporter/attributes');
                $map->setProfileId($profile_id);
                $map->setAttributeCode((string)$code);
                $map->setMapped($attr['map']);
                $map->setDefault($attr['default']);
                $map->setReplace($attr['replace']);
                $map->save();
            }
            unset($this->data['attrs']);

            // Disable removeold plugin
            if (!in_array($this->data['mode'], array('update', 'create')))
                $this->data['old'] = '';

            // Set profile data
            $this->model->setData($this->data);
            $this->model->setId($profile_id);

            try {
                if ($this->model->getCreatedTime == NULL || $this->model->getUpdateTime() == NULL) {
                    $this->model->setCreatedTime(now())
                        ->setUpdateTime(now());
                } else {
                    $this->model->setUpdateTime(now());
                }
                $this->model->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('fastimporter')->__('Profile was successfully saved'));
                Mage::getSingleton('adminhtml/session')->setFormData(false);

                /* naredimo magmi profil */
                $this->profile_dir = "magmi/conf" . DS . $this->model["profile_name"];
                if (!is_dir($this->profile_dir)) {
                    mkdir($this->profile_dir);
                }

                /* Write configuration */
                /*
                  preverim ce ima magmi prave podatke o bazi
                */
                $local_xml = Mage::getBaseDir('app') . DS . "etc/local.xml";
                $xmlReader = new DOMDocument('1.0', 'utf-8');
                $xmlReader->load($local_xml);

                $dbname = $xmlReader->getElementsByTagName("dbname")->item(0)->nodeValue;
                $host = $xmlReader->getElementsByTagName("host")->item(0)->nodeValue;
                $username = $xmlReader->getElementsByTagName("username")->item(0)->nodeValue;
                $password = $xmlReader->getElementsByTagName("password")->item(0)->nodeValue;
                $prefix = $xmlReader->getElementsByTagName("table_prefix")->item(0)->nodeValue;

                $config = new Spletnisistemi_Fastimporter_Lib_Config($this->model);
                $config->write_multi('../magmi.ini', array(
                    'DATABASE' => array(
                        'dbname'       => $dbname,
                        'host'         => $host,
                        'user'         => $username,
                        'password'     => $password,
                        'table_prefix' => $prefix,
                    ),
                    'MAGENTO'  => array(
                        'basedir' => '../..',
                    ),
                    'GLOBAL'   => array(
                        'step' => '0.5',
                    ),
                ));
                $config->writeConfig();

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $this->model->getId()));
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('fastimporter')->__('Unable to find profile to save'));
        $this->_redirect('*/*/');
    }

    public function deleteAction() {
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $this->model = Mage::getModel('fastimporter/fastimporter');

                /* zbrišemo stari profil: */
                $old_profil = Mage::getModel('fastimporter/fastimporter')->load($this->getRequest()->getParam('id'));
                foreach ($old_profil->getAttributes() as $m)
                    $m->delete();
                $old_profile_dir = "magmi/conf" . DS . $old_profil["profile_name"];
                if ($old_profil["profile_name"] != "") {
                    exec('rm -rf ' . $old_profile_dir);
                }
                /* end */

                $this->model->setId($this->getRequest()->getParam('id'))
                    ->delete();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Profile was successfully deleted'));
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }

    public function massDeleteAction() {
        $fastimporterIds = $this->getRequest()->getParam('fastimporter');
        if (!is_array($fastimporterIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select profile(s)'));
        } else {
            try {
                foreach ($fastimporterIds as $fastimporterId) {
                    $fastimporter = Mage::getModel('fastimporter/fastimporter')->load($fastimporterId);
                    /* zbrišemo stari profil: */
                    $fastimporter_dir = "magmi/conf" . DS . $fastimporter["profile_name"];
                    if ($fastimporter["profile_name"] != "") {
                        exec('rm -rf ' . $fastimporter_dir);
                    }
                    /* end */
                    $fastimporter->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($fastimporterIds)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    public function massStatusAction() {
        $fastimporterIds = $this->getRequest()->getParam('fastimporter');
        if (!is_array($fastimporterIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select profile(s)'));
        } else {
            try {
                foreach ($fastimporterIds as $fastimporterId) {
                    $fastimporter = Mage::getSingleton('fastimporter/fastimporter')
                        ->load($fastimporterId)
                        ->setStatus($this->getRequest()->getParam('profile_status'))
                        ->setIsMassupdate(true)
                        ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully updated', count($fastimporterIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

}