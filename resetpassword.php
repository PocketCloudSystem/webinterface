<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8">
    <title>Set a new password</title>
    <link rel="stylesheet" href="css/login.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.2/css/all.css" integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="css/favicon.ico">
</head>
<body>
<form class="login" action="resetpassword.php?name=<?php

use util\Utils;
use rest\RestAPI;

echo $_GET["name"] ?>" method="post">
    <?php
    session_start();

    require("./util/Utils.php");
    require("./rest/RestAPI.php");

    if (!isset($_SESSION["username"])) {
        header("Location: login.php");
        exit;
    }

    if (!isset($_GET["name"])) {
        ?>
        <div class="error">
            <h4>There is no request.</h4>
        </div>
        <?php
        exit;
    }

    if (isset($_POST["submit"]) && isset($_POST["CSRFToken"])) {
        if ($_POST["CSRFToken"] != $_SESSION["CSRF"]) {
            Utils::logOut();
            exit;
        }

        if ($_POST["password"] == $_POST["password_repeat"]) {
            if (($data = RestAPI::getAccountData($name = $_GET["name"])) !== null) {
                if ($data["initialPassword"]) {
                    RestAPI::updatePassword($name, $_POST["password"]);
                    header("Location: index.php");
                } else {
                    ?>
                    <div class="error">
                        <h4>The password can't be changed for this account.</h4>
                    </div>
                    <?php
                }
            } else {
                ?>
                <div class="error">
                    <h4>The account doesn't exists.</h4>
                </div>
                <?php
            }
        } else {
            ?>
            <div class="error">
                <h4>The passwords are not the same!</h4>
            </div>
            <?php
        }
    } else {
        $_SESSION["CSRF"] = Utils::generateString(25);
    }
    ?>
    <h1 id="pw"><i class="fas fa-key">Set new Password</h1>
    <input type="hidden" name="CSRFToken" value="<?php echo $_SESSION["CSRF"]; ?>">
    <input type="password" name="password" placeholder="New Password" minlength="6" autocomplete="new-password" required><br>
    <input type="password" name="password_repeat" placeholder="Repeat new Password" minlength="6" autocomplete="new-password" required><br>
    <button type="submit" name="submit">Set new Password</button><br>
</form>
</body>
</html>
