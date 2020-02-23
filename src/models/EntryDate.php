<?php

    require_once 'DBConn.php';

    class EntryDate
    {
        public $dbConn;

        function __construct() {
            $this->dbConn = new DBConn();
        }

        function getEntryDateSearchResult($start, $end) {

            $conn = $this->dbConn->getNewDBConn();
            $query = "SELECT * FROM attendence_date WHERE att_date >='".$start."' AND att_date <='".$end."' ORDER BY att_date ASC;";
            $stmt = $conn->prepare($query);
            $stmt->execute();
            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            $ret = $stmt->fetchAll();
            $this->dbConn->closeDBConn();

            return $ret;
        }

        function getEntryDateBySN($sn) {

            $conn = $this->dbConn->getNewDBConn();
            $query = "SELECT * FROM attendence_date WHERE sn=".$sn.";";
            $stmt = $conn->prepare($query);
            $stmt->execute();
            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            $ret = $stmt->fetchAll();
            $this->dbConn->closeDBConn();

            return $ret;
        }

        function addEntryDate($date, $description) {

            $ret = -1;
            try {

                $conn = $this->dbConn->getNewDBConn();
                $query = "SELECT * FROM attendence_date WHERE att_date='".$date."'";
                $stmt = $conn->prepare($query);
                $stmt->execute();
                $num_of_rows = $stmt->rowCount();
                if($num_of_rows>0) {
                    $ret = -2;
                } else {
                    //date not exist, add it
                    $day = date('w', strtotime($date));
                    $query = "INSERT INTO attendence_date (att_date, type, details) VALUES (:in1, :in2, :in3)";
                    $stmt = $conn->prepare($query);
                    $stmt->bindParam(':in1', $in1);
                    $stmt->bindParam(':in2', $in2);
                    $stmt->bindParam(':in3', $in3);
                    $in1 = $date;
                    $in2 = $day;
                    $in3 = $description;
                    $stmt->execute();
                    
                    $id = $conn->lastInsertId();
                    $ret = $id;
                }
            }
            catch(PDOException $e)
            {
                $ret = -1;
            }
            $this->dbConn->closeDBConn();
            return $ret;
        }

        function deleteEntryDate($sn) {
            $ret = false;
            try {
                $conn = $this->dbConn->getNewDBConn();
                $query = "DELETE FROM attendence_date WHERE sn=".$sn;
                $stmt = $conn->prepare($query);
                $stmt->execute();
                $this->dbConn->closeDBConn();
                $ret = true;
            } catch(PDOException $e) {
                $ret = false;
            }
            return $ret;
        }

        function updateEntryDateDescription($sn, $description) {
            try {

                $conn = $this->dbConn->getNewDBConn();
                $query = "UPDATE attendence_date SET details=:description WHERE sn=:sn";
                $stmt = $conn->prepare($query);
                $stmt->bindParam(':description', $description, PDO::PARAM_STR);
                $stmt->bindParam(':sn', $sn, PDO::PARAM_INT);
                $stmt->execute();
                $this->dbConn->closeDBConn();

                return true;

            } catch(PDOException $e) {
                return false;
            }
        }

        static function getEntryDateDay($dayNumber) {

            switch($dayNumber) {
                case 0:
                    $ret = '주일';
                    break;
                case 1:
                    $ret = '월';
                    break;
                case 2:
                    $ret = '화';
                    break;
                case 3:
                    $ret = '수';
                    break;
                case 4:
                    $ret = '목';
                    break;
                case 5:
                    $ret = '금';
                    break;
                case 6:
                    $ret = '토';
                    break;
                default:
                    $ret = '';
                    break;
            }
            
            return $ret;
        }
    }
