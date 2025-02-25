<?php

session_start();

$config = include __DIR__.'/../db_config.php';

$servername = $config["db_host"];
$username = $config["db_user"];
$password = $config["db_pass"];
$dbname = $config["db_name"];

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

# -- ADDON FUNCTIONS ------

function snmpFormat($snmp_arr, $separator) {
    $snmp_formatted_arr = [];

    if ($snmp_arr !== false || !empty($snmp_arr)) {
        foreach ($snmp_arr as $key => $value) {
            $value = preg_replace('/^.*: :/', '', $value);
            $value = explode($separator, $value)[1];
            $snmp_formatted_arr[] = $value;
        }
    }

    return $snmp_formatted_arr;
}


# -- Network ----

function ping($host, $timeout = 1) {
    $output = [];
    $status = null;

    // Adjust the command based on the operating system
    if (stristr(PHP_OS, 'WIN')) { 
        // Windows
        $cmd = "ping -n 1 -w " . ($timeout * 1000) . " $host";
    } else {
        // Linux / macOS
        $cmd = "ping -c 1 -W $timeout $host";
    }

    // Execute the command
    exec($cmd, $output, $status);

    // Return true if the ping was successful
    return $status === 0 ? true : false;
}


function telnet($host, $ports = [80, 443, 22], $timeout = 1) {
    // Attempt to open a socket connection
    foreach ($ports as $port) {
        $connection = @fsockopen($host, $port, $errno, $errstr, $timeout);
        if ($connection) {
            fclose($connection); // Close the connection
            return true; // Device is reachable on this port
        }
    }
    return false; // Device is not reachable on any of the specified port
}

function isDeviceAlive($host, $ports = [80, 443, 22], $timeout = 1) {
    if (ping($host)) {
        return true;
    } elseif (telnet($host)) {
        return true;
    } else {
        return false;
    }
}

function getStateHtml($ip, $text = "", $timeout = 1) {
    $state = isDeviceAlive($ip);
    $class = $state ? "online" : "offline";
    if ($text) {
        $text = $class;
    }

    $html = '<div class="'.$class.'">'.$text.'</div>';

    return $html;
}


# -- GET REAL STATE --------

function getRealStateArray($profileId = false, $deviceIP = false, $text = false) {
    global $conn;
    $data = [];

    if ($deviceIP) {
        $stateHtml = getStateHtml($deviceIP, $text);
        if ($stateHtml) {
            $elementId = "deviceState";
            $data[$elementId] = $stateHtml;
        }
    } elseif ($profileId) {

        /*
        $devices = [];
        if (isset($GLOBALS['profileReleations']) && is_array($GLOBALS['profileReleations'])) {
            foreach ($GLOBALS['profileReleations'] as $relation) {
                // Check if the current relation matches the desired profileId
                if (isset($relation['profileId']) && $relation['profileId'] == $profileId) {
                    // Add the deviceId to the $devices array
                    if (isset($relation['deviceId'])) {
                        $devices[] = $relation['deviceId'];
                    }
                }
            }
        }*/
        
        $devices = "SELECT deviceId FROM profileReleations WHERE profileId = ".$profileId;
        $devices = $conn->query($devices);
        $devices = $devices->fetch_all(MYSQLI_ASSOC);

        foreach ($devices as $key => $value) {

            $deviceIP = "SELECT ip FROM devices WHERE id = ".$value["deviceId"];
            $deviceIP = $conn->query($deviceIP);
            $deviceIP = $deviceIP->fetch_all(MYSQLI_ASSOC)[0]["ip"];



            $stateHtml = getStateHtml($deviceIP, $text);
            if ($stateHtml) {
                $elementId = "deviceState-".$value["deviceId"];
                $data[$elementId] = $stateHtml;
            }
        }
    }

    return $data;
    
}


# -- MAIN FUNCTION ---------

/*
INFO:

{} === replaced with the oid value / returned call function value
|| === replaced with integer ($i) returned from the for cycle

"id" => "element-id" => "one-printed-html"
"id" => "element-id" => ["cycled-html"]

"element-id" ========= where the OID value is printed
"one-printed-html" === printed just once
["cycled-html"] ====== printed in for cycle and transfered to string which is printed to the element-id

*/

