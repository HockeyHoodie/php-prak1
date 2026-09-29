<?php

session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: tasks/index.php');
} else {
    header('Location: auth/login.php');
}

exit;