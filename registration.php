<?php
    session_start ();
    include("db.php");
	if(isset($_POST["Done_reg"])) {
        $Email = htmlspecialchars ($_POST["E-Mail"]);
        $Login = htmlspecialchars ($_POST["Login"]);
        $Password = htmlspecialchars ($_POST["Password"]);
        $Password1 = htmlspecialchars ($_POST["Password1"]);
        $Admin = htmlspecialchars ($_POST["as"]);
						
		$Error_email = "";
		$Error_Login = "";
        $Error_Password = "";
        $Error_Password1 = "";
        $Mes = "";
        $Error = false;	
        		
		if($Email == "" || !preg_match ("/@/", $_POST["E-Mail"])) {
			$Error_email = "Введите адрес.";
			$Error = True;
        }
        
        if(strlen($Email) >=50) {
			$Error_email = "Максимальная длина электронной почты: 50 символов.";
			$Error = True;
		}
						
		if($Login == "") {
			$Error_Login = "Введите логин.";
			$Error = True;
        }
        
        if(strlen($Login) >=20) {
			$Error_Login = "Максимальная длина логина: 20 символов.";
			$Error = True;
		}
        
        if($Password == "") {
			$Error_Password = "Введите пароль.";
			$Error = True;
        }
        
        if($Password != $Password1) {
			$Error_Password1 = "Пароли не совпадают.";
			$Error = True;
        }
        
        
        $sql_login = "SELECT * FROM users WHERE login = '$Login'";
        $result_login = mysqli_query($conn, $sql_login);
        $row_login = $result_login->fetch_array(MYSQLI_ASSOC);
        $test_login = $row_login['id'];
        if($test_login != NULL) {
            $Error_Login = "Такой логин уже существует.";
			$Error = True;
        }
        
        $sql_email = "SELECT * FROM users WHERE email = '$Email'";
        $result_email = mysqli_query($conn, $sql_email);
        $row_email = $result_email->fetch_array(MYSQLI_ASSOC);
        $test_email = $row_email['id'];
        if($test_email != NULL) {
            $Error_email = "Такой E-Mail уже существует.";
			$Error = True;
        }

		if(!$Error) {
            $base_url = "guestbook/";
            $options = [
                'cost' => 5,
            ];
            $Password_Hash = password_hash($Password, PASSWORD_BCRYPT, $options);
            $activation = md5($Email);
            $time_reg = time();
            if ($Admin == 'admin') {
                $sql = "INSERT INTO `users` VALUES (
                    id,
                    '$Email',
                    '$Login',
                    '$Password_Hash',
                    '$activation',
                    status,
                    '1',
                    '$time_reg'
                    )";
            } else {
                
                $sql = "INSERT INTO `users` VALUES (
                    id,
                    '$Email',
                    '$Login',
                    '$Password_Hash',
                    '$activation',
                    status,
                    '0',
                    '$time_reg'
                    )";
            }
            
            $result = mysqli_query($conn, $sql) or die("Ошибка ".mysqli_error($conn));
            $Theme = "Активация аккаунта DUDULKA";
            $Body= "Здравствуйте! <br/> <br/> Мы должны убедиться в том, что вы не робот. Пожалуйста, подтвердите созданный аккаунт, и после этого вы сможете начать пользоваться нашим сайтом. <br/> <br/> <a href=\"".$base_url."activation.php?hash=".$activation."\">".$base_url."activation.php?hash".$activation."</a>";
            $headers  = 'MIME-Version: 1.0' . "\r\n";
            $headers .= "Content-type: text/html; charset=utf-8 \r\n";
            mail ($Email, $Theme, $Body, $headers);
            $Mes = "Регистрация выполнена успешно, пожалуйста, проверьте электронную почту.";
            
		}
	}
?>




<!-- 'admin) or (=' -->
<!-- admin' or "=' -->
<!-- pattern для защиты -->
<html>
<head>
    <meta charset="utf-8">
    <link href="style.css" rel="stylesheet" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <div class="forma">
        <form id="registration" name="registration" action="" method="post">
            <p>E-MAIL</p>
            <p><input type="email" name="E-Mail" placeholder="E-MAIL" value="<?=$Email?>"></p>
            <p><label><?=$Error_email?></label></p>
            <p>ЛОГИН</p>
            <p><input type="text" autocomplete="off" name="Login" placeholder="ЛОГИН" value="<?=$Login?>"></p>
            <p><label><?=$Error_Login?></label></p>
            <p>ПАРОЛЬ</p>
            <p><input type="password" name="Password" placeholder="ПАРОЛЬ" value="<?=$Password?>"></p>
            <p><label><?=$Error_Password?></label></p>
            <p>ПОВТОРИТЕ ПАРОЛЬ</p>
            <p><input type="password" name="Password1" placeholder="ПОВТОРИТЕ ПАРОЛЬ" value="<?=$Password1?>"></p>
            <p><label><?=$Error_Password1?></label></p>

            <p><select size="3" multiple name="as">
                <option disabled>Войти как:</option>
                <option value="admin">Администратор</option>
                <option selected value="users">Пользователь</option>
            </select></p>

            <p><input type="submit" id = "sub_reg" name="Done_reg" value="РЕГИСТРАЦИЯ">
            <p><label><?=$Mes?></label></p>
            <a href="http://guestbook/">АВТОРИЗАЦИЯ</a>
        </form>
    </div>
</body>
</html>