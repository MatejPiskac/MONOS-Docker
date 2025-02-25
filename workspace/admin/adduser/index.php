<?php

    # MONOS --- MONitoring Open-source Solution
    # MONOS --- MONitoring Over Snmp
    # MONOS --- Mobile Optimal Network Open-source System
    # There is more than you could imagine ... MONOS

    include "../../main.php";
    
    if ($USER != "admin") {
        header("location: /MONOS/workspace/login/login.php");
    }

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="../../style.css">
<link rel="icon" type="image/x-icon" href="../../favicon.ico">
<title>MONOS</title>
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="../../scripts/main.js"></script>
</head>
<body>
<div id="loading">
    <div class="logo-img"></div>
</div>
    <div class="navbar">
        <a href="../">
            <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="currentColor"><path d="M400-80 0-480l400-400 71 71-329 329 329 329-71 71Z"/></svg>
        </a>
        <?php
            if ($USER == "admin") {
                echo "
                <a href=\"../\" class=\"admin-tools\">
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
            <div class="log">
                <div class="login-wrap">
                    <h2>Create user</h2>
                    <form action="../../action/validate.php?user" method="POST">
                        <div class="input-fly">
                            <div>
                                <input type="text" name="username" id="username">
                                <label for="username">Username</label>
                            </div>
                        </div>
                        <div class="input-fly">
                            <div>
                                <input type="password" name="password" id="password">
                                <label for="password">Password</label>
                            </div>
                        </div>
                        <div class="input-fly">
                            <div>
                                <input type="password" name="password_confirm" id="password_confirm">
                                <label for="password_confirm">Confirm password</label>
                            </div>
                        </div>
                        <input type="submit" name="submit" value="Create">
                    </form>
                    <div class="error">
                        <?php
                            if (isset($_SESSION["error"])) {
                                echo $_SESSION["error"];
                            }
                        ?>
                    </div>
                </div>
            </div>
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
                                    <a href="../../edit/profile">
                                        <div class="add-img">Add profile</div>
                                    </a>
                                    <a href="../../edit/device">
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
                        <img src="../../icons/plus.png" alt="">
                        <div class="roll pop-add">
                            <div>
                                <a href="../../edit/profile/">
                                    <img src="../../icons/plus.png" alt="">
                                    <div class="add-img">Add profile</div>
                                </a>
                                <a href="../../edit/device/">
                                    <img src="../../icons/plus.png" alt="">
                                    <div class="add-img">Add device</div>
                                </a>
                            </div>
                        </div>
                    </div>';
                } else {
                    echo '
                    <a href="../../account">
                        <img src="../../icons/account.svg" alt="">
                    </a>';
                }
            ?>
            <a href="../../">
                <img src="../../icons/home.png" alt="">
            </a>
            <div class="small open-menu">
                <img src="../../icons/menu.png" alt="">
            </div>
        </div>
    </div>
</body>
</html>