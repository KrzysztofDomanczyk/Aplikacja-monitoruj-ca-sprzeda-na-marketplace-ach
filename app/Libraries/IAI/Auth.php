<?php
namespace App\Libraries\IAI;

class Auth
{
    private $login = "xxx";
    private $password = "xxxx";

    
    public function getLogin()
    {
        return  $this->login;
    }

    public function getAuthenticatedKey()
    {
         return $this->authenticateKey();
    }

    private function authenticateKey()
    {
        return  $authenticatedKey = sha1(date('Ymd') . sha1($this->password));
    }
}
