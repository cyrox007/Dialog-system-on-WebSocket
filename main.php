<?php
class Main {
    public $users;

    public function get($get_user) {
        $this->users = array(1, 2, 3);
        $new = array();
        foreach ($this->users as $key => $value) {
            if ($value == $get_user) {
                continue;
            }
            $new[$key] = $value;
        }
        return $new;
    }

    public function conn_db() {
        return new SQLite3("test.db");
    }

    public function get_dialog($get_user) {
        $db = $this->conn_db();
        $sql = "SELECT utd.id, d.user_id, 
                    utd.dialog_id
                FROM user_to_dialog AS utd
                INNER JOIN dialoges AS d
                    ON utd.dialog_id = d.id
                WHERE utd.user_id = {$get_user}";
        $raw = $db->query($sql);
        
        $result = [];
        while ($row = $raw->fetchArray()) {
            $result[] = $row;
        }
        return $result;
    }
}