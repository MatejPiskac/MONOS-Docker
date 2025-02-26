<?php

include __DIR__."/../db.php";

$_SESSION["error"] = "";

function validate($input, $empty = false) {
    $error = [];

    //overovani jestli jsou vsechna pole vyplnena

    foreach ($input as $key => $value) {
        if (is_array($value)) {
            foreach ($value as $key => $value2) {
                if (isset($_POST[$value2])) {
                    if ($empty) {
                        if (!empty($_POST[$value2])) {
                            $$value2 = $_POST[$value2];
                        } else {
                            $error[] = $value2;
                        }
                    } else {
                        $$value2 = $_POST[$value2];
                    }

                    if (isset($$value2) && !isset($_POST["password"])) {
                        $_SESSION[$value2] = $$value2;
                    }
                } else {
                    $error[] = $value2;
                }
            }		
        } else {
            if (isset($_POST[$value])) {
                if ($empty) {
                    if (!empty($_POST[$value])) {
                        $$value = $_POST[$value];
                    } else {
                        $error[] = $value;
                    }
                } else {
                    $$value = $_POST[$value];
                }

                if (isset($$value) && !isset($_POST["password"])) {
                    $_SESSION[$value] = $$value;
                }
            } else {
                $error[] = $value;
            }
        }
    }

    return $error;
}


function valEditProfile() {
    $input = ["name"];

    if (count($error = validate($input)) > 0) {
        if (in_array("name", $error)) {

        }
    }

}


function hashAlgoritm($str1, $str2) {
    $len1 = strlen($str1);
    $len2 = strlen($str2);
    $len = max($len1, $len2);
    $result = '';
    
    for ($i = 0; $i < $len; $i++) {
        $char1 = $i < $len1 ? $str1[$i] : '';
        $char2 = $i < $len2 ? $str2[$i] : '';
        
        if ($char1 === $char2) {
            $result .= $char1; // Same characters are merged
        } else {
            $result .= "{$char1}{$char2}"; // Different characters with delimiter
        }
    }
    return $result;
}


function pass_hash($pass) {
    $raw_salt = "solnicka";
    $algo = "sha256";

    $hash_pass = hash($algo, $pass);
    $salt = hash($algo, $raw_salt);

    

    $hash = hashAlgoritm($hash_pass, $salt);
    return $hash;
}


function exists($table, $column) {
    # Returns True if it is ok (no same row)
    global $conn;

    $exact = "SELECT id FROM {$table} WHERE {$column} = '{$_SESSION[$column]}'";
    $exact = $conn->query($exact);
    $exists = $exact->fetch_all(MYSQLI_ASSOC);

    if (empty($exists)) {
        return True;
    } else {
        return False;
    }
}


function updatePass($password, $password_confirm, $userId) {
    global $conn;

    if ($password == $password_confirm) {

        $_SESSION["password"] = "";
        $_SESSION["password_confirm"] = "";
        $hash = pass_hash($_POST["password"]);

        $update = "UPDATE users SET hash='{$hash}' WHERE id={$userId}";
        $updateStatus = $conn->query($update);

        if ($updateStatus === false) {
            $_SESSION["error"] = "Couldn't update password!";
        }
    } else {
        $_SESSION["error"] = "You entered your password wrong!";
    }
}

# IF BASH sends admin password..

if (isset($argv) && count($argv) > 1) {
    // Skip the first argument (script name) and process the rest
    $password_add_indicator = "adminpass_#Ad5f78:";
    $arguments = array_slice($argv, 1);
    foreach ($arguments as $arg) {
        if (strpos($arg, $password_add_indicator) >= 0 && is_int(strpos($arg, $password_add_indicator))) {
            $arg_indicator = explode(":", $arg)[0].':';
            if ($arg_indicator == $password_add_indicator) {
                $password = str_replace($password_add_indicator, "", $arg);
                $hash = pass_hash($password);

                $insert = "INSERT INTO users (username, hash) VALUES ('admin', '{$hash}')";
                $insertStatus = $conn->query($insert);
                
                if (!$insertStatus) {
                    echo "ERROR: Password could not be set!";
                } else {
                    exit;
                }
            }
        } else {
            echo "ERROR: Password could not be set!";
        }
    }


    $indicator = "admin:";
    $string = "admin:heslo";
    $string_indicator = explode(":", $string)[0].':';
    if ($string_indicator == $indicator) {
        if (strpos($string, $indicator) >= 0 && is_int(strpos($string, $indicator))) {
            $pass = str_replace($indicator, "", $string);
            echo $pass;
        } else {
            echo strpos($string, $indicator);
        }
    }
}


