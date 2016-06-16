<?php

class Spletnisistemi_Fastimporter_IndexController extends Mage_Core_Controller_Front_Action {
    public function indexAction() {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function delayAction() {
        $s = Mage::getSingleton('core/session');
        for ($i = 0; $i < 1000; $i++) {
            $s->setX($i);
            system("sleep 1");
        }
    }

    public function readAction() {
        print_r($_SESSION);
        $s = Mage::getSingleton('core/session');
        $logs = $s->getLogs();
        var_dump($logs);
        $s->setLogs(array());
        print json_encode($logs);
    }
}