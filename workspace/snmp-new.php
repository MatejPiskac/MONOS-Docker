<?php


$oids_list = [
    0 => [
        "oid" => "1.3.6.1.2.1.25.2.3.1.5",
        "name" => "diskSize",
        "type" => [3,4],
        "separator" => "INTEGER: "
    ],
    1 => [
        "oid" => "1.3.6.1.2.1.25.2.3.1.6",
        "name" => "diskUsed",
        "type" => [3,4],
        "separator" => "INTEGER: "
    ],
    2 => [
        "oid" => "1.3.6.1.2.1.25.2.3.1.2",
        "name" => "diskType",
        "type" => [3,4],
        "separator" => "OID: "
    ],
    3 => [
        "oid" => "1.3.6.1.2.1.25.3.2.1.3",
        "name" => "cpuName",
        "type" => [3,4],
        "separator" => ": "
    ],
    4 => [
        "oid" => "1.3.6.1.2.1.25.3.3.1.2",
        "name" => "cpuUsage",
        "type" => [3,4],
        "separator" => "INTEGER: "
    ],
    5 => [
        "oid" => "1.3.6.1.2.1.1.3",
        "name" => "systemTimeUp",
        "type" => [3,4],
        "separator" => ") "
    ],
    6 => [
        "oid" => "1.3.6.1.4.1.2021.4.6.0",
        "name" => "ramFree",
        "type" => [3,4],
        "separator" => "INTEGER: "
    ],
    7 => [
        "oid" => "1.3.6.1.4.1.2021.4.5.0",
        "name" => "ramTotal",
        "type" => [3,4],
        "separator" => "INTEGER: "
    ],
];


# Referented Functions

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



function getOidValue($name, $type, $connection) {
    global $oids_list;

    $returned_oid = false;

    foreach ($oids_list as $oid) {
        if ($oid["name"] == $name && in_array($type, $oid["type"])) {
            $oid_value = @snmpwalk($connection["ip"], $connection["community"], $oid["oid"]);
            $var_dump1 = var_dump($oid_value);
            $oid_formatted_arr = snmpFormat($oid_value, $oid["separator"]);
            if ($oid_formatted_arr !== false) {
                if (count($oid_formatted_arr) > 1) {
                    $returned_oid = $oid_formatted_arr;
                } else {
                    $returned_oid = $oid_formatted_arr[0];
                }
            }
            break;
        }
    }

    return $returned_oid;
}

function getSNMPData($hostIp, $deviceType, $community) {
    // Create SNMP session
    $session = new SNMP(SNMP::VERSION_2c, $hostIp, $community);

    // Check for type and redirect to return value

    $deviceTypeArray = [
        3 => 'workstation',
    ];

    foreach ($deviceTypeArray as $key => $value) {
        if ($deviceType == $key) {
            $return = $value($hostIp, $community);
        } else {
            $return = "NO SUCH DEVICE TYPE!!";
        }
    }

    return $return;
}

# Device OID Functions

function workstation($hostIp, $community) {
    $type = 3;
    $generative_content = '';

    $connection = [
        "ip" => $hostIp,
        "community" => $community
    ];

    $session = new SNMP(SNMP::VERSION_2c, $hostIp, $community);

    if ($session->getError()) {
        $generative_content = "Error: " . $session->getError();
    } else {
        # CPU
        $cpu_name_get = getOidValue("cpuName", $type, $connection);
        $cpu_load_get = getOidValue("cpuLoad", $type, $connection);

        $i = 1; # ||
        $htmlResolved = "";
        $htmlTemplate = "
        <div class='core-load'>
            <div>Core ||</div>
            <div class='percent-wrap'>
                <div class='percent'>{}% </div>
                <div class='percent-line-wrap'>
                    <div class='percent-line' style='width: calc({}%)'></div>
                </div>
            </div>
        </div>";
        foreach ($cpu_load_get as $key => $oid_value) {
            # Replacing all {} and || with actual values, append to $htmlResolved
            $currentHtmlResolved = strval(str_replace("{}", $oid_value, $htmlTemplate));
            $currentHtmlResolved = strval(str_replace("||", $i, $currentHtmlResolved));
            $htmlResolved .= $currentHtmlResolved;

            $i++;
        }
        $cpu_arr_load = $htmlResolved;
        $items_count = count($cpu_load_get);
        foreach ($cpu_load_get as $item) {
            $items_sum += (int) $item;
        }
        $cpu_load = $items_sum / $items_count;



        # RAM
        $ram_free_get = intval(getOidValue("ramFree", $type, $connection));
        $ram_free = round($ram_free_get / 1024 / 1024, 2);
        $ram_total_get = intval(getOidValue("ramTotal", $type, $connection));
        $ram_total = round($ram_total_get / 1024 / 1024, 2);
        $ram_used_perc = round(($ram_free / $ram_total) * 100, 2);

        # DISK
        $disk_used_get = intval(getOidValue("diskUsed", $type, $connection));
        $disk_used = round($disk_used_get / 1024 / 1024, 2);
        $disk_size_get = intval(getOidValue("diskSize", $type, $connection));
        $disk_size = round($disk_size_get / 1024 / 1024, 2);
        $disk_free = $disk_size - $disk_used;
        $disk_used_percentage = round(($disk_used_get / $disk_size_get) * 100, 2);
        $disk_free_percentage = 100 - $disk_used_percentage;

        # FOR CHART - Make variables global
        $GLOBALS["usedSpace"] = $disk_used_percentage;
        $GLOBALS["freeSpace"] = $disk_free_percentage;

        $generative_content = "
            <div class='content'>
                <div class='main-banner'>
                    <div id='donutchart'></div>
                </div>
                <div class='mon-list'>
                    <div>
                        <div class='title'>
                            CPU
                        </div>
                        <div class='roll'>
                            <div>
                                <div>{$cpu_name_get}</div>
                                <div class='drop-roll'>
                                    <div id='cpuLoad' class='title'>CPU Usage: {$cpu_load}%</div>
                                    <div id='coreLoads' class='group roll'>{$cpu_arr_load}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <div class='title'>
                            GPU
                        </div>
                        <div class='roll'>
                            <div>
                                <div>GPU Usage: 32%</div>
                                <div>Current Frequency: 2 GHz</div>
                                <div>Processing Units: 106</div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <div class='title'>
                            RAM
                        </div>
                        <div class='roll'>
                            <div>
                                <div>Usage: {$ram_used_perc}%</div>
                                <div>Total Size: {$ram_total} GB</div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <div class='title'>
                            DISK
                        </div>
                        <div class='roll'>
                            <div>
                                <div>Size: {$disk_size} GB</div>
                                <div>Free Space: {$disk_free} GB</div>
                                <div>Used Space: {$disk_used} GB</div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <div class='title'>
                            USERS
                        </div>
                        <div class='roll'>
                            <div>
                                <div>user</div>
                                <div>root</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        ";


    }


    $session->close();

    return $generative_content;
}

?>
