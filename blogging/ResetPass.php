<html>
<body>
<form method="post">
    <input type="text" name="usr">
    <input type="submit" name="sub" class="btn-outline-info">
</form>
</body>
</html>
<?php
require_once "includs/Classes.php";
if (!isset($_SESSION))
{
    session_start();
}
if (isset($_POST['sub']))
{
    $reset=new ResetPass();
    $reset->SendEmailToken($_POST['usr']);
}