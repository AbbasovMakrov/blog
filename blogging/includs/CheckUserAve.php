<?php
require_once "Classes.php";
if (!isset($_SESSION))
{
    session_start();
}
$Check=new CheckUserAv();
$Check->CheckUser($_SESSION['user']);