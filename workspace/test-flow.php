<?php

session_start();

function snmpFormat($snmp_arr, $separator) {
    $snmp_formatted_arr = [];

    if ($snmp_arr !== false) {
        foreach ($snmp_arr as $key => $value) {
            $value = preg_replace('/^.*: :/', '', $value);
            $value = explode($separator, $value)[1];
            $snmp_formatted_arr[] = $value;
        }
    }

    return $snmp_formatted_arr;
}

if (isset($_SESSION["chart-interface"])) {
    $interface = $_SESSION["chart-interface"];
} else {
    $interface = "1";
}

if (isset($_GET["host"]) && isset($_GET["community"])) {
    $host = $_GET["host"];
    $community = $_GET["community"];
}


$inOid = "IF-MIB::ifInOctets.$interface";
$outOid = "IF-MIB::ifOutOctets.$interface";

// Function to get current SNMP data
function getSnmpData($oid) {
    global $host, $community;

    $oid_req = @snmpwalk($host, $community, $oid);
    $oid_res = snmpFormat($oid_req, "Counter32: ");

    return isset($oid_res[0]) ? (int)$oid_res[0] : 0;
}

// Function to calculate rates
function calculateRates() {

    // Fetch the current values
    $currentIn = getSnmpData("IF-MIB::ifInOctets.1");
    $currentOut = getSnmpData("IF-MIB::ifOutOctets.1");
    $timestamp = time();

    // Get the previous data from the session
    $previousData = isset($_SESSION['previousData']) ? $_SESSION['previousData'] : null;

    // Calculate rates
    if ($previousData) {
        $timeDiff = $timestamp - $previousData['timestamp'];
        $downloadRate = ($currentIn - $previousData['in']) / $timeDiff; // Bytes/sec
        $uploadRate = ($currentOut - $previousData['out']) / $timeDiff; // Bytes/sec
    } else {
        $downloadRate = $uploadRate = 0;
    }

    // Store the current data in the session
    $_SESSION['previousData'] = [
        'timestamp' => $timestamp,
        'in' => $currentIn,
        'out' => $currentOut
    ];

    // Return data as JSON
    return [
        'time' => date("H:i:s", $timestamp),
        'downloadRate' => round($downloadRate, 2),
        'uploadRate' => round($uploadRate, 2)
    ];
}

// Output the calculated data as JSON
header('Content-Type: application/json');
echo json_encode(calculateRates());
?>
