<?php
session_start();

//permet de vider les variables global askip
$_SESSION = [];

session_destroy();

header("Location: login");
exit();
