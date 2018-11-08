<html>
<head>
    <link rel="stylesheet" type="text/css" href="../bootstrap/css/bootstrap.css">
</head>
<body>
<form method="post">
    <input type="text" name="user">
    <br>
    <input type="password" name="pass">
    <br>
    <button type="submit" name="sub" class="btn-primary">SignIn</button>
</form>
<?php
require_once ("includs/Classes.php");
if (isset($_POST['sub']))
{
    $signIn=new SignIn();
    $signIn->Login($_POST['user'],$_POST['pass']);
}
?>