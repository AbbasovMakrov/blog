<?php
require_once "includs/Classes.php";
require_once "includs/CheckUserAve.php";
if (!isset($_SESSION))
{
    session_start();
}
if (isset($_GET['token']))
{
    $password=new newPassC();
    $password->newPass($_GET['token']);
}