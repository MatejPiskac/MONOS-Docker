FROM ubuntu:latest

# Set environment variables
ENV DEBIAN_FRONTEND=noninteractive

# Install required dependencies
RUN apt update && apt install -y \
    snmp snmpd libsnmp-dev snmp-mibs-downloader \
    php-snmp php php-mysqli apache2 libapache2-mod-php \
    iputils-ping \
    mariadb-server wget git && \
    apt clean && rm -rf /var/lib/apt/lists/*

# Download and install MIBs
RUN download-mibs

# Copy SNMP configuration
RUN cat <<EOF > /etc/snmp/snmpd.conf
rocommunity public localhost

# Disk monitoring
disk  / 100

# Agent user
agentuser  user

# Agent address
agentAddress udp:161

# System location and contact
syslocation Unknown

EOF

# Enable mysqli extension in PHP
RUN sed -i 's/;extension=mysqli/extension=mysqli/' /etc/php/*/apache2/php.ini

# Clone MONOS repository
WORKDIR /var/www/html/
RUN git clone https://github.com/MatejPiskac/MONOS-Docker.git MONOS

# Ensure the script is executable
RUN chmod +x /var/www/html/MONOS/db-setup.sh

# Expose ports
EXPOSE 80 161

# Start Apache2, MariaDB, and SNMP services, then run the db-setup.sh script
CMD service apache2 start && \
    service snmpd start && \
    service mariadb start && \
    # Wait for MariaDB to initialize before running db-setup.sh
    sleep 8 && \
    bash /var/www/html/MONOS/db-setup.sh && \
    tail -f /dev/null
