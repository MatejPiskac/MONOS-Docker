#!/bin/bash

# Database credentials
DB_USER="root"
DB_PASS=""
DB_NAME="monos"

GENERATED_PASS=$(head /dev/urandom | tr -dc 'A-Za-z0-9@#$&' | head -c 12)

# Generate or edit config_file
config_file="db_config.php"

config_host='localhost'
config_user='mroot'
config_pass=$GENERATED_PASS
config_name=$DB_NAME

content=$(cat <<EOF
<?php
return [
    'db_host' => '$config_host',
    'db_user' => '$config_user',
    'db_pass' => '$config_pass',
    'db_name' => '$config_name'
];
EOF
)

echo "$content" > "MONOS/$config_file"
chmod 777 "MONOS/$config_file"


# Creates database "monos" and primary user

mysql -u $DB_USER <<EOF
CREATE DATABASE IF NOT EXISTS $DB_NAME;
USE $DB_NAME;

-- Create users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hash VARCHAR(255) NOT NULL
);

-- Create types table
CREATE TABLE IF NOT EXISTS types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL
);


-- Create profiles table
CREATE TABLE IF NOT EXISTS profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL
);

-- Create devices table
CREATE TABLE IF NOT EXISTS devices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    ip VARCHAR(45) NOT NULL,
    FOREIGN KEY (type) REFERENCES types(id)
);

-- Create profileReleations table
CREATE TABLE IF NOT EXISTS profileReleations (
    profileId INT NOT NULL,
    deviceId INT NOT NULL,
    PRIMARY KEY (profileId, deviceId),
    FOREIGN KEY (profileId) REFERENCES profiles(id) ON DELETE CASCADE,
    FOREIGN KEY (deviceId) REFERENCES devices(id) ON DELETE CASCADE
);


CREATE USER IF NOT EXISTS '$config_user'@'%' IDENTIFIED BY '$config_pass';
GRANT ALL PRIVILEGES ON $config_name.* TO '$config_user'@'%';
FLUSH PRIVILEGES;

-- Insert data into types table
INSERT INTO types (id, name) VALUES
(1, 'router'),
(2, 'switch'),
(3, 'workstation'),
(4, 'server'),
(5, 'printer'),
(6, 'firewall'),
(7, 'load-balancer'),
(8, 'hub'),
(9, 'camera'),
(10, 'ip-telephone'),
(11, 'cable-modem');
EOF

echo "Database and tables created successfully."

php /var/www/html/MONOS/workspace/action/validate.php "adminpass_#Ad5f78:$config_pass"

echo ""
echo "Your temporary admin password is: $config_pass"
