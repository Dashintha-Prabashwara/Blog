<?php
session_start();
$_SESSION['error'] = 'Registration is disabled on this personal journal.';
header('Location: /Blog/');
exit;
