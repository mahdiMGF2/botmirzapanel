#!/bin/bash

if [ "$(id -u)" -ne 0 ]; then
    echo -e "\033[33mPlease run as root\033[0m"
    exit
fi

wait

# Check if the OS is Ubuntu 22.04
OS=$(lsb_release -si)
VERSION=$(lsb_release -sr)

if [ "$OS" != "Ubuntu" ] || [ "$VERSION" != "22.04" ]; then
    echo -e "\033[31mThis script can only be run on Ubuntu 22.04\033[0m"
    exit 1
fi

echo -e "\e[32mInstalling mirza script ... \033[0m\n"


# Function to add the Ondřej Surý PPA for PHP
add_php_ppa() {
    sudo add-apt-repository -y ppa:ondrej/php
}

# Function to add the Ondřej Surý PPA for PHP with locale override
add_php_ppa_with_locale() {
    sudo LC_ALL=C.UTF-8 add-apt-repository -y ppa:ondrej/php
}

# Try adding the PPA with the system's default locale settings
if ! add_php_ppa; then
    echo "Failed to add PPA with default locale, retrying with locale override..."
    # If it fails, add the PPA with a locale override
    if ! add_php_ppa_with_locale; then
        echo "Failed to add PPA even with locale override. Exiting..."
        exit 1
    fi
fi

sudo apt update && apt upgrade -y
echo -e "\e[92mThe server was successfully updated ...\033[0m\n"
DEBIAN_FRONTEND=noninteractive sudo apt install -y php8.2 php8.2-fpm php8.2-mysql


PKG=(
    lamp-server^
    libapache2-mod-php 
    mysql-server 
    apache2 
    php-mbstring 
    php-zip 
    php-gd 
    php-json 
    php-curl 
)


for i in "${PKG[@]}"; do
    dpkg -s $i &> /dev/null
    if [ $? -eq 0 ]; then
        echo "$i is already installed"
    else
        if ! DEBIAN_FRONTEND=noninteractive sudo apt install -y $i; then
            echo "Error installing $i. Exiting..."
            exit 1
        fi
    fi
done

echo -e "\n\e[92mPackages Installed Continuing ...\033[0m\n"

echo 'phpmyadmin phpmyadmin/dbconfig-install boolean true' | debconf-set-selections
echo 'phpmyadmin phpmyadmin/app-password-confirm password mirzahipass' | debconf-set-selections
echo 'phpmyadmin phpmyadmin/mysql/admin-pass password mirzahipass' | debconf-set-selections
echo 'phpmyadmin phpmyadmin/mysql/app-pass password mirzahipass' | debconf-set-selections
echo 'phpmyadmin phpmyadmin/reconfigure-webserver multiselect apache2' | debconf-set-selections
sudo apt-get install phpmyadmin -y
sudo ln -s /etc/phpmyadmin/apache.conf /etc/apache2/conf-available/phpmyadmin.conf
sudo a2enconf phpmyadmin.conf
sudo systemctl restart apache2
wait
sudo apt-get install -y php-soap
sudo apt-get install libapache2-mod-php
sudo systemctl enable mysql.service
sudo systemctl start mysql.service
sudo systemctl enable apache2
sudo systemctl start apache2
wait
sudo apt-get install ufw -y
ufw allow 'Apache'
sudo systemctl restart apache2
sudo apt-get install -y git
sudo apt-get install -y wget
sudo apt-get install -y unzip
sudo apt install curl -y
sudo apt-get install -y php-ssh2
sudo apt-get install -y libssh2-1-dev libssh2-1
sudo systemctl restart apache2.service
wait
git clone https://github.com/mahdiMGF2/botmirzapanel.git /var/www/html/mirzabotconfig
sudo chown -R www-data:www-data /var/www/html/mirzabotconfig/
sudo chmod -R 755 /var/www/html/mirzabotconfig/
echo -e "\n\033[33mmirza config and script have been installed successfully\033[0m"
wait
if [ ! -d "/root/confmirza" ]; then

    sudo mkdir /root/confmirza

    sleep 1

    touch /root/confmirza/dbrootmirza.txt
    sudo chmod -R 777 /root/confmirza/dbrootmirza.txt
    sleep 1

    randomdbpasstxt=$(openssl rand -base64 10 | tr -dc 'a-zA-Z0-9' | cut -c1-8)

    ASAS="$"

    echo "${ASAS}user = 'root';" >> /root/confmirza/dbrootmirza.txt
    echo "${ASAS}pass = '${randomdbpasstxt}';" >> /root/confmirza/dbrootmirza.txt
    echo "${ASAS}path = '${RANDOM_NUMBER}';" >> /root/confmirza/dbrootmirza.txt

    sleep 1

    passs=$(cat /root/confmirza/dbrootmirza.txt | grep '$pass' | cut -d"'" -f2)
    userrr=$(cat /root/confmirza/dbrootmirza.txt | grep '$user' | cut -d"'" -f2)

    sudo mysql -u $userrr -p$passs -e "alter user '$userrr'@'localhost' identified with mysql_native_password by '$passs';FLUSH PRIVILEGES;"

    echo "SELECT 1" | mysql -u$userrr -p$passs 2>/dev/null

    echo "Folder created successfully!"
