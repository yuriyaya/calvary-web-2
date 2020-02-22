<?php

    require_once 'DBConn.php';

    class MemberState
    {
        public $dbConn;

        function __construct() {
            $this->dbConn = new DBConn();
        }

        function addMemberState($id, $date, $state) {

            $ret = false;
            try {

                $conn = $this->dbConn->getNewDBConn();
                //add record to database
                $query = "INSERT INTO member_state (id, state_update_date, state) VALUES (:in1, :in2, :in3)";
                $stmt = $conn->prepare($query);
                $stmt->bindParam(':in1', $in1);
                $stmt->bindParam(':in2', $in2);
                $stmt->bindParam(':in3', $in3);

                $in1 = $id;
                $in2 = $date;
                $in3 = $state;
                $stmt->execute();

                $this->dbConn->closeDBConn();

                $ret = true;

            } catch(PDOException $e) {
                $ret = false;
            }
            return $ret;

        }

        function deleteMemberState($id) {

            try {

                $conn = $this->dbConn->getNewDBConn();

                $query = "DELETE FROM member_state WHERE id=".$id;
                $stmt = $conn->prepare($query);
                $stmt->execute();

                $this->dbConn->closeDBConn();

                return true;

            } catch(PDOException $e) {

                return false;
            }

        }

        static function getMemberStateNumber($memberStateName) {

            switch($memberStateName) {
                case '정대원':
                    $ret = 1;
                    break;
                case '솔리스트':
                    $ret = 2;
                    break;
                case '신입':
                    $ret = 3;
                    break;
                case '임시':
                    $ret = 4;
                    break;
                case '특별':
                    $ret = 5;
                    break;
                case '휴식':
                    $ret = 6;
                    break;
                case '제적':
                    $ret = 7;
                    break;
                case '은퇴':
                    $ret = 8;
                    break;
                case '명예':
                    $ret = 9;
                    break;
                default:
                    $ret = 0;
                    break;
            }
            
            return $ret;
        }

        static function getMemberStateName($memberStateNumber) {

            switch($memberStateNumber) {
                case 1:
                    $ret = '정대원';
                    break;
                case 2:
                    $ret = '솔리스트';
                    break;
                case 3:
                    $ret = '신입';
                    break;
                case 4:
                    $ret = '임시';
                    break;
                case 5:
                    $ret = '특별';
                    break;
                case 6:
                    $ret = '휴식';
                    break;
                case 7:
                    $ret = '제적';
                    break;
                case 8:
                    $ret = '은퇴';
                    break;
                case 9:
                    $ret = '명예';
                    break;
                default:
                    $ret = '';
                    break;
            }
            
            return $ret;
        }

    }
