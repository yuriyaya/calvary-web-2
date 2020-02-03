<?php

    class Login
    {
        static function checkLogin($dbConn, $id, $pw)
        {
            $ret = false;

            $query = "SELECT * FROM user_login WHERE user_id='".$id."'";
            $stmt = $dbConn->prepare($query);
            $stmt->execute();
            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            while($row = $stmt->fetch()) {
                if(password_verify($pw, $row['user_pw'])) {
                    //correct password
                    $ret = true;
                } else {
                    $ret = false;
                }
            }

            return $ret;
        }
    }