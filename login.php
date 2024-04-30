<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8">
    <title>Login</title>
    <link rel="stylesheet" href="css/login.css">
    <script src="https://kit.fontawesome.com/953731a208.js" crossorigin="anonymous"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="css/favicon.ico">
</head>
<body>
<form class="login" action="login.php" method="post">
    <?php

    use rest\RestAPI;

    require("./util/Utils.php");
    require("./rest/RestAPI.php");

    if (isset($_GET["forgot_password"]) && $_GET["forgot_password"]) {
        echo '<div class="error"><h4>Please contact an administrator!</h4></div>';
    }

    if (isset($_POST["submit"])) {
        if (($data = RestAPI::getAccountData($_POST["username"])) !== null) {
            if (password_verify($_POST["password"], $data["password"])) {
                session_start();
                $_SESSION['username'] = $data["username"];
                if ($data["initialPassword"]) {
                    header("Location: resetpassword.php?name=" . $data["username"]);
                    exit;
                }

                header('Location: index.php');
            } else {
                echo '<div class="error"><h4>Login failed. Password is incorrect.</h4></div>';
            }
        } else {
            echo '<div class="error"><h4>Login failed. </h4></div>';
        }
    }
    ?>
    <h1><i class="fas fa-user"></i> Login</h1>
    <label>
        <input type="text" name="username" placeholder="Username" autocomplete="username" required>
    </label>
    <label>
        <input type="password" name="password" placeholder="Password" autocomplete="current-password" required>
    </label>
    <br>
    <button type="submit" name="submit">Login</button>
    <br><br><br><br>
    <a href="login.php?forgot_password=true"><i class="fas fa-key"></i> Forgot Password?</a>
</form>
</body>
</html>
