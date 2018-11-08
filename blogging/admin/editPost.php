<?php
require_once ("../includs/Classes.php");
require_once ("../includs/CheckLogin.php");
require_once ("../includs/CheckprivAdmin.php");
if (isset($_GET['id']))
{
   $fid =filter_var($_GET['id'],FILTER_VALIDATE_INT);
    if (!empty($fid))
    {
        $edit=new EditPosts();
        $edit->edit($_GET['id']);
    } else
    {
        echo "<p style='color: red'>"."Can't be empty"."</p>";
    }

}