<?php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);


$servername = "localhost";
$username = "muser";
$password = "y%8YB@*T$@7dTPhCfhge9xNJ9fxTvEmYs8sSzrJ6";
$dbname = "monos";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
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

# IF BASH sends admin password..

var_dump($argv);

if (isset($argv[0])) {
    $argument = $argv[0];

    $pass_needle = "adminpass_#Ad5f78:";
    if (strpos($argument, $pass_needle)) {
        $password = str_replace($pass_needle, "", $argument);
        
        $hash = pass_hash($password);
        $insert = "INSERT INTO users (hash) VALUES ('{$hash}')";
        $insertStatus = $conn->query($insert);
        if (!$insertStatus) {
           echo "ERROR: Password could not be set!";
        } else {
            echo $hash;
        }
    }
}

?>