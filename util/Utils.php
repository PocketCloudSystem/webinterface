<?php

namespace util;

use rest\RestAPI;

class Utils {

    private static ?array $configContent = null;

    private static function loadConfigContent(): void {
        if (self::$configContent === null) self::$configContent = json_decode(file_get_contents(__DIR__ . "/../resources/config.json"), true);
    }

    public static function cloudHttpHost(): string {
        self::loadConfigContent();
        return self::$configContent["cloud-http-settings"]["host"];
    }

    public static function cloudHttpPort(): string {
        self::loadConfigContent();
        return self::$configContent["cloud-http-settings"]["port"];
    }

    public static function cloudHttpAuthKey(): string {
        self::loadConfigContent();
        return self::$configContent["cloud-http-settings"]["auth-key"];
    }

    public static function injectSideBar(): void {
        $currentFile = basename($_SERVER["REQUEST_URI"]);
        $output = '<li' . ($currentFile == "index.php" ? ' class=active' : '') . '><a href="index.php"><i class="fa-solid fa-home"></i> Overview</a></li>
            <li' . ($currentFile == "players.php" ? ' class=active' : '') . '><a href="players.php"><i class="fa-solid fa-search"></i> Players</a></li>
            <li' . ($currentFile == "servers.php" ? ' class=active' : '') . '><a href="servers.php"><i class="fa-solid fa-server"></i> Servers</a></li>
            <li' . ($currentFile == "templates.php" ? ' class=active' : '') . '><a href="templates.php"><i class="fa-solid fa-folder"></i> Templates</a></li>
            <li' . ($currentFile == "modules.php" ? ' class=active' : '') . '><a href="modules.php"><i class="fa-solid fa-brain"></i> Modules</a></li>
        ';

        if (RestAPI::isAdmin($_SESSION["username"])) {
            $output .= '<li' . ($currentFile == "accounts.php" ? ' class=active' : '') . '><a href="accounts.php"><i class="fa-solid fa-users"></i> Accounts</a></li>';
        }

        echo $output;
    }

    public static function injectHeader(): void {
        $output = '<i class="fa-solid fa-bars fa-2x menu mobileicon"></i>';
        $output .= '<a href="logout.php"><i class="fa-solid fa-right-from-bracket fa-2x headericon"></i></a>';
        $output .= '<a href="https://discord.gg/3HbPEpaE3T"><i class="fa-brands fa-discord fa-2x headericon"></i></a>';
        $output .= '<a href="https://github.com/PocketCloudSystem"><i class="fa-brands fa-github fa-2x headericon"></i></a>';

        echo $output;
    }

    public static function showModal($type, $title, $message): void {
        ?>
        <script type="text/javascript">
            $.sweetModal({
                title: "<?php echo $title; ?>",
                content: "<?php echo $message; ?>",
                theme: $.sweetModal.THEME_DARK,
                icon: $.sweetModal.ICON_<?php echo $type; ?>
            });
        </script>
        <?php
    }

    public static function showModalRedirect($type, $title, $message, $location): void {
        ?>
        <script type="text/javascript">
            $.sweetModal({
                title: "<?php echo $title; ?>",
                content: "<?php echo $message; ?>",
                theme: $.sweetModal.THEME_DARK,
                icon: $.sweetModal.ICON_<?php echo $type; ?>,
                onClose: function() {
                    window.location = "<?php echo $location; ?>";
                }
            });
        </script>
        <?php
    }

    public static function validateSession(): void {
        $username = $_SESSION["username"];
        if (($data = RestAPI::getAccountData($username)) === null) {
            session_destroy();
            header("Location: login.php");
        } else {
            if ($data["initialPassword"]) {
                header("Location: resetpassword.php?name=" . $_SESSION['username']);
                exit;
            }
        }
    }

    public static function logOut(): void {
        session_start();
        session_destroy();
        header("Location: index.php");
    }

    public static function checkCloudStatus(): bool {
        $host = self::cloudHttpHost();
        $port = self::cloudHttpPort();

        $socket = @fsockopen($host, $port, $errno, $errstr, 2);

        if ($socket) {
            fclose($socket);
            return true;
        }

        return false;
    }

    public static function generateString(int $length = 5): string {
        $characters = "1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $string = "";
        for ($i = 0; $i < $length; $i++) $string .= $characters[mt_rand(0, (strlen($characters) - 1))];
        return $string;
    }

    public static function getServerLogs(string $server, int $type = 0): string
    {
        $logs = RestAPI::getServerLogs($server, $type);

        if (empty($logs)) {
            return "No logs found or an error occurred.";
        } else {
            return implode("\n", $logs);
        }
    }
}