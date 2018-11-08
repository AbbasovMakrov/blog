<?php
require_once ("Classes.php");
if (!isset($_SESSION))
{
    session_start();
}
$check=new CheckAdmin();
$check->adminPriv($_SESSION['priv']);