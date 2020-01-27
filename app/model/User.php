<?php
namespace App\Model;

/**
 * Created by Yishi Lu.
 * User: Yishi Lu
 * Date: 2020/01/25
 */

class User{
 
    private $name = "";
    private $password = "";
    private $confirmPassword = "";
    private $email = "";
 
    public function setName($name){

        $this->name = $name;
    }

    public function setPassword($password){

        $this->password = $password;
    }

    public function setConfirmPassword($confirmPassword){

        $this->confirmPassword = $confirmPassword;
    }

    public function setEmail($email){

        $this->email = $email;
    }

    public function getName(){

        return $this->name;
    }

    public function getPassword(){

        return $this->password;
    }

    public function getConfirmPassword(){

        return $this->confirmPassword;
    }

    public function getEmail(){

        return $this->email;
    }
}
?>