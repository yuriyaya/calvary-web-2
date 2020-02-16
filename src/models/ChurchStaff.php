<?php

    // require_once 'DBConn.php';

    class ChurchStaff
    {
        // public $dbConn;

        // function __construct() {
        //     $this->dbConn = new DBConn();
        // }

        static function getChurchStaffNumber($churchStaffName) {

            switch($churchStaffName) {
                case '성도':
                    $ret = 1;
                    break;
                case '집사':
                    $ret = 2;
                    break;
                case '안수집사':
                    $ret = 3;
                    break;
                case '권사':
                    $ret = 4;
                    break;
                case '장로':
                    $ret = 5;
                    break;
                case '전도사':
                    $ret = 6;
                    break;
                case '목사':
                    $ret = 7;
                    break;
                default:
                    $ret = 0;
                    break;
            }
            
            return $ret;
        }

        static function getChurchStaffName($churchStaffNumber) {

            switch($churchStaffNumber) {
                case 1:
                    $ret = '성도';
                    break;
                case 2:
                    $ret = '집사';
                    break;
                case 3:
                    $ret = '안수집사';
                    break;
                case 4:
                    $ret = '권사';
                    break;
                case 5:
                    $ret = '장로';
                    break;
                case 6:
                    $ret = '전도사';
                    break;
                case 7:
                    $ret = '목사';
                    break;
                default:
                    $ret = '';
                    break;
            }
            
            return $ret;
        }

    }