else
    echo "Folder already exists."
fi

clear

echo " "
echo -e "\e[32m SSL \033[0m\n"

read -p "Enter the domain: " domainname
if [ "$domainname" = "" ]; then

wait

else
# variables
DOMAIN_NAME="$domainname"
PATHS=$(cat /root/confmirza/dbrootmirza.txt | grep '$path' | cut -d"'" -f2)
sudo ufw allow 80
sudo ufw allow 443


echo -e "\033[33mDisable apache2\033[0m"
wait

sudo systemctl stop apache2
sudo systemctl disable apache2
sudo apt install letsencrypt -y
sudo systemctl enable certbot.timer
sudo certbot certonly --standalone --agree-tos --preferred-challenges http -d $DOMAIN_NAME
sudo apt install python3-certbot-apache -y
sudo certbot --apache --agree-tos --preferred-challenges http -d $DOMAIN_NAME
wait

echo " "
echo -e "\033[33mEnable apache2\033[0m"
wait
sudo systemctl enable apache2
sudo systemctl start apache2
ROOT_PASSWORD=$(cat /root/confmirza/dbrootmirza.txt | grep '$pass' | cut -d"'" -f2)
ROOT_USER="root"
echo "SELECT 1" | mysql -u$ROOT_USER -p$ROOT_PASSWORD 2>/dev/null

if [ $? -eq 0 ]; then

