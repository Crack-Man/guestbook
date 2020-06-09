<?php
    session_start ();
    include("db.php");
	if(isset($_POST["Done_log"])) {
        $Email = htmlspecialchars ($_POST["E-Mail"]);
        $Password = htmlspecialchars ($_POST["Password"]);
						
		$Error_email = "";
		$Error_Login = "";
        $Error_Password = "";
        $Error_Password1 = "";
        $Error = false;	
        		
		if($Email == "" || !preg_match ("/@/", $_POST["E-Mail"])) {
			$Error_email = "Введите адрес.";
			$Error = True;
		}
			
        if($Password == "") {
			$Error_Password = "Введите пароль.";
			$Error = True;
        }
        
        
        $sql_email = "SELECT * FROM users WHERE email = '$Email'";
        $result_email = mysqli_query($conn, $sql_email);
        $row = $result_email->fetch_array(MYSQLI_ASSOC);
        $test_email = $row['id'];
        if($test_email != NULL) {
            $Hash_Password = $row['password'];
            if (password_verify($Password, $Hash_Password)) {
                if($row['status'] == '1') {
                    if(!$Error) {
                        setcookie ("email", $row['email'], time() + 50000); 						
                        setcookie ("password", md5($row['email'].$row['password']), time() + 50000); 					
                        $_SESSION['id'] = $row['id'];
                        $id = $_SESSION['id'];
                        header('Location: http://'.$_SERVER['HTTP_HOST'].'/');
                    }
                } else {
                    $Error_Password = "Неверное имя пользователя или пароль.";
                    $Error = True;
                }
            } else {
                $Error_Password = "Неверное имя пользователя или пароль.";
			    $Error = True;
            }
        } else {
            $Error_Password = "Неверное имя пользователя или пароль.";
            $Error = True;
        }
        
		
    }
    

    function auth() {
        if(isset($_COOKIE['email']) && isset($_COOKIE['password'])){
            include("db.php");
            $Email_session = $_COOKIE['email'];
            $sql_session = "SELECT * FROM users WHERE email = '$Email_session'";
            $result_session = mysqli_query($conn, $sql_session);
            $row_session = $result_session->fetch_array(MYSQLI_ASSOC);
            $sess = $row_session['id'];
            unset($_SESSION['id']);
            $_SESSION['id'] = $row_session['id'];
            $id = $_SESSION['id'];
            return true; 		
        } else {return false;}
    }
        
    function is_admin($id) {
        include("db.php");
        $Email_session = $_COOKIE['email'];
        $sql_session = "SELECT * FROM users WHERE email = '$Email_session'";
        $result_session = mysqli_query($conn, $sql_session);
        $row_session = $result_session->fetch_array(MYSQLI_ASSOC);
        $sess = $row_session['id'];
        unset($_SESSION['id']);
        $_SESSION['id'] = $row_session['id'];
        $id = $_SESSION['id'];
        $sql = "SELECT * FROM users WHERE id = '$id'";
        $result = mysqli_query($conn, $sql);
        $row = $result->fetch_array(MYSQLI_ASSOC);
        $admin = $row['admin'];
        if($admin=='1') {return true;}
        else {return false;}
    }
    
    if(isset($_POST["Done_out"])) {
        session_start();
        unset($_SESSION['id']);
        setcookie ("email", "", time() - 1); 						
        setcookie ("password", "", time() - 1);
        header('Location: http://'.$_SERVER['HTTP_HOST'].'/');
    }

    if(isset($_POST["Clean"])) {
        $clean_time = time() - 260000;
        $query = "DELETE FROM users WHERE time < '$clean_time' and status = '0'";
        mysqli_query($conn, $query);
        header('Location: http://'.$_SERVER['HTTP_HOST'].'/');
    }


    if(isset($_POST["add"])) {
        $Email_session = $_COOKIE['email'];
        $sql_session = "SELECT * FROM users WHERE email = '$Email_session'";
        $result_session = mysqli_query($conn, $sql_session);
        $row_session = $result_session->fetch_array(MYSQLI_ASSOC);
        $sess = $row_session['id'];
        unset($_SESSION['id']);
        $_SESSION['id'] = $row_session['id'];
        $id = $_SESSION['id'];

        $comment = htmlspecialchars ($_POST["comment"]);
        $Error_comment = false;
        if($comment=="") {
            $Error_comment = true;
        }
        if(!$Error_comment) {
            $time = time();
            $sql_comment = "INSERT INTO `comments` VALUES (
                id,
                '$id',
                '$comment',
                '$time'
                )";
            $result_comment = mysqli_query($conn, $sql_comment) or die("Ошибка ".mysqli_error($conn));
        }
        header('Location: http://'.$_SERVER['HTTP_HOST'].'/');
    }


    if(!empty($_GET['delete_id'])) {
        $id_d = (int) $_GET['delete_id'];
        if($id_d > 0) {
         $query_del = 'DELETE FROM `comments` WHERE `id`="'.$id_d.'"';
         mysqli_query($conn, $query_del);
         header('Location: http://'.$_SERVER['HTTP_HOST'].'/');
        }
      }


    if(!empty($_GET['rename_id'])) {
        $id_ren = (int) $_GET['rename_id'];
        if($id_ren > 0) {
            $sql_ren = "SELECT * FROM comments WHERE `id` = '$id_ren'";
            $result_ren = mysqli_query($conn, $sql_ren);
            $row_ren = $result_ren->fetch_array(MYSQLI_ASSOC);
            $id_text = $row_ren['text'];
            $_SESSION['text'] = $id_text;
            echo
                "<script>
                    let i = 1;
                </script>";
        }
    }

    if(isset($_POST["back"])) {
        $id_text = "";
        echo
                "<script>
                    i = 0;
                </script>";
    }

    if(isset($_POST["rename"])) {
        $rename = htmlspecialchars ($_POST["comment"]);
        if ($rename != "") {
            $sql_rename = "UPDATE comments SET text = '$rename' WHERE `id` = '$id_ren'";
            $result_rename = mysqli_query($conn, $sql_rename);
            echo "<script>  i = 0; </script>";
            $id_text = "";
            echo '<script>document.location.href = "index.php"; </script>';
        }
        
    }
    
