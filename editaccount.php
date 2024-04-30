<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8">
    <title>Edit Account</title>
    <link rel="stylesheet" href="css/master.css">
    <link rel="stylesheet" href="css/animate.css">
    <link rel="stylesheet" href="css/jquery.sweet-modal.min.css" />
    <link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <script src="https://kit.fontawesome.com/953731a208.js" crossorigin="anonymous"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.0/jquery.min.js"></script>
    <script src="js/jquery.sweet-modal.min.js"></script>
    <meta name="viewport" content="width=device-width,
            initial-scale=1.0,
            minimum-scale=1.0">
    <link rel="icon" type="image/x-icon" href="css/favicon.ico">
</head>

<?php

use rest\RestAPI;
use util\Utils;

session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

require("./util/Utils.php");
require("./rest/RestAPI.php");

if (RestAPI::getAccountData($_SESSION['username'])["initialPassword"]) {
    header("Location: resetpassword.php?name=" . $_SESSION['username']);
    exit;
}

Utils::validateSession();
?>

<body>
<?php

if (!RestAPI::isAdmin($_SESSION['username'])) {
    Utils::showModalRedirect("ERROR", "Action failed.", "You are not able to view this page.", "index.php");
    exit;
}

?>
<div class="container">
    <div class="sidebar">
        <ul>
            <?php
            Utils::injectSideBar();
            ?>
        </ul>
    </div>
    <div class="header">
        <?php
        Utils::injectHeader();
        ?>
    </div>
    <div class="content">
        <div class="mobilenavbar">
            <nav>
                <ul class="navbar animated bounceInDown">
                    <?php
                    Utils::injectSideBar();
                    ?>
                </ul>
            </nav>
        </div>
        <script type="text/javascript">
            $(document).ready(function() {
                $('.menu').click(function() {
                    $('ul').toggleClass("navactive");
                })
            })
        </script>
        <div class="flex-container animated fadeIn">
            <div class="flex item-1 sidebox">
                <?php
                if (!isset($_GET["name"])) {
                    Utils::showModalRedirect("ERROR", "Action failed.", "No request was sent.", "accounts.php");
                    exit;
                }

                if (isset($_POST["submit"]) && isset($_SESSION["CSRF"])) {
                    if ($_POST["CSRFToken"] != $_SESSION["CSRF"]) {
                        Utils::logOut();
                    } else {
                        $name = $_GET["name"];

                        if (trim($_POST["password"]) !== "") RestAPI::updatePassword($name, $_POST["password"]);
                        RestAPI::updateRole($name, $_POST["role"]);

                        Utils::showModalRedirect("SUCCESS", "Success!", "The account has been updated!", "accounts.php");
                        exit;
                    }
                } else {
                    $_SESSION["CSRF"] = Utils::generateString(25);
                }
                ?>
                <h1><?php echo "Edit Account of " . $_GET["name"]; ?></h1>
                <form action="editaccount.php?name=<?php echo $_GET["name"]; ?>" method="post">
                    <input type="hidden" name="CSRFToken" value="<?php echo $_SESSION["CSRF"]; ?>">
                    <p>New Password</p>
                    <label>
                        <input type="password" name="password" placeholder="New Password">
                    </label>
                    <br>
                    <p>Role</p>
                    <label>
                        <select name="role">
                            <?php
                            if (RestAPI::isAdmin($_GET["name"])) {
                                ?>
                                <option value="admin">Admin</option>
                                <option value="default">Default</option>
                                <?php
                            } else {
                                ?>
                                <option value="default">Default</option>
                                <option value="admin">Admin</option>
                                <?php
                            }
                            ?>
                        </select>
                    </label>
                    <button type="submit" name="submit">Save</button>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>