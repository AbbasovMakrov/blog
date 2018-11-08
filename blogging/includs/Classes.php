<html>
<head>
    <link rel="stylesheet" href="../bootstrap/css/bootstrap.css" type="text/css">
</head>
</html>
<?php
require_once "db.php";

abstract  class functions
{
    function uploadImages ($name)
    {
        $upDir="../"."images"."/";
        $upFile=$name;
        $extn=end(explode('.',$upFile['name']));
        $AlowdedExtn=array("jpg","png","jpeg");
        $AlowdedMime=array("image/jpg","image/png","image/jpeg");
        $FinalName=$upDir."image-".rand(10,14*7754^44).".".$extn;
        $FinalNameDB=explode("../",$FinalName);
        $upAccept=null;
        if (!empty($upFile))
        {

            if (in_array($extn,$AlowdedExtn))
            {
                if (in_array($upFile['type'],$AlowdedMime))
                {
                    if (getimagesize($upFile['tmp_name']) == true)
                    {
                        $upAccept=true;
                    } else
                    {
                        $upAccept=false;
                       echo $this->ShowError("Sorry This is not image");
                    }
                } else
                {
                    $upAccept=false;
             echo       $this->ShowError("Sorry Alowded Extns is only jpg,jpeg,png");
                }
            } else
            {
                $upAccept=false;
            echo    $this->ShowError("Sorry Alowded Extns is only jpg,jpeg,png");
            }
        }
        else
        {
            $upAccept=false;
          echo  $this->ShowError("Sorry upload Can not empty");
        }
        if ($upAccept == true)
        {
            if (move_uploaded_file($upFile['tmp_name'],$FinalName))
            {
                return end($FinalNameDB);
            }
        }
        else
        {
            error_reporting(0);
            echo $this->ShowError("Sorry you can't set post");
        }
    }
    function getSalt() {
        $charset = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789/\\][{}\'";:?.>,<!@#$%^&*()-_=+|';
        $randStringLen = 64;

        $randString = "";
        for ($i = 0; $i < $randStringLen; $i++) {
            $randString .= $charset[mt_rand(0, strlen($charset) - 1)];
        }

        return $randString;
    }
    function Hashing ($password,$cost=11)
    {

        $salt=$this->getSalt();
        $options=array(
            'cost'=>$cost,
            'salt'=>$salt
        );
        $Hashed=password_hash($password,1,$options);
        return $Hashed;
    }
    function Filter ($parm)
    {
        if (filter_var($parm,FILTER_VALIDATE_INT) == true)
        {
            $Filterd=filter_var($parm,FILTER_SANITIZE_NUMBER_INT);
            return $Filterd;
        }
        elseif (filter_var($parm,FILTER_VALIDATE_EMAIL) == true)
        {
            $Filterd=filter_var($parm,FILTER_SANITIZE_EMAIL);
            return $Filterd;
        }
        else
        {
            $Filterd=filter_var($parm,FILTER_SANITIZE_STRING);
            return $Filterd;
        }
    }
    function ShowError($Error)
    {
        $e="<p style='color: red'>".$Error."</p>";
        return $e;
    }
    function ShowSec($Sec)
    {
        $s="<p style='color: green'> ".$Sec."</p>";
        return $s;
    }


}

class SignUp extends functions
{
    private $user;
    private $pass;

