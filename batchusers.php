<?php
/**
 * korte beschrijving
 *
 * @package standaard
 * @author  Henk Rijneveld
 * @version 0.0.1
 */
use  userhandling\userhandler;

// constants
define("MODE_EXPORT", "mode_export");
define("MODE_IMPORT", "mode_import");
define("MODE_UNDEFINED", "mode_undefined");

// globals
$mode = MODE_UNDEFINED;
$file = "export.csv";
$configfile = "batchusers.json";
$exitOnError = false;

// autoloader
spl_autoload_register(function ($class) {
    $class = str_replace('\\', '/', $class);
    include 'class/' . $class . '.php';
});

// commandline handling
class Commandlinehandler
{
    function e($par)
    {
        global $mode;
        global $file;

        if (!($file = array_shift($par))) {
            throw new Exception("Parameter error");
        }
        if ($mode != MODE_UNDEFINED) {
            throw new Exception("Parameter error");
        }
        $mode = MODE_EXPORT;
        return $par;
    }

    function i($par)
    {
        global $mode;
        global $file;

        if (!($file = array_shift($par))) {
            throw new Exception("Parameter error");
        }
        if ($mode != MODE_UNDEFINED) {
            throw new Exception("Parameter error");
        }
        $mode = MODE_IMPORT;
        return $par;
    }

    function c($par)
    {
        global $configfile;

        if (!($configfile = array_shift($par))) {
            throw new Exception("Parameter error");
        }

        return $par;
    }

    function x($par)
    {
        global $exitOnError;

        $exitOnError = true;

        return $par;
    }
}

$params = $_SERVER["argv"];
array_shift($params); // ignore filename
try {
    while ($par = array_shift($params)) {
        if (substr($par, 0, 1) != '-') {
            throw new Exception("Parameter error");
        }
        $params = call_user_func("Commandlinehandler::" . substr($par, 1), $params);
    }
} catch (Exception $e) {
    die("Usage: batchusers [-i <importfile>]|[-e <exportfile>] [-c <configfile>] [-x (exit on all errors)]");
}

// configuration
if (!file_exists($configfile)) {
    class config {
        var $ip = "192.168.56.101";
        var $db = "test";
        var $port = "3306";
        var $user = "root";
        var $password = "helhond";
        var $prefix = "j";
    }
    file_put_contents($configfile, json_encode(new config, JSON_PRETTY_PRINT));
    die("Configfile not found, default written to '".$configfile."'");
}
$config = json_decode(file_get_contents($configfile));

// postprocessing command line arguments
if ($mode == MODE_UNDEFINED) {
    $mode = MODE_EXPORT;
}

// Do the actual work
try {
    $a = new Userhandler($config, $exitOnError);
    if ($mode == MODE_EXPORT) {
        $a->export($file);
    }
    if ($mode == MODE_IMPORT) {
        $a->import($file);
    }
} catch (Exception $e) {
    die("Import stopped\n".$e->getMessage());
}

