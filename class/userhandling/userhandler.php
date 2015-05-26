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
    protected $tbl_users = "users";
    protected $tbl_groups = "usergroups";
    protected $tbl_map = "user_usergroup_map";

    public function __construct($config) {
        $this->db = @new \mysqli($config->ip, $config->user, $config->password, $config->db, $config->port);
        if ($this->db->connect_error) {
            throw new \Exception("Database error: ". $this->db->connect_error);
        }
        $prefix = $config->prefix."_";
        $this->tbl_users = $prefix.$this->tbl_users;
        $this->tbl_groups = $prefix.$this->tbl_groups;
        $this->tbl_map = $prefix.$this->tbl_map;
    }

    public function export($file) {
        // select all including groupmap (max 3)
        if (($result = $this->db->query("SELECT * FROM ".$this->tbl_users)) === FALSE) {
            throw new \Exception("Database select error: ".$this->db->error);
        }

        // open file for writing
        if  (!($ofile = fopen($file, "w"))) {
            throw new \Exception("Error writing file: ".$file);
        }

        // write column headers
        $fieldObjects = $result->fetch_fields();
        $fieldNames = array_map(function($el) {return $el->name;}, $fieldObjects);
        $fieldNames[] = "Group-1";
        $fieldNames[] = "Group-2";
        $fieldNames[] = "Group-3";
        fputcsv($ofile, $fieldNames);

        // write data
        while ($row = $result->fetch_row()) {
            // get group titles
            if (($groupResult = $this->db->query("SELECT $this->tbl_groups.title FROM $this->tbl_map INNER JOIN $this->tbl_groups ON $this->tbl_map.group_id = $this->tbl_groups.id WHERE user_id = $row[0] LIMIT 3")) === FALSE) {
                throw new \Exception("Database select error: ".$this->db->error);
            }
            while ($groupRow = $groupResult->fetch_row()) {
                $row[]= $groupRow[0];
            }
            $groupResult->close();
            fputcsv($ofile, $row);
        }

        // and close
        echo "Rows selected: ".$result->num_rows;
        $result->close();
        fclose($ofile);
    }


    //  import is NOT compatible with export (!)
    //
    // fields in csv format:
    //   name, username, password, group-1, group-2, group-3
    //
    // Groups are reference by title, so must exist in target database
    public function import($file) {


        // password_hash($password, PASSWORD_DEFAULT);




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
