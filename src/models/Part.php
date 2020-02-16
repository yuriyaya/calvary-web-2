<?php

    // require_once 'DBConn.php';

    class Part
    {
        // public $dbConn;

        // function __construct() {
        //     $this->dbConn = new DBConn();
        // }

        static function getPartNumber($partName) {

            switch($partName) {
                case '소프라노A':
                    $ret = 1;
                    break;
                case '소프라노B':
                    $ret = 2;
                    break;
                case '소프라노B+':
                    $ret = 3;
                    break;
                case '알토A':
                    $ret = 4;
                    break;
                case '알토B':
                    $ret = 5;
                    break;
                case '테너':
                    $ret = 6;
                    break;
                case '베이스':
                    $ret = 7;
                    break;
                default:
                    $ret = 0;
                    break;
            }
            
            return $ret;
        }

        static function getPartName($partNumber) {

            switch($partNumber) {
                case 1:
                    $ret = '소프라노A';
                    break;
                case 2:
                    $ret = '소프라노B';
                    break;
                case 3:
                    $ret = '소프라노B+';
                    break;
                case 4:
                    $ret = '알토A';
                    break;
                case 5:
                    $ret = '알토B';
                    break;
                case 6:
                    $ret = '테너';
                    break;
                case 7:
                    $ret = '베이스';
                    break;
                default:
                    $ret = '';
                    break;
            }
            
            return $ret;
        }

    }
