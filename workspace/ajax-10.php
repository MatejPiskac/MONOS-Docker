<?php
session_start();
include "real-time-snmp.php";

header('Content-Type: application/json');

if (isset($_SESSION['user']) && !empty($_SESSION['user'])) {
    $USER = $_SESSION['user'];
} else {
    header("location: /MONOS/workspace/login/login.php");
}

# -- Functions ----

function device() {
    $error = "";

    if (isset($_SESSION['device-type']) && isset($_SESSION['device-ip']) && !empty($_SESSION['device-type']) && !empty($_SESSION['device-ip'])) {
        $deviceIp = $_SESSION['device-ip'];

        $data = getRealStateArray(false, $deviceIp);

    } elseif (isset($_SESSION['profile']) && !empty($_SESSION['profile'])) {
        $profileId = $_SESSION['profile'];

        $data = getRealStateArray($profileId);
        
    } else {
        $data = false;
    }

    return ["data" => $data, "error" => $error];
}


# -- Calling Function ----

if (isset($_GET['func'])) {
    $func = $_GET['func'];
    if (function_exists($func)) {
        $array = $func();
        $data = $array["data"];
        $error = $array["error"];
    } else {
        $data = false;
    }
} else {
    $data = false;
}


if ($data !== false) {
    echo json_encode(['success' => true, 'data' => $data]);
} else {
    echo json_encode(['success' => false, 'message' => $error]);
}