<?php
if (!isset($_SESSION))
{
    session_start();
}
require_once ("../includs/Classes.php");
require_once ("../includs/CheckLogin.php");
if (isset($_GET['id']))
{
    $get=new GetPostsAndComments();
    if (!empty($get->Filter($_GET['id'])))
    {
        $get->getIt($_GET['id']);
        if ($_SESSION['priv'] == 0 )
        {
            $setComment=new AddCommentByUser();
        } elseif ($_SESSION['priv'] == 1 )
        {
            $setComment=new AddCommentByAdmin();
        }
       if (isset($_POST['sub']))
       {
           $setComment->AddComment($_POST['Addcomment'],$_SESSION['user'],$_GET['id']);
       }
       if (isset($_POST['del']))
       {
           $del=new DeleteCommentsFromUserSide();
           $del->DeleteComments($_POST['commentID'],$_SESSION['user'],$get->Filter($_GET['id']));
       }
    }
}