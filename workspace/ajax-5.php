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

    if (isset($_SESSION['device-type']) && $_SESSION['device-ip']) {
        $deviceType = $_SESSION['device-type'];
        $deviceIp = $_SESSION['device-ip'];
    
        if (!empty($deviceType) && !empty($deviceIp)) {
            $data = getRealTimeArray($deviceType, $deviceIp);
        } else {
            $error = "Error: Missing either IP or Device Type";
            $data = false;
        }
    } else {
        $error = "Error: Missing either IP or Device Type session";
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