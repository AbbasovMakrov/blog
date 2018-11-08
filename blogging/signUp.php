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
    <input type="email" name="email">
    <br>
    <button type="submit" name="sub" class="btn-primary">Reg</button>
</form>
<?php
require_once("includs/db.php");
require_once ("includs/Classes.php");
if (isset($_POST['sub']))
{
    $setUser=new SignUp();
    $setUser->setUsers($_POST['user'],$_POST['pass'],$_POST['email']);
}
?>
</body>
</html>