<?php
include 'includes/config.php';

if (isset($_POST['user'])) {
    $user = new User;
    $user->user = $_POST['user'];
    $user->pass = $_POST['pass'];

    if ($user->login()) {
        if (isset($_SESSION['r'])) {
            header($_SESSION['r']);
        } else {
            header('Location:/test/');
        }
    }

    $formError = true;
}

include 'includes/templates/login.php';