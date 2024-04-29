#!/bin/bash

if [ "$(id -u)" -ne 0 ]; then
    echo -e "\033[33mPlease run as root\033[0m"
    exit
fi

wait

echo -e "\e[32mUpdating mirza ... \033[0m\n"

sudo apt update && apt upgrade -y
echo -e "\e[92mThe server was successfully updated ...\033[0m\n"



echo " "
sudo apt-get install -y git
sudo apt install curl -y
echo -e "\n\e[92mUpdating Bot ...\033[0m\n"
sleep 2
mv /var/www/html/mirzabotconfig/config.php /root/
rm -r /var/www/html/mirzabotconfig
git clone https://github.com/mahdiMGF2/botmirzapanel.git /var/www/html/mirzabotconfig
sudo chown -R www-data:www-data /var/www/html/mirzabotconfig/
sudo chmod -R 755 /var/www/html/mirzabotconfig/
mv /root/config.php /var/www/html/mirzabotconfig/
urlbot=$(cat /var/www/html/mirzabotconfig/config.php | grep '$domainhosts' | cut -d'"' -d"'" -f2)
curl  "https://$urlbot/table.php"
clear
echo -e "\n\e[92mMirza robot has been successfully updated!"