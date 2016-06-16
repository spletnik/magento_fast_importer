<?php

require_once("app/Mage.php");
require_once("magmi/inc/magmi_defs.php");
require_once("magmi/engines/magmi_productimportengine.php");

/*
require_once(dirname(__FILE__)."/../../../../../Mage.php");
require_once(dirname(__FILE__)."/../../../../../../magmi/inc/magmi_defs.php");
require_once(dirname(__FILE__)."/../../../../../../magmi/engines/magmi_productimportengine.php");
*/

Mage::app();

class CLILoggerCron {
    public function log($data, $type) {
        if ($type == "title") {
            $log_data = "Magento Fast Product Importer by Spletnisistemi - v1.2" . '<br />';
        } else {
            $log_data = $data . '<br />';
        }
        file_put_contents("media/import/fastimporter-cron.log", "$log_data \n", FILE_APPEND | LOCK_EX);
    }
}

/**
 * Provides basic cron syntax parsing functionality
 *
 * @author:  Jan Konieczny <jkonieczny@gmail.com>
 * @copyright: Copyright (C) 2009, Jan Konieczny
 */
class Crontab {
    /*
    This is a simple cron notation parser. Returns the
    nearest timestamp matching given criteria that is
    greater than or equal to given timestamp (current
    time() by default)

    Eg.:   $timestamp = Crontab::parse('12 * * * 1-5');

    */

    /**
     *  Finds next execution time(stamp) parsin crontab syntax,
     *  after given starting timestamp (or current time if ommited)
     *
     * @param string $_cron_string :
     *
     *      0     1    2    3    4
     *      *     *    *    *    *
     *      -     -    -    -    -
     *      |     |    |    |    |
     *      |     |    |    |    +----- day of week (0 - 6) (Sunday=0)
     *      |     |    |    +------- month (1 - 12)
     *      |     |    +--------- day of month (1 - 31)
     *      |     +----------- hour (0 - 23)
     *      +------------- min (0 - 59)
     * @param int $_after_timestamp timestamp [default=current timestamp]
     * @return int unix timestamp - next execution time will be greater
     *              than given timestamp (defaults to the current timestamp)
     * @throws InvalidArgumentException
     */
    public static function parse($_cron_string, $_after_timestamp = null) {
        if (!preg_match('/^((\*(\/[0-9]+)?)|[0-9\-\,\/]+)\s+((\*(\/[0-9]+)?)|[0-9\-\,\/]+)\s+((\*(\/[0-9]+)?)|[0-9\-\,\/]+)\s+((\*(\/[0-9]+)?)|[0-9\-\,\/]+)\s+((\*(\/[0-9]+)?)|[0-9\-\,\/]+)$/i', trim($_cron_string))) {
            throw new InvalidArgumentException("Invalid cron string: " . $_cron_string);
        }
        if ($_after_timestamp && !is_numeric($_after_timestamp)) {
            throw new InvalidArgumentException("\$_after_timestamp must be a valid unix timestamp ($_after_timestamp given)");
        }
        $cron = preg_split("/[\s]+/i", trim($_cron_string));
        $start = empty($_after_timestamp) ? time() : $_after_timestamp;

        $date = array('minutes' => self::_parseCronNumbers($cron[0], 0, 59),
                      'hours'   => self::_parseCronNumbers($cron[1], 0, 23),
                      'dom'     => self::_parseCronNumbers($cron[2], 1, 31),
                      'month'   => self::_parseCronNumbers($cron[3], 1, 12),
                      'dow'     => self::_parseCronNumbers($cron[4], 0, 6),
        );
        // limited to time()+366 - no need to check more than 1year ahead
        for ($i = 0; $i <= 60 * 60 * 24 * 366; $i += 60) {
            if (in_array(intval(date('j', $start + $i)), $date['dom']) &&
                in_array(intval(date('n', $start + $i)), $date['month']) &&
                in_array(intval(date('w', $start + $i)), $date['dow']) &&
                in_array(intval(date('G', $start + $i)), $date['hours']) &&
                in_array(intval(date('i', $start + $i)), $date['minutes'])

            ) {
                return $start + $i;
            }
        }
        return null;
    }


    /**
     * get a single cron style notation and parse it into numeric value
     *
     * @param string $s cron string element
     * @param int $min minimum possible value
     * @param int $max maximum possible value
     * @return int parsed number
     */
    protected static function _parseCronNumbers($s, $min, $max) {
        $result = array();

        $v = explode(',', $s);
        foreach ($v as $vv) {
            $vvv = explode('/', $vv);
            $step = empty($vvv[1]) ? 1 : $vvv[1];
            $vvvv = explode('-', $vvv[0]);
            $_min = count($vvvv) == 2 ? $vvvv[0] : ($vvv[0] == '*' ? $min : $vvv[0]);
            $_max = count($vvvv) == 2 ? $vvvv[1] : ($vvv[0] == '*' ? $max : $vvv[0]);

            for ($i = $_min; $i <= $_max; $i += $step) {
                $result[$i] = intval($i);
            }
        }
        ksort($result);
        return $result;
    }
}

class Spletnisistemi_Fastimporter_Model_Observer {

    public function cron() {
        $importer = new Magmi_ProductImportEngine();
        $options['logger'] = "CLILoggerCron";
        $importer->setLogger(new $options['logger']());

        $profiles = Mage::getModel("fastimporter/fastimporter")->getCollection();
        foreach ($profiles as $profile) {
            $cronjob = $profile["cronjob"];
            if ($cronjob != "") {
                $timestamp_cronjob_executed = strtotime($profile["cronjob_executed"]);
                $sqlTime = date("Y-m-d h:i:s");
                $crontab_timestamp_run = Crontab::parse($cronjob, $timestamp_cronjob_executed);
                $crontab_date_run = date("Y-m-d h:i:s", $crontab_timestamp_run);

                if ($crontab_date_run <= $sqlTime) {
                    //echo "RUN CRON IMPORT <br />";
                    $options["profile"] = $profile["profile_name"];
                    $options["mode"] = $profile["mode"];

                    $importer->engineInit($options);
                    $importer->run($options);

                    $profile->setData("cronjob_executed", $sqlTime);
                    $profile->save();

                }
            }
        }
    }
}