<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="utf-8">
    <title>Accounts</title>
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
        <?php

        if (isset($_GET["delete"]) && isset($_GET["name"])) {
            if (!isset($_GET["confirmed"])) {
                if (!empty($_GET["name"])) {
                    $name = htmlspecialchars($_GET["name"], ENT_QUOTES, 'UTF-8');
                    ?>
                    <script>
                        $.sweetModal.defaultSettings.confirm.yes.label = "Delete";
                        $.sweetModal.defaultSettings.confirm.cancel.label = "Cancel";
                        $.sweetModal.confirm('<?php echo "Are you sure you want to delete the account from " . $name . "?" ?>', function() {
                            var xhttp = new XMLHttpRequest();
                            xhttp.open("GET", "accounts.php?delete&name=<?php echo $name ?>&confirmed");
                            xhttp.send();
                            $.sweetModal({
                                content: 'The account has been deleted successfully.',
                                icon: $.sweetModal.ICON_SUCCESS,
                                onClose: function() {
                                    window.location = "accounts.php";
                                }
                            });
                        });
                    </script>
                    <?php
                } else {
                    header("Location: accounts.php");
                }
            } else {
                RestAPI::deleteAccount($_GET["name"]);
            }
        }

        if (isset($_POST["submit"]) && isset($_SESSION["CSRF"])) {
            if (!empty($username = $_POST["username"])) {
                if ($_POST["CSRFToken"] != $_SESSION["CSRF"]) {
                    Utils::logOut();
                } else {
                    if (RestAPI::getAccountData($username) === null) {
                        $initialPassword = RestAPI::createAccount($username, $_POST["role"]);
                        if ($initialPassword === null) {
                            Utils::showModal("ERROR", "Action failed.", "Something went wrong...");
                            exit;
                        }

                        Utils::showModalRedirect("SUCCESS", "Success", "The account has been created successfully and the initial password is: <strong>" . $initialPassword . "</strong>", "accounts.php");
                        exit;
                    } else {
                        Utils::showModal("ERROR", "Action failed.", "The account already exists.");
                    }
                }
            } else {
                Utils::showModal("ERROR", "Action failed.", "The request is invalid.");
            }
        } else {
            $_SESSION["CSRF"] = Utils::generateString(25);
        }

        ?>
        <div class="flex-container animated fadeIn">
            <div class="flex item-1">
                <h1>Accounts</h1>
                <table>
                    <tr>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Fully Initialized</th>
                    </tr>
                    <?php
                    foreach (RestAPI::getAccounts() as $account) {
                        echo "<tr>";
                        echo '<td><strong>'.htmlspecialchars($account["username"]).'</strong></td>';
                        echo "<td>" . ucfirst($account["role"]) . "</td>";
                        echo "<td>" . ($account["initialPassword"] ? "No" : "Yes") . "</td>";
                        echo '<td><a href="editaccount.php?name=' . $account["username"] . '""><i class="material-icons">edit</i></a>';

                        echo "</tr>";
                    }
                    ?>
                </table>
            </div>
            <div class="flex item-2 sidebox">
                <h1>Create Account</h1>
                <form action="accounts.php" method="post">
                    <input type="hidden" name="CSRFToken" value="<?php echo $_SESSION["CSRF"]; ?>">
                    <label>
                        <input type="text" name="username" placeholder="Username" required>
                    </label>
                    <br>
                    <label>
                        <select name="role">
                            <option value="admin">Admin</option>
                            <option value="default">Default</option>
                        </select>
                    </label>
                    <button type="submit" name="submit">Create</button>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>