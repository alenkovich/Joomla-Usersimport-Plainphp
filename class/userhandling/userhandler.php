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
    //   name, username, email, password, group-1, group-2, group-3
    //
    // Groups are reference by title, so must exist in target database
    public function import($file) {
        // open file for reading
        if (!($ifile = fopen($file, "r"))) {
            throw new \Exception("Error reading file: ".$file);
        }

        // read first line. Needs special care because it is possibly a headerrow
        $row = fgetcsv($ifile);
        if ($row && ((strtolower(trim($row[0])) == "name") || (strtolower(trim($row[0])) == "naam"))) {
            // assume first row is a header row
            $row = fgetcsv($ifile);
        }

        // process all rows
        $numRows = 0;
        do {
            $numRows += $this->importRow($row);
        } while ($row = fgetcsv($ifile));

        // close
        fclose($ifile);
        echo "Rows imported: ".$numRows;
    }

    // @return: number of rows imported
    private function importRow($row) {
        // need at least 4 fields, so sanitize
        if (!$row || count($row) < 5 ) {
            return 0;
        }
        // check if userid and email already exist
        if (!$this->checkUser($row[1], $row[2])) {
            return 0;
        }

        // Insert user, no error or duplicat checking for fields (username and email)
        $user = array(
            'name' => $row[0],
            'username' => $row[1],
            'email' => $row[2],
            'password' => password_hash($row[3], PASSWORD_DEFAULT),
            'block' => 0,
            'sendEmail' => 1,
            'registerDate' => date("Y-m-d H:i:s"),
            'lastvisitDate' => "0000-00-00 00:00:00",
            'activation' => 0,
            'params' => '{"admin_style":"","admin_language":"","language":"","editor":"","helpsite":"","timezone":""}',
            'lastResetTime' => "0000-00-00 00:00:00",
            'resetCount' => 0,
            'otpKey' => '',
            'otep' => '',
            'requireReset' => 0
        );
        $this->insertTableRow($user, $this->tbl_users);

        // Find the groups and insert map rows
        $this->insertMap($this->db->insert_id, array_slice($row, 4));

        // conclude
        return 1;
    }

    private function checkUser($username, $email) {
        $username = $this->db->real_escape_string($username);
        $email = $this->db->real_escape_string($email);
        if (($result = $this->db->query("SELECT username, email FROM $this->tbl_users WHERE username='$username' OR email='$email'")) === FALSE) {
            throw new \Exception("Database select error: ".$this->db->error);
        }
        if ($result->num_rows != 0) {
            echo "User: $username or Email: $email allready present in usertable, ignored\n";
            return False;
        }
        return True;
    }

    private function insertMap($userId, $groupNames) {
        foreach($groupNames as $groupName) {
            $group = $this->db->real_escape_string($groupName);
            if (($result = $this->db->query("SELECT id FROM $this->tbl_groups WHERE title='$group'")) === FALSE) {
                throw new \Exception("Groupname ".$group." database select error: ".$this->db->error);
            }
            if ($result->num_rows == 0) {
                echo "Group $groupName not found for userid $userId, ignored";
                $result->close();
                continue;
            }
            $groupId = $result->fetch_row()[0];
            $map = array(
                "user_id" => $userId,
                "group_id" => $groupId
            );
            $this->insertTableRow($map, $this->tbl_map);
            $result->close();
        }
    }

    private function insertTableRow($data, $table) {
        $cols = implode(',', array_keys($data));
        $values = implode(
            ',',
            array_map(
                function ($el) {
                    return '\''.$this->db->real_escape_string($el).'\'';
                },
                array_values($data)
            )
        );

        if (!$this->db->query('INSERT INTO '.$table.' ('.$cols.') VALUES ('.$values.')')) {
            throw new \Exception("Database insert error on table $table: ".$this->db->error);
        }
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
