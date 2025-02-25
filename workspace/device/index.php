<?php

    //include "snmp.php";
    include "../main.php";

    if (isset($_GET['profile']) && isset($_GET['device'])) {
        $profile = $_GET['profile'];
        $device = $_GET['device'];
    } else {
        header('location: ../');
    }

    if (isset($_GET['chart-interface'])) {
        $_SESSION['chart-interface'] = $_GET['chart-interface'];
    }

    // Get the current path (without domain) and all GET parameters
    $currentPath = $_SERVER['REQUEST_URI'];

    // Alternatively, to only get the query parameters and append to the path:
    $queryString = $_SERVER['QUERY_STRING'];

    if ($queryString) {
        $currentPath = strtok($_SERVER['REQUEST_URI'], '?') . '?' . $queryString;
    } else {
        $currentPath = $_SERVER['REQUEST_URI'];
    }

    $_SESSION["path"] = $currentPath;

    $conditions = ["id" => $device];
    $ipv4 = findValueByConditions($devices, $conditions, "ip");
    $typeId = findValueByConditions($devices, $conditions, "type");

    $_SESSION["device-ip"] = $ipv4;
    $_SESSION["device-type"] = $typeId;

    $conditions = ["id" => $profile];
    $profileName = findValueByConditions($profiles, $conditions, "name");

    $result = getSNMPData($ipv4, $typeId, "public");
    
    //echo $result;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../style.css">
    <link rel="icon" type="image/x-icon" href="../favicon.ico">
    <title>Document</title>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <?php 
        if (ping($ipv4)) {
            echo '<script src="../ajax.js"></script>';
        }
    ?>
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

        function onLoad() {

            $(".mon-list > div > .title, .drop-roll > .title").click(function() {
                if ($(this).hasClass("up")) {
                    $(this).siblings(".roll").slideToggle(200, () => {
                        $(this).removeClass("up");
                    });
                } else {
                    $(this).siblings(".roll").slideToggle(200);
                    $(this).addClass("up");
                    $(this).siblings(".roll").css("display", "flex");
                }
            });

            $(".add").click(function() {
                $(this).children(".roll").fadeToggle(200);
            });

            $(".open-menu").click(toggleSidebar);
            $(".close-menu").click(toggleSidebar);

            $("body:has(div.generated #offline) #deviceState > div").removeClass("unknown").addClass("offline");
        }

        $(document).ready(onLoad);

    </script>
</head>
<body>
    <div class="navbar">
        <a href="../?profile=<?php echo $profile ?>">    
            <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="currentColor"><path d="M400-80 0-480l400-400 71 71-329 329 329 329-71 71Z"/></svg>
        </a>
        <div class="path">
            <a href="../"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="20px" fill="currentColor"><path d="M240-200h120v-240h240v240h120v-360L480-740 240-560v360Zm-80 80v-480l320-240 320 240v480H520v-240h-80v240H160Zm320-350Z"/></svg></a>
            <a href="../?profile=<?php echo $profile ?>"><?php echo $profileName ?></a>
        </div>
        <?php
            if ($USER == "admin") {
                echo "
                <a href=\"../admin\" class=\"admin-tools\">
                    ADMIN
                </a>";
            }
        ?>
    </div>
    <div class="all">
        <?php
            if ($USER == "admin") {
                echo '
                <a href="../edit/device/?device='.$device.'" class="edit-btn edit-dev">
                    <img src="../icons/edit.png" alt="edit-icon">
                </a>';
            }
        ?>
        <div id="deviceState">
            <div class="unknown"></div>
        </div>
        <div class="header">
            <h1 class="device"><?php
                $conditions = ["id" => $device];
                echo findValueByConditions($devices, $conditions, "name");
            ?></h1>
            <h3 class="ip"><?php
                $conditions = ["id" => $device];
                echo findValueByConditions($devices, $conditions, "ip");
            ?></h3>
        </div>
        <div class="generated">
            <?php echo $result; ?>
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