<?php

    require_once 'DBSettings.php';

    class DBConn
    {
        
        private $hostString;
        private $user;
        private $password;

        function __construct()
        {
            $dbSettingFile = __DIR__ . '/../../../db_config.json';
            $dbSettings = new DBSettings($dbSettingFile);
            $this->hostString = $dbSettings->getPDOHostString();
            $this->user = $dbSettings->getUser();
            $this->password = $dbSettings->getPassword();

        }

        public function getNewDBConn()
        {
            
            $pdoConn = new PDO($this->hostString, $this->user, $this->password, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
            return $pdoConn;
        }

        public function closeDBConn($pdoConn)
        {
            $pdoConn = null;
        }
    }