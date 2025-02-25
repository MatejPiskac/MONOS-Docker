<?php

    # MONOS --- MONitoring Open-source Solution
    # MONOS --- MONitoring Over Snmp
    # MONOS --- Mobile Optimal Network Open-source System
    # There is more than you could imagine ... MONOS

    //include "snmp.php";
    include "../main.php";

    if ($USER != "admin") {
        header("location: /MONOS/workspace/login/login.php");
    }

    if (isset($_GET['user'])) {
        $content = manageUsers($_GET['user']);
    } else {
        $content = manageUsers();
    }

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="../style.css">
<link rel="icon" type="image/x-icon" href="../favicon.ico">
<title>MONOS</title>
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script type="text/javascript">

    function toggleSidebar() {
        if ($(".sidebar-wrap").css("right").includes("-")) {
            $(".sidebar-wrap").animate({ right: '0%' }, 500);

            $(".close-menu").addClass("show");

            $(".darken").show(0);
            $(".darken").animate({opacity: 0.5}, 500);
        } else {
            $(".sidebar-wrap").animate({ right: '-100%' }, 500);

            $(".close-menu").removeClass("show");

            $(".darken").animate({opacity: 0}, 500).after().hide(0);
        }
    }


    function loaded() {

        $(".add").click(function() {
            $(this).children(".roll").fadeToggle(200);
        });

        $(".open-menu").click(toggleSidebar);
        $(".close-menu").click(toggleSidebar);
    }

</script>
<script src="../scripts/main.js"></script>
</head>
<body>
    <div id="loading">
        <div class="logo-img"></div>
    </div>
    <div class="navbar">
        <a href="../">
            <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="currentColor"><path d="M400-80 0-480l400-400 71 71-329 329 329 329-71 71Z"/></svg>
        </a>
        <div class="path">
            <a href="../"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="20px" fill="currentColor"><path d="M240-200h120v-240h240v240h120v-360L480-740 240-560v360Zm-80 80v-480l320-240 320 240v480H520v-240h-80v240H160Zm320-350Z"/></svg></a>
            <?php
                if (isset($_GET['user'])) {
                    $user = "SELECT * FROM users WHERE id = {$_GET['user']}";
                    $user = $conn->query($user);
                    $user = $user->fetch_all(MYSQLI_ASSOC)[0];

                    $username = $user["username"];

                    echo "
                        <a href='?user={$_GET['user']}'>{$username}</a>
                    ";
                }
            ?>
        </div>
        <?php
            if ($USER == "admin") {
                echo "
                <a href=\"#\" class=\"admin-tools\">
                    ADMIN
                </a>";
            }
        ?>
    </div>
    <div class="all">
        <div class="header">
            <h1>MONOS</h1>
        </div>
        <div class="content">
            <?php echo $content; ?>
        </div>
        <div class="sidebar-wrap">
            <div class="sidebar">
                <div class="sidebar-content">
                    <?php
                        if ($USER == "admin") {
                            echo '
                            <div>
                                <div class="title up">Add</div>
                                <div class="roll">
                                    <a href="../edit/profile">
                                        <div class="add-img">Add profile</div>
                                    </a>
                                    <a href="../edit/device">
                                        <div class="add-img">Add device</div>
                                    </a>
                                </div>
                            </div>';
                        }
                    ?>
                    <div>
                        <div class="title up">Manage</div>
                        <div class="roll">
                            <?php
                                if ($USER == "admin") {
                                    $adminTools = "
                                    <a href=\"../admin\">
                                        <div class=\"users\">Users</div>
                                    </a>";

                                    echo $adminTools;
                                }
                            ?>
                            <a href="../account/">
                                <div class="person">Profile</div>
                            </a>
                        </div>
                    </div>
                </div>
                <img src="../icons/close-menu.png" class="close-menu" alt="close-menu">
                <div class="btm-bar">
                    <a href="?logout" class="log-out">Log out</a>
                </div>
            </div>
        </div>
        <div class="footer">
            <?php
                if ($USER == "admin") {
                    echo '
                    <div class="small add">
                        <img src="../icons/plus.png" alt="">
                        <div class="roll pop-add">
                            <div>
                                <a href="../edit/profile/">
                                    <img src="../icons/plus.png" alt="">
                                    <div class="add-img">Add profile</div>
                                </a>
                                <a href="../edit/device/">
                                    <img src="../icons/plus.png" alt="">
                                    <div class="add-img">Add device</div>
                                </a>
                            </div>
                        </div>
                    </div>';
                } else {
                    echo '
                    <a href="../account">
                        <img src="../icons/account.svg" alt="">
                    </a>';
                }
            ?>
            <a href="../">
                <img src="../icons/home.png" alt="">
            </a>
            <div class="small open-menu">
                <img src="../icons/menu.png" alt="">
            </div>
        </div>
    </div>
    <div class="darken"></div>
</body>
</html>