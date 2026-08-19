<?php

require_once __DIR__ ."/../config/config.php";

session_start();

$_SESSION = [];

session_destroy();

header("Location: " . URL_BASE . "/public/Login.php");
exit;

?>