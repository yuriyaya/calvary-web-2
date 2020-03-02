<?php

    require_once 'DBConn.php';

    class Login
    {
        public $dbConn;
        public $loginId;
        public $loginName;

        function __construct() {
            $this->dbConn = new DBConn();
        }

        function checkLogin($id, $pw) {
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
                    $this->loginId = $id;
                    $this->loginName = $this->getLoginName($id);
                } else {
                    $ret = false;
                }
            }

            $this->dbConn->closeDBConn();

            return $ret;
        }

        static function getLoginName($loginId) {
            switch($loginId) {
                case 'admin':
                    $ret = "관리자";
                    break;
                case 'sopa':
                    $ret = "소프라노A";
                    break;
                case 'sopb':
                    $ret = "소프라노B";
                    break;
                case 'sopbp':
                    $ret = "소프라노B+";
                    break;
                case 'altoa':
                    $ret = "알토A";
                    break;
                case 'altob':
                    $ret = "알토B";
                    break;
                case 'tenor':
                    $ret = "테너";
                    break;
                case 'bass':
                    $ret = "베이스";
                    break;
                default:
                    $ret = '';
                    break;
            }
            return $ret;
        }
    }