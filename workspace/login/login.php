<?php

    # MONOS --- MONitoring Open-source Solution
    # MONOS --- MONitoring Over Snmp
    # MONOS --- Mobile Optimal Network Open-source System
    # There is more than you could imagine ... MONOS

    session_start();

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="../style.css">
<title>MONOS</title>
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="../scripts/main.js"></script>
</head>
<body>
<div id="loading">
    <div class="logo-img"></div>
</div>
    <div class="all">
        <div class="header">
            <h1>MONOS</h1>
        </div>
        <div class="content">
            <div class="log">
                <div class="login-wrap">
                    <h2>Login</h2>
                    <form action="../action/validate.php?login" method="POST">
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
                        <input type="submit" name="submit" value="Log in">
                    </form>
                    <div class="error">
                        <?php
                            if (isset($_SESSION["error"])) {
                                echo $_SESSION["error"];
                            }
                        ?>
                    </div>
                </div>
                <div class="monos-log">
                    <h3>MONOS Beta v1.1</h3>
                    <img src="../icons/logo.svg" alt="monos-logo">
                </div>
            </div>
        </div>
    </div>
</body>
</html>