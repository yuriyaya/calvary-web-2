<?php

    require_once 'DBConn.php';

    class Login
    {
        public $dbConn;

        function __construct()
        {
            $this->dbConn = new DBConn();
        }

        function checkLogin($id, $pw)
        {
            $ret = false;

            $conn = $this->dbConn->getNewDBConn();

            $query = "SELECT * FROM user_login WHERE user_id='".$id."'";
            $stmt = $conn->prepare($query);
            $stmt->execute();
            $stmt->setFetchMode(PDO::FETCH_ASSOC);

            while($row = $stmt->fetch()) {
                if(password_verify($pw, $row['user_pw'])) {
                    //correct password
                    $ret = true;
                } else {
                    $ret = false;
                }
            }

            $this->dbConn->closeDBConn();

            return $ret;
        }
    }