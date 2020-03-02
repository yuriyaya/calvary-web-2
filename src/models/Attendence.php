<?php

    require_once 'DBConn.php';

    class Attendence
    {
        public $dbConn;

        function __construct() {
            $this->dbConn = new DBConn();
        }

        function getAttLog($partNum, $date) {

            $ret = array();
            $conn = $this->dbConn->getNewDBConn();

            $dbName = Attendence::getAttLogDBName($partNum);
            if(!empty($dbName)) {
                $query = "SELECT id, attend_value, late_value FROM ".$dbName." WHERE date='".$date."'";
                $stmt = $conn->prepare($query);
                $stmt->execute();
                $stmt->setFetchMode(PDO::FETCH_ASSOC);
                while($row = $stmt->fetch()) {
                    $ret[$row['id']] = array($row['attend_value'], $row['late_value']);
                }
            }
            
            $this->dbConn->closeDBConn();

            return $ret;
        }

        function getMonthAttLog($partNum, $date) {

            $ret = array();

            $conn = $this->dbConn->getNewDBConn();

            $dbName = Attendence::getMonthAttLogDBName($partNum);

            if(!empty($dbName)) {
                $query = "SELECT id, att_month_rate FROM ".$dbName." WHERE date=:in1;";
                $stmt = $conn->prepare($query);
                $stmt->bindParam(':in1', $in1);
                $in1 = $date;
                $stmt->execute();
                $stmt->setFetchMode(PDO::FETCH_ASSOC);
                while($row = $stmt->fetch()) {
                    $ret[$row['id']] = $row['att_month_rate'];
                }
            }
            
            $this->dbConn->closeDBConn();

            return $ret;
        }

        function getTDClass($value) {
            $ret = '';

            if($value>0) {
                if($value == 100) {
                    $ret = 'bg-success';
                } else if($value < 50) {
                    $ret = 'bg-danger text-white';
                }
            }
            return $ret;
        }

        function updateAttLog($partNum, $entryDate, $id, $att, $late) {

            $ret = array();

            $conn = $this->dbConn->getNewDBConn();

            $dbName = Attendence::getAttLogDBName($partNum);

            if(!empty($dbName)) {
                for($idx=0; $idx<count($id); $idx++) {
                    // check attendence log already exist or not
                    $query = "SELECT * FROM ".$dbName." WHERE date='".$entryDate."' AND id=".$id[$idx].";";
                    $stmt = $conn->prepare($query);
                    $stmt->execute();
                    $stmt->setFetchMode(PDO::FETCH_ASSOC);
                    $flag = false;
                    $attNumber = Attendence::getAttNumber($att[$idx]);
                    $lateNumber = Attendence::getAttNumber($late[$idx]);
                    if($lateNumber == 10) {
                        $attNumber = $lateNumber;
                    }
                    while($row = $stmt->fetch()) {
                        $flag = true;
                        $attExist = $row['attend_value'];
                        $lateExist = $row['late_value'];
                        if(($attExist != $attNumber) || ($lateExist != $lateNumber)) {
                            // update
                            $sn = $row['sn'];
                            $query_update = "UPDATE ".$dbName." SET attend_value=".$attNumber.", late_value=".$lateNumber." WHERE sn=".$sn.";";
                            $stmt_update = $conn->prepare($query_update);
                            $stmt_update->execute();
                            $ret[$id[$idx]] = 'u';
                        } else {
                            // keep current data (do nothing)
                            $ret[$id[$idx]] = '-';
                        }
                    }
                    if(!$flag) {
                        // insert
                        $query_insert = "INSERT INTO ".$dbName."(id, date, attend_value, late_value) VALUES (".$id[$idx].", '".$entryDate."', ".$attNumber.", ".$lateNumber.");";
                        $stmt_insert = $conn->prepare($query_insert);
                        $stmt_insert->execute();
                        $ret[$id[$idx]] = 'i';
                    }
                }
            }
            
            $this->dbConn->closeDBConn();

            return $ret;

        }

        static function getAttNumber($input) {
            $ret = 0;
    
            if($input == 'on') {
                $ret = 10;
            } else {
                $ret = 0;
            }
            return $ret;
        }

        static function getAttLogDBName($partNum) {

            switch($partNum) {
                case 1:
                    $ret = 'attendence_sopa';
                    break;
                case 2:
                    $ret = 'attendence_sopb';
                    break;
                case 3:
                    $ret = 'attendence_sopbp';
                    break;
                case 4:
                    $ret = 'attendence_altoa';
                    break;
                case 5:
                    $ret = 'attendence_altob';
                    break;
                case 6:
                    $ret = 'attendence_tenor';
                    break;
                case 7:
                    $ret = 'attendence_bass';
                    break;
                default:
                    $ret = '';
                    break;
            }
            
            return $ret;
        }

        static function getMonthAttLogDBName($partNum) {

            switch($partNum) {
                case 1:
                    $ret = 'attendence_month_sopa';
                    break;
                case 2:
                    $ret = 'attendence_month_sopb';
                    break;
                case 3:
                    $ret = 'attendence_month_sopbp';
                    break;
                case 4:
                    $ret = 'attendence_month_altoa';
                    break;
                case 5:
                    $ret = 'attendence_month_altob';
                    break;
                case 6:
                    $ret = 'attendence_month_tenor';
                    break;
                case 7:
                    $ret = 'attendence_month_bass';
                    break;
                default:
                    $ret = '';
                    break;
            }
            
            return $ret;
        }

    }
