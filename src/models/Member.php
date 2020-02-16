<?php

    require_once 'DBConn.php';

    class Member
    {
        public $dbConn;

        function __construct() {
            $this->dbConn = new DBConn();
        }

        function getMemberSearchResult($id, $name, $partNumber) {

            // make query
            $flag=0;
            if(!empty($id)) {
                $flag = $flag+1;
            }
            if(!empty($name)) {
                $flag = $flag+2;
            }
            if(!empty($partNumber)) {
                $flag = $flag+4;
            }

            switch($flag) {
                case 1:
                    $querySearch = " WHERE sn=".$id;
                break;
                case 2:
                    $querySearch = " WHERE name='".$name."'";
                break;
                case 4:
                    $querySearch = " WHERE part=".$partNumber;
                break;
                case 3:
                    $querySearch = " WHERE sn=".$id." AND name='".$name."'";
                break;
                case 5:
                    $querySearch = " WHERE sn=".$id." AND part=".$partNumber;
                break;
                case 6:
                    $querySearch = " WHERE name='".$name."' AND part=".$partNumber;
                break;
                case 7:
                    $querySearch = " WHERE sn=".$id." AND name='".$name."' AND part=".$partNumber;
                break;
            }

            if($flag == 0) {
                $ret = array();
            } else {
                $conn = $this->dbConn->getNewDBConn();
                $query = "SELECT * FROM member_info".$querySearch." ORDER BY part ASC, last_state ASC, name ASC";
                $stmt = $conn->prepare($query);
                $stmt->execute();
                $stmt->setFetchMode(PDO::FETCH_ASSOC);
                $ret = $stmt->fetchAll();
                $this->dbConn->closeDBConn();
            }

            return $ret;
            
        }

        function updateMemberInformation($id, $name, $part, $churchStaff, $calvaryStaff) {
            
            try {

                $conn = $this->dbConn->getNewDBConn();
                $query = "UPDATE member_info SET name = :member_name, part = :member_part, church_staff = :member_staff, calvary_staff = :member_calvary_staff WHERE sn = :member_sn";
                $stmt = $conn->prepare($query);
                echo $query;
                $stmt->bindParam(':member_name', $name, PDO::PARAM_STR);
                $stmt->bindParam(':member_part', $part, PDO::PARAM_INT);
                $stmt->bindParam(':member_staff', $churchStaff, PDO::PARAM_INT);
                $stmt->bindParam(':member_calvary_staff', $calvaryStaff, PDO::PARAM_INT);
                $stmt->bindParam(':member_sn', $id, PDO::PARAM_INT);
                $stmt->execute();
                $this->dbConn->closeDBConn();

                return true;

            } catch(PDOException $e) {

                return false;
            }

        }

    }