if (isset($_GET['login'])) {
    # $input = ["password"];
    $input = ["password", "username"];

    if (count(validate($input)) == 0) {
        $username = $_POST["username"];
        $hash = pass_hash($_POST["password"]);

        $findUser = "SELECT * FROM users WHERE username = '{$username}' AND hash = '{$hash}'";
        $findUser = $conn->query($findUser);
        $findUser = $findUser->fetch_all(MYSQLI_ASSOC)[0];

        var_dump($findUser);

        if (!empty($findUser) || $findUser !== false && isset($findUser['id'])) {
            $_SESSION["user"] = $username;
            $_SESSION["userId"] = $findUser['id'];

            $_SESSION["error"] = "";
            header("location: ../");
            exit();
            
        } else {
            $_SESSION["error"] = "Wrong username or password";
            header("location: ../login/login.php");
            exit();
        }
        
    } else {
        $_SESSION["error"] = "Wrong format";
        header("location: ../login/login.php");
        exit();
    }

} elseif (isset($_GET['user'])) {
    $userId = $_GET['user'];

    if ($userId || !empty($userId)) {
        $input_name = ["username"];
        $input_passwords = ["password", "password_confirm"];
        $input = array_merge($input_name, $input_passwords);


        if (isset($_GET["self-update"])) {
            if (count(validate($input_passwords, true)) == 0) {
                if (count(validate(["old_password"], true)) == 0) {
                    $entered_hash = pass_hash($_POST["old_password"]);
                    
                    $check = "SELECT * FROM users WHERE hash = '{$entered_hash}' AND id = $userId";
                    $check = $conn->query($check);
                    $check = $check->fetch_all(MYSQLI_ASSOC)[0];

                    if (!empty($check)) {
                        updatePass($_POST['password'], $_POST['password_confirm'], $userId);
                        header("location: ../account?user={$userId}");
                        exit();
                    } else {
                        $_SESSION["error"] = "Wrong password.";
                    }
                } else {
                    $_SESSION["error"] = "You have to enter your current password!";
                    header("location: ../account?user={$userId}");
                    exit();
                }
            
            } elseif (!isset($_POST["old_password"])) {
                updatePass($_POST['password'], $_POST['password_confirm'], $userId);
            }

            header("location: ../account?user={$userId}");
            exit();

        } elseif (isset($_GET['delete'])) {
            $query = "DELETE FROM users WHERE id = $userId AND not username = 'admin'";
            $deleteStatus = $conn->query($query);

            if ($deleteStatus === false) {
                $_SESSION["error"] = "Deleting was not successful!";
            }
            
            if (isset($_GET['self-update'])) {
                header("location: ../login/login.php");
                exit();
            } else {
                header("location: ../admin");
                exit();
            }
            

        } elseif (count(validate($input, true)) == 0) {
            if ($_POST["password"] == $_POST["password_confirm"]) {
                $_SESSION["password"] = "";
                $_SESSION["password_confirm"] = "";
                $hash = pass_hash($_POST["password"]);

                $update = "UPDATE users SET username='{$_POST['username']}', hash='{$hash}' WHERE id={$userId} AND not username = 'admin'";
                $updateStatus = $conn->query($update);

                if ($updateStatus === false) {
                    $_SESSION["error"] = "Couldn't update user!";
                }

            } else {
                $_SESSION["error"] = "Passwords doesn't match";
            }

            if (isset($_GET['self-update'])) {
                header("location: ../account/?edit={$userId}");
                exit();
            } else {
                header("location: ../admin");
                exit();
            }

        } elseif (count(validate($input_name, true)) == 0) {
            $update = "UPDATE users SET username='{$_POST['username']}' WHERE id={$userId} AND not username = 'admin'";
            $updateStatus = $conn->query($update);

            if ($updateStatus === false) {
                $_SESSION["error"] = "Couldn't update username!";
            } else {
                $_SESSION["error"] = "";
            }
            
            if (isset($_GET['self-update'])) {
                header("location: ../account/?edit={$userId}");
                exit();
            } else {
                header("location: ../admin/?user={$userId}");
                exit();
            }

        } elseif (count(validate($input_passwords, true)) == 0) {
            if (isset($_GET["self-update"])) {
                if (count(validate(["old_password"], true)) == 0) {
                    $entered_hash = pass_hash($_POST["old_password"]);
                    
                    $check = "SELECT * FROM users WHERE hash = '{$entered_hash}' AND id = $userId";
                    $check = $conn->query($check);
                    $check = $check->fetch_all(MYSQLI_ASSOC)[0];

                    if (!empty($check)) {
                        updatePass($_POST['password'], $_POST['password_confirm'], $userId);
                        header("location: ../account?user={$userId}");
                        exit();
                    } else {
                        $_SESSION["error"] = "Wrong password.";
                    }
                } else {
                    $_SESSION["error"] = "You have to enter your current password!";
                    header("location: ../account?user={$userId}");
                    exit();
                }
            
            } elseif (!isset($_POST["old_password"])) {
                updatePass($_POST['password'], $_POST['password_confirm'], $userId);
            }

            header("location: ../account?user={$userId}");
            exit();
        } else {
            $_SESSION["error"] = "Nothing to edit or delete!";
            if (isset($_GET['self-update'])) {
                header("location: ../account/?edit={$userId}");
                exit();
            } else {
                header("location: ../admin/?user={$userId}");
                exit();
            }
        }
    } else {
        $input = ["username", "password", "password_confirm"];

        if (count(validate($input, true)) == 0) {
            if ($_POST["password"] == $_POST["password_confirm"]) {
                $_SESSION["password"] = "";
                $_SESSION["password_confirm"] = "";
                $hash = pass_hash($_POST["password"]);

                $check = "SELECT * FROM users WHERE username='{$_POST['username']}'";
                $check = $conn->query($check);
                $check = $check->fetch_all(MYSQLI_ASSOC);

                if (empty($check)) {
                    $insert = "INSERT INTO users (username, hash) VALUES ('{$_POST['username']}', '{$hash}')";
                    $insertStatus = $conn->query($insert);
                    
                    if ($insertStatus === false) {
                        $_SESSION["error"] = "Couldn't create user!";
                    } else {
                        $_SESSION["error"] = "";
                    }
                } else {
                    $_SESSION["error"] = "User with this name already exists!";
                }

            } else {
                $_SESSION["error"] = "Passwords doesn't match!";
            }
        } else {
            $_SESSION["error"] = "Enter username and password!";
        }
        
        if ($_SESSION["error"] === "") {
            header("location: ../admin");
            exit();
        } else {
            header("location: ../admin/adduser/");
            exit();
        }
        
    }

} elseif (isset($_GET["profile"])) {
    $profileId = $_GET["profile"];
    $input = ["name"];

    if (count(validate(["delete_id"], true)) == 0) {
        $query = "SELECT deviceId FROM profileReleations WHERE profileId = $profileId";
        $selectStatus = $conn->query($query);

        $deviceIds = [];
        while ($row = $selectStatus->fetch_assoc()) {
            $deviceIds[] = $row['deviceId'];
        }

        // Convert IDs array to string
        $deviceIdsString = implode(',', $deviceIds);

        // Delete profile
        $query = "DELETE FROM profiles WHERE id = $profileId";
        $deleteStatus = $conn->query($query);

        if ($deleteStatus === false) {
            $_SESSION['error'] = $conn->error;
            header("location: ../edit/profile/?profile=".$profileId);
            exit();
        }

        // Delete devices with associated IDs (if any)
        if (!empty($deviceIds)) {
            $query = "DELETE FROM devices WHERE id IN ($deviceIdsString)";
            $deleteStatus = $conn->query($query);
        }

        header("location: ../");
        exit();

    } elseif (count(validate($input, true)) == 0) {

        if (exists("profiles", "name")) {
            if (!empty($profileId)) {

                $update = "UPDATE profiles SET name='{$_SESSION['name']}' WHERE id={$profileId}";
                $updateStatus = $conn->query($update);

                if ($updateStatus === false) {
                    $_SESSION['error'] = $updateStatus;

                    header("location: ../edit/profile/?profile=".$profileId);
                    exit();
                } else {
                    header("location: ../");
                    exit();
                }
            } else {
                $insert = "INSERT INTO profiles (name) VALUES ('{$_SESSION['name']}')";
                $insertStatus = $conn->query($insert);

                echo $insertStatus;

                if ($insertStatus === false) {
                    $_SESSION['error'] = $insertStatus;

                    header("location: ../edit/profile/");
                    exit();
                } else {
                    header("location: ../");
                    exit();
                }
            }

        } else {
            $_SESSION['error'] = "There is already a profile with the same name!";
        }
        
            
    } else {
        $_SESSION['error'] = "You have to enter a name for the profile.";
    
        header("location: ../edit/profile/?profile=".$profileId);
        exit();
    }

} elseif (isset($_GET["device"])) {
    $deviceId = $_GET["device"];
    $input = ["name", "ip", "type", "profiles"];

    if (count(validate(["delete_id"], true)) == 0) {

        $device_id = mysqli_real_escape_string($conn, $_POST['delete_id']);
        $query = "DELETE FROM devices WHERE id = '$device_id'";
        $deleteStatus = $conn->query($query);

        if ($deleteStatus === false) {
            $_SESSION['error'] = $deleteStatus;
        } else {
            header("location: ../");
            exit();
        }
        

    } elseif (count(validate($input, true)) == 0) {
        if (!empty($deviceId)) {

            $update = "UPDATE devices SET name='{$_SESSION['name']}', ip = '{$_SESSION['ip']}', type = '{$_SESSION['type']}' WHERE id={$deviceId}";
            $updateStatus = $conn->query($update);

            if ($updateStatus === false) {
                $_SESSION['error'] = $updateStatus;
                header("location: ../edit/device/?device=".$deviceId);
                exit();
            } else {
                $profileIds = $_POST["profiles"];

                $profiles = "SELECT profileId FROM profileReleations WHERE deviceId = ".$deviceId;
                $profiles = $conn->query($profiles);
                $currentProfiles = $profiles->fetch_all(MYSQLI_ASSOC);
                $currentProfiles = array_column($currentProfiles, 'profileId');

                $to_delete = array_diff($currentProfiles, $profileIds); // IDs to remove
                $to_insert = array_diff($profileIds, $currentProfiles); // IDs to insert

                
                if (!empty($to_delete)) {
                    $ids_to_delete = implode(',', array_map('intval', $to_delete)); // Ensure values are integers
                    foreach ($to_delete as $id) {
                        $deleteRel = $conn->query("DELETE FROM profileReleations WHERE deviceId = $deviceId AND profileId = $id");
                    }
                }
                if (!empty($to_insert)) {
                    foreach ($to_insert as $id) {
                        $insertRel = $conn->query("INSERT INTO profileReleations (profileId, deviceId) VALUES ('" . $id . "', '".$deviceId."')");
                    }
                }

                
                if (isset($_SESSION["profile"])) {
                    header("location: ../device/?profile=".$_SESSION["profile"]."&device=".$deviceId);
                    exit();
                } else {
                    header("location: ../");
                    exit();
                }
                
            }
        } else {

            $insert = "INSERT INTO devices (name, type, ip) VALUES ('{$_SESSION['name']}', '{$_SESSION['type']}', '{$_SESSION['ip']}')";
            $insertStatus = $conn->query($insert);

            if ($insertStatus === false) {
                $_SESSION['error'] = $insertStatus;
                header("location: ../edit/device/");
                exit();
            } else {
                $deviceID = $conn->insert_id;
                $profileIds = $_POST["profiles"];
                
                foreach ($profileIds as $id) {
                    $id = (int)$id;
                    $insert = "INSERT INTO profileReleations (profileId, deviceId) VALUES ('{$id}', '{$deviceID}')";
                    $insertStatus = $conn->query($insert);

                    if ($insertStatus === false) {
                        $_SESSION['error'] = $insertStatus;
                        header("location: ../edit/device/");
                        exit();
                    } else {
                        header("location: ../");
                        exit();
                    }
                }
            }

            header("location: ../");
            exit();
        }
        
            
    } else {
        $_SESSION['error'] = "You have to enter all device data.";

        if (!empty($deviceId)) {
            header("location: ../edit/device/?device=$deviceId");
        } else {
            header("location: ../edit/device/");
        }

        exit();
    }
}

?>