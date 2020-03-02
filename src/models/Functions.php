<?php

    class Functions
    {

        static function getFormatDate($date) {

            $ret = '';

            if(strlen($date) == 8) {
                $ret = substr($date, 0, 4).'-'.substr($date, 4, 2).'-'.substr($date, 6, 2);
            }
            
            return $ret;
        }

    }