wait

    randomdbpass=$(openssl rand -base64 10 | tr -dc 'a-zA-Z0-9' | cut -c1-8)

    randomdbdb=$(openssl rand -base64 10 | tr -dc 'a-zA-Z' | cut -c1-8)

    if [[ $(mysql -u root -p$ROOT_PASSWORD -e "SHOW DATABASES LIKE 'mirzabot'") ]]; then
        clear
        echo -e "\n\e[91mYou have already created the database\033[0m\n"
    else
        dbname=mirzabot
        clear
        echo -e "\n\e[32mPlease enter the database username!\033[0m"
        printf "[+] Default user name is \e[91m${randomdbdb}\e[0m ( let it blank to use this user name ): "
        read dbuser
        if [ "$dbuser" = "" ]; then
        dbuser=$randomdbdb
        fi

        echo -e "\n\e[32mPlease enter the database password!\033[0m"
        printf "[+] Default password is \e[91m${randomdbpass}\e[0m ( let it blank to use this password ): "
        read dbpass
        if [ "$dbpass" = "" ]; then
        dbpass=$randomdbpass
        fi

        mysql -u root -p$ROOT_PASSWORD -e "CREATE DATABASE $dbname;" -e "CREATE USER '$dbuser'@'%' IDENTIFIED WITH mysql_native_password BY '$dbpass';GRANT ALL PRIVILEGES ON * . * TO '$dbuser'@'%';FLUSH PRIVILEGES;" -e "CREATE USER '$dbuser'@'localhost' IDENTIFIED WITH mysql_native_password BY '$dbpass';GRANT ALL PRIVILEGES ON * . * TO '$dbuser'@'localhost';FLUSH PRIVILEGES;"

        echo -e "\n\e[95mDatabase Created.\033[0m"

        clear

        printf "\n\e[33m[+] \e[36mBot Token: \033[0m"
        read YOUR_BOT_TOKEN
        printf "\e[33m[+] \e[36mChat id: \033[0m"
        read YOUR_CHAT_ID
        printf "\e[33m[+] \e[36mDomain: \033[0m"
        read YOUR_DOMAIN
        printf "\e[33m[+] \e[36musernamebot: \033[0m"
        read YOUR_BOTNAME
        echo " "
        if [ "$YOUR_BOT_TOKEN" = "" ] || [ "$YOUR_DOMAIN" = "" ] || [ "$YOUR_CHAT_ID" = "" ] || [ "$YOUR_BOTNAME" = "" ]; then
           exit
        fi

        ASAS="$"

        wait

        sleep 1

        file_path="/var/www/html/mirzabotconfig/config.php"

        if [ -f "$file_path" ]; then
          rm "$file_path"
          echo -e "File deleted successfully."
        else
          echo -e "File not found."
        fi

        sleep 1

        echo -e "<?php" >> /var/www/html/mirzabotconfig/config.php
        echo -e "${ASAS}APIKEY = '${YOUR_BOT_TOKEN}';" >> /var/www/html/mirzabotconfig/config.php
        echo -e "${ASAS}usernamedb = '${dbuser}';" >> /var/www/html/mirzabotconfig/config.php
        echo -e "${ASAS}passworddb = '${dbpass}';" >> /var/www/html/mirzabotconfig/config.php
        echo -e "${ASAS}dbname = '${dbname}';" >> /var/www/html/mirzabotconfig/config.php
        echo -e "${ASAS}domainhosts = '${YOUR_DOMAIN}/mirzabotconfig';" >> /var/www/html/mirzabotconfig/config.php
        echo -e "${ASAS}adminnumber = '${YOUR_CHAT_ID}';" >> /var/www/html/mirzabotconfig/config.php
        echo -e "${ASAS}usernamebot = '${YOUR_BOTNAME}';" >> /var/www/html/mirzabotconfig/config.php
        echo -e "${ASAS}connect = mysqli_connect('localhost', \$usernamedb, \$passworddb, \$dbname);" >> /var/www/html/mirzabotconfig/config.php
        echo -e "if (${ASAS}connect->connect_error) {" >> /var/www/html/mirzabotconfig/config.php
        echo -e "die(' The connection to the database failed:' . ${ASAS}connect->connect_error);" >> /var/www/html/mirzabotconfig/config.php
        echo -e "}" >> /var/www/html/mirzabotconfig/config.php
        echo -e "mysqli_set_charset(${ASAS}connect, 'utf8mb4');" >> /var/www/html/mirzabotconfig/config.php
        text_to_save=$(cat <<EOF
\$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];
\$dsn = "mysql:host=localhost;dbname=${ASAS}dbname;charset=utf8mb4";
try {
     \$pdo = new PDO(\$dsn, \$usernamedb, \$passworddb, \$options);
} catch (\PDOException \$e) {
     throw new \PDOException(\$e->getMessage(), (int)\$e->getCode());
}
EOF
)
echo -e "$text_to_save" >> /var/www/html/mirzabotconfig/config.php
        echo -e "?>" >> /var/www/html/mirzabotconfig/config.php

        sleep 1

        curl -F "url=https://${YOUR_DOMAIN}/mirzabotconfig/index.php" "https://api.telegram.org/bot${YOUR_BOT_TOKEN}/setWebhook"
        MESSAGE="✅ The bot is installed! for start bot send comment /start"
        curl -s -X POST "https://api.telegram.org/bot${YOUR_BOT_TOKEN}/sendMessage" -d chat_id="${YOUR_CHAT_ID}" -d text="$MESSAGE"

        sleep 1
        sudo systemctl start apache2
        url="https://${YOUR_DOMAIN}/mirzabotconfig/table.php"
        curl $url

        clear

        echo " "

        echo -e "\e[102mDomain Bot: https://${YOUR_DOMAIN}\033[0m"
        echo -e "\e[104mDatabase address: https://${YOUR_DOMAIN}/phpmyadmin\033[0m"
        echo -e "\e[33mDatabase name: \e[36m${dbname}\033[0m"
        echo -e "\e[33mDatabase username: \e[36m${dbuser}\033[0m"
        echo -e "\e[33mDatabase password: \e[36m${dbpass}\033[0m"
        echo " "
        echo -e "Mirza Bot"
        fi


        elif [ "$ROOT_PASSWORD" = "" ] || [ "$ROOT_USER" = "" ]; then
        echo -e "\n\e[36mThe password is empty.\033[0m\n"
        else 

        echo -e "\n\e[36mThe password is not correct.\033[0m\n"

        fi

fi
