<?php
require '../database/db.php';
session_start();

session_destroy();
header('Location: gameinfo.php');
exit;
?>