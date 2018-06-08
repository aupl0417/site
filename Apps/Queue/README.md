#Swoole
#Author: Lazycat <673090083@qq.com>
#linux shell下运行下指令 
#/opt/lampp/bin/php 为PHP路径，
#/opt/Swoole/main.php为服务程序路径

/opt/lampp/bin/php /opt/Queue/main.php -s restart --worker true -d

#内网执行
/usr/local/php/bin/php /data/wwwroot/www.dtmall.com/Queue/main.php -s restart --worker true -d

#批量杀进程，重启前一定要先使用以下指令先杀掉旧的残留进程
ps -ef|grep lazycat|grep -v grep|cut -c 9-15|xargs kill -9
