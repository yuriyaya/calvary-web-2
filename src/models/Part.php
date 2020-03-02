<?php

    require_once 'DBConn.php';

    class Part
    {
        public $dbConn;
        public $partNum;

        function __construct($partNum) {
            $this->dbConn = new DBConn();
            $this->partNum = $partNum;
        }

        function getPartAttendenceMemberList($date) {

            $ret = array();
            $conn = $this->dbConn->getNewDBConn();
            $query = "SELECT mi.sn, name, calvary_staff, state FROM member_info AS mi RIGHT JOIN member_state AS ms ON mi.sn=ms.id WHERE ms.state_update_date<='".$date."' AND mi.part=".$this->partNum." ORDER BY mi.name ASC, ms.state_update_date DESC;";
            $stmt = $conn->prepare($query);
            $stmt->execute();
            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            // $ret = $stmt->fetchAll();
            $prevId = 0;
            $partMember = array();
            $partStaff = array();
            $partNewbie = array();
            $partTemp = array();
            $partSpecial = array();
            $partPause = array();
            while($row = $stmt->fetch()) {
                if($prevId != $row['sn']) {
                    $temp = array('id'=>$row['sn'], 'name'=>$row['name'], 'state'=>$row['state']);
                    if($row['calvary_staff'] == 2) {
                        // part staff
                        array_push($partStaff, $temp);
                    } else {
                        if($row['state'] <= 2) {
                            array_push($partMember, $temp);
                        } else if($row['state'] == 3) {
                            array_push($partNewbie, $temp);
                        } else if($row['state'] == 4) {
                            array_push($partTemp, $temp);
                        } else if($row['state'] == 5) {
                            array_push($partSpecial, $temp);
                        } else if($row['state'] == 6) {
                            array_push($partPause, $temp);
                        } else {}
                    }
                    $prevId = $row['sn'];
                }
            }
            $ret = array_merge($partStaff, $partMember);
            $ret = array_merge($ret, $partNewbie);
            $ret = array_merge($ret, $partTemp);
            $ret = array_merge($ret, $partSpecial);
            $ret = array_merge($ret, $partPause);
            $this->dbConn->closeDBConn();

            return $ret;
        }

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

        static function getPartNumberByLoginId($loginId) {

            switch($loginId) {
                case 'sopa':
                    $ret = 1;
                    break;
                case 'sopb':
                    $ret = 2;
                    break;
                case 'sopbp':
                    $ret = 3;
                    break;
                case 'altoa':
                    $ret = 4;
                    break;
                case 'altob':
                    $ret = 5;
                    break;
                case 'tenor':
                    $ret = 6;
                    break;
                case 'bass':
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
