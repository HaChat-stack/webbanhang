<?php

session_start();


$_SESSION = array();


session_destroy();

header('Location: dangnhap.php');
exit();
?>