    function setUsers($username,$password,$email)
    {
        if (!empty($username) && !empty($password) && !empty($email))
        {
            $User=$this->user=$username;
            $Pass=$this->pass=$password;
            $db=new DataBase();
            $con=$db->Connect();
            $sql="SELECT * FROM `users` where username=?";
            $res=$db->getData($con,$sql,[$this->Filter($User)]);
            if (count($res)>0)
            {
                echo $this->ShowError("Sorry Username is Used");
            }
            else
            {
                $priv=0;
                $status=0;
                $sql="INSERT INTO `users`(`username`, `password`, `privliges`, `email`,`status`) VALUES (?,?,?,?,?)";
                $res=$db->setData($con,$sql,[
                    $this->Filter($User),
                    $this->Hashing($this->Filter($Pass)),
                    $priv,
                    $this->Filter($email),
                    $status
                ]);
                if (count($res)>0)
                {
                    unset($User);
                    unset($Pass);
                    unset($email);
                    echo $this->ShowSec("Done");
                }
                else
                {
                    echo $this->ShowError("Failed");
                }
            }
        }
        else
        {
            echo $this->ShowError("All Fields is Req");
        }
    }
}
class SignIn extends functions
{
private $username=null;
private $password=null;
function Login ($user,$pass)
{
    $usern=$this->Filter($this->username=$user);
    $passwd=$this->Filter($this->password=$pass);
    if (!empty($usern) && !empty($passwd))
    {
        $datab=new DataBase();
        $con=$datab -> Connect();
        $q="SELECT * FROM `users` where username=?";
        $res=$datab->getData($con,$q,[$usern]);
        if (count($res)>0)
        {
            if (password_verify($passwd,$res[0]['password']))
            {
                if (!isset($_SESSION))
                {
                    session_start();
                    session_regenerate_id();
                }
                $_SESSION['id']=$res[0]['id'];
                $_SESSION['user']=$usern;
                $_SESSION['priv']=$res[0]['privliges'];

                if ($res[0]['privliges'] == 0)
                {
                    header("Location:user/events.php");
                    die();
                } elseif ($res[0]['privliges'] == 1)
                {
                    header("Location:admin/panel.php");
                    die();
                }
            } else
            {
               echo $this->ShowError("Sorry Check your password");
            }

        } else
        {
          echo  $this->ShowError("Sorry username not found ");
        }
    } else
    {
      echo  $this->ShowError("Sorry all fields is req");
    }
}
}
Class CheckLogin
{
    function checkLog ($ID)
    {
        $db=new DataBase();
        $con=$db->Connect();
        $q="SELECT * FROM `users` where id=?";
        $res=$db->getData($con,$q,[$ID]);
        if (count($res) != 1)
        {
            header("Location:../login.php");
            die();
        }
    }
}
class CheckAdmin
{
   private $priv=null;
   function adminPriv($privl)
   {
       $db=new DataBase();
       $con=$db->Connect();
       $res=$db->getData($con,"SELECT * FROM `users` WHERE privliges = ?",[$this->priv=$privl]);
       if ($res[0]['privliges'] != 1)
       {
           header("Location:user/panel");
           die();
       }
   }
}
class NewPost extends functions
{
    private $tit=null;
    private $des=null;
    private $img=null;
    private $user=null;
    function setPost ($title,$desckrep,$image,$username)
    {
        $Title=$this->Filter($this->tit=$title);
        $Des=$this->Filter($this->des=$desckrep);
        $userN=$this->user=$username;
        if (!empty($Title) && !empty($Des))
        {

            $db=new DataBase();
            $con=$db->Connect();
            $query="INSERT INTO `posts`( `title`, `des`, `images`,  `username`) VALUES (?,?,?,?)";
            $res=$db->setData($con,$query,[
                $Title,
                $Des,
                $this->uploadImages($image),
                $userN
            ]);
            if (count($res)>0)
            {
                $this->ShowSec("Done");
            } else
            {
                $this->ShowError("Failed");
            }
        } else
        {
            $this->ShowError("Sorry All Fields is Req");
        }

    }
}
class MangePosts extends functions
{
    function getPOSTS()
    {
        $db=new DataBase();
        $con=$db->Connect();
        $res=$db->getData($con,"SELECT * FROM `posts`",[]);
        if (count($res)>0)
        {
            echo "<table >";
            echo "<tr style='background: yellow;border: 1px solid black'>";
            echo "<th>Title post||  </th>";
            echo "<th>Descrip of post||   </th>";
            echo "<th>Action||   </th>";
            echo "<th>Writer||   </th>";
            echo "</tr>";
            foreach ($res as $re)
            {
                echo "<tr style='background: green'>";
                echo "<td>".$re['title']."</td>";
                echo "<td>".$re['des']."</td>";
                $id=$re['id'];
                echo "<td>"."<form method='post'>
<input type='hidden' value='$id' name='id'>
<input type='submit' class='btn-danger' name='delpost' value='Delete'>

<input type='submit' class='btn-primary' name='edit' value='edit'>
</form>"."</td>";
                echo "<td>".$re['username']."</td>";
                echo "</tr>";
            }
            echo "</table>";
            if (isset($_POST['del']))
            {
                $db=new DataBase();
                $con=$db->Connect();
                $query="SELECT * FROM `posts` WHERE `id` =?";
                $res=$db->getData($con,$query,[$this->Filter($_POST['id'])]);
                if (count($res)>0)
                {
                    $res=$db->getData($con,"DELETE FROM `comments` WHERE `post_id` = ?",$this->Filter($_POST['id']));
                    if (count($res)>0)
                    {
                        $img="../".$res[0]['images'];
                        if (unlink($img))
                        {
                            $query = "DELETE FROM `posts` WHERE `id` =?";
                            $res=$db->setData($con,$query,[$_POST['id']]);
                            if (count($res)>0)
                            {
                                echo $this->ShowSec("Done");
                            } else
                            {
                                echo $this->ShowError("FAIL");
                            }
                        } else
                        {
                            echo $this->ShowError("FAIL");
                        }
                    }

                } else
                {
                    echo $this->ShowError("FAIL");
                }
            }
            if (isset($_POST['edit']))
            {
                $idH=$this->Filter($_POST['id']);
                header("Location:editPost.php?id=$idH");
            }
        }
    }
}
class EditPosts extends functions
{
    function edit($ID)
    {
        $idE=$this->Filter($ID);
        $db=new DataBase();
        $con=$db->Connect();
        $res=$db->getData($con,"SELECT * FROM `posts` where id=?",[$idE]);
        if (count($res)>0)
        {
            $title=$res[0]['title'];
            $des=$res[0]['des'];
            echo "<form method='post'>
<input type='text' name='title' value='$title'>
<textarea name='des'>$des</textarea>
<input type='submit' class='btn-outline-primary' name='sub'>
  </form>";
            if (isset($_POST['sub']))
            {
                $query="UPDATE `posts` SET `title`=?,`des`=? WHERE `id` = ?";
                $res=$db->setData($con,$query,[
                        $this->Filter($_POST['title']),
                        $this->Filter($_POST['des']),
                    $idE
                ]);
                if (count($res)>0)
                {
                    echo $this->ShowSec("Done");
                }
                else
                {
                    echo $this->ShowError("Error");
                }
            }
        }

    }
}
class mangeUsers extends functions
{
    function getusers ()
    {

        $db=new DataBase();
        $con=$db->Connect();


            $q="SELECT * FROM `users`";
            $res=$db->getData($con,$q,[]);
            if (count($res)>0)
            {
                echo "<table>";
                echo "<tr style='background: yellow'>
<th>Username</th>
<th>Action</th>
</tr>
";
                foreach ($res as $re)
                {
                    $id=$re['id'];
                    echo "<tr style='background: green'>";
                    echo "<td>".$re['username']."</td>";
                    echo "<td>
<form method='post'>
<input type='hidden' value='$id' name='IDU'>
<input type='submit' class='btn-danger' value='Delete' name='delU'>
";
                    if ($re['status'] == 0)
                    {
                        echo "<input type='submit' class='btn-primary' value='Active' name='active'>";
                    }
                    if ($re['privliges'] == 1 )
                    {
                        echo "<input type='submit' class='btn-primary' value='Make as User' name='mkuser'>";
                    } elseif ($re['privliges'] == 0)
                    {
                        echo "<input type='submit' name='mkAdmin' value='Set Him as Admin' class='btn-success'>";

                    }
                    echo "</form>
</td>";
                    echo "</tr>";
                }
                echo "</table>";
                if (isset($_POST['delU']))
                {
                    $fID=$this->Filter($_POST['IDU']);
                    $res=$db->setData($con,"DELETE FROM `users` where id=?",[$fID]);
                    if (count($res)>0)
                    {
                        echo $this->ShowSec("Done");
                    } else
                    {
                        echo $this->ShowError("Fail");
                    }
                }
            }


    }
}
class ActivateUsers extends functions
{
    function ActiveUser($ID)
    {
        $db=new DataBase();
        $con=$db->Connect();
        $query="UPDATE `users` SET `status`=1 WHERE `id` = ?";
        $res=$db->setData($con,$query,[$this->Filter($ID)]);
        if (count($res)>0)
        {
            echo $this->ShowSec("Done");
        } else
        {
            echo $this->ShowError("Fail");
        }
    }
}
class EditAccount extends functions
{
    function EditINF ($username,$email,$ID,$password=null)
    {
        $db=new DataBase();
        $con=$db->Connect();
        $fId=$this->Filter($ID);
        $fUsername=$this->Filter($username);
        $fPassword=$this->Filter($password);
        $fEmail=$this->Filter($email);
        $query="SELECT * from `users` where id=?";
        $res=$db->getData($con,$query,[$fId]);

        if (!empty($fPassword))
        {
            $query="UPDATE `users` SET `username` =? , `password` =? , `email` = ? WHERE `id` =?";
            $res=$db->setData($con,$query,[
                    $fUsername,
                    $this->Hashing($fPassword),
                $fEmail,
                $fId
            ]);
            if (count($res)>0)
            {
                echo $this->ShowSec("Done");
            } else
            {
                echo $this->ShowError("FAil");
            }
        } else
        {
            $query="UPDATE `users` SET `username` =? , `email` = ? WHERE `id` =?";
            $res=$db->setData($con,$query,[
                    $fUsername,
                $fEmail,
                $fId
            ]);
            if (count($res)>0)
            {
                echo $this->ShowSec("Done");
            } else
            {
                echo $this->ShowError("FAil");
            }
        }

    }
}
class SetAdminFromUsers extends functions
{
    function setAdmin ($ID)
    {
        $fID=$this->Filter($ID);
        $db=new DataBase();
        $con=$db->Connect();
        $q="UPDATE `users` SET `privliges` = 1 WHERE `id` =?";
        $res=$db->setData($con,$q,[$fID]);
        if (count($res)>0)
        {
            echo $this->ShowSec("done");
        } else
        {
            echo $this->ShowError("Fail");
        }
    }
}
class makeAsUser extends functions
{
    function mkUser($ID)
    {
        $fID=$this->Filter($ID);
        $db=new DataBase();
        $con=$db->Connect();
        $res=$db->setData($con,"UPDATE `users` SET `privliges` = 0 WHERE `id` =?",[$fID]);
        if (count($res)>0)
        {
            echo $this->ShowSec("Done");
        } else
        {
            echo $this->ShowError("Fail");
        }
    }
}
class AddCommentByUser extends functions
{
    function AddComment ($comment,$username,$Post_id)
    {
        $fComment=$this->Filter($comment);
        $fUser=$this->Filter($username);
        $fPost_id=$this->Filter($Post_id);
        if (!empty($fComment))
        {
         $db=new DataBase();
         $con=$db->Connect();
         $status=0;
         $q="INSERT INTO `comments`(`username`, `post_id`, `comment`,`status`) VALUES (?,?,?,?)";
         $res=$db->setData($con,$q,[
            $fUser,
            $fPost_id,
            $fComment,
            $status
         ]);
         if (count($res)>0)
         {
             unset($fComment);
             echo $this->ShowSec("Done");
         } else
         {
             echo $this->ShowError("Fail");
         }
        } else
        {
            echo $this->ShowError("Can not be empty");
        }
    }
}
class mangeComments extends functions
{
    function getComments()
    {
        $db=new DataBase();
        $con=$db->Connect();


        $q="SELECT * FROM `comments`";
        $res=$db->getData($con,$q,[]);
        if (count($res)>0)
        {
            echo "<table>
<tr style='background: yellow'>
<th>Username||</th>
<th>Comment||</th>
<th>Title Post||</th>
<th>Action||</th>
</tr>
";
            foreach ($res as $re)
            {
                $id=$this->Filter($re['c_id']);
                echo "<tr style='background: green'>";
                echo "<td>".$re['username']."</td>";
                echo "<td>".$re['comment']."</td>";
                $resPosts=$db->getData($con,"SELECT `post_id` FROM `comments` where `c_id` = ?",[$id]);
                foreach ($resPosts as $resPost)
                {
                    $fRes=$db->getData($con,"SELECT `title` FROM `posts` where id=?",[$resPost['post_id']]);
                    foreach ($fRes as $fRe)
                    {
                        echo "<td>".$fRe['title']."</td>";
                    }
                }
                echo "<td>
<form method='post'>
<input type='hidden' value='$id' name='ID'>";
                if ($re['status'] == 0)
                {
                    echo "<input type='submit' class='btn-primary' value='Accept Comment' name='accept'>";
                    echo "<input type='submit' name='reject' value='Reject Comment' class='btn-danger'>";
                } elseif ($re['status'] == 1)
                {
                    echo "<input type='submit' class='btn-danger' value='Delete' name='delCom'>";
                }

                echo "</form>
</td>";
                echo "</tr>";
            }
            echo "</table>";
            if (isset($_POST['delCom']))

            {
                $fDelComment=$this->Filter($_POST['ID']);
                $res=$db->setData($con,"DELETE FROM `comments` where `c_id` = ?",[$fDelComment]);
                if (count($res)>0)
                {
                    echo $this->ShowSec("Done");
                } else
                {
                    echo $this->ShowError("Fail");
                }
            }
        }
    }
}
class AcceptComment extends functions
{
    function Accept($CommentID)
    {
        $fCommentID=$this->Filter($CommentID);
        $db=new DataBase();
        $con=$db->Connect();
        $q="SELECT * FROM `comments` where c_id=?";
        $res=$db->getData($con,$q,[$fCommentID]);
        if (count($res)>0)
        {
            $q="UPDATE `comments` SET `status` = 1 WHERE `c_id` = ?";
            $res=$db->setData($con,$q,[$fCommentID]);
            if (count($res)>0)
            {
                echo $this->ShowSec("Done");
            } else
            {
                echo $this->ShowError("Fail");
            }
        } else
        {
            echo $this->ShowError("NO comment ");
        }
    }
}
class RejectComment extends functions
{
    function Reject($CommentID)
    {
        $fCommentID=$this->Filter($CommentID);
        $db=new DataBase();
        $con=$db->Connect();
        $q="SELECT * FROM `comments` where c_id=?";
        $res=$db->getData($con,$q,[$fCommentID]);
        if (count($res)>0)
        {
            $q="DELETE FROM `comments` WHERE `c_id` = ?";
            $res=$db->setData($con,$q,[$fCommentID]);
            if (count($res)>0)
            {
                echo $this->ShowSec("Done");
            } else
            {
                echo $this->ShowError("Fail");
            }
        } else
        {
            echo $this->ShowError("NO comment ");
        }
    }
}
class getPosts extends functions
{
 function getP()
 {
     $db=new DataBase();
     $con=$db->Connect();
     $q="SELECT * FROM `posts`";
     $res=$db->getData($con,$q,[]);
     foreach ($res as $re)
     {
         $id=$re['id'];
         $title=$re['title'];
         echo "<a href='../user/events.php?id=$id'>
$title
</a>";
     }
 }
}
class AddCommentByAdmin extends functions
{
    function AddComment ($comment,$username,$Post_id)
    {
        $fComment=$this->Filter($comment);
        $fUser=$this->Filter($username);
        $fPost_id=$this->Filter($Post_id);
        if (!empty($fComment))
        {
            $db=new DataBase();
            $con=$db->Connect();
            $status=1;
            $q="INSERT INTO `comments`(`username`, `post_id`, `comment`,`status`) VALUES (?,?,?,?)";
            $res=$db->setData($con,$q,[
                $fUser,
                $fPost_id,
                $fComment,
                $status
            ]);
            if (count($res)>0)
            {
                unset($fComment);
                echo $this->ShowSec("Done");
            } else
            {
                echo $this->ShowError("Fail");
            }
        } else
        {
            echo $this->ShowError("Can not be empty");
        }
    }
}
class GetPostsAndComments extends functions
{
 function getIt($ID)
 {
     $fID=$this->Filter($ID);
     $db=new DataBase();
     $con=$db->Connect();
     $res=$db->getData($con,"SELECT * FROM `posts` where id=?",[$fID]);
     if (count($res)>0)
     {
         echo "<div style='position: center'>";
         echo "<h1>".$res[0]['title']."</h1>";
         $img="../".$res[0]['images'];
         echo "<img src='$img' style='max-width: 70%'>";
         echo "<p>".$res[0]['des']."</p>";
         echo "------------------------------------------------------"."<br>";
         $res=$db->getData($con,"SELECT * FROM `comments` where `post_id` =? AND `status` = 1",[$fID]);
         if (count($res)>0)
         {
             $ValUserDelComment=$db->getData($con,"SELECT `username` FROM `users` where `id` = ? ",[$_SESSION['id']]);
             foreach ($res as $re)
             {
                 echo "<label><b>".$re['username']."</b></label>";
                 echo "<p>".$re['comment']."</p>";
                 $id=$re['c_id'];
                 if ($ValUserDelComment[0]['username'] == $_SESSION['user'])
                 {
                     echo "<form method='post'>";
                     echo "<input type='hidden' value='$id' name='commentID'>";
                     echo "<input type='submit' name='del' value='Delete' class='btn-danger'>";
                     echo"</form>";
                 }
                 echo "------------------------------------------------------"."<br>";
             }

         }
         else
         {
             echo $this->ShowError("No Comments");
         }
         $u=$_SESSION['user'];
         echo "<form method='post'>
<label><b>$u</b></label>
<input type='text' name='Addcomment' placeholder='Add Comment'>
<input type='submit' value='Add' name='sub' class='btn-primary'>
</form>";
     }
     else
     {
         echo $this->ShowError("No Post");
     }
 }
}
class DeleteCommentsFromUserSide extends functions
{
    function DeleteComments($CommentID,$username,$POST_ID)
    {
        $fC_id=$this->Filter($CommentID);
        $fUsername=$this->Filter($username);
        $fPost_id=$this->Filter($POST_ID);
        $db=new DataBase();
        $con=$db->Connect();
        $q="SELECT `c_id` FROM `comments` where `username` = ? AND `post_id` =? AND `status` = 1";
        $res=$db->getData($con,$q,[
                $fUsername,
                $fPost_id
        ]);
        if (count($res)>0)
        {
            $YourCommentID=[$res[0]['c_id']];
            if (in_array($CommentID,$YourCommentID))
            {
                $q="DELETE FROM `comments` WHERE `c_id` = ?";
                $res=$db->setData($con,$q,[
                   $CommentID
                ]);
                if (count($res)>0)
                {
                    echo $this->ShowSec("Done");
                } else
                {
                    echo $this->ShowError("Sorry Fail to delete Comment ");
                }
            } else
            {
                echo $this->ShowError("Sorry Fail to delete Comment ");
            }
        } else
        {
            echo $this->ShowError("Sorry Fail to delete Comment ");
        }
    }
}
class ResetPass extends functions
{
    function SendEmailToken($userInput)
    {
        $fUserInput=$this->Filter($userInput);
        if (!empty($fUserInput))
        {
            $token=rand(100,(15*277)^2);
            $db=new DataBase();
            $con=$db->Connect();

            $q="SELECT * FROM `users` where `username`=?";
            $res=$db->getData($con,$q,[
                    $fUserInput
            ]);
            if (count($res)>0)
            {
                $_SESSION['user']=$fUserInput;
                $userS= $_SESSION['user'];
                $q="INSERT INTO `passwordReset`( `username`, `token`) VALUES (?,?)";
                $set=$db->setData($con,$q,[
                    $userS,
                    $token
                ]);
                if (count($set)>0)
                {
                    echo $this->ShowSec("Done");
                    unset($fUserInput);
                    echo "<a href='newPass.php?token=$token'>Password Reset</a>";
                }
            }
             else
             {
                 echo $this->ShowError("username not found");
             }

        }
    }
}
class CheckUserAv extends functions
{
    function CheckUser($username)
    {
        $db=new DataBase();
        $con=$db->Connect();
        $q="SELECT * FROM `passwordReset` WHERE `username` =?";
        $res=$db->getData($con,$q,[$username]);
        if (count($res) != 1 )
        {
            header("Location:ResetPass.php");
            die();
        }
    }
}
class newPassC extends functions
{
    function newPass($token)
    {
        $fToken = $this->Filter($token);
        if (!empty($fToken)) {
            $db = new DataBase();
            $con = $db->Connect();
            $q = "SELECT * FROM `passwordReset` where `token`=?";
            $res = $db->getData($con, $q, [$fToken]);
            if (count($res) > 0) {
                echo "<form method='post'>
<input type='text' name='pass' placeholder='New Pass' id='pass1'><br>
<input type='text'  placeholder='Validate New Pass' id='pass2'><br>
<input type='submit' name='sub' class='btn-outline-info' value='reset Pass'>
</form>";
                ?>
                <script>
                    var pass1 = document.getElementById('pass1');
                    var pass2 = document.getElementById('pass2');

                    function CheckEq() {

                        if (pass1.value === pass2.value) {
                            <?php
                            if (isset($_POST['sub'])) {
                                $fPass=$this->Filter($_POST['pass']);
                                $q = "UPDATE `users` SET `password` = ? WHERE `username` = ?";
                                $userS= $_SESSION['user'];
                                $res=$db->setData($con,$q,[
                                        $this->Hashing($fPass),
                                        $userS
                                ]);
                                if (count($res)>0)
                                {
                                    $q="DELETE FROM passwordReset where username=?";
                                    $res=$db->setData($con,$q,[
                                       $userS
                                    ]);
                                    if (count($res)>0)
                                    {
                                        unset($fToken);
                                        header("Location:done.html");
                                        die();
                                    } else
                                    {
                                        header("Location:fail.html");
                                        die();
                                    }
                                } else
                                {
                                    header("Location:fail.html");
                                    die();
                                }
                            }
                            ?>
                        } else {
                            pass2.style.background = "red";
                        }

                    }

                    pass2.addEventListener('blur', CheckEq, false);
                </script>
                <?php
            }
            else
            {
                echo $this->ShowError("undefiend Token");
            }

        } else
        {
            echo $this->ShowError("Can not be empty");
        }
    }
}
$project=[
        'End' => "<p style='color: #1c7430'>"."Project is Done"."</p>"
];
echo $project['End'];
