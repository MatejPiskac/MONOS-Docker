# MONOS
*See for free*

**M O N**itoring **O**pen-source **S**ystem


*MONOS* is a free software which allows you to monitor devices in your network. Monitored devices are shown on a web application.

# MANUAL FOR SNMP MONITORING


<a href="#install">Install Monos</a>  |  <a href="#monos-srv">Monos Server Setup</a>  |  <a href="#db">Install DB</a>
<details>
  <summary>Monos Client</summary>
  <a href="#station">Workstation/Server</a>  |  <a href="#router">Router</a>
</details>

## <a name="install">Install Monos on your server</a>

### Install dockerfile
```sh
TODO
```

### Build the docker container
```sh
docker build -t <image-name> .
```

### Run the docker
Find docker name:
```sh
docker images
```
Run the docker
```sh
docker run -it -p 80:80 <container-id>
```


### To run commands in container
Find docker name:
```sh
docker ps
```

Enter the docker
```sh
docker exec -it <container-id> bash
```


## <a name="monos-srv">Setup Debian Server for MONOS</a>

### Install required dependencies
```sh
sudo apt install -y snmp snmpd libsnmp-dev snmp-mibs-downloader php-snmp php php-mysqli apache2 libapache2-mod-php mariadb-server 
```

### Install MIBs for SNMP
```sh
sudo download-mibs
```

### Edit configuration of SNMP (snmpd.conf)
```sh
nano /etc/snmp/snmpd.conf
```
Content:
```sh
rwcommunity [COMMUNITY] default

# Disk monitoring
disk  / 100

# Agent user
agentuser  [USER]

# Agent address
agentAddress udp:161

# System location and contact
syslocation Unknown
syscontact Root <root@localhost>

# Access control
access [COMMUNITY] "" any noauth exact systemview none none

# Logging
dontLogTCPWrappersConnects yes
```

### Enable `mysqli` extension
Locate `php.ini` file
```sh
find / | grep php.ini
```
Edit the file
```sh
nano /etc/php/<version>/apache2/php.ini
```
Enable the extension by adding or uncommenting:
```sh
extension=mysqli
```

### Install MONOS Aplication
Navigate to `/var/www/html/` directory:
```sh
cd /var/www/html/
```
Download the Monos App using `wget` or `git`
```sh
wget https://monos.debstream.org/app/download
```
```sh
git clone https://github.com/DebStream-Solutions/monos.git
# git clone https://username:<pat>@github.com/<your account or organization>/<repo>.git
```

### <a name="db">Configure Monos database</a>

Navigate to directory MONOS
```sh
cd /var/www/html/MONOS
```

Run `db-setup.sh` script to configure database
```sh
sudo /.db-setup.sh
```

You were given generated password. This password is used for the monos to access database. The password is also first password for admin. We advise you to change the password.

> Login to Monos with username `admin` and the generated password <br>
> Login to database with username `mroot` and the generated password


Restart services
```sh
sudo systemctl restart apache2
sudo systemctl restart mariadb
sudo systemctl restart snmpd
```

Now everything is set up on your server and you can prepare the clients


## Client setup - Server & Workstation

