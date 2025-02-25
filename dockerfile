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
COPY snmpd.conf /etc/snmp/snmpd.conf

# Enable mysqli extension in PHP
RUN sed -i 's/;extension=mysqli/extension=mysqli/' /etc/php/*/apache2/php.ini

# Clone MONOS repository
WORKDIR /var/www/html/
RUN git clone https://github.com/DebStream-Solutions/MONOS.git MONOS

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
