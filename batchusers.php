<?php
/**
 * korte beschrijving
 * 
 * @package standaard
 * @author  Henk Rijneveld
 * @version 0.0.1
 */
require "class/exporter.php";
use  userimportplainphp\exporter;

// validator
define("CONFIGFILE", "batchusers.json");
if (!file_exists(CONFIGFILE)) {
    class config {
        var $ip = "192.168.56.101";
        var $db = "test";
        var $port = "3306";
        var $user = "root";
        var $password = "helhond";
    }
    file_put_contents(CONFIGFILE, json_encode(new config, JSON_PRETTY_PRINT));
    die("Configfile not found, default written to '".CONFIGFILE."'");
}

// read config
$config = json_decode(file_get_contents(CONFIGFILE));

$a = new exporter($config);

