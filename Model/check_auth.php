<?php
    include_once 'session.inc';
       
    if (empty($_SESSION['username'])) {
        header('Location: login.php');
    }
    
    function is_administrator() {
        return $_SESSION['administrator'];
    }
    