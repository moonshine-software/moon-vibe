CREATE DATABASE IF NOT EXISTS `my_database`;
CREATE DATABASE IF NOT EXISTS `my_database_test`;

CREATE USER IF NOT EXISTS 'my_database'@'%' IDENTIFIED BY 'MYSQL_ROOT_PASSWORD';

GRANT ALL PRIVILEGES ON `my_database`.* TO 'my_database'@'%';
GRANT ALL PRIVILEGES ON `my_database_test`.* TO 'my_database'@'%';

GRANT SELECT  ON `information\_schema`.* TO 'my_database'@'%';
FLUSH PRIVILEGES;