<?php

class Custom_AssignmentTypes
{
    public $Types = array(
                    'CWK' => "Class Work",
                    'HWK' => "Home Work",
                    'UNT' => "Unit Test",
                    'GPW' => "Group Work",
                    'PRJ' => "Project Work",
                    );


    public static function Types(){
        $types = new self();
        return $types->Types;
    }

    public static function getTypeName($type){
        $types = new self();
        return $types->Types[$type];
    }

}

?>