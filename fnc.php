<?php

class fnc{
    var $fname;
    public $username;
    protected $email_address;
    private $password;

    public function computer_user($fname){
        return $fname;
    }

    public function user_age($name, $yob){
        $age = date('Y') - $yob;
        return $name . " is " . $age;
    }
}

?>