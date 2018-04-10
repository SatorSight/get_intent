<?php

class Helper{
    public static function getFieldValue($field_name){
        if(!empty($_REQUEST[$field_name]))
            return $_REQUEST[$field_name];
        return '';
    }
}