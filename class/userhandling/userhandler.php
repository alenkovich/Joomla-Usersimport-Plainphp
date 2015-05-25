<?php
/**
 * Handler function for Joomla database
 * 
 * @package standaard
 * @author  Henk Rijneveld
 * @version 0.0.1
 */

namespace userhandling;



class Userhandler {
    protected $db = NULL;
    protected $prefix = "";

    public function __construct($config) {
        $this->db = @new \mysqli($config->ip, $config->user, $config->password, $config->db, $config->port);
        if ($this->db->connect_error) {
            throw new \Exception("Database error: ". $this->db->connect_error);
        }
        $prefix = $config->prefix."_";
    }

    public function export($file) {
        // select all including groupmap (max 3)
        if (($result = $this->db->query("SELET")) == 0) {
            throw new \Exception("Database select error: ".$this->db->error);
        }

        // write to file in csv format



    }

    public function close() {
        if (!$this->db) {
            $this->db->close();
            $this->db = NULL;
        }
    }

    public function __destruct() {
        $this->close();
    }

}
