<?php
require_once "../includs/Classes.php";
require_once "../includs/CheckprivAdmin.php";
require_once "../includs/CheckLogin.php";
?>
<html>
<head>
    <link rel="stylesheet" type="text/css" href="../../bootstrap/css/bootstrap.css">
</head>
<body>
<?php
if (isset($_GET['id']))
{
    $fid=filter_var($_GET['id'],FILTER_SANITIZE_NUMBER_INT);


    if (!empty($fid))
    {
        $datab=new DataBase();
        $con=$datab->Connect();
        $res=$datab->getData($con,"SELECT * FROM `users` where id = ?",[$fid]);
        if (count($res)>0)
        {
            $u= $res[0]['username'];
            $e=$res[0]['email'];
            echo "
        <form method='post'>
        <input type='text' name='user' value='$u'>
        <br>
        <input type='password' name='pass'>
        <input type='email' name='email' value='$e' >
        <input type='submit' name='sub' class='btn-primary' value='Update'>
        </form>
        ";
            if (isset($_POST['sub']))
            {
                $update=new EditAccount();
                if (!empty($_POST['pass']))
                {
                    $update->EditINF($_POST['user'],$_POST['email'],$fid,$_POST['pass']);
                } else
                {
                    $update->EditINF($_POST['user'],$_POST['email'],$fid);
                }
            }


        }

    }
}
?>
<form method="post"></form>
</body>
</html>