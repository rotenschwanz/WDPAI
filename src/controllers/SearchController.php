<?php

require_once "AppController.php";
require_once __DIR__."/../models/Message.php";
require_once __DIR__."/../repository/AccountRepository.php";
require_once __DIR__."/../repository/MessageRepository.php";

class SearchController extends AppController 
{
    public function __construct()
    {
        parent::__construct();
        $this->userRepository = new UserRepository();
        $this->accountRepository = new AccountRepository();
        $this->messageRepository = new MessageRepository();
    }

    public function search()
    {
        if (!isset($_SESSION['user']))
        {
            $url = "http://$_SERVER[HTTP_HOST]";
            header("Location: {$url}/login");
        }

        if (!$this->isPost()) {
            $accounts = $this->accountRepository->getAccounts();
            return $this->render('search', ['accounts' => $accounts]);
        }
        
        $contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
        
        if ($contentType === "application/json") {
            $content = trim(file_get_contents("php://input"));
            $decoded = json_decode($content, true);

            header('Content-type: application/json');
            http_response_code(200);

            $user = $this->userRepository->getRoleAndJoinDate($_SESSION['user']);
            if ($user['role_id'] != "1")
                $decoded['dateFrom'] = $user['join_date'];
            
            echo json_encode($this->messageRepository->getMessages($decoded['user'], $decoded['dateFrom'], $decoded['dateTo'], $decoded['searchedString']));
        }
    }
}