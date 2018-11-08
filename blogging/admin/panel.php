<?php
require_once ("../includs/CheckLogin.php");
require_once ("../includs/CheckprivAdmin.php");
require_once ("../includs/Classes.php");
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="../../bootstrap/css/bootstrap.css" type="text/css">
</head>
<body>
<form method="post" enctype="multipart/form-data">
    <input type="text" name="title">
    <br>
    <textarea name="des"></textarea><br>
    <input type="file" name="up"><br>
    <button type="submit" name="sub" class="btn-primary">Submit</button>
</form>
<?php
if (isset($_POST['sub']))
{
    if (isset($_FILES['up']))
    {
        $setNewPost=new NewPost();
        $setNewPost->setPost($_POST['title'],$_POST['des'],$_FILES['up'],$_SESSION['user']);
    }
}
$mangePosts=new MangePosts();
$mangePosts->getPOSTS();
for ($i=0;$i<5;$i++)
{
    echo "<br>";
}
$mangeUsers=new mangeUsers();
$mangeUsers->getusers();
for ($j=0;$i<4;$i++)
{
    echo "<br>";
}
$mangeComments=new mangeComments();
$mangeComments->getComments();
if (isset($_POST['active']))
{
    $ac=new ActivateUsers();
    $ac->ActiveUser($_POST['ID']);
}
if (isset($_POST['mkAdmin']))
{
    $mkAdmin=new SetAdminFromUsers();
    $mkAdmin->setAdmin($_POST['ID']);
}


$userID=$_SESSION['id'];
echo "<a href='profileAdmin.php?id=$userID'>Change INF</a>";
?>
<form method="post">
    <input type="submit" name="logOut" class="btn-danger" value="LogOUT">
</form>
<?php
if (isset($_POST['logOut']))
{
    if (isset($_SESSION))
    {
        session_destroy();
    }
    header("Location:login.php");
    die();
}
?>
</body>
</html>
