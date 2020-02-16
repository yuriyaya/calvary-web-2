<?php

class DBSettings
{
    private $hostname;
    private $user;
    private $password;
    private $db;

    function __construct($configFile) {
        $db_config_str = file_get_contents($configFile);
        $db_config = json_decode($db_config_str, true);
        if(array_key_exists('mysql', $db_config))
        {
            $this->hostname = $db_config['mysql']['servername'];
            $this->user = $db_config['mysql']['username'];
            $this->password = $db_config['mysql']['password'];
            $this->db = $db_config['mysql']['dbname'];
        }
    }

    public function getPDOHostString() {
        return 'mysql:host='.$this->hostname.';dbname='.$this->db;
    }

    public function getUser() {
        return $this->user;
    }

    public function getPassword() {
        return $this->password;
    }

}
