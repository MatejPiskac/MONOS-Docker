<?php

    # MONOS --- MONitoring Open-source Solution
    # MONOS --- MONitoring Over Snmp
    # MONOS --- Mobile Optimal Network Open-source System
    # There is more than you could imagine ... MONOS

    //include "snmp.php";
    include "main.php";

    if (isset($_GET['profile'])) {
        $profile = $_GET['profile'];
        $_SESSION['profile'] = $profile;
        $_SESSION['device-ip'] = "";

        $conditions = ["id" => $profile];
        $profileName = findValueByConditions($profiles, $conditions, "name");
    }

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="style.css">
<link rel="icon" type="image/x-icon" href="favicon.ico">
<title>MONOS</title>
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="ajax.js"></script>
<script type="text/javascript">

    function hideLoad() {
        $("#loading").animate({ top: '-50%', opacity: 0 }, 1000, function() {
            $(this).fadeOut(1000);
        });
    }

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


    function onLoad() {
        $(".sidebar-content > div").click(function() {
            if ($(this).children(".title.up").length > 0) {
                $(this).children(".roll").slideToggle(200, () => {
                    $(this).children(".title").removeClass("up");
                });
            } else {
                $(this).children(".roll").slideToggle(200);
                $(this).children(".title").addClass("up");
                $(this).children(".roll").css("display", "flex");
            }
        });

        $(".add").click(function() {
            $(this).children(".roll").fadeToggle(200);
        });

        $(".open-menu").click(toggleSidebar);
        $(".close-menu").click(toggleSidebar);

        hideLoad();
    }

    $(document).ready(onLoad);
</script>
</head>
<body>
<div id="loading">
    <div class="logo-img"></div>
</div>
    <?php
        if (isset($profile)) {
            if ($USER == "admin") {
                $adminTools = "
                <a href=\"admin\" class=\"admin-tools\">
                    ADMIN
                </a>";
            } else {
                $adminTools = "";
            }
            echo "
            <div class=\"navbar\">
                <a href=\"../workspace\">
                    <svg xmlns=\"http://www.w3.org/2000/svg\" height=\"24px\" viewBox=\"0 -960 960 960\" width=\"24px\" fill=\"currentColor\"><path d=\"M400-80 0-480l400-400 71 71-329 329 329 329-71 71Z\"/></svg>
                </a>
                <div class=\"path\">
                    <a href=\"../workspace\"><svg xmlns=\"http://www.w3.org/2000/svg\" height=\"24px\" viewBox=\"0 -960 960 960\" width=\"20px\" fill=\"currentColor\"><path d=\"M240-200h120v-240h240v240h120v-360L480-740 240-560v360Zm-80 80v-480l320-240 320 240v480H520v-240h-80v240H160Zm320-350Z\"/></svg></a>
                    <a href='?profile={$profile}'>{$profileName}</a>
                </div>
                    ".$adminTools."
            </div>";
        }
        
    ?>
    <div class="all">
        <div class="header">
            <h1>MONOS</h1>
        </div>
        <div class="content">
            <?php echo isset($_GET["profile"]) ? monitorContent($profile) : profileContent() ?>
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
                                    <a href="edit/profile">
                                        <div class="add-img">Add profile</div>
                                    </a>
                                    <a href="edit/device">
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
                                    <a href=\"admin\">
                                        <div class=\"users\">Users</div>
                                    </a>";

                                    echo $adminTools;
                                }
                            ?>
                            <a href="account/">
                                <div class="person">Profile</div>
                            </a>
                        </div>
                    </div>
                </div>
                <img src="icons/close-menu.png" class="close-menu" alt="close-menu">
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
                        <img src="icons/plus.png" alt="">
                        <div class="roll pop-add">
                            <div>
                                <a href="edit/profile/">
                                    <img src="icons/plus.png" alt="">
                                    <div class="add-img">Add profile</div>
                                </a>
                                <a href="edit/device/">
                                    <img src="icons/plus.png" alt="">
                                    <div class="add-img">Add device</div>
                                </a>
                            </div>
                        </div>
                    </div>';
                } else {
                    echo '
                    <a href="account">
                        <img src="icons/account.svg" alt="">
                    </a>';
                }
            ?>
            <a href="../workspace">
                <img src="icons/home.png" alt="">
            </a>
            <div class="small open-menu">
                <img src="icons/menu.png" alt="">
            </div>
        </div>
    </div>
    <div class="darken"></div>
</body>
</html>