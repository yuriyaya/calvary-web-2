<?php

    // require_once 'DBConn.php';

    class CalvaryStaff
    {
        // public $dbConn;

        // function __construct() {
        //     $this->dbConn = new DBConn();
        // }

        static function getCalvaryStaffNumber($calvaryStaffName) {

            switch($calvaryStaffName) {
                case '대원':
                    $ret = 1;
                    break;
                case '파트장':
                    $ret = 2;
                    break;
                default:
                    $ret = 0;
                    break;
            }
            
            return $ret;
        }

        static function getCalvaryStaffName($calvaryStaffNumber) {

            switch($calvaryStaffNumber) {
                case 1:
                    $ret = '대원';
                    break;
                case 2:
                    $ret = '파트장';
                    break;
                default:
                    $ret = '';
                    break;
            }
            
            return $ret;
        }

    }