function getRealTimeArray($type, $ip) {
    $community = "public";

    $real_time_oids = [
        "1.3.6.1.2.1.25.3.3.1.2" => [ # cpu Usage
            "type" => [3, 4],
            "id" => [
                "cpuLoad" => "CPU Usage: {}%",
                "coreLoads" => ["
                    <div class='core-load'>
                        <div>Core ||</div>
                        <div class='percent-wrap'>
                            <div class='percent'>{}% </div>
                            <div class='percent-line-wrap'>
                                <div class='percent-line' style='width: calc({}%)'></div>
                            </div>
                        </div>
                    </div>"]
            ],
            "separator" => "INTEGER: "
        ],
        "1.3.6.1.4.1.2021.4.5.0" => [ # total RAM
            "type" => [3, 4],
            "id" => [
                "totalRam" => "{}"
            ],
            "separator" => "INTEGER: "
        ],
        "1.3.6.1.4.1.2021.4.6.0" => [ # free RAM
            "type" => [3, 4],
            "id" => [
                "freeRam" => "{}"
            ],
            "separator" => "INTEGER: "
        ],
        "1.3.6.1.2.1.1.3" => [ # system Up
            "type" => [3, 4],
            "id" => [
                "sysUp" => "{}"
            ],
            "separator" => ") "
        ],
    ];

    $snmpData = [];

    foreach ($real_time_oids as $key => $value) {
        if (is_array($value["type"]) || $type == $value["type"]) {
            if (in_array($type, $value["type"])) {
                $oid_req = @snmpwalk($ip, $community, $key);
                $oid_arr = snmpFormat($oid_req, $value["separator"]);

                foreach ($value["id"] as $elemetId => $htmlTemplate) {
                    $htmlResolved = "";

                    # []
                    if (is_array($htmlTemplate)) {
                        $i = 1; # ||
                        $htmlTemplate = $htmlTemplate[0];
                        foreach ($oid_arr as $key => $oid_value) {
                            # Replacing all {} and || with actual values, append to $htmlResolved
                            $currentHtmlResolved = strval(str_replace("{}", $oid_value, $htmlTemplate));
                            $currentHtmlResolved = strval(str_replace("||", $i, $currentHtmlResolved));
                            $htmlResolved .= $currentHtmlResolved;

                            $i++;
                        }
                    } else {
                        # Specified
                        if ($elemetId == "cpuLoad" ) {
                            if (count($oid_arr) > 1) {
                                # Get avarage - must be int (we are not checking if it is int, it should be)
                                $items_count = count($oid_arr);
                                foreach ($oid_arr as $item) {
                                    $items_sum += (int) $item;
                                }
                                $oid_value = $items_sum / $items_count;
                                $htmlResolved = str_replace("{}", $oid_value, $htmlTemplate);
                            }
                        } elseif ($elemetId == "totalRam" ) {
                            $oid_value = $oid_arr[0];

                            $ram_gb = $oid_value / 1048576;
                            $ram_gb_str = round($ram_gb, 3)." GB";
                            $GLOBALS[$elemetId] = $ram_gb;
                            $htmlResolved = str_replace("{}", $ram_gb_str, $htmlTemplate);
                        } elseif ($elemetId == "freeRam" ) {
                            $oid_value = $oid_arr[0];

                            $ram_gb = $oid_value / 1048576;
                            $ram_gb_str = round($ram_gb, 3)." GB";
                            $used_ram_perc = round($ram_gb / $GLOBALS["totalRam"] * 100, 0)."%";
                            $htmlResolved = str_replace("{}", $used_ram_perc, $htmlTemplate);
                        } elseif ($elemetId == "sysUp") {
                            $oid_value = $oid_arr[0];

                            $sys_up = preg_replace_callback(
                                '/(\d+) days?, (\d+):(\d+):(\d+).00/',
                                function ($matches) {
                                    $days = $matches[1];
                                    $hours = $matches[2];
                                    $minutes = $matches[3];
                                    return "$days days $hours h $minutes min";
                                },
                                $oid_value
                            );

                            $htmlResolved = str_replace("{}", $sys_up, $htmlTemplate);
                        }
                        # General for Single Values
                        else {
                            if (count($oid_arr) == 1) {
                                $oid_value = $oid_arr[0];
                                $htmlResolved = str_replace("{}", $oid_value, $htmlTemplate);
                            }
                        }
                        
                    }

                    $snmpData[$elemetId] = $htmlResolved;
                }
            }
        } # else -- not the type
    }

    return $snmpData;
}

$snmpData = [
    'systemDescription' => 'Linux Test System', 
    'uptime' => '123456 seconds', 
    'contact' => 'admin@example.com'
];

?>