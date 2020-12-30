<?php

require_once "AppController.php";
require_once __DIR__."/../models/User.php";
require_once __DIR__."/../repository/UserRepository.php";


class SecurityController extends AppController
{
    public function __construct()
    {
        parent::__construct();
        $this->userRepository = new UserRepository();
    }

    public function login()
    {
        if (!$this->isPost()) {
            if (isset($_SESSION['user']))
            {
                $url = "http://$_SERVER[HTTP_HOST]";
                header("Location: {$url}/home");
            }
            return $this->render('login');
        }

        $login = $_POST['login'];
        $password = $_POST['password'];

        $user = $this->userRepository->getUser($login);

        if (!$user)
        {
            return $this->render('login', ['messages' => ['User doesn\'t exist']]);
        }

        if (!password_verify($password, $user->getPassword()))
            return $this->render('login', ["messages" => ["Wrong password!"]]);

        $_SESSION['user'] = $user->getLogin();
        
        $url = "http://$_SERVER[HTTP_HOST]";
        header("Location: {$url}/home");
    }

    public function logout()
    {
        session_start();
        session_unset();
        session_destroy(); 
        $url = "http://$_SERVER[HTTP_HOST]";
        header("Location: {$url}/");
    }

    public function changePassword() 
    {
        if (!$this->isPost()) {
            return $this->render('change_password');
        }

        $old_password = $_POST['old_password'];
        $password = $this->userRepository->getPassword($_SESSION['user']);

        if (!password_verify($old_password, $password))
        {
            return $this->render('change_password', ['messages' => ['Złe hasło']]);
        }

        if (empty($_POST['new_password']))
        {            
            return $this->render('change_password', ['messages' => ['Pole "Nowe hasło" jest puste']]);
        }

        if (empty($_POST['repeated_password']))
        {            
            return $this->render('change_password', ['messages' => ['Pole "Powtórz hasło" jest puste']]);
        }

        $new_password = $_POST['new_password'];
        $repeated_password = $_POST['repeated_password'];

        if ($new_password != $repeated_password)
        {
            return $this->render('change_password', ['messages' => ['Podane hasła różnią się']]);
        }

        $this->userRepository->changePassword($_SESSION['user'], password_hash($new_password, PASSWORD_DEFAULT));
        return $this->render('change_password', ['messages' => ['Pomyślna zmiana hasła']]);
    }
}