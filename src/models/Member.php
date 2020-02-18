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

        function addMember($name, $part, $churchStaff, $calvaryStaff, $lastState) {
            
                $conn = $this->dbConn->getNewDBConn();
                
                $ret[0] = -2; //result
                $ret[1] = 0; //last insert id
                //check whether same name is already registered in same part
                $query = "SELECT * FROM member_info WHERE part=".$part." AND name='".$name."'";
                $stmt = $conn->prepare($query);
                $stmt->execute();
                $num_of_rows = $stmt->rowCount();
                
                if($num_of_rows>0) {
                    $ret[0] = -1;
                }

                $query = "INSERT INTO member_info (id, name, part, church_staff, calvary_staff, last_state) VALUES (:in1, :in2, :in3, :in4, :in5, :in6)";
                $stmt = $conn->prepare($query);
                $stmt->bindParam(':in1', $in1);
                $stmt->bindParam(':in2', $in2);
                $stmt->bindParam(':in3', $in3);
                $stmt->bindParam(':in4', $in4);
                $stmt->bindParam(':in5', $in5);
                $stmt->bindParam(':in6', $in6);
                $in1 = $part;
                $in2 = $name;
                $in3 = $part;
                $in4 = $churchStaff;
                $in5 = $calvaryStaff;
                $in6 = $lastState;
                $stmt->execute();

                $id = $conn->lastInsertId();
                if($ret[0] != -1) {
                    $ret[0] = 0;
                }
                $ret[1] = $id;

                $this->dbConn->closeDBConn();

                return $ret;

        }

    }
