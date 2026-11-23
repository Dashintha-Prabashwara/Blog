<?php
session_start();
if (!empty($_SESSION['user_id'])) {
    header('Location: /Blog/dashboard.php');
} else {
    header('Location: /Blog/login.php');
}
exit;