?>

<html>
<head>
    <meta charset="utf-8">
    <link href="style.css" rel="stylesheet" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <?php if(!(auth())) { ?>
        <div class="forma">
            <form id="log_in" name="log_in" action="" method="post">
                <p>E-MAIL</p>
                <p><input type="email" name="E-Mail" placeholder="E-MAIL" value="<?=$Email?>"></p>
                <p><label><?=$Error_EMAIL?></label></p>
                <p>ПАРОЛЬ</p>
                <p><input type="password" name="Password" placeholder="ПАРОЛЬ" value="<?=$Password?>"></p>
                <p><label><?=$Error_Password?></label></p>
                <p><input type="submit" id = "sub_reg" name="Done_log" value="ВХОД">
                <p><label><?=$Mes?></label></p>
            </form>
            <a href="/registration.php">ЗАРЕГИСТРИРОВАТЬСЯ</a>
        </div>
    <?php } else {
            if(is_admin($id)) { ?>
            <form name="log_clean" action="" method="post" style="width: 10%; position: absolute; left: 10px;">
                <input type="submit" id = "Clean" name="Clean" value="ОЧИСТКА">
            </form>
            <?php }?>
            <form name="log_out" action="" method="post" style="width: 10%; position: absolute; right: 10px;">
                <?
                $Email_session = $_COOKIE['email'];
                $sql_session = "SELECT * FROM users WHERE email = '$Email_session'";
                $result_session = mysqli_query($conn, $sql_session);
                $row_session = $result_session->fetch_array(MYSQLI_ASSOC);
                $sess = $row_session['id'];
                unset($_SESSION['id']);
                $_SESSION['id'] = $row_session['id'];
                $id = $_SESSION['id'];
                
                echo "<div class='login'>".$row_session['login']."</div>";
                ?>
                <p><input type="submit" id = "sub_out" name="Done_out" value="ВЫХОД"></p>                
            </form>
            
            
        <?php
        $sql_out = "SELECT * FROM comments ORDER BY id";
        $result_out = mysqli_query($conn, $sql_out);
        $row_out = $result_out->fetch_array(MYSQLI_ASSOC);

        $Login_author = $row_out['id_user'];
        $sql_author = "SELECT * FROM users WHERE id = '$Login_author'";
        $result_author = mysqli_query($conn, $sql_author);
        $row_author = $result_author->fetch_array(MYSQLI_ASSOC);
        $id_del = $row_out['id'];
        ?>
        <div class='field' id='field'>
        <?php
        if ($row_out != NULL) {
            echo "<br />";
            if($row_author == NULL) {
                echo "<div class='author'>"."ПОЛЬЗОВАТЕЛЬ УДАЛЁН";
            } else {
                echo "<div class='author'>".$row_author['login'];
            }
            if ((is_admin($id)) && ($row_session['login'] != $row_author['login'])) {
                echo "<a href='?delete_id=$id_del' style='float: right; font-size: 40px; color: black; margin-right: 10px; text-decoration: none;'>&#215;</a>";
            }
            if ($row_session['login'] == $row_author['login']) {
                echo "<a href='?delete_id=$id_del' style='float: right; font-size: 40px; color: black; margin-right: 10px; text-decoration: none;'>&#215;</a>";
                echo "<a href='?rename_id=$id_del' style='float: right; font-size: 30px; color: black; margin-right: 10px; text-decoration: none;'>&#128736;</a>";  
            }            
            echo "</div>";
            echo "<div class='commentary'>".$row_out['text']."</div>";
            
            
            echo "<br />";
        }
        while ($row_out = $result_out->fetch_assoc()) {
            $Login_author = $row_out['id_user'];
            $sql_author = "SELECT * FROM users WHERE id = '$Login_author'";
            $result_author = mysqli_query($conn, $sql_author);
            $row_author = $result_author->fetch_array(MYSQLI_ASSOC);
            $id_del = $row_out['id'];
            if($row_author == NULL) {
                echo "<div class='author'>"."ПОЛЬЗОВАТЕЛЬ УДАЛЁН";
            } else {
                echo "<div class='author'>".$row_author['login'];
            }
            
            if ((is_admin($id)) && ($row_session['login'] != $row_author['login'])) {
                echo "<a href='?delete_id=$id_del' style='float: right; font-size: 40px; color: black; margin-right: 10px; text-decoration: none;'>&#215;</a>";
            }
            if ($row_session['login'] == $row_author['login']) {
                echo "<a href='?delete_id=$id_del' style='float: right; font-size: 40px; color: black; margin-right: 10px; text-decoration: none;'>&#215;</a>";
                echo "<a href='?rename_id=$id_del' style='float: right; font-size: 30px; color: black; margin-right: 10px; text-decoration: none;'>&#128736;</a>";
            } 
            echo "</div>";
            echo "<div class='commentary'>".$row_out['text']."</div>";
            echo "<br />";
        }
        ?>
        </div>
        <form name="comm" action="" method="post" style="width: 50%; margin: 0px auto 20px auto;">
            <p><textarea name="comment" autocomplete="off" style="font-size: 20px; width: 100%; height: 150px; border: 1px solid #3b2020; border-radius: 20px;"><? echo $id_text; ?></textarea></p>
            <input type="submit" id = "add" name="add" value="Отправить">
            <input type="submit" id = "rename" style="width: 89%; float: left; display: none;" name="rename" value="Изменить">
            <input type="submit" id = "back" style="float: right; width: 10%; display: none;" name="back" value="Отменить">
        </form>
    <?php
    }
    ?>
    <script>
        field = document.getElementById('field');
        field.scrollTop = field.scrollHeight;
        if(i == 1) {
            let rename = document.getElementById('rename');
            rename.style.display = 'block';
            let back = document.getElementById('back');
            back.style.display = 'block';
            let add = document.getElementById('add');
            add.style.display = 'none';
        } else {
            let rename = document.getElementById('rename');
            rename.style.display = 'none';
            let back = document.getElementById('back');
            back.style.display = 'none';
            let add = document.getElementById('add');
            add.style.display = 'block';
        }  
    </script>
</body>
</html